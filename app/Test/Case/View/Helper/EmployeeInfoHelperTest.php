<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('View', 'View');
App::uses('Helper', 'View');
App::uses('EmployeeInfoHelper', 'View/Helper');

/**
 * EmployeeInfoHelper Test Case
 */
class EmployeeInfoHelperTest extends AppCakeTestCase {

/**
 * Object of model `Employee`
 *
 * @var object
 */
	protected $_modelEmployee = null;

/**
 * Code of the current language of the user interface.
 *
 * @var string
 */
	protected $_uiLcid = null;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		'app.department_extension',
		'plugin.cake_ldap.department',
		'plugin.cake_ldap.employee',
		'plugin.cake_ldap.employee_ldap',
		'plugin.cake_ldap.othermobile',
		'plugin.cake_ldap.othertelephone',
	];

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->_uiLcid = Configure::read('Config.language');
		Configure::write('Config.language', 'eng');
		$View = new View();
		$EmployeeInfo = new EmployeeInfoHelper($View);
		$this->_targetObject = $this->createProxyObject($EmployeeInfo);
		$this->_modelEmployee = ClassRegistry::init('Employee');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		if (!empty($this->_uiLcid)) {
			Configure::write('Config.language', $this->_uiLcid);
		}
		unset($this->_uiLcid);
		unset($this->_targetObject);
		unset($this->_modelEmployee);

		parent::tearDown();
	}

/**
 * testGetCountryCode method
 *
 * @return void
 */
	public function testGetCountryCode() {
		$result = $this->_targetObject->_getCountryCode();
		$expected = 'BY';
		$this->assertData($expected, $result);
	}

/**
 * testGetLanguageCode method
 *
 * @return void
 */
	public function testGetLanguageCode() {
		$result = $this->_targetObject->_getLanguageCode();
		$expected = 'en';
		$this->assertData($expected, $result);
	}

/**
 * testGetLibPhoneNumberFormat method
 *
 * @return void
 */
	public function testGetLibPhoneNumberFormat() {
		$result = $this->_targetObject->_getLibPhoneNumberFormat();
		$expected = 'NATIONAL';
		$this->assertData($expected, $result);
	}

/**
 * testGetInfoForTelephoneEmptyDataForName method
 *
 * @return void
 */
	public function testGetInfoForTelephoneEmptyDataForName() {
		$result = $this->_targetObject->_getInfoForTelephone('', false);
		$expected = __d('view_extension', '&lt;None&gt;');
		$this->assertData($expected, $result);
	}

/**
 * testGetInfoForTelephoneInvalidDataForName method
 *
 * @return void
 */
	public function testGetInfoForTelephoneInvalidDataForName() {
		$result = $this->_targetObject->_getInfoForTelephone('+37529', false);
		$expected = '29';
		$this->assertData($expected, $result);
	}

/**
 * testGetInfoForTelephoneValidDataMobForName method
 *
 * @return void
 */
	public function testGetInfoForTelephoneValidDataMobForName() {
		$result = $this->_targetObject->_getInfoForTelephone('8 029 123-45-67', false);
		$expected = '<abbr title="Velcom" data-toggle="tooltip">8 029 123-45-67</abbr>';
		$this->assertData($expected, $result);
	}

/**
 * testGetInfoForTelephoneValidDataLandForName method
 *
 * @return void
 */
	public function testGetInfoForTelephoneValidDataLandForName() {
		$result = $this->_targetObject->_getInfoForTelephone('+375172345678', false);
		$expected = '8 017 234-56-78';
		$this->assertData($expected, $result);
	}

/**
 * testGetInfoForTelephoneInvalidDataForDescription method
 *
 * @return void
 */
	public function testGetInfoForTelephoneInvalidDataForDescription() {
		$result = $this->_targetObject->_getInfoForTelephone('1235', true);
		$expected = '<abbr title="Belarus" data-toggle="tooltip">1235</abbr>';
		$this->assertData($expected, $result);
	}

/**
 * testGetInfoForTelephoneValidDataMobForDescription method
 *
 * @return void
 */
	public function testGetInfoForTelephoneValidDataMobForDescription() {
		$result = $this->_targetObject->_getInfoForTelephone('8 029 123-45-67', true);
		$expected = '<abbr title="Belarus" data-toggle="tooltip">8 029 123-45-67</abbr>';
		$this->assertData($expected, $result);
	}

