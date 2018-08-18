<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('Setting', 'Model');

/**
 * Setting Test Case
 */
class SettingTest extends AppCakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.cake_ldap.employee_ldap',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_targetObject = ClassRegistry::init('Setting');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->_targetObject);

        parent::tearDown();
    }

    /**
     * testGetVars method
     *
     * @return void
     */
    public function testGetVars()
    {
        $result = $this->_targetObject->getVars();
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['extendViewFieldsList']));
        $this->assertTrue(isset($result['readOnlyFieldsList']));
        $this->assertTrue(asort($result['extendViewFieldsList']));
        $this->assertTrue(asort($result['readOnlyFieldsList']));
        $expected = [
            'numberFormatList' => [
                'E164' => __d('number_format', 'E164'),
                'INTERNATIONAL' => __d('number_format', 'INTERNATIONAL'),
                'NATIONAL' => __d('number_format', 'NATIONAL'),
                'RFC3966' => __d('number_format', 'RFC3966'),
            ],
            'groupDeferredSaveList' => [
                USER_ROLE_HUMAN_RESOURCES => mb_ucfirst(__('human resources')),
                USER_ROLE_ADMIN => mb_ucfirst(__('administrator')),
            ],
            'countryCodePhoneLib' => 'BY',
            'extendViewFieldsList' => [
                'pager' => __d('app_ldap_field_name', 'Birthday'),
                'wwwhomepage' => __d('app_ldap_field_name', 'Computer'),
                'department' => __d('app_ldap_field_name', 'Department'),
                'mail' => __d('app_ldap_field_name', 'E-mail'),
                'employeeid' => __d('app_ldap_field_name', 'Employee ID'),
                'givenname' => __d('app_ldap_field_name', 'Given name'),
                'telephonenumber' => __d('app_ldap_field_name', 'Internal telephone'),
                'initials' => __d('app_ldap_field_name', 'Initials'),
                'othertelephone' => __d('app_ldap_field_name', 'Landline telephone'),
                'manager' => __d('app_ldap_field_name', 'Manager'),
                'middlename' => __d('app_ldap_field_name', 'Middle name'),
                'mobile' => __d('app_ldap_field_name', 'Mobile telephone'),
                'displayname' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'physicaldeliveryofficename' => __d('app_ldap_field_name', 'Office room'),
                'othermobile' => __d('app_ldap_field_name', 'Personal mobile telephone'),
                'thumbnailphoto' => __d('app_ldap_field_name', 'Photo'),
                'title' => __d('app_ldap_field_name', 'Position'),
                'ipphone' => __d('app_ldap_field_name', 'SIP telephone'),
                'division' => __d('app_ldap_field_name', 'Subdivision'),
                'sn' => __d('app_ldap_field_name', 'Surname'),
            ],
            'readOnlyFieldsList' => [
                'pager' => __d('app_ldap_field_name', 'Birthday'),
                'wwwhomepage' => __d('app_ldap_field_name', 'Computer'),
                'department' => __d('app_ldap_field_name', 'Department'),
                'mail' => __d('app_ldap_field_name', 'E-mail'),
                'employeeid' => __d('app_ldap_field_name', 'Employee ID'),
                'givenname' => __d('app_ldap_field_name', 'Given name'),
                'telephonenumber' => __d('app_ldap_field_name', 'Internal telephone'),
                'initials' => __d('app_ldap_field_name', 'Initials'),
                'othertelephone' => __d('app_ldap_field_name', 'Landline telephone'),
                'manager' => __d('app_ldap_field_name', 'Manager'),
                'middlename' => __d('app_ldap_field_name', 'Middle name'),
                'mobile' => __d('app_ldap_field_name', 'Mobile telephone'),
                'displayname' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'physicaldeliveryofficename' => __d('app_ldap_field_name', 'Office room'),
                'othermobile' => __d('app_ldap_field_name', 'Personal mobile telephone'),
                'thumbnailphoto' => __d('app_ldap_field_name', 'Photo'),
                'title' => __d('app_ldap_field_name', 'Position'),
                'ipphone' => __d('app_ldap_field_name', 'SIP telephone'),
                'division' => __d('app_ldap_field_name', 'Subdivision'),
                'sn' => __d('app_ldap_field_name', 'Surname'),
            ],
        ];
        $this->assertTrue(asort($expected['extendViewFieldsList']));
        $this->assertTrue(asort($expected['readOnlyFieldsList']));
        $this->assertData($expected, $result);
    }
}
