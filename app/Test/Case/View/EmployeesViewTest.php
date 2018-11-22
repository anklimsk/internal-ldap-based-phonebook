<?php
App::uses('AppControllerTestCase', 'Test');
App::uses('EmployeesController', 'Controller');

/**
 * EmployeesView Test Case
 */
class EmployeesViewTest extends AppControllerTestCase {

/**
 * Target Controller name
 *
 * @var string
 */
	public $targetController = 'Employees';

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		'core.cake_session',
		'app.deferred',
		'app.department_extension',
		'app.last_processed',
		'plugin.cake_ldap.department',
		'plugin.cake_ldap.employee',
		'plugin.cake_ldap.employee_ldap',
		'plugin.cake_ldap.othermobile',
		'plugin.cake_ldap.othertelephone',
		'plugin.cake_ldap.subordinate',
		'plugin.queue.queued_task',
	];

/**
 * testIndex method
 *  User role: user, secretary, human resources, admin
 *
 * @return void
 */
	public function testIndex() {
		$userRoles = [
			USER_ROLE_USER => '',
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
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
				'action' => 'index',
			];
			if (!empty($userPrefix)) {
				$url['prefix'] = $userPrefix;
				$url[$userPrefix] = true;
			}
			$view = $this->testAction($url, $opt);
			$numForm = $this->getNumberItemsByCssSelector($view, 'div#content div.container form[action$="/employees/search"]');
			$expected = 1;
			$this->assertData($expected, $numForm);

			$numDl = $this->getNumberItemsByCssSelector($view, 'div#content div.container ul.list-statistics li');
			$expected = 2;
			$this->assertData($expected, $numDl);
		}
	}

/**
 * testSearchEmptyQuery method
 *  User role: user, secretary, human resources, admin
 *
 * @return void
 */
	public function testSearchEmptyQuery() {
		$userRoles = [
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
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
			$view = $this->testAction($url, $opt);
			$numForm = $this->getNumberItemsByCssSelector($view, 'div#header div.container form[action$="/employees/search"]');
			$expected = 1;
			$this->assertData($expected, $numForm);

			$numTableRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container-fluid table > tbody > tr');
			$expected = 0;
			$this->assertData($expected, $numTableRows);
		}
	}

/**
 * testSearchValidQueryWoRresult method
 *  User role: user, secretary, human resources, admin
 *
 * @return void
 */
	public function testSearchValidQueryWoRresult() {
		$userRoles = [
			USER_ROLE_USER => '',
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
			'data' => [
				'query' => 'some',
				'target' => [
					'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
				]
			]
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
			$view = $this->testAction($url, $opt);
			$numForm = $this->getNumberItemsByCssSelector($view, 'div#header div.container form[action$="/employees/search"]');
			$expected = 1;
			$this->assertData($expected, $numForm);

			$numTableRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container-fluid table > tbody > tr');
			$expected = 0;
			$this->assertData($expected, $numTableRows);
		}
	}

/**
 * testSearchValidQueryMinChars method
 *  User role: user, secretary, human resources, admin
 *
 * @return void
 */
	public function testSearchValidQueryMinChars() {
		$querySearchMinLength = 3;
		$result = Configure::write('CakeSearchInfo.QuerySearchMinLength', $querySearchMinLength);
		$this->assertTrue($result);

		$userRoles = [
			USER_ROLE_USER => '',
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
			'data' => [
				'query' => mb_substr('егоров', 0, $querySearchMinLength - 1),
				'target' => [
					CAKE_SEARCH_INFO_TARGET_FIELD_ANY_PART,
					'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
				]
			]
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
			$view = $this->testAction($url, $opt);
			$numForm = $this->getNumberItemsByCssSelector($view, 'div#header div.container form[action$="/employees/search"]');
			$expected = 1;
			$this->assertData($expected, $numForm);

			$numTableRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container-fluid table > tbody > tr');
			$expected = 0;
			$this->assertData($expected, $numTableRows);
		}
	}

