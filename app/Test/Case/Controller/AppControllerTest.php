<?php
App::uses('AppControllerTestCase', 'Test');
App::uses('AppController', 'Controller');
App::uses('EmployeesController', 'Controller');
App::uses('Language', 'CakeBasicFunctions.Utility');

/**
 * SomeController class
 *
 * @package       Cake.Test.Case.Controller
 */
class SomeController extends AppController {

/**
 * Action `index`.
 *  User role - user.
 *
 * @return void
 */
	public function index() {
	}

/**
 * Action `index`.
 *  User role - admin.
 *
 * @return void
 */
	public function admin_index() {
	}

}

/**
 * AppController Test Case
 */
class AppControllerTest extends AppControllerTestCase {

/**
 * Target Controller name
 *
 * @var string
 */
	public $targetController = 'Some';

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		'core.cake_session',
		'app.deferred',
		'plugin.cake_ldap.department',
		'plugin.cake_ldap.employee',
		'plugin.cake_ldap.employee_ldap',
		'plugin.cake_ldap.othermobile',
		'plugin.cake_ldap.othertelephone',
		'plugin.cake_settings_app.ldap',
	];

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		App::build([
			'View' => [APP . 'Test' . DS . 'test_app' . DS . 'View' . DS]
		]);
		parent::setUp();
	}

/**
 * testBeforeFilterUserJson method
 *  User role: user
 *
 * @return void
 */
	public function testBeforeFilterUserJson() {
		$this->applyUserInfo();
		$this->generateMockedController();
		$this->Controller->getEventManager()->detach($this->Controller, 'Controller.beforeRender');
		$opt = [
			'method' => 'GET',
			'return' => 'vars',
		];
		$result = $this->testAction('/some/index.json', $opt);
		$expected = [];
		$this->assertData($expected, $result);
	}

/**
 * testBeforeFilterUser method
 *  User role: user
 *
 * @return void
 */
	public function testBeforeFilterUser() {
		$language = new Language();
		$this->applyUserInfo();
		$this->generateMockedController();
		$this->Controller->getEventManager()->detach($this->Controller, 'Controller.beforeRender');
		$opt = [
			'method' => 'GET',
			'return' => 'vars',
		];
		$result = $this->testAction('/some/index', $opt);
		$expected = [
			'additionalCssFiles' => [],
			'additionalJsFiles' => [],
			'search_targetFields' => [],
			'search_targetFieldsSelected' => [
				CAKE_SEARCH_INFO_TARGET_FIELD_ANY_PART
			],
			'search_querySearchMinLength' => 2,
			'search_targetDeep' => 1,
			'uiLcid2' => $language->getCurrentUiLang(true),
			'uiLcid3' => $language->getCurrentUiLang(false),
			'search_urlActionSearch' => [
				'controller' => 'employees',
				'action' => 'search',
			],
			'isExternalAuth' => false,
			'emailContact' => 'test@localhost.local',
			'emailSubject' => 'Phonebook',
			'showSearchForm' => false,
			'countDeferredSaves' => 4,
			'useNavbarContainerFluid' => false,
			'projectName' => __d('project', PROJECT_NAME),
		];
		$this->assertData($expected, $result);
	}