/**
 * testGetInfoForTelephoneValidDataLandForDescription method
 *
 * @return void
 */
	public function testGetInfoForTelephoneValidDataLandForDescription() {
		$result = $this->_targetObject->_getInfoForTelephone('+375172345678', true);
		$expected = '<abbr title="Minsk" data-toggle="tooltip">8 017 234-56-78</abbr>';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForTelephoneDescriptionForListItem method
 *
 * @return void
 */
	public function testGetValueForTelephoneDescriptionForListItem() {
		$result = $this->_targetObject->_getValueForTelephoneDescription('', false);
		$expected = __d('view_extension', '&lt;None&gt;');
		$this->assertData($expected, $result);
	}

/**
 * testGetInfoForTelephoneInvalidDataForDescriptionForListItem method
 *
 * @return void
 */
	public function testGetValueForTelephoneDescriptionInvalidDataForDescriptionForListItem() {
		$result = $this->_targetObject->_getValueForTelephoneDescription('+37529', false);
		$expected = '<abbr title="Belarus" data-toggle="tooltip">29</abbr>';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForTelephoneDescriptionValidDataMobForListItem method
 *
 * @return void
 */
	public function testGetValueForTelephoneDescriptionValidDataMobForListItem() {
		$result = $this->_targetObject->_getValueForTelephoneDescription('8 029 123-45-67', false);
		$expected = '<abbr title="Belarus" data-toggle="tooltip">8 029 123-45-67</abbr>';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForTelephoneDescriptionValidDataLandForDescriptionForListItem method
 *
 * @return void
 */
	public function testGetValueForTelephoneDescriptionValidDataLandForDescriptionForListItem() {
		$result = $this->_targetObject->_getValueForTelephoneDescription('+375172345678', false);
		$expected = '<abbr title="Minsk" data-toggle="tooltip">8 017 234-56-78</abbr>';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForTelephoneNameForListItem method
 *
 * @return void
 */
	public function testGetValueForTelephoneNameForListItem() {
		$result = $this->_targetObject->_getValueForTelephoneName('', false);
		$expected = __d('view_extension', '&lt;None&gt;');
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForTelephoneNameInvalidDataForDescriptionForListItem method
 *
 * @return void
 */
	public function testGetValueForTelephoneNameInvalidDataForDescriptionForListItem() {
		$result = $this->_targetObject->_getValueForTelephoneName('+37529', false);
		$expected = '29';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForTelephoneNameValidDataMobForListItem method
 *
 * @return void
 */
	public function testGetValueForTelephoneNameValidDataMobForListItem() {
		$result = $this->_targetObject->_getValueForTelephoneName('8 029 123-45-67', false);
		$expected = '<abbr title="Velcom" data-toggle="tooltip">8 029 123-45-67</abbr>';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForTelephoneNameValidDataLandForDescriptionForListItem method
 *
 * @return void
 */
	public function testGetValueForTelephoneNameValidDataLandForDescriptionForListItem() {
		$result = $this->_targetObject->_getValueForTelephoneName('+375172345678', false);
		$expected = '8 017 234-56-78';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForDepartmentNameEmptyDataForListItem method
 *
 * @return void
 */
	public function testGetValueForDepartmentNameEmptyDataForListItem() {
		$result = $this->_targetObject->_getValueForDepartmentName('', false, []);
		$expected = __d('view_extension', '&lt;None&gt;');
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForDepartmentNameInvalidDataEmptyFullDataForListItem method
 *
 * @return void
 */
	public function testGetValueForDepartmentNameInvalidDataEmptyFullDataForListItem() {
		$result = $this->_targetObject->_getValueForDepartmentName('Test', false, []);
		$expected = 'Test';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForDepartmentNameInvalidDataFullDataForListItem method
 *
 * @return void
 */
	public function testGetValueForDepartmentNameInvalidDataFullDataForListItem() {
		$fullData = $this->_modelEmployee->get(2, [], false, [], ['DepartmentExtension']);
		$result = $this->_targetObject->_getValueForDepartmentName('Test', false, $fullData);
		$expected = 'Отдел связи (Test)';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForDepartmentNameEmptyDataFullDataForListItem method
 *
 * @return void
 */
	public function testGetValueForDepartmentNameEmptyDataFullDataForListItem() {
		$fullData = $this->_modelEmployee->get(2, [], false, [], ['DepartmentExtension']);
		$result = $this->_targetObject->_getValueForDepartmentName('', false, $fullData);
		$expected = 'Отдел связи';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForDepartmentNameValidDataFullDataForListItem method
 *
 * @return void
 */
	public function testGetValueForDepartmentNameValidDataFullDataForListItem() {
		$fullData = $this->_modelEmployee->get(2, [], false, [], ['DepartmentExtension']);
		$result = $this->_targetObject->_getValueForDepartmentName('ОС', false, $fullData);
		$expected = 'Отдел связи (ОС)';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForDepartmentNameInvalidDataEmptyFullDataForTableRow method
 *
 * @return void
 */
	public function testGetValueForDepartmentNameInvalidDataEmptyFullDataForTableRow() {
		$result = $this->_targetObject->_getValueForDepartmentName('Test', true, []);
		$expected = 'Test';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForDepartmentNameInvalidDataFullDataForTableRow method
 *
 * @return void
 */
	public function testGetValueForDepartmentNameInvalidDataFullDataForTableRow() {
		$fullData = $this->_modelEmployee->get(2, [], false, [], ['DepartmentExtension']);
		$result = $this->_targetObject->_getValueForDepartmentName('Test', true, $fullData);
		$expected = '<abbr title="Отдел связи" data-toggle="tooltip">Test</abbr>';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForDepartmentNameEmptyDataFullDataForTableRow method
 *
 * @return void
 */
	public function testGetValueForDepartmentNameEmptyDataFullDataForTableRow() {
		$fullData = $this->_modelEmployee->get(2, [], false, [], ['DepartmentExtension']);
		$result = $this->_targetObject->_getValueForDepartmentName('', true, $fullData);
		$expected = 'Отдел связи';
		$this->assertData($expected, $result);
	}

/**
 * testGetValueForDepartmentNameValidDataFullDataForTableRow method
 *
 * @return void
 */
	public function testGetValueForDepartmentNameValidDataFullDataForTableRow() {
		$fullData = $this->_modelEmployee->get(2, [], false, [], ['DepartmentExtension']);
		$result = $this->_targetObject->_getValueForDepartmentName('ОС', true, $fullData);
		$expected = '<abbr title="Отдел связи" data-toggle="tooltip">ОС</abbr>';
		$this->assertData($expected, $result);
	}
}