/**
 * testSearchEmptyNotAllowedFieldsQueryForUser method
 *  User role: user
 *
 * @return void
 */
	public function testSearchEmptyNotAllowedFieldsQueryForUser() {
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
			'data' => [
				'query' => '1631',
				'target' => [
					CAKE_SEARCH_INFO_TARGET_FIELD_ANY_PART,
					'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
					'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID,
				]
			]
		];
		$userInfo = [
			'role' => USER_ROLE_USER,
			'prefix' => '',
		];
		$this->applyUserInfo($userInfo);
		$this->generateMockedController();
		$url = [
			'controller' => 'employees',
			'action' => 'search',
		];
		$view = $this->testAction($url, $opt);
		$numForm = $this->getNumberItemsByCssSelector($view, 'div#header div.container form[action$="/employees/search"]');
		$expected = 1;
		$this->assertData($expected, $numForm);

		$numTableRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container-fluid table > tbody > tr');
		$expected = 0;
		$this->assertData($expected, $numTableRows);
	}

/**
 * testSearchValidQuerySuccessForNotUser method
 *  User role: secretary, human resources, admin
 *
 * @return void
 */
	public function testSearchValidQuerySuccessForNotUser() {
		$userRoles = [
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
			'data' => [
				'query' => '1000002',
				'target' => [
					CAKE_SEARCH_INFO_TARGET_FIELD_ANY_PART,
					'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
					'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER,
					'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER,
				]
			]
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
			$view = $this->testAction($url, $opt);
			$numForm = $this->getNumberItemsByCssSelector($view, 'div#header div.container form[action$="/employees/search"]');
			$expected = 1;
			$this->assertData($expected, $numForm);

			$numTableRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container-fluid table > tbody > tr');
			$expected = 2;
			$this->assertData($expected, $numTableRows);
		}
	}