/**
 * testBeforeFilterEmployeesSearchUser method
 *  User role: user
 *
 * @return void
 */
	public function testBeforeFilterEmployeesSearchUser() {
		$this->targetController = 'Employees';
		$this->applyUserInfo();
		$this->generateMockedController();
		$opt = [
			'method' => 'GET',
			'return' => 'vars',
		];
		$url = [
			'controller' => 'employees',
			'action' => 'search',
		];
		$result = $this->testAction($url, $opt);
		$this->assertTrue(isset($result['search_targetFields']));
		$expected = [
			__('Employees') => [
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surname'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Given name'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Middle name'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP telephone'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Landline telephone'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mobile telephone'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office room'),
				'Employee.Department.value' => __d('app_ldap_field_name', 'Department'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Position'),
			],
		];
		$this->assertData($expected, $result['search_targetFields']);
		$this->assertTrue(isset($result['search_targetFieldsSelected']));
		$expected = [
			'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
			'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME,
			'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME,
			'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME,
			'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL,
			'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE,
			'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER,
			'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER,
			'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER,
			'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME,
			'Employee.Department.value',
			'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
			CAKE_SEARCH_INFO_TARGET_FIELD_ANY_PART
		];
		$this->assertData($expected, $result['search_targetFieldsSelected']);
	}

/**
 * testBeforeFilterNotUser method
 *  User role: secretary, human resources, admin
 *
 * @return void
 */
	public function testBeforeFilterNotUser() {
		$language = new Language();
		$userRoles = [
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'vars',
		];
		$searchUrlActionSearch = [
			'controller' => 'employees',
			'action' => 'search',
		];
		$expected = [
			'additionalCssFiles' => [],
			'additionalJsFiles' => [],
			'search_targetFields' => [],
			'search_targetFieldsSelected' => [
				CAKE_SEARCH_INFO_TARGET_FIELD_ANY_PART
			],
			'search_querySearchMinLength' => 2,
			'search_targetDeep' => 1,
			'uiLcid2' => $language->getCurrentUiLang(true),
			'uiLcid3' => $language->getCurrentUiLang(false),
			'search_urlActionSearch' => $searchUrlActionSearch,
			'isExternalAuth' => false,
			'emailContact' => 'test@localhost.local',
			'emailSubject' => 'Phonebook',
			'showSearchForm' => false,
			'countDeferredSaves' => 4,
			'useNavbarContainerFluid' => false,
			'projectName' => __d('project', PROJECT_NAME),
		];
		foreach ($userRoles as $userRole => $userPrefix) {
			$userInfo = [
				'role' => $userRole,
				'prefix' => $userPrefix,
			];
			$expected['search_urlActionSearch'] = $searchUrlActionSearch + [$userPrefix => true];
			$this->applyUserInfo($userInfo);
			$this->generateMockedController();
			$this->Controller->getEventManager()->detach($this->Controller, 'Controller.beforeRender');
			$result = $this->testAction('/some/index', $opt);
			$this->assertData($expected, $result, __d('test', 'User role: %d', $userRole));
		}
	}

/**
 * testBeforeFilterEmployeesSearchNotUser method
 * User role: secretary, human resources, admin
 *
 * @return void
 */
	public function testBeforeFilterEmployeesSearchNotUser() {
		$this->targetController = 'Employees';
		$userRoles = [
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'vars',
		];
		$expected = [
			__('Employees') => [
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surname'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Given name'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Middle name'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP telephone'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Landline telephone'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mobile telephone'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Personal mobile telephone'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office room'),
				'Employee.Department.value' => __d('app_ldap_field_name', 'Department'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('app_ldap_field_name', 'Subdivision'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Position'),
				'Employee.Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __d('app_ldap_field_name', 'Manager'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('app_ldap_field_name', 'Birthday'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('app_ldap_field_name', 'Computer'),
				'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('app_ldap_field_name', 'Employee ID'),
			],
		];
		foreach ($userRoles as $userRole => $userPrefix) {
			$userInfo = [
				'role' => $userRole,
				'prefix' => $userPrefix,
			];
			$this->applyUserInfo($userInfo);
			$this->generateMockedController();
			$url = [
				'controller' => 'employees',
				'action' => 'search',
			];
			if (!empty($userPrefix)) {
				$url['prefix'] = $userPrefix;
				$url[$userPrefix] = true;
			}
			$result = $this->testAction($url, $opt);
			$this->assertTrue(isset($result['search_targetFields']));
			$this->assertData($expected, $result['search_targetFields'], __d('test', 'User role: %d', $userRole));
		}
	}

/**
 * testBeforeRenderUser method
 *  User role: user
 *
 * @return void
 */
	public function testBeforeRenderUser() {
		$language = new Language();
		$opt = [
			'method' => 'GET',
			'return' => 'vars',
		];
		$this->applyUserInfo();
		$this->generateMockedController();
		$this->Controller->getEventManager()->detach($this->Controller, 'Controller.initialize');
		$result = $this->testAction('/some/index', $opt);
		$expected = [
			'pageTitlePrefix' => __d('project', PROJECT_PAGE_TITLE) . '::',
			'pageTitlePostfix' => '',
			'additionalCssFiles' => [],
			'additionalJsFiles' => [],
			'search_targetFields' => [],
			'search_targetFieldsSelected' => [
				CAKE_SEARCH_INFO_TARGET_FIELD_ANY_PART
			],
			'search_querySearchMinLength' => 2,
			'search_targetDeep' => 1,
			'uiLcid2' => $language->getCurrentUiLang(true),
			'uiLcid3' => $language->getCurrentUiLang(false),
		];
		$this->assertData($expected, $result);
	}

/**
 * testBeforeRenderSecret method
 *  User role: secretary
 *
 * @return void
 */
	public function testBeforeRenderSecret() {
		$language = new Language();
		$opt = [
			'method' => 'GET',
			'return' => 'vars',
		];
		$userInfo = [
			'role' => USER_ROLE_USER | USER_ROLE_SECRETARY,
			'prefix' => 'secret',
		];
		$this->applyUserInfo($userInfo);
		$this->generateMockedController();
		$this->Controller->getEventManager()->detach($this->Controller, 'Controller.initialize');
		$result = $this->testAction('/some/index', $opt);
		$expected = [
			'pageTitlePrefix' => __d('project', PROJECT_PAGE_TITLE) . '::',
			'pageTitlePostfix' => '::' . mb_ucfirst(__('secretary')),
			'additionalCssFiles' => [],
			'additionalJsFiles' => [],
			'search_targetFields' => [],
			'search_targetFieldsSelected' => [
				CAKE_SEARCH_INFO_TARGET_FIELD_ANY_PART
			],
			'search_querySearchMinLength' => 2,
			'search_targetDeep' => 1,
			'uiLcid2' => $language->getCurrentUiLang(true),
			'uiLcid3' => $language->getCurrentUiLang(false),
		];
		$this->assertData($expected, $result);
	}

/**
 * testIsAuthorizedDenyNotAdmin method
 *  User role: user, secretary, human resources
 *
 * @return void
 */
	public function testIsAuthorizedDenyNotAdmin() {
		$userRoles = [
			USER_ROLE_USER => '',
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
		];
		$urls = [
			'/cake_search_info/search/index',
			'/cake_settings_app/settings/index',
			//'/cake_ldap/employees/index',
			'/some/index',
		];
		$opt = [
			'method' => 'GET',
		];
		foreach ($userRoles as $userRole => $userPrefix) {
			$userInfo = [
				'role' => $userRole,
				'prefix' => $userPrefix,
			];
			$this->applyUserInfo($userInfo);
			$this->generateMockedController();
			foreach ($urls as $url) {
				$result = $this->testAction($url, $opt);
				$this->checkIsNotAuthorized();
				$this->checkRedirect(true);
			}
		}
	}

/**
 * testIsAuthorizedAllowAdmin method
 *  User role: admin
 *
 * @return void
 */
	public function testIsAuthorizedAllowAdmin() {
		$urls = [
			'/cake_search_info/search/index',
			'/cake_settings_app/settings/index',
			//'/cake_ldap/employees/index',
			'/admin/some/index',
		];
		$opt = [
			'method' => 'GET',
		];
		$userInfo = [
			'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
			'prefix' => 'admin',
		];
		$this->applyUserInfo($userInfo);
		$this->generateMockedController();
		foreach ($urls as $url) {
			$result = $this->testAction($url, $opt);
			$this->checkIsNotAuthorized(true);
			$this->checkRedirect(false);
		}
	}
}