/**
 * testViewValidId method
 *  User role: user, secretary, human resources, admin
 *
 * @return void
 */
	public function testViewValidId() {
		$userRoles = [
			USER_ROLE_USER => '',
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$expected = 1;
		foreach ($userRoles as $userRole => $userPrefix) {
			$userInfo = [
				'role' => $userRole,
				'prefix' => $userPrefix,
			];
			$this->applyUserInfo($userInfo);
			$this->generateMockedController();
			$url = [
				'controller' => 'employees',
				'action' => 'view',
				'2',
			];
			if (!empty($userPrefix)) {
				$url['prefix'] = $userPrefix;
				$url[$userPrefix] = true;
			}
			$view = $this->testAction($url, $opt);
			$numDl = $this->getNumberItemsByCssSelector($view, 'div#content div.container dl.dl-horizontal');
			$this->assertData($expected, $numDl);
		}
	}

/**
 * testViewValidDn method
 *  User role: user, secretary, human resources, admin
 *
 * @return void
 */
	public function testViewValidDn() {
		$userRoles = [
			USER_ROLE_USER => '',
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$expected = 1;
		foreach ($userRoles as $userRole => $userPrefix) {
			$userInfo = [
				'role' => $userRole,
				'prefix' => $userPrefix,
			];
			$this->applyUserInfo($userInfo);
			$this->generateMockedController();
			$url = [
				'controller' => 'employees',
				'action' => 'view',
				'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com',
			];
			if (!empty($userPrefix)) {
				$url['prefix'] = $userPrefix;
				$url[$userPrefix] = true;
			}
			$view = $this->testAction($url, $opt);
			$numDl = $this->getNumberItemsByCssSelector($view, 'div#content div.container dl.dl-horizontal');
			$this->assertData($expected, $numDl);
		}
	}

/**
 * testEditGetSuccess method
 *  User role: user
 *
 * @return void
 */
	public function testEditGetSuccess() {
		$userRoles = [
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
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
				'action' => 'edit',
				'8c149661-7215-47de-b40e-35320a1ea508',
			];
			if (!empty($userPrefix)) {
				$url['prefix'] = $userPrefix;
				$url[$userPrefix] = true;
			}
			$view = $this->testAction($url, $opt);
			$numFormEmployee = $this->getNumberItemsByCssSelector($view, 'div#content div.container form[action$="/employees/edit/8c149661-7215-47de-b40e-35320a1ea508"]');
			$expected = 1;
			$this->assertData($expected, $numFormEmployee);

			$numFormPhoto = $this->getNumberItemsByCssSelector($view, 'div#content div.container form[enctype="multipart/form-data"]');
			$expected = 1;
			$this->assertData($expected, $numFormPhoto);
		}
	}

/**
 * testGallery method
 *  User role: user, secretary, human resources, admin
 *
 * @return void
 */
	public function testGallery() {
		$userRoles = [
			USER_ROLE_USER => '',
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$expected = 9;
		foreach ($userRoles as $userRole => $userPrefix) {
			$userInfo = [
				'role' => $userRole,
				'prefix' => $userPrefix,
			];
			$this->applyUserInfo($userInfo);
			$this->generateMockedController();
			$url = [
				'controller' => 'employees',
				'action' => 'gallery',
			];
			if (!empty($userPrefix)) {
				$url['prefix'] = $userPrefix;
				$url[$userPrefix] = true;
			}
			$view = $this->testAction($url, $opt);
			$numRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container div.employees-gallery div.panel');
			$this->assertData($expected, $numRows);
		}
	}

/**
 * testTreeEmptyId method
 *  User role: user, secretary, human resources, admin
 *
 * @return void
 */
	public function testTreeEmptyId() {
		$userRoles = [
			USER_ROLE_USER => '',
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$expected = 9;
		foreach ($userRoles as $userRole => $userPrefix) {
			$userInfo = [
				'role' => $userRole,
				'prefix' => $userPrefix,
			];
			$this->applyUserInfo($userInfo);
			$this->generateMockedController();
			$url = [
				'controller' => 'employees',
				'action' => 'tree',
			];
			if (!empty($userPrefix)) {
				$url['prefix'] = $userPrefix;
				$url[$userPrefix] = true;
			}
			$view = $this->testAction($url, $opt);
			$numA = $this->getNumberItemsByCssSelector($view, 'div#content div.container ul.bonsai-treeview a[href*="\/employees\/view\/"]');
			$this->assertData($expected, $numA);
		}
	}

/**
 * testTreeForId method
 *  User role: user, secretary, human resources, admin
 *
 * @return void
 */
	public function testTreeForId() {
		$userRoles = [
			USER_ROLE_USER => '',
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$expected = 4;
		foreach ($userRoles as $userRole => $userPrefix) {
			$userInfo = [
				'role' => $userRole,
				'prefix' => $userPrefix,
			];
			$this->applyUserInfo($userInfo);
			$this->generateMockedController();
			$url = [
				'controller' => 'employees',
				'action' => 'tree',
				'8',
			];
			if (!empty($userPrefix)) {
				$url['prefix'] = $userPrefix;
				$url[$userPrefix] = true;
			}
			$view = $this->testAction($url, $opt);
			$numA = $this->getNumberItemsByCssSelector($view, 'div#content div.container ul.bonsai-treeview a[href*="\/employees\/view\/"]');
			$this->assertData($expected, $numA);
		}
	}

/**
 * testTreeForIdUseMoveForUserAndSecretary method
 *  User role: user, secretary
 *
 * @return void
 */
	public function testTreeForIdUseMoveForUserAndSecretary() {
		$userRoles = [
			USER_ROLE_USER => '',
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$expected = 0;
		foreach ($userRoles as $userRole => $userPrefix) {
			$userInfo = [
				'role' => $userRole,
				'prefix' => $userPrefix,
			];
			$this->applyUserInfo($userInfo);
			$this->generateMockedController();
			$url = [
				'controller' => 'employees',
				'action' => 'tree',
				'8',
				'1',
			];
			if (!empty($userPrefix)) {
				$url['prefix'] = $userPrefix;
				$url[$userPrefix] = true;
			}
			$view = $this->testAction($url, $opt);
			$numA = $this->getNumberItemsByCssSelector($view, 'div#content div.container ul.bonsai-treeview a[href*="\/employees\/move\/top\/"]');
			$this->assertData($expected, $numA);
		}
	}

/**
 * testTreeForIdUseMoveForHrAndAdmin method
 *  User role: human resources, admin
 *
 * @return void
 */
	public function testTreeForIdUseMoveForHrAndAdmin() {
		$userRoles = [
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$expected = 4;
		foreach ($userRoles as $userRole => $userPrefix) {
			$userInfo = [
				'role' => $userRole,
				'prefix' => $userPrefix,
			];
			$this->applyUserInfo($userInfo);
			$this->generateMockedController();
			$url = [
				'controller' => 'employees',
				'action' => 'tree',
				'8',
				'1',
			];
			if (!empty($userPrefix)) {
				$url['prefix'] = $userPrefix;
				$url[$userPrefix] = true;
			}
			$view = $this->testAction($url, $opt);
			$numA = $this->getNumberItemsByCssSelector($view, 'div#content div.container ul.bonsai-treeview a[href*="\/employees\/move\/top\/"]');
			$this->assertData($expected, $numA);
		}
	}

/**
 * testCheckUnsuccessForAdmin method
 *  User role: admin
 *
 * @return void
 */
	public function testCheckUnsuccessForAdmin() {
		$userInfo = [
			'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
			'prefix' => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$this->applyUserInfo($userInfo);
		$this->generateMockedController();
		$modelSubordinateDb = ClassRegistry::init('CakeLdap.SubordinateDb');
		$modelSubordinateDb->id = 2;
		$result = (bool)$modelSubordinateDb->saveField('rght', null);
		$this->assertTrue($result);

		$url = '/admin/employees/check';
		$view = $this->testAction($url, $opt);
		$numTableRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container table > tbody > tr');
		$expected = 2;
		$this->assertData($expected, $numTableRows);
	}

/**
 * testCheckSuccessForAdmin method
 *  User role: admin
 *
 * @return void
 */
	public function testCheckSuccessForAdmin() {
		$userInfo = [
			'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
			'prefix' => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$this->applyUserInfo($userInfo);
		$this->generateMockedController();
		$url = '/admin/employees/check';
		$view = $this->testAction($url, $opt);
		$numAlert = $this->getNumberItemsByCssSelector($view, 'div#content div.container div.alert-success');
		$expected = 1;
		$this->assertData($expected, $numAlert);
	}

/**
 * testExportForUser method
 *  User role: user
 *
 * @return void
 */
	public function testExportForUser() {
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$expected = 6;
		$userInfo = [
			'role' => USER_ROLE_USER,
			'prefix' => '',
		];
		$this->applyUserInfo($userInfo);
		$this->generateMockedController(true);
		$url = [
			'controller' => 'employees',
			'action' => 'export',
		];
		$view = $this->testAction($url, $opt);
		$numTableRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container table > tbody > tr');
		$this->assertData($expected, $numTableRows);
	}

/**
 * testExportForNotUser method
 *  User role: user, secretary, human resources, admin
 *
 * @return void
 */
	public function testExportForNotUser() {
		$userRoles = [
			USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
			USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
			USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
		];
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$expected = 10;
		foreach ($userRoles as $userRole => $userPrefix) {
			$userInfo = [
				'role' => $userRole,
				'prefix' => $userPrefix,
			];
			$this->applyUserInfo($userInfo);
			$this->generateMockedController(true);
			$url = [
				'controller' => 'employees',
				'action' => 'export',
			];
			if (!empty($userPrefix)) {
				$url['prefix'] = $userPrefix;
				$url[$userPrefix] = true;
			}
			$view = $this->testAction($url, $opt);
			$numTableRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container table > tbody > tr');
			$this->assertData($expected, $numTableRows);
		}
	}
}
