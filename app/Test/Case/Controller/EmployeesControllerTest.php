<?php
App::uses('AppControllerTestCase', 'Test');
App::uses('EmployeesController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('CakeTime', 'Utility');

/**
 * EmployeesController Test Case
 */
class EmployeesControllerTest extends AppControllerTestCase
{

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
     * Path to import directory
     *
     * @var string
     */
    protected $_pathImportDir = TMP . 'tests' . DS . 'import' . DS;

    /**
     * Path to export directory
     *
     * @var string
     */
    protected $_pathExportDir = TMP . 'tests' . DS . 'export' . DS;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $Folder = new Folder($this->_pathImportDir, true);
        $Folder = new Folder($this->_pathExportDir, true);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        $Folder = new Folder($this->_pathImportDir);
        $Folder->delete();

        $Folder = new Folder($this->_pathExportDir);
        $Folder->delete();

        parent::tearDown();
    }

    /**
     * testIndex method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testIndex()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $expected = [
            'showBreadcrumb' => false,
            'lastUpdate' => '2017-11-16 17:03:00',
            'countEmployees' => 9,
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'index',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->excludeCommonAppVars($result);
            $this->assertTrue(isset($result['birthdays']));
            unset($result['birthdays']);
            $this->assertData($expected, $result);
        }
    }

    /**
     * testSearchEmptyQueryForUser method
     *
     * User role: user
     * @return void
     */
    public function testSearchEmptyQueryForUser()
    {
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $expected = [
            'specificJS' => 'index',
            'showBreadcrumb' => false,
            'paginatorOptions' => [
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                    'label' => __dx('app_ldap_field_name', 'employee', 'Name'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                    'label' => __d('app_ldap_field_name', 'E-mail'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                    'label' => __d('app_ldap_field_name', 'SIP tel.'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                    'label' => __d('app_ldap_field_name', 'Int. tel.'),
                ],
                'Othertelephone.{n}.value' => [
                    'label' => __d('app_ldap_field_name', 'Land. tel.'),
                    'disabled' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                    'label' => __d('app_ldap_field_name', 'Mob. tel.'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                    'label' => __d('app_ldap_field_name', 'Office'),
                ],
                'Department.value' => [
                    'label' => __d('app_ldap_field_name', 'Depart.'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                    'label' => __d('app_ldap_field_name', 'Pos.'),
                ],
            ],
            'fieldsConfig' => [
                'Employee.id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.department_id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.manager_id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => [
                    'type' => 'guid',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                    'type' => 'telephone_name',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                    'type' => 'mail',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => [
                    'type' => 'photo',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => [
                    'type' => 'date',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.block' => [
                    'type' => 'boolean',
                    'truncate' => false,
                ],
                'Department.value' => [
                    'type' => 'department_name',
                    'truncate' => true,
                ],
                'Othertelephone.{n}.value' => [
                    'type' => 'telephone_description',
                    'truncate' => false,
                ],
                'Othermobile.{n}.value' => [
                    'type' => 'telephone_name',
                    'truncate' => false,
                ],
                'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                    'type' => 'manager',
                    'truncate' => true,
                ],
                'Subordinate.{n}' => [
                    'type' => 'element',
                    'truncate' => false,
                ],
            ],
            'query' => '',
            'queryCorrect' => '',
            'queryConfig' => [
                'anyPart' => true,
                'modelConfig' => [
                    'Employee' => [
                        'fields' => [
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surname'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Given name'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Middle name'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP telephone'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Landline telephone'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mobile telephone'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office room'),
                            'Department.value' => __d('app_ldap_field_name', 'Department'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Position'),
                        ],
                        'order' => [
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'asc'
                        ],
                        'name' => __('Employees'),
                        'contain' => [
                            'Othertelephone',
                            'Department',
                            'DepartmentExtension',
                        ],
                        'conditions' => [
                            'Employee.block' => false
                        ]
                    ]
                ]
            ],
            'result' => false,
            'target' => [
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
                'anyPart'
            ],
            'correct' => false,
        ];
        $userInfo = [
            'role' => USER_ROLE_USER,
            'prefix' => '',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'search',
        ];
        $result = $this->testAction($url, $opt);
        $this->excludeCommonAppVars($result);
        $this->assertData($expected, $result);

        $this->checkFlashMessage(__d('cake_search_info', 'Enter your query in the search bar'));
    }

    /**
     * testSearchEmptyQueryForNotUser method
     *
     * User role: secretary, human resources, admin
     * @return void
     */
    public function testSearchEmptyQueryForNotUser()
    {
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
            'specificJS' => 'index',
            'showBreadcrumb' => false,
            'paginatorOptions' => [
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                    'label' => __dx('app_ldap_field_name', 'employee', 'Name'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                    'label' => __d('app_ldap_field_name', 'E-mail'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                    'label' => __d('app_ldap_field_name', 'SIP tel.'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                    'label' => __d('app_ldap_field_name', 'Int. tel.'),
                ],
                'Othertelephone.{n}.value' => [
                    'label' => __d('app_ldap_field_name', 'Land. tel.'),
                    'disabled' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                    'label' => __d('app_ldap_field_name', 'Mob. tel.'),
                ],
                'Othermobile.{n}.value' => [
                    'label' => __d('app_ldap_field_name', 'Person. mob. tel.'),
                    'disabled' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                    'label' => __d('app_ldap_field_name', 'Office'),
                ],
                'Department.value' => [
                    'label' => __d('app_ldap_field_name', 'Depart.'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => [
                    'label' => __d('app_ldap_field_name', 'Subdiv.'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                    'label' => __d('app_ldap_field_name', 'Pos.'),
                ],
                'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                    'label' => __d('app_ldap_field_name', 'Manag.'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => [
                    'label' => __d('app_ldap_field_name', 'Birthd.'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => [
                    'label' => __d('app_ldap_field_name', 'Comp.'),
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [
                    'label' => __d('app_ldap_field_name', 'Empl. ID'),
                ]
            ],
            'fieldsConfig' => [
                'Employee.id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.department_id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.manager_id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => [
                    'type' => 'guid',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                    'type' => 'telephone_name',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                    'type' => 'mail',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => [
                    'type' => 'photo',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => [
                    'type' => 'date',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.block' => [
                    'type' => 'boolean',
                    'truncate' => false,
                ],
                'Department.value' => [
                    'type' => 'department_name',
                    'truncate' => true,
                ],
                'Othertelephone.{n}.value' => [
                    'type' => 'telephone_description',
                    'truncate' => false,
                ],
                'Othermobile.{n}.value' => [
                    'type' => 'telephone_name',
                    'truncate' => false,
                ],
                'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                    'type' => 'manager',
                    'truncate' => true,
                ],
                'Subordinate.{n}' => [
                    'type' => 'element',
                    'truncate' => false,
                ],
            ],
            'query' => '',
            'queryCorrect' => '',
            'queryConfig' => [
                'anyPart' => true,
                'modelConfig' => [
                    'Employee' => [
                        'fields' => [
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surname'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Given name'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Middle name'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP telephone'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Landline telephone'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mobile telephone'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Personal mobile telephone'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office room'),
                            'Department.value' => __d('app_ldap_field_name', 'Department'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('app_ldap_field_name', 'Subdivision'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Position'),
                            'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __d('app_ldap_field_name', 'Manager'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('app_ldap_field_name', 'Birthday'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('app_ldap_field_name', 'Computer'),
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('app_ldap_field_name', 'Employee ID'),
                        ],
                        'order' => [
                            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'asc',
                        ],
                        'name' => __('Employees'),
                        'contain' => [
                            'Othertelephone',
                            'Othermobile',
                            'Department',
                            'DepartmentExtension',
                            'Manager',
                        ],
                        'conditions' => [
                            'Employee.block' => false
                        ]
                    ]
                ]
            ],
            'result' => false,
            'target' => [
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME,
                'Employee.Department.value',
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
                'Employee.Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER,
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID,
                'anyPart'
            ],
            'correct' => false,
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'search',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->excludeCommonAppVars($result);
            $this->assertData($expected, $result);

            $this->checkFlashMessage(__d('cake_search_info', 'Enter your query in the search bar'));
        }
    }

    /**
     * testSearchInvalidQuery method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testSearchInvalidQuery()
    {
        $this->setExpectedException('InternalErrorException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'data' => [
                'query' => 'Миронов',
                'target' => [
                    CAKE_SEARCH_INFO_TARGET_FIELD_ANY_PART,
                    'BadModel'
                ]
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'search',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
    }

    /**
     * testSearchValidQueryWoResult method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testSearchValidQueryWoResult()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'search',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);

            $this->assertTrue(isset($result['result']));
            $this->assertEmpty($result['result']);
        }
    }

    /**
     * testSearchValidQueryMinChars method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testSearchValidQueryMinChars()
    {
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
            'return' => 'vars',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'search',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__d(
                'cake_search_info',
                'Input minimum %d %s',
                $querySearchMinLength,
                __dn('cake_search_info', 'character', 'characters', $querySearchMinLength)
            ));
        }
    }

    /**
     * testSearchEmptyNotAllowedFieldsQueryForUser method
     *
     * User role: user
     * @return void
     */
    public function testSearchEmptyNotAllowedFieldsQueryForUser()
    {
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
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
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'search',
        ];
        $result = $this->testAction($url, $opt);
        $this->assertTrue(isset($result['result']));
        $this->assertEmpty($result['result']);
    }

    /**
     * testSearchValidQuerySuccessForNotUser method
     *
     * User role: secretary, human resources, admin
     * @return void
     */
    public function testSearchValidQuerySuccessForNotUser()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
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
        $expected = [
            'Employee' => [
                'amount' => 2,
                'data' => [
                    [
                        'Employee' => [
                            'id' => '2',
                            CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Егоров Т.Г.',
                            CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '0010b7b8-d69a-4365-81ca-5f975584fe5c',
                            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Егоров Т.Г.',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Егоров',
                            CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Тимофей',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Геннадьевич',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 't.egorov@fabrikam.com',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501261',
                            CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '261',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '',
                            CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '504',
                            CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа связи №1',
                            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-07-27',
                            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0390',
                            CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1631',
                        ],
                        'Manager' => [
                            'id' => '3',
                            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист'
                        ],
                        'Department' => [
                            'id' => '2',
                            'value' => 'ОС',
                            'block' => false
                        ],
                        'DepartmentExtension' => [
                            'id' => '2',
                            'lft' => '3',
                            'name' => 'Отдел связи'
                        ],
                        'Othertelephone' => [
                            [
                                'id' => '2',
                                'value' => '+375171000002',
                                'employee_id' => '2'
                            ]
                        ],
                        'Othermobile' => []
                    ],
                    [
                        'Employee' => [
                            'id' => '1',
                            CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Миронов В.М.',
                            CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '1dde2cdc-5264-4286-9273-4a88b230237c',
                            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Миронов В.М.',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Миронов',
                            CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Вячеслав',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Миронович',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'v.mironov@fabrikam.com',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '50380',
                            CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '380',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '',
                            CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '214',
                            CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Геологический отдел (ГО)',
                            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
                            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '2015-07-20',
                            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => '',
                            CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '8060',
                        ],
                        'Manager' => [
                            'id' => null,
                            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => null,
                            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => null
                        ],
                        'Department' => [
                            'id' => '1',
                            'value' => 'УИЗ',
                            'block' => false
                        ],
                        'DepartmentExtension' => [
                            'id' => '1',
                            'lft' => '1',
                            'name' => 'Управление инженерных изысканий'
                        ],
                        'Othertelephone' => [
                            [
                                'id' => '1',
                                'value' => '+375171000001',
                                'employee_id' => '1'
                            ]
                        ],
                        'Othermobile' => [
                            [
                                'id' => '1',
                                'value' => '+375291000001',
                                'employee_id' => '1'
                            ],
                            [
                                'id' => '2',
                                'value' => '+375291000002',
                                'employee_id' => '1'
                            ]
                        ]
                    ]
                ]
            ],
            'count' => 2,
            'total' => 2
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'search',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->assertTrue(isset($result['result']));
            $this->assertData($expected, $result['result']);
        }
    }

    /**
     * testViewEmptyId method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testViewEmptyId()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'view',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__d('cake_ldap', 'Invalid ID for employee'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testViewInvalidId method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testViewInvalidId()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'view',
                '1000',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__d('cake_ldap', 'Invalid ID for employee'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testViewInvalidDn method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testViewInvalidDn()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'view',
                'CN=SomeUser,OU=Users,DC=fabrikam,DC=com',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__d('cake_ldap', 'Invalid ID for employee'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testViewValidIdForUser method
     *
     * User role: user
     * @return void
     */
    public function testViewValidIdForUser()
    {
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $expected = [
            'pageHeader' => __('Information of employees'),
            'headerMenuActions' => [
                [
                    'fas fa-sitemap',
                    __('Tree of subordinate'),
                    ['controller' => 'employees', 'action' => 'tree', '2'],
                    ['title' => __('Edit tree of subordinate employee')]
                ]
            ],
            'employee' => [
                'Employee' => [
                    'id' => '2',
                    'department_id' => '2',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '0010b7b8-d69a-4365-81ca-5f975584fe5c',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Егоров Т.Г.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Егоров Т.Г.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Т.Г.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Егоров',
                    CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Тимофей',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Геннадьевич',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '261',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '504',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 't.egorov@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501261',
                    'block' => false,
                ],
                'DepartmentExtension' => [
                    'id' => '2',
                    'lft' => '3',
                    'name' => 'Отдел связи',
                ],
                'Department' => [
                    'id' => '2',
                    'value' => 'ОС',
                    'block' => false,
                ],
                'Othertelephone' => [
                    [
                        'id' => '2',
                        'value' => '+375171000002',
                        'employee_id' => '2',
                    ],
                ],
            ],
            'fieldsLabel' => [
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surname'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Given name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Middle name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
                'Othertelephone.{n}.value' => __d('app_ldap_field_name', 'Landline telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mobile telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office room'),
                'Department.value' => __d('app_ldap_field_name', 'Department'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Position'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_name', 'Photo'),
            ],
            'fieldsLabelExtend' => [],
            'fieldsConfig' => [
                'Employee.id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.department_id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.manager_id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => [
                    'type' => 'guid',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                    'type' => 'telephone_name',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                    'type' => 'mail',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => [
                    'type' => 'photo',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => [
                    'type' => 'date',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.block' => [
                    'type' => 'boolean',
                    'truncate' => false,
                ],
                'Department.value' => [
                    'type' => 'department_name',
                    'truncate' => true,
                ],
                'Othertelephone.{n}.value' => [
                    'type' => 'telephone_description',
                    'truncate' => false,
                ],
                'Othermobile.{n}.value' => [
                    'type' => 'telephone_name',
                    'truncate' => false,
                ],
                'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                    'type' => 'manager',
                    'truncate' => true,
                ],
                'Subordinate.{n}' => [
                    'type' => 'element',
                    'truncate' => false,
                ],
            ],
            'id' => '2',
            'isTreeReady' => true
        ];
        $userInfo = [
            'role' => USER_ROLE_USER,
            'prefix' => '',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'view',
            '2',
        ];
        $result = $this->testAction($url, $opt);
        $this->excludeCommonAppVars($result);
        $this->assertData($expected, $result);
    }

    /**
     * testViewValidIdForSecretary method
     *
     * User role: secretary
     * @return void
     */
    public function testViewValidIdForSecretary()
    {
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $expected = [
            'pageHeader' => __('Information of employees'),
            'headerMenuActions' => [
                [
                    'fas fa-pencil-alt',
                    __('Edit information'),
                    ['controller' => 'employees', 'action' => 'edit', '8c149661-7215-47de-b40e-35320a1ea508'],
                    ['title' => __('Edit information of this employee')]
                ],
                [
                    'fas fa-sitemap',
                    __('Tree of subordinate'),
                    ['controller' => 'employees', 'action' => 'tree', '7'],
                    ['title' => __('Edit tree of subordinate employee')]
                ]
            ],
            'employee' => [
                'Employee' => [
                    'id' => '7',
                    'department_id' => '3',
                    'manager_id' => '4',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '8c149661-7215-47de-b40e-35320a1ea508',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Хвощинский В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Хвощинский',
                    CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Виктор',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Владимирович',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Начальник отдела',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '320',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000004',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '217',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'v.hvoshchinskiy@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0386',
                    CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1304',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                    CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-08',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501321',
                    'block' => false
                ],
                'DepartmentExtension' => [
                    'id' => '3',
                    'lft' => '5',
                    'name' => 'Отдел информационных технологий'
                ],
                'Department' => [
                    'id' => '3',
                    'value' => 'ОИТ',
                    'block' => false,
                ],
                'Manager' => [
                    'id' => '4',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Дементьева А.С.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                ],
                'Subordinate' => [
                    [
                        'SubordinateDb' => [
                            'id' => '6',
                            'parent_id' => '7',
                            'lft' => '12',
                            'rght' => '13',
                        ],
                        'Employee' => [
                            'id' => '6',
                            'name' => 'Козловская Е.М.',
                            'title' => 'Заведующий сектором',
                        ],
                        'children' => [],
                    ]
                ],
                'Othertelephone' => [
                    [
                        'id' => '8',
                        'value' => '+375171000008',
                        'employee_id' => '7',
                    ],
                    [
                        'id' => '9',
                        'value' => '+375171000009',
                        'employee_id' => '7',
                    ]
                ],
                'Othermobile' => []
            ],
            'fieldsLabel' => [
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surname'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Given name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Middle name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
                'Othertelephone.{n}.value' => __d('app_ldap_field_name', 'Landline telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mobile telephone'),
                'Othermobile.{n}.value' => __d('app_ldap_field_name', 'Personal mobile telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office room'),
                'Department.value' => __d('app_ldap_field_name', 'Department'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('app_ldap_field_name', 'Subdivision'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Position'),
                'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __d('app_ldap_field_name', 'Manager'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('app_ldap_field_name', 'Birthday'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('app_ldap_field_name', 'Computer'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('app_ldap_field_name', 'Employee ID'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_name', 'Photo'),
            ],
            'fieldsLabelExtend' => [
                'Subordinate.{n}' => __d('cake_ldap_field_name', 'Subordinate'),
            ],
            'fieldsConfig' => [
                'Employee.id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.department_id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.manager_id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => [
                    'type' => 'guid',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                    'type' => 'telephone_name',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                    'type' => 'mail',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => [
                    'type' => 'photo',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => [
                    'type' => 'date',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.block' => [
                    'type' => 'boolean',
                    'truncate' => false,
                ],
                'Department.value' => [
                    'type' => 'department_name',
                    'truncate' => true,
                ],
                'Othertelephone.{n}.value' => [
                    'type' => 'telephone_description',
                    'truncate' => false,
                ],
                'Othermobile.{n}.value' => [
                    'type' => 'telephone_name',
                    'truncate' => false,
                ],
                'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                    'type' => 'manager',
                    'truncate' => true,
                ],
                'Subordinate.{n}' => [
                    'type' => 'element',
                    'truncate' => false,
                ],
            ],
            'id' => '7',
            'isTreeReady' => true
        ];
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_SECRETARY,
            'prefix' => 'secret',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'view',
            'prefix' => 'secret',
            'secret' => true,
            '7',
        ];
        $result = $this->testAction($url, $opt);
        $this->excludeCommonAppVars($result);
        $this->assertData($expected, $result);
    }

    /**
     * testViewValidDnForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testViewValidDnForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $expected = [
            'pageHeader' => __('Information of employees'),
            'headerMenuActions' => [
                [
                    'fas fa-pencil-alt',
                    __('Edit information'),
                    ['controller' => 'employees', 'action' => 'edit', '1dde2cdc-5264-4286-9273-4a88b230237c'],
                    ['title' => __('Edit information of this employee')]
                ],
                [
                    'fas fa-sync-alt',
                    __('Synchronize information'),
                    ['controller' => 'employees', 'action' => 'sync', '1dde2cdc-5264-4286-9273-4a88b230237c'],
                    [
                        'title' => __('Synchronize information of this employee with LDAP server'),
                        'data-toggle' => 'request-only'
                    ]
                ],
                [
                    'fas fa-sitemap',
                    __('Tree of subordinate'),
                    ['controller' => 'employees', 'action' => 'tree', '1'],
                    ['title' => __('Edit tree of subordinate employee')]
                ]
            ],
            'employee' => [
                'Employee' => [
                    'id' => '1',
                    'department_id' => '1',
                    'manager_id' => null,
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '1dde2cdc-5264-4286-9273-4a88b230237c',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Миронов В.М.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Миронов В.М.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'В.М.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Миронов',
                    CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Вячеслав',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Миронович',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Геологический отдел (ГО)',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '380',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '214',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'v.mironov@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '8060',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                    CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '2015-07-20',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '50380',
                    'block' => false,
                ],
                'DepartmentExtension' => [
                    'id' => '1',
                    'lft' => '1',
                    'name' => 'Управление инженерных изысканий',
                ],
                'Department' => [
                    'id' => '1',
                    'value' => 'УИЗ',
                    'block' => false
                ],
                'Manager' => [
                    'id' => null,
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => null,
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => null,
                ],
                'Subordinate' => [],
                'Othertelephone' => [
                    [
                        'id' => '1',
                        'value' => '+375171000001',
                        'employee_id' => '1',
                    ],
                ],
                'Othermobile' => [
                    [
                        'id' => '1',
                        'value' => '+375291000001',
                        'employee_id' => '1',
                    ],
                    [
                        'id' => '2',
                        'value' => '+375291000002',
                        'employee_id' => '1',
                    ],
                ],
            ],
            'fieldsLabel' => [
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surname'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Given name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Middle name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
                'Othertelephone.{n}.value' => __d('app_ldap_field_name', 'Landline telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mobile telephone'),
                'Othermobile.{n}.value' => __d('app_ldap_field_name', 'Personal mobile telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office room'),
                'Department.value' => __d('app_ldap_field_name', 'Department'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('app_ldap_field_name', 'Subdivision'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Position'),
                'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __d('app_ldap_field_name', 'Manager'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('app_ldap_field_name', 'Birthday'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('app_ldap_field_name', 'Computer'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('app_ldap_field_name', 'Employee ID'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_name', 'Photo'),
            ],
            'fieldsLabelExtend' => [
                'Subordinate.{n}' => __d('cake_ldap_field_name', 'Subordinate'),
            ],
            'fieldsConfig' => [
                'Employee.id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.department_id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.manager_id' => [
                    'type' => 'integer',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => [
                    'type' => 'guid',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                    'type' => 'telephone_name',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                    'type' => 'mail',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => [
                    'type' => 'photo',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => [
                    'type' => 'string',
                    'truncate' => true,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => [
                    'type' => 'date',
                    'truncate' => false,
                ],
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                    'type' => 'string',
                    'truncate' => false,
                ],
                'Employee.block' => [
                    'type' => 'boolean',
                    'truncate' => false,
                ],
                'Department.value' => [
                    'type' => 'department_name',
                    'truncate' => true,
                ],
                'Othertelephone.{n}.value' => [
                    'type' => 'telephone_description',
                    'truncate' => false,
                ],
                'Othermobile.{n}.value' => [
                    'type' => 'telephone_name',
                    'truncate' => false,
                ],
                'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                    'type' => 'manager',
                    'truncate' => true,
                ],
                'Subordinate.{n}' => [
                    'type' => 'element',
                    'truncate' => false,
                ],
            ],
            'id' => 'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com',
            'isTreeReady' => true
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'view',
                'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->excludeCommonAppVars($result);
            $this->assertData($expected, $result);
        }
    }

    /**
     * testEditEmptyGuid method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testEditEmptyGuid()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'edit',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid GUID for employee'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testEditInvalidGuid method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testEditInvalidGuid()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'edit',
                '6b798168-13f4-4fea-8dae-51a73a362499',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid GUID for employee'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testEditNotAllowedGuidForUserAndSecretary method
     *
     * User role: user, secretary
     * @return void
     */
    public function testEditNotAllowedGuidForUserAndSecretary()
    {
        $this->setExpectedException('MethodNotAllowedException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'edit',
                '0010b7b8-d69a-4365-81ca-5f975584fe5c',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
    }

    /**
     * testEditGetSuccessForUser method
     *
     * User role: user
     * @return void
     */
    public function testEditGetSuccessForUser()
    {
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $expected = [
            'dn' => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
            'guid' => '8c149661-7215-47de-b40e-35320a1ea508',
            'managers' => [],
            'departments' => [
                'АТО' => 'Автотранспортный отдел (АТО)',
                'ОИТ' => 'Отдел информационных технологий (ОИТ)',
                'ОРС' => 'Отдел распределительных сетей (ОРС)',
                'ОС' => 'Отдел связи (ОС)',
                'Охрана труда' => 'Охрана Труда (Охрана труда)',
                'СО' => 'Строительный отдел (СО)',
                'УИЗ' => 'Управление инженерных изысканий (УИЗ)',
            ],
            'fieldsLabel' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => __d('app_ldap_field_name', 'GUID'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => __d('app_ldap_field_name', 'Distinguished name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => __d('app_ldap_field_name', 'Initials'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surname'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Given name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Middle name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Position'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => __d('app_ldap_field_name', 'Department'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Landline telephone'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mobile telephone'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office room'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_name', 'Photo'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => __d('app_ldap_field_name', 'Company name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP telephone'),
            ],
            'fieldsLabelAlt' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => __d('app_ldap_field_name', 'GUID'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => __d('app_ldap_field_name', 'Disting. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => __d('app_ldap_field_name', 'Init.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surn.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Giv. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Mid. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Pos.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => __d('app_ldap_field_name', 'Depart.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Int. tel.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Land. tel.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mob. tel.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_name', 'Photo'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => __d('app_ldap_field_name', 'Comp. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP tel.'),
            ],
            'fieldsInputMask' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                    'data-inputmask-mask' => '(a{2,} a.[ ]a.|a.[ ]a. a{2,})',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [
                    'data-inputmask-mask' => 'a.[ ]a.',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [
                    'data-inputmask-mask' => 'a{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => [
                    'data-inputmask-mask' => 'a{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => [
                    'data-inputmask-mask' => 'a{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                    'data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => [
                    'data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)\#\№]{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => [
                    'data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                    'data-inputmask-mask' => '9{4}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    'data-inputmask-alias' => 'phone',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                    'data-inputmask-alias' => 'phone',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    'data-inputmask-alias' => 'phone',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                    'data-inputmask-mask' => '(9{1,4}[a{1}])|(9{1,4}-9{1})',
                    'data-inputmask-greedy' => 'false',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                    'data-inputmask-alias' => 'email',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [
                    'data-inputmask-mask' => '9{1,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => [
                    'data-inputmask-alias' => 'yyyy-mm-dd',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                    'data-inputmask-mask' => '9{2,}',
                ]
            ],
            'fieldsInputTooltip' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __d('app_ldap_field_tooltip', 'Display name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => __d('app_ldap_field_tooltip', 'Initials name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_tooltip', 'Surname of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_tooltip', 'Given name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_tooltip', 'Middle name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_tooltip', 'Position of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('app_ldap_field_tooltip', 'Subdivision of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => __d('app_ldap_field_tooltip', 'Department of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Interoffice telephone of employee. Format: %s, where X - number from 0 to 9', 'XXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Local telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Mobile telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Other mobile telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_tooltip', 'Office room of employee. Format: %s, where X - number from 0 to 9, L - letter', 'X(L)'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_tooltip', 'E-mail of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => __d('app_ldap_field_tooltip', 'Manager of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_tooltip', 'Photo of employee %dpx X %dpx in JPEG format', PHOTO_WIDTH, PHOTO_HEIGHT),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('app_ldap_field_tooltip', 'Computer of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('app_ldap_field_tooltip', 'Employee ID. Format: %s, where X - number from 0 to 9', 'X'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => __d('app_ldap_field_tooltip', 'Company name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('app_ldap_field_tooltip', 'Date of birthday. Format: %s, where YYYY - year, MM - month and DD - day', 'YYYY-MM-DD'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_tooltip', 'SIP telephone. Format: %s, where X - number from 0 to 9', 'XX'),
            ],
            'readOnlyFields' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            ],
            'maxfilesize' => UPLOAD_FILE_SIZE_LIMIT,
            'acceptfiletypes' => '(\.|\/)(jpe?g)$',
            'maxLinesMultipleValue' => 4,
            'employeePhoto' => base64_decode('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'),
            'forceDeferred' => false,
            'changedFields' => [],
            'pageHeader' => __('Editing employee')
        ];
        $userInfo = [
            'role' => USER_ROLE_USER,
            'prefix' => '',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'edit',
            '8c149661-7215-47de-b40e-35320a1ea508',
        ];
        $result = $this->testAction($url, $opt);
        $this->excludeCommonAppVars($result);
        $this->assertData($expected, $result);
    }

    /**
     * testEditGetSuccessForSecretary method
     *
     * User role: secretary
     * @return void
     */
    public function testEditGetSuccessForSecretary()
    {
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $expected = [
            'dn' => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
            'guid' => '8c149661-7215-47de-b40e-35320a1ea508',
            'managers' => [
                'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com' => 'Дементьева А.С. - Инженер',
            ],
            'departments' => [
                'АТО' => 'Автотранспортный отдел (АТО)',
                'ОИТ' => 'Отдел информационных технологий (ОИТ)',
                'ОРС' => 'Отдел распределительных сетей (ОРС)',
                'ОС' => 'Отдел связи (ОС)',
                'Охрана труда' => 'Охрана Труда (Охрана труда)',
                'СО' => 'Строительный отдел (СО)',
                'УИЗ' => 'Управление инженерных изысканий (УИЗ)',
            ],
            'fieldsLabel' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => __d('app_ldap_field_name', 'GUID'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => __d('app_ldap_field_name', 'Distinguished name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => __d('app_ldap_field_name', 'Initials'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surname'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Given name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Middle name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Position'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('app_ldap_field_name', 'Subdivision'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => __d('app_ldap_field_name', 'Department'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Landline telephone'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mobile telephone'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Personal mobile telephone'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office room'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => __d('app_ldap_field_name', 'Manager'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_name', 'Photo'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('app_ldap_field_name', 'Computer'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('app_ldap_field_name', 'Employee ID'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => __d('app_ldap_field_name', 'Company name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('app_ldap_field_name', 'Birthday'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP telephone'),
            ],
            'fieldsLabelAlt' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => __d('app_ldap_field_name', 'GUID'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => __d('app_ldap_field_name', 'Disting. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => __d('app_ldap_field_name', 'Init.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surn.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Giv. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Mid. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Pos.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('app_ldap_field_name', 'Subdiv.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => __d('app_ldap_field_name', 'Depart.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Int. tel.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Land. tel.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mob. tel.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Person. mob. tel.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => __d('app_ldap_field_name', 'Manag.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_name', 'Photo'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('app_ldap_field_name', 'Comp.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('app_ldap_field_name', 'Empl. ID'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => __d('app_ldap_field_name', 'Comp. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('app_ldap_field_name', 'Birthd.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP tel.'),
            ],
            'fieldsInputMask' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                    'data-inputmask-mask' => '(a{2,} a.[ ]a.|a.[ ]a. a{2,})',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [
                    'data-inputmask-mask' => 'a.[ ]a.',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [
                    'data-inputmask-mask' => 'a{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => [
                    'data-inputmask-mask' => 'a{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => [
                    'data-inputmask-mask' => 'a{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                    'data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => [
                    'data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)\#\№]{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => [
                    'data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                    'data-inputmask-mask' => '9{4}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    'data-inputmask-alias' => 'phone',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                    'data-inputmask-alias' => 'phone',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    'data-inputmask-alias' => 'phone',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                    'data-inputmask-mask' => '(9{1,4}[a{1}])|(9{1,4}-9{1})',
                    'data-inputmask-greedy' => 'false',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                    'data-inputmask-alias' => 'email',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [
                    'data-inputmask-mask' => '9{1,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => [
                    'data-inputmask-alias' => 'yyyy-mm-dd',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                    'data-inputmask-mask' => '9{2,}',
                ]
            ],
            'fieldsInputTooltip' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __d('app_ldap_field_tooltip', 'Display name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => __d('app_ldap_field_tooltip', 'Initials name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_tooltip', 'Surname of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_tooltip', 'Given name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_tooltip', 'Middle name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_tooltip', 'Position of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('app_ldap_field_tooltip', 'Subdivision of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => __d('app_ldap_field_tooltip', 'Department of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Interoffice telephone of employee. Format: %s, where X - number from 0 to 9', 'XXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Local telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Mobile telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Other mobile telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_tooltip', 'Office room of employee. Format: %s, where X - number from 0 to 9, L - letter', 'X(L)'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_tooltip', 'E-mail of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => __d('app_ldap_field_tooltip', 'Manager of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_tooltip', 'Photo of employee %dpx X %dpx in JPEG format', PHOTO_WIDTH, PHOTO_HEIGHT),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('app_ldap_field_tooltip', 'Computer of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('app_ldap_field_tooltip', 'Employee ID. Format: %s, where X - number from 0 to 9', 'X'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => __d('app_ldap_field_tooltip', 'Company name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('app_ldap_field_tooltip', 'Date of birthday. Format: %s, where YYYY - year, MM - month and DD - day', 'YYYY-MM-DD'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_tooltip', 'SIP telephone. Format: %s, where X - number from 0 to 9', 'XX'),
            ],
            'readOnlyFields' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            ],
            'maxfilesize' => UPLOAD_FILE_SIZE_LIMIT,
            'acceptfiletypes' => '(\.|\/)(jpe?g)$',
            'maxLinesMultipleValue' => 4,
            'employeePhoto' => base64_decode('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'),
            'forceDeferred' => false,
            'changedFields' => [],
            'pageHeader' => __('Editing employee')
        ];
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_SECRETARY,
            'prefix' => 'secret',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'edit',
            '8c149661-7215-47de-b40e-35320a1ea508',
            'prefix' => 'secret',
            'secret' => true,
        ];
        $result = $this->testAction($url, $opt);
        $this->excludeCommonAppVars($result);
        $this->assertData($expected, $result);
    }

    /**
     * testEditGetSuccessForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testEditGetSuccessForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $expected = [
            'dn' => 'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com',
            'guid' => '1dde2cdc-5264-4286-9273-4a88b230237c',
            'managers' => [],
            'departments' => [
                'АТО' => 'Автотранспортный отдел (АТО)',
                'ОИТ' => 'Отдел информационных технологий (ОИТ)',
                'ОРС' => 'Отдел распределительных сетей (ОРС)',
                'ОС' => 'Отдел связи (ОС)',
                'Охрана труда' => 'Охрана Труда (Охрана труда)',
                'СО' => 'Строительный отдел (СО)',
                'УИЗ' => 'Управление инженерных изысканий (УИЗ)',
            ],
            'fieldsLabel' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => __d('app_ldap_field_name', 'GUID'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => __d('app_ldap_field_name', 'Distinguished name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => __d('app_ldap_field_name', 'Initials'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surname'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Given name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Middle name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Position'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('app_ldap_field_name', 'Subdivision'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => __d('app_ldap_field_name', 'Department'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Landline telephone'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mobile telephone'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Personal mobile telephone'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office room'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => __d('app_ldap_field_name', 'Manager'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_name', 'Photo'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('app_ldap_field_name', 'Computer'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('app_ldap_field_name', 'Employee ID'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => __d('app_ldap_field_name', 'Company name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('app_ldap_field_name', 'Birthday'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP telephone'),
            ],
            'fieldsLabelAlt' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => __d('app_ldap_field_name', 'GUID'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => __d('app_ldap_field_name', 'Disting. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => __d('app_ldap_field_name', 'Init.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_name', 'Surn.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_name', 'Giv. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_name', 'Mid. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_name', 'Pos.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('app_ldap_field_name', 'Subdiv.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => __d('app_ldap_field_name', 'Depart.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Int. tel.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Land. tel.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Mob. tel.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Person. mob. tel.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_name', 'Office'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_name', 'E-mail'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => __d('app_ldap_field_name', 'Manag.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_name', 'Photo'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('app_ldap_field_name', 'Comp.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('app_ldap_field_name', 'Empl. ID'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => __d('app_ldap_field_name', 'Comp. name'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('app_ldap_field_name', 'Birthd.'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_name', 'SIP tel.'),
            ],
            'fieldsInputMask' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                    'data-inputmask-mask' => '(a{2,} a.[ ]a.|a.[ ]a. a{2,})',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [
                    'data-inputmask-mask' => 'a.[ ]a.',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [
                    'data-inputmask-mask' => 'a{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => [
                    'data-inputmask-mask' => 'a{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => [
                    'data-inputmask-mask' => 'a{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                    'data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => [
                    'data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)\#\№]{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => [
                    'data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                    'data-inputmask-mask' => '9{4}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    'data-inputmask-alias' => 'phone',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                    'data-inputmask-alias' => 'phone',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    'data-inputmask-alias' => 'phone',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                    'data-inputmask-mask' => '(9{1,4}[a{1}])|(9{1,4}-9{1})',
                    'data-inputmask-greedy' => 'false',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                    'data-inputmask-alias' => 'email',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [
                    'data-inputmask-mask' => '9{1,}',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => [
                    'data-inputmask-alias' => 'yyyy-mm-dd',
                ],
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                    'data-inputmask-mask' => '9{2,}',
                ]
            ],
            'fieldsInputTooltip' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __d('app_ldap_field_tooltip', 'Display name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => __d('app_ldap_field_tooltip', 'Initials name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('app_ldap_field_tooltip', 'Surname of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('app_ldap_field_tooltip', 'Given name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('app_ldap_field_tooltip', 'Middle name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('app_ldap_field_tooltip', 'Position of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('app_ldap_field_tooltip', 'Subdivision of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => __d('app_ldap_field_tooltip', 'Department of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Interoffice telephone of employee. Format: %s, where X - number from 0 to 9', 'XXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Local telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Mobile telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => __d('app_ldap_field_tooltip', 'Other mobile telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('app_ldap_field_tooltip', 'Office room of employee. Format: %s, where X - number from 0 to 9, L - letter', 'X(L)'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('app_ldap_field_tooltip', 'E-mail of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => __d('app_ldap_field_tooltip', 'Manager of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('app_ldap_field_tooltip', 'Photo of employee %dpx X %dpx in JPEG format', PHOTO_WIDTH, PHOTO_HEIGHT),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('app_ldap_field_tooltip', 'Computer of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('app_ldap_field_tooltip', 'Employee ID. Format: %s, where X - number from 0 to 9', 'X'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => __d('app_ldap_field_tooltip', 'Company name of employee'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('app_ldap_field_tooltip', 'Date of birthday. Format: %s, where YYYY - year, MM - month and DD - day', 'YYYY-MM-DD'),
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('app_ldap_field_tooltip', 'SIP telephone. Format: %s, where X - number from 0 to 9', 'XX'),
            ],
            'readOnlyFields' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            ],
            'maxfilesize' => UPLOAD_FILE_SIZE_LIMIT,
            'acceptfiletypes' => '(\.|\/)(jpe?g)$',
            'maxLinesMultipleValue' => 4,
            'employeePhoto' => '',
            'forceDeferred' => false,
            'changedFields' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO,
            ],
            'pageHeader' => __('Editing employee')
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'edit',
                '1dde2cdc-5264-4286-9273-4a88b230237c',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->excludeCommonAppVars($result);
            $this->assertData($expected, $result);
        }
    }

    /**
     * testEditPostBadData method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testEditPostBadData()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'BAD_MODEL' => []
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'edit',
                '8c149661-7215-47de-b40e-35320a1ea508',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Information about the employee could not be been saved. Please, try again.'));
        }
    }

    /**
     * testEditPostInvalidData method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testEditPostInvalidData()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'EmployeeEdit' => [
                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '8c149661-7215-47de-b40e-35320a1ea508',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Хвощинский В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Хвощинский',
                    CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Виктор',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Владимирович',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Начальник отдела',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОИТ',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '320',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375172000012',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '217',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'v.hvoshchinskiy@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0386',
                    CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1304',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                    CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-08',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501321',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => ['+375296000001', '+375172000001'],
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
                ]
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'edit',
                '8c149661-7215-47de-b40e-35320a1ea508',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Information about the employee could not be been saved. Please, try again.'));
        }
    }

    /**
     * testEditPostDataNotChanged method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testEditPostDataNotChanged()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'EmployeeEdit' => [
                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '8c149661-7215-47de-b40e-35320a1ea508',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Хвощинский В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Хвощинский',
                    CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Виктор',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Владимирович',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Начальник отдела',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОИТ',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '320',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000004',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '217',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'v.hvoshchinskiy@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0386',
                    CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1304',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                    CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-08',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501321',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => ['+375171000008', '+375171000009'],
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
                ]
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'edit',
                '8c149661-7215-47de-b40e-35320a1ea508',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Information about the employee could not be been saved. Please, try again.'));
        }
    }

    /**
     * testEditPostValidDataForUserAndSecret method
     *
     * User role: user, secretary
     * @return void
     */
    public function testEditPostValidDataForUserAndSecret()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'EmployeeEdit' => [
                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '8c149661-7215-47de-b40e-35320a1ea508',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Хвощинский В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Хвощинский',
                    CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Виктор',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Владимирович',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Главный специалист',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОИТ',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '320',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000010',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '217',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'v.hvoshchinskiy@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0400',
                    CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1304',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                    CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-08',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501321',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => ['+375171000008', '+375171000009'],
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
                ]
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'edit',
                '8c149661-7215-47de-b40e-35320a1ea508',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Deferred saving with an updated employee information was created.<br />Information will be updated after approval by the administrator.'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testEditPostValidDataForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testEditPostValidDataForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'EmployeeEdit' => [
                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '8c149661-7215-47de-b40e-35320a1ea508',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Хвощинский В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Хвощинский',
                    CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Виктор',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Владимирович',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Главный специалист',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОИТ',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '320',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000010',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '217',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'v.hvoshchinskiy@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0400',
                    CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1304',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                    CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-08',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501321',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => ['+375171000008', '+375171000009'],
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
                ]
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'edit',
                '8c149661-7215-47de-b40e-35320a1ea508',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__(
                'Deferred saving with an updated employee information was created.<br />Information on LDAP server will be updated by queue.<br />Information in phonebook will be updated %s after processing.',
                CakeTime::timeAgoInWords(strtotime('+' . DEFERRED_SAVE_SYNC_DELAY . ' second'), ['accuracy' => ['second' => 'minute']])
            ));
            $this->checkRedirect(true);
        }
    }

    /**
     * testSyncForHrAndAdminEmptyGuid method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testSyncForHrAndAdminEmptyGuid()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'sync',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__d('cake_ldap', 'Synchronization information of employees put in queue...'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testSyncForHrAndAdminWithGuid method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testSyncForHrAndAdminWithGuid()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'sync',
                '1dde2cdc-5264-4286-9273-4a88b230237c',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__d('cake_ldap', 'Synchronization information of employees put in queue...'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testGalleryNotAllowed method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testGalleryNotAllowed()
    {
        $this->setExpectedException('MethodNotAllowedException');
        $result = $this->addExtendedFields(CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO);
        $this->assertTrue($result);

        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'gallery',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
    }

    /**
     * testGalleryForUser method
     *
     * User role: user
     * @return void
     */
    public function testGalleryForUser()
    {
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $expected = [
            'employeesGallery' => [
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Миронов В.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Миронов В.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'В.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        'id' => '1',
                    ],
                    'OrderEmployees' => [
                        'id' => '1',
                        'lft' => '1',
                    ],
                    'DepartmentExtension' => [
                        'id' => '1',
                        'lft' => '1',
                        'name' => 'Управление инженерных изысканий',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        'id' => '3',
                    ],
                    'OrderEmployees' => [
                        'id' => '3',
                        'lft' => '3',
                    ],
                    'DepartmentExtension' => [
                        'id' => '2',
                        'lft' => '3',
                        'name' => 'Отдел связи',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Егоров Т.Г.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Егоров Т.Г.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Т.Г.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAPAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQABgQEBAUEBgUFBgkGBQYJCwgGBggLDAoKCwoKDBAMDAwMDAwQDA4PEA8ODBMTFBQTExwbGxscHx8fHx8fHx8fHwEHBwcNDA0YEBAYGhURFRofHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8f/8AAEQgAyADIAwERAAIRAQMRAf/EAG0AAAIDAQEBAAAAAAAAAAAAAAIDAAEEBQYIAQEBAQEBAAAAAAAAAAAAAAAAAQIDBBAAAgICAgIBBAIDAQAAAAAAAAECAxEEIQUxEiJBURMGMkJhUhQWEQEBAQEBAAAAAAAAAAAAAAAAAQIREv/aAAwDAQACEQMRAD8A+qQBn4AxbXhlHB3VywOVauQhfqBaiASiASQBIBiZQXsgLU0AxTQRbkUAyKBoAXECvUgrADK/IHS1PoRXZ1vCA2xAsCAVLwBh2Vwyjibi8gcuyPJUAogEoAF6AU4gBKSRAqe1GP1ARPsYL6lArtIfcBtfZQf1CNNe5GX1A0RsUgo0sgT0ApwIAcQCguQOjqLlEV2tbwgNkQLAgFSAx7K4YHG24+TSOZZDkAFEA1EAvUBVrUUBy9zcjBPkDgb3cxhn5EHE2P2HDfyAy/8Ao+f5Aadf9jy18gO1pd6pNfIqPQaXYxmlyFdei1SQGlLIEcAAcAJCHJB0NWPKCuxr+EQa4gWBAKkBlvXBRydqPko5tkOQgPQAlECpcIDmb+yoRfIHj+47T19uSDxXZ9xLL5Irz+x2k23yQZX2U8+QHU9rNPyOjtdd3Uk18i9R7Lp+5z6/Io9p1m+ppclHeosUkgHpZApwAuNfIG3Wj4IrqULgg1RAsCAVIDNeuCjmbMfJRgshyVAegE9AM+zL1iyDyvdbfqpcko8B3O3KTlhmbWuPK7asm2Tq8c+zWsf0J04RLVmvoOnFRommOnGzVVkWi9OPS9Tszi1yOpx7vpN9/HLNSpx7Tr9j2iuSo69XKKG+oBRhyBroiRXQpXBBoiBYEAqQCLlwUc69FGOceSoD0AqUcIDl9hLEWQeI7y1v2M1qR47bplZNmLW5CodPKf0MddJkx/r0mv4jp5Is/Xpf6l6nlnfQST/iOr5FDpJL+pOr5a6OulW1wJUuXoOr9oSRuVyse26i1tI6RivTazzFFRrSAKKA00oit1XggegLAgFMBNvgowXIoyyjyVA+gA2R4A4na8RZKrwvbrMmYrcjlU6inPwcrXbMd7Q6mMkuDHXWR1q+jg1/EdOBs/X4v+pes8ZbP1+P+pOtSEy6KK/qTrXGS/qVH6CVLkqnW9J+DpmuGo9L1PGDrHGx6rT/AIo2w3JcAEgNFJFbqiB6AgEAjARaUYrkUZ3HkqIogBbH4gcDt18WZrUeH7NfNnPTpmEacF7o5V6Mx6nrVHCMOjv68YNIsStDog14KyRbrQ+wWMd1EEnwZrccncpjyQscuVSUzpmuOo63WRw0dZXn1HqdJfFHSOdb1HgqLSA0VIitlZA9AQCARgJsKMlqKhDRREgAtXxIrz/bRzFma1I8T2Vb92c9O2Yya6akcq75jv6FzWDLbuauzwinHQhfwOpwu24dXjDdcRZHO2Je2SLxi/HmRqVjUdXr6sNHXNefUel04/FHWOFjfGPBpgSgA6uJFaq0QOQEAgEYCplGWxFCGioiACxcEWOL2VWYszXTLyPY62ZPg46ejMc+Gu1I512kdHVqksEb46uumiLxthJ4J1eJPLHTjNZW2VOM86GyKGGvyWMWOnpUYaO2XDcdzVhhI6x5tN8I8G3MaiENhEinwRAxAQCARgKsKM1hQiRUUgJKPBFjm71WUzFdMvNb+v8AJ8HLT04YYa69vByr0Ruo1l9iN8a41YIpkURTYwyEovwJmmS566+xAEaFksZ03a1WMHbLzbrq0QwkdY8+muCNOZiQQyKCmxIDAgEAjAVPwUZbWUIZUWgCa4IrHtV5TM1vLgb1PLOWo9GKwKtKRysejNbKUsGXWGyaQaB78k4p9UgzWqOMFZVKKCBjXlmpHPVbdeo65jzbroVQwjrHC0+KKyNAHEBsSAgIBAIwFWeCjJayhDfJQUQGLwQIvjlEqxxN6C5OdjrmuTPCkcq9OKZXYc69GaKVnAdIX+TkNcPpsDNjbXZwGLB+2Sxzp1Ucs3I4aroUVnWPPqtkI8G3OjwVlaAZEKZEgICAQCMBNhRjtZYEN8lBRYDUwFXPgg4u+/Jit5rhXzxI46ejFBC0516c073yZds1EnkOh9YStMJ4Dlqmwnyajjqt2vy0dcvNuuprrhHWOFrXFcFZXgorAQcUFMRAQEAgEYCbHwUYrmaGdvkqIpBR/kIE3W8Eo4+9LOTFWOFs5yzlXbNZ4yaZzsd86aap5Jx3zpphhk46ez48DiXQkxxy1o+rLZqRx1p0tVeDrHn1XUo8HSOda4sqCwUTABJAGiCwIBAKYCbfBRguZqIzSlyUV7gVKwz1Wa63glo5+w/bJijnXU5MVuVllQ0zNjpNLhBonHWbaq8jjfs+KbJxLs6FbY4xdNlFLNSOV06FMMGo52tlbwbjLTCZoNTAIAkAaAgEAgFSAz3Pgo597KMkpcl6isk6oZMzaM9iZnozThkgW6ckUuWrn6EWUP8Ayf4HGpoyGq/sTjXo+vWf2HE9NVWt/gcS1srpwVjp8YYKg0VDYSKHRmVTIyKGRYBoCwIBAKYGa7wBzryjK1yTqIkTqo4kC51kCZVEFKoA1UgCVCCjjrr7A6dChEOnQqSCGxiAXqUX6gWkUEmUHGRQ6Eih0WAQEAgFMDPd4A596IMzjyQFGIBehBTrACVQA/iILVYBqABxgAyMQDUQGKIBKIBegFOJRWAIkUOgUPgUGgIBAKkBntAxWrkyEeoBKJASiBfqBPQCvxoCfjQF/jAJQAJQANQAYogEogXgCmgBaAiQDIIodEoMogEAqQCLfBBjtRAnBASQBJAWkBaQBKIF+pRPUC1EAlEA1EAkgLwBTIBYEAiRQyJQ2JQQEAgFMBNpBjsRAogtAGkBeALSAJFF4AJIC0igkgLwBZBAKYAsCAWgGRKGIoICAQCMBNhBjtAQ2QEmQMQBAQAkUWgCSAJIosCZAgFkFMAWBACQBxKGIoICAQCMBNhBiuYGVy5IDhIB0QDyBMgEgDQBJAWUUBMgQAkBTAFgUASYDIsBkSggIBAI/ACLXwBz9iRBjlPkgbVLIGmL4ALIETAZEA0AQEZRWQJkC0ASAgAsAQImAyLAbEoMCAf/2Q=='),
                        'id' => '2',
                    ],
                    'OrderEmployees' => [
                        'id' => '2',
                        'lft' => '4',
                    ],
                    'DepartmentExtension' => [
                        'id' => '2',
                        'lft' => '3',
                        'name' => 'Отдел связи',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Дементьева А.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Дементьева А.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'А.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        'id' => '4',
                    ],
                    'OrderEmployees' => [
                        'id' => '4',
                        'lft' => '10',
                    ],
                    'DepartmentExtension' => [
                        'id' => '3',
                        'lft' => '5',
                        'name' => 'Отдел информационных технологий',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Хвощинский В.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'В.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Начальник отдела',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        'id' => '7',
                    ],
                    'OrderEmployees' => [
                        'id' => '7',
                        'lft' => '11',
                    ],
                    'DepartmentExtension' => [
                        'id' => '3',
                        'lft' => '5',
                        'name' => 'Отдел информационных технологий',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Козловская Е.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Козловская Е.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Е.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Заведующий сектором',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        'id' => '6',
                    ],
                    'OrderEmployees' => [
                        'id' => '6',
                        'lft' => '12',
                    ],
                    'DepartmentExtension' => [
                        'id' => '3',
                        'lft' => '5',
                        'name' => 'Отдел информационных технологий',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Матвеев Р.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Матвеев Р.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Р.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAPAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQABgQEBAUEBgUFBgkGBQYJCwgGBggLDAoKCwoKDBAMDAwMDAwQDA4PEA8ODBMTFBQTExwbGxscHx8fHx8fHx8fHwEHBwcNDA0YEBAYGhURFRofHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8f/8AAEQgAyADIAwERAAIRAQMRAf/EAG4AAAEFAQEBAAAAAAAAAAAAAAMAAgQFBgEHCAEBAQEBAQAAAAAAAAAAAAAAAAECAwQQAAICAgICAQQCAwEAAAAAAAABAgMRBCEFMSISQVEyBhMUYUJSIxEBAQEBAQAAAAAAAAAAAAAAAAERAhL/2gAMAwEAAhEDEQA/APqkDjAFYajNRpnSMUM2y6kQOSIpyQDiK6kRXSK6B3AVzAQsALACwAsAcwAsFQkgHxRmqJEzWjyKQCA4wBWGozUaZ0jFMwaZOSCnJEHURXSKTkkQcdqQU3+eP3Ipf2I/cgX9iP3Gh8bYsugikmA7ACwAviBz4gdwB1IgeiKcRSAQCYAZmozUeZ0jFMNI6gHoiu5IBztSIqHfuxj9SKr7u2hH/YyIs+7iv9iaob72P/RNCj3sc/kNE3X7iMmvYui11t2M8cgT67E0VRVyB3ACwBzAHUgHIiukCAQCYAZmozUeZ0jFMNI6gHZIoVtqiiCq3uwjBPkzVZnsu9Uc+xm1Wb3P2Ll+xm1VdZ+xPP5GdAn+wy/6Jquw/YXn8hotNH9geV7FlRqur7tSx7GhqdHsIzS5Kq2qtUkAdchXcAIBYAciBAIBAJgBmajNR5nSMUPJpHUwGznhEFXv7ihF8kqsZ3Xc/H5exiqw3ad3JyeJGK1Iz9/ZTk3yZ1rEaW7P7kXA3uT+5lMOhuTz5C4sNTfnFrkupjTdV3EotexrUxt+m7j5fH2Lo2XX7qnFclFxVPKCjIBAIo6iBAIBAJgCmajNRpnSMUJs2hZCI2zbiLIMp3m64xlyZqx5x3vZScpcnOtxkti+U5vk52ushkaJSM63OTnqS+xNPJj05fYHk6GlL7EPKRDWlEaeUzXslXJFlYsaXp+xlGUeTUrNehdH2HyUeTcZbDTu+UUVU+L4A7kiulHUQIBAIBMAMzUZqNYdYxQmzbJkmEQN2eIsYMR+w2vEjFajzjt3KU5HHp15VdVDlI42u3MWevpppcGNdZEtaKf0Grhy61P6F1MEj1qX0JpjlmikvBnTEG6j4ssrFg+hY42I68uNje/r2w/Xk6xivQOsszBFxNXEJcDFPyMV1MByIrpAgEAmAGw1Gai2HWMUGTNxgOTNIgbv4siMT38cqRz6bjAdlV/6M4dO/KHTWkzjXflZUSikYdImVziXGkmDgAT5QJQG2UWjKKzZimWMUPVg/wCRHblw6bf9ei8xO0cq9B6ziCN4mriEuBhoiZFPTIp6MqcRSAQHGAKw3Gai2HSMVHkzpGKZJlZQdvmLAyHd15Ujn01ywvZVe7PP09HKracWca7w5XNExsavZf3KqVDaePIV2W2/uMA3tNmbGdMcvkJGal6NGZo68xx6bfotfHx4O8jjW30I4ijTKzg+Ap6ZFEizNUSJlo8ikAgOMANhuM1EsOvLnQJM6RmhyZWUTZXqwjM9vXlM59OnLFdnR7M8/T0cKW2nk4V3kR5VMjWFGDTK1gsc4C45LITHYxeSM4lUVNtFjNXvWauZLg68xx6bXp9bCXB3kcK1GrHEUaZTYkU9MlUSJmtQWJho9EUgEBxgBsNxmodrOvLnUeTOsYocmVlGv8MUUPZwymc+m+WQ7Krlnn6enhSW1rJwsenkCdaMt4DKKQXDU0Fw+MUwYPXUgzYnatPKNSOfTR9Xr8rg78x5+2w62rCR2kcKvKVhIrKQjLRyZKosTNag0TLR6MqQCA4wAWm4zUO1nblyqNJnWMUOTKgFvgVFN2EcpnPpvllOzhyzh1Hp4rP7HDZxsenmos5GMdYi2zGNAq3kYJNM8gTqcPBEqz04ZaN8xx7afq6/B6OY8vdarRhiKOscatK/BEFTIp6ZGhYGKsGgZrYiMqQCA5IoBb4NRioVp25c6jSO0YocisgW+CCr3VwzFajLdpHyceo78VmNt4kzhXq5qvsmYdoi2yDcBTeQqVRIgsKJ+BGLV1oYbR15jz91rOqh4O/MeXqtPqLhHRyT4EBEyNHpmVFgzNag8DFagiMtEAgOSKAW+DUYqDd5O/LnUaZ1jFDkyso9s0kQVW7asMzVjK9rcueTj07csnvbC+T5OHT08q2V6b8nOu/JjmmR0hqxkNDQkkRKl03pNcljn1V51lyckduXm7radTNNI9HLy9Vp9V+qNsJsGRREzKnJkUaDM1qDwOdbgqMtEAgOSCAW+DcZqBc0duXOolk0jrGKi23pfUaiv2t2KT5Jooew7FJPkzasjJ9p2KeeTl1XXlld3dzJ8nHp6OUH+3z5OVd4JDa/yRuUVbCDWu/2V9wlp0NvnyWOfS66vc9lydua83bedJtJqPJ35rz9NfpXpxRvWFjXNNBRVIinpkUatmK1EmBzrcFRlogEByQEW+eEbjFVW1sKOeTrK51UbG/FZ5N6zir2uzSzyS9Jik3u288mb0uM32Hat55M3pqRnN7ecs8mLW4pdm9tvkxXflEdryYsdZT43MmN6Kr2Rdd/nZE0oXPJYzVz1l7UlydJXDpuel3MKPJ1nTh1Gx0N5YXJudOeLijdTXk1omV7Kf1KJELUwqTVIxWol1s510gqMNOgIBk3wBW713xizUZrLdp2HxzyblYsZjd7fl8j0zio2O0bzyZvS4qNzek88k0xRbe223yTVVOxsN55K1EC23LI6ShqWWZsdZRoRyTG4KoMmNE4smI5HhhmrPQtxJFjj01XWbnxS5NSuNaTU7PCXJqdMYttftlxybnSYtNftU8cmp0LLX7BPHJrRaa20njkza1FnRPKMV0iVFmGnQEAK18AUXa2YjIJWB7zaacuS6yyG3tycnyTUxDldJk0RNiTwTRT7cnyagqb7HlmoIc5vJcalOrlyTHSVOowzOOsqVGKwRs2aSJhajTmkxjnaJr7Pxa5Ljj1V3pb+Mckc6utfsHhchE6nsmscl1MWWt2j45NToxc6faN45NejGj67f8AljkasabSu+SRmtxZ1vgjR4CADd+IGd7l+sgjznvp+0iIyWxL3ZEDXggBseGBS7n1NRFVdBtm4ASoZocUGmRqVJplgjrOkyE+CY3OjbZcEwvSBfMuOdoEbWpDHO1Zad8solZXetc2kZon12sgm690s+Rot9LYllcl0anqL22i6ra9XNuKK1F7U+AooCADf+IGa7qXpII8276ftIiMndL3ZEcUuCCPsS4KKrYjllgjf18vwa1HXqceC6I9ms19BoHGpphqVKrreA16Kyp4CXpBvpYZtRlS8hE7UqaaJUXWqsJGKqdWzIl0PkC20nygrVdO+Ymorc9V+KKq/p8FUYBAA2PxYGX7x+sgjzXvn7SIjLWr3ZEJReAAXwYEKdWWA6vXz9CoP/V48DRFv1P8F0Rlq8+C6JNWtx4Ggk9TjwNEK7T/AMDRG/qc+CaJFGvj6E0T6oYRkGTwQHps5AttG3lBWu6aWXEqt31L9UaVoKfBVGAQANj8WBlu8/GQR5t3v5yCM1Ne5EPjBYIA3RQERwWQD01oCWqlgiI99KKIbrSZQWqKCDfFNBQLaUyCLKhZAJVSiCSquAGTg0A2GUwLTRk/kgNl0j5iVW/6j8YlajQ0+CqMAgAbH4sDKd7L1kEead7Z7yCM3KxfMiCRsWCAN1iAiuxZAkUWICbGawAC+SCIM5clHYTAkRlwByckQR5NZIC1YAkxSwQDsiigKXJRYaK9kBs+jXMSq3/UL1iVpoafAUYBAR9n8WBkP2CWISKjy79gtxOQRl5X+4QWN3BMQO20KjOzkCTRYRE2FnAA7pgQpy5KH1hEmPggZY8IKizswyB9V3IEuF3BAp2ooD/KslFhoWL5IDa9FJZiFeg9Q/WJWmgp8BRgEBG2fxYGN/Yn6SKjyj9il7yKyy0pv5hBq5PBA2xsADfIVIokETq3wQNt8ARZLkA1MSolRjwQCujwBAtTyRQ4zaYB4XPACncAJXclFl193sgNz+v2ZcQr0bpnmMQ00dP4hRgP/9k='),
                        'id' => '5',
                    ],
                    'OrderEmployees' => [
                        'id' => '5',
                        'lft' => '7',
                    ],
                    'DepartmentExtension' => [
                        'id' => '4',
                        'lft' => '7',
                        'name' => 'Отдел распределительных сетей',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Чижов Я.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Чижов Я.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Я.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер 1 категории',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
                        'id' => '10',
                    ],
                    'OrderEmployees' => [
                        'id' => '10',
                        'lft' => '17',
                    ],
                    'DepartmentExtension' => [
                        'id' => '4',
                        'lft' => '7',
                        'name' => 'Отдел распределительных сетей',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Голубев Е.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Голубев Е.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Е.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Водитель',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAPAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQABgQEBAUEBgUFBgkGBQYJCwgGBggLDAoKCwoKDBAMDAwMDAwQDA4PEA8ODBMTFBQTExwbGxscHx8fHx8fHx8fHwEHBwcNDA0YEBAYGhURFRofHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8f/8AAEQgAyADIAwERAAIRAQMRAf/EAHAAAAEFAQEBAAAAAAAAAAAAAAUAAQMEBgIHCAEAAwEBAQAAAAAAAAAAAAAAAAECAwQFEAACAgICAQQDAQEBAQAAAAAAAQIDEQQhBTFBEiIyYRMGFFGBIxEBAQEBAQEBAAAAAAAAAAAAAAECERIDE//aAAwDAQACEQMRAD8A+qQBMAYAQAgBADNgEcrEgCvZsJeounIq27aXqLq5lVs3fyL0uYRf7fyL0fh1Hd/I/ReE8Nxf9H1Nys17SfqPqLlZhamNNiaMsjS6QB0AIAQGQA6EDgZACAEwBgBACAGbAIbLMAajfspepNqpA+/c/JNrTOVK3bb9SLptnCvO9si6azCP9zF6V4Or2vUPQ/N3Dba9SppF+a1Tu/kuaZa+Yjr7ifqXKx1gQpvTLlZWLUZZGl2gI4AhGQA6AHAyAEAJgDACAEwCKyeEBh+zfhPkm1UgPtbXnki1tnIdZsNvyZXTozhH72yLW8wdIi1cyf2i6rjiSDp+UUpND9FcFG9p+Sppnr5ruvttNcmmdOffzF9Tazjk2lc2si1FuUjSVhYtRlkaXYAgBCB0AOBkAIATAGAEAcyYBU2LMJiVIDbt/ki1tmAuxc22Y6rpxlX92WZWunOUsET1pIlSA+OsCNxJCOK9iF1UivJ4DouXVdrTLzphvAnp7HK5N86cm8DunflI3lcmsilU8o0Y2J0BHAEAOgBxGQAgBMAYATAIrJYQGG7duEyavMAd27lmOq6cQLsnlmOq68Q0GQ3kTwEriVMAfIBzJiNDZgmqirYJSL3YY4jUW9a3DRvmuT6ZHdG7wdGa4twc1p5SNY5tRdi+CkOgBADoQOBkAIATAGAGYBXvlhAcBt6zyZ6rbMANuzlmOq6sRQlPkxrqy7jNEtYljagU7VqEHX7EA45nakBq9l6/6I1ad6EfUX7VkIm1Pr2co0y59jvX2eDozXH9I0OnLhG8cmhGD4LZVIBEAOhGcDIAQAmAMAcyAKmzLhiqoA78/JlpvgA2p8sx06sKM7cMzroiN7KXqSuU3+z8iVEle3n1EuJ47HHkXVcRXbOF5Do4oXbnPkCQPbz6gTqGxkE1d1rOUaZZaH+vn4N8uT6RpdKXCN8uPYpW+DRjUiAjgDoRnAyAEAJgDAEc3wBqG3ZhMmrkZ/fs8mWq3xAHanyzCurMDbrGiK2kUrb2JUQf6JZFVxPRdLJNaSL1djwJfEWxY8AOBl1ksgViFTlkabFiqbBNgjqzeUXGWo0HX2eDbNc2402hZwjoy49wYpllGkc9ToZHEDoAcDIAQAmAcsAgunhMVVID7t+MkWts5ANy/LZjqunGQi+WcmVrozkPui2Q1kUrKmxL8o1Q8gqZWKaWiWki5CDSErji6vKAcUbaG2McRf52NNiWFLQk8XKItNDiLkY0rMNG2a595aPr7vB0Zri+mR7WnlI1jl1FyLKZugI6AziMgBACYBzLwAU9qWEya0yz3Y2tZMtV1YjP7NuWzHVdWcqjeTO1tmOHVkhtI4esn6AuZc/5l/wDkdRqSBXHeEhHxxNJgOI3SmA4X+df8GXC/QkIcdKGAibFnXnho0zWG8jvX28o6M1w/TLS6U8pG8cO4IwfBbGpAI4GcRkAIATAOZeACjtrhk1plm+zT5MdOz5s7st+5mGnXlDF8mdbZSxE2kd4QluJ4QGrzsSGEUr0Buf3piCSuaYGsRw0AKSQhxFIZWHqfJcYbg110uUb5cP1ajQfCOjLz/oK1+DRz1KBHEZwMgBACYBy/ABU2Y5iyavLPdlX5MtOv51mtuvEmYadeKqeGZV0ZqSMyW2Xf7OAaILbOAJRutGOqsrmA6aNryI+rdFgjXa58CN1KYGilIaa6q+xpGG6N9cuUb5cP1rU6C4R0Zef9BWvwaOepUBHEZwMgBACYBywCC6OUxVUBt+nKZnqN8Vm96jDZhqOzGgm2OGY10ZqJzwRXRmmdwNOoLLQK1UtnkZdV5MDKL5EazVPAj6twt4EfXbtAdc+7I4i1Y11lo0y596aDra+UdGY4vppptKOEjoy4t0Th4LYV2gI4G6EZACAEwBgCOaygED9unKZFjXNAN/W88GOo6caZ/bqw2Y6jqxoNteGZV0ZqtOwTSVBO4D6gnaMRE7QXDK0RpIXEpTwvEOpo25AdSwlllxnrQnpQy0bZjm3pp+tp8HRmOL6aaHVhhI2jk1V2KKZukAOBuhGQAgBMAYA5aAkF0MoVVKEb1GUzPUbZ0zXY04yYajqxoA2VhswsdWaH2MltKrzyJSGUWNURyiwXHOGIzrIkVLCTElPCY4Vq3RLLRpmMdaH+shlo3xHJ9NNZ11XCOjMce9DdMcI1jG1OgSdADgboRkAIATAGAGAnE1wAUNuvKZNi81muzq8mOo6cVlt6OGzn1HXihdnkyrozXHtyJpCdQ1xxKpAqI3UhG5cESmm8Aikp4KjO1b1bF7ka5jDdabqbFlHTiOTdbDrpL2o3jl1Rmt8FskiAOhA4G6QgQGQAmAMAICcsAq7KWGKnGc7SKwzPUbYrIdlhNnNqOzFBbJ4ZjXTmuY2olrK7/dEa5UcrogrqKV8QHUcr4klainegRaj/dyOMtVY178NG2XPutB1e6k1ydGXJutn1W7Fpcm8c9aPXuUkikLUWMOhB0gN0hAgMgBMAYAbIE5lJDCjtXJJiEZrtdmOHyZ6a5rIdjcm2c+3Vigd9nJhXVmqstjBDWVFPdx6grqCfYfkZ+kT7D8gXpz/ALs+oh6JbLYk2pq5NjjPVWYZRrlz7q3rbjhJcm+a5dVqOo7ZJpZOjNY1suu7KMorktI3TsxkvIEsxsTAJEwN2hGQAgBnIAjlYkAQz2Yr1AlW7eil5GQL2HaRSfIjZPs+1Tb5I0vNAb9n9jfJz7dGNKdsGzn06c6UNiElkzbSht85IFdUbbpL1GOof3ybAup6pSkIur+vTKTQiuhXW1HhcDjLWk9lHtiaZYa0G33uuRvlhqrGh2zhJcm+azrX9T3qSXyNJUtVpd3FpfIoDGt2cZY5AhKnajL1A1uE0xGkAOZSwAVb9hRQAM2uzjDPIEEbPexjn5DARt/0McP5ATPdj/QZz8hGzuz27nL7EVUd6uz+xrkx1FzQtVR74nPqNs7Q7PXvD4MrG+dge9puOeBcazQFsw9rY+H6QVxzIOF6FtHV97XAuJumj0esylwJF2MVdf7Y+CpGOtqXYVqEWaRlayPZW4kzbKAyG44y8msIW0e4lDHyNJSaDR/oWsfIfSaTrv6DLXyK6TWdZ2qsxyAaXUv9yQGvReUAQ3zwgAB2m77E+QDGdt3bg38gJld3+hll/IAD7HeyefkIBmx2spZ5AKa3W5eSaY11WxmS5M7B1tOsxOKMrDmhC7Vi4eDO5aZ2zPb1RimT5bT6MZ2NkYyZUwr9FKjYj7x+B7afppwk4kXCbtt+sqg4onyzuhO2uMYDkZ3TMd1NJSLkLrC9pb8maQAk7H7jSB3XsyXqV0LlG/KLXI+kPdZ2kvcuSpSb/wDn+wlL28jD0LqrvdBFAcrfABW25YiwDGf0Gw4xlyAeZd9vSUpciJkdrek5PkApS2ZP1EEcrWwM0LHkQGuqvxJE0m86XYXtjyTYkctvX6iLDlZHv9lJSCZVNPPu12373yaTB+wurcan5K8H7ano99+6PJnrJ+nofTbuYx5MbE2jF+yv1+SeEyXebP25KkNhuxtzJlwwuUuSw5UhhJCbTGQr1tr96HA9E/mbXmJUJ6h0sswiUGkp+owqbv0YBhf6R/GQieV9/J++QBktiXyYgr+4QLIGeL5ACOhbiSETZ9Pt4S5JqaPS2HKokmZ7tSkpDhsF2tUvczbJWhMK5e8uwpWi6aE/dEx0uV6B0znGCMKYvfsNV+SeBku52c55HDjJblmZMqKUW+SgbIEeMuRgU62XzQ4Hov8AMPmJcJ6n0f0iUGmp+owq730YBhP6T6yETyrv/vIQZHZ+zAKwgdAZ0ILerPEkBNN1N745JpVqtVucESlV7LT90HwPMK1iu30UpPg6MxFoJDU/+nguwpWn6TQy48GG2krb6On7K1wY2K6j7F+yDJ4bF9vdyx8VGb2JZkxmrsYMAPFPIwK9an70OE9F/mFzAuB6p0f0iUGmp+qGFXd+jEGF/pPrICeV9+vnIQZHZXyYBW9ogWABCNNS+QJoOqn8kKlW26tZghcTVvdqX62XmM7WI7uCTZ05jK0Bqgv2/wDpVgla/oa4/E59xrK2NFaVRhYvoL3MsRYuHGE7WfyY+LgFa8sFI8ADqGQCWurLGBfrafmhk9D/AJqvDiVA9P6RYhEoNJT9Rh//2Q=='),
                        'id' => '8',
                    ],
                    'OrderEmployees' => [
                        'id' => '8',
                        'lft' => '9',
                    ],
                    'DepartmentExtension' => [
                        'id' => '5',
                        'lft' => '9',
                        'name' => 'Автотранспортный отдел',
                    ],
                ],
            ],
            'emptyDepartmentName' => __('Non-staff personnel'),
            'pageHeader' => __('Gallery of employees')
        ];
        $userInfo = [
            'role' => USER_ROLE_USER,
            'prefix' => '',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'gallery',
        ];
        $result = $this->testAction($url, $opt);
        $this->excludeCommonAppVars($result);
        $this->assertData($expected, $result);
    }

    /**
     * testGalleryForNotUser method
     *
     * User role: secretary, human resources, admin
     * @return void
     */
    public function testGalleryForNotUser()
    {
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
            'employeesGallery' => [
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Миронов В.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Миронов В.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'В.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Геологический отдел (ГО)',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        'id' => '1',
                    ],
                    'OrderEmployees' => [
                        'id' => '1',
                        'lft' => '1',
                    ],
                    'DepartmentExtension' => [
                        'id' => '1',
                        'lft' => '1',
                        'name' => 'Управление инженерных изысканий',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAPAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQABgQEBAUEBgUFBgkGBQYJCwgGBggLDAoKCwoKDBAMDAwMDAwQDA4PEA8ODBMTFBQTExwbGxscHx8fHx8fHx8fHwEHBwcNDA0YEBAYGhURFRofHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8f/8AAEQgAyADIAwERAAIRAQMRAf/EAHUAAAIDAQEBAQAAAAAAAAAAAAAFAwQGBwIBCAEAAwEBAQAAAAAAAAAAAAAAAAECAwQFEAACAQMEAgEDBAMBAAAAAAAAAQIDBAURIRIGMSJBUYEyYaETFEIjFTMRAQEBAQEBAQEAAAAAAAAAAAABAhEDEgQT/9oADAMBAAIRAxEAPwD9UgAAAAAAAAB5ckgCGrcRivIgW3eThBPcD4Q33YqcNfYSpCC87dTi37i6qQqq90gn+f7k9V8vEe6Rb/P9w6fyu23cINr3Do+Tqy7RCWnuPqbk+s85Tnp7D6i5OLe+hNLcaeLsKqYySp6jAAAAAAAAAAAAAAAAABvQAiqVVEAo3N/CCe4unwgyWehTT9hdVxis122MOWkxdVIwWY7ro5aT/cSpGRv+6Sbfv+4lSFNTt9Rv8xLkFPtlTX8xKkMbTts017iP5aHG9wkmvcOlctjiO368dZjlZ3Lb4jssZqPsXKzuWusMpGoluVKiw4o1lJFJWE9QIAAAAAAAAAAAAADYBBWqqKAyXIZSNNPck5GOzPZ401L3F1pMufZ7uOnLSf7k9VxzrOdulJy9wPjE5LsVSbfsMElbLVJP8gNEr6bfkXFSp6V3P6isXF2jezXyTVwztcpUi1uI+NDjM/ODXsCbG6wPaJJxTmVGeo6Z17sKmo+xcY6jf4vIKpFblsrDylPVDSlAAAAAAAAAAAAAjqT0QAlyd8qcXuKqkc87J2JU1L2ItaSOU9j7W9ZJTJaSOdZjsk5uXsM2UvsrOo3uMqU1biUn5GlGpNsAnpJgqLtKDE0lWoRJ4qVNCTQuK6t0LmUWtw4On2LysoSj7DkRXRur598oLkVGWnYOs5b+SMdyoxre2NfnBbjQYReqGT6AAAAAAAAAA/ABRvKvGDEcYbs2U/jhPcVXI4v2/PtOa5ENY5LnMzOc5ewKZa5u5Tb3GXVKblIZPPBsZJIUmAW6NJgcXaVPRC4qVMloHFdDloHB9BVdA4PpYt7pxktw4XWt69lXGpHcOIrs/TctyUPYbOuvYS65047jRWhpS1Q0pAAAAAAAAAA8zewAmy1bjTkI45P3PIuMZ7/UmtI4R2zJSlOe4mkc9va8pze4cHVNU3JjJLG1b+Bh7Vm/oMJ6dm/oPgW6Vm/oHAm/rtLwHD6jqLQODqtOeg+F1E6gcHXyNZpi4OneHvHGpHcQdf6TkXrDf6CTXc+sXXKnDcbOttbS1ihpWAAAAAAAAAA8VfDAM5nZ6U5CVHFu812lU+4lxwrstVurMXF9ZOUHKY+Eu2tm5abDBlSxra8Bw0yxj+gwmpYx6+Bhdp4p6eBhHXxzivAwV3NrJa7CBbVoy+gDitOlIXS4gkpJgF7HVWqiJDqfSrl84b/QQrv3UK+tOH2Gzro1lLWCGldXgCAAAAAAAAHir+IBms9FunIDjiveab0qfcS44d2Kk/5ZBxTP0rfWfgfAfY6y102DgaC2xqaWw+GtRxWvwM1mjiN/AGv08R6/iMKt3idnsBEF7i93sKqkKquJbfgi1cyq1sTJLwT9D5LLrHyj8D6m5Q21JxqoEcdG6bJqpD7AVd/6ZP0h9hs66dj3/rQ0mK8AQAAAAAAAA8zWwAjzFLlTkBuQ91sm4z2+oKjh3ZLJqrLYZs5Rt9KngZtJi7dbbAbT2dstFsPgM6VnF/Azi9QsI/QSouKyio+ANSvLSOj2GCK6sYtvYmqir/y4t+DLTbMR1sMnH8TK1fyQZPE8U9hyo1ln5WfCr4LlY6jZdSg1VgUyrvfTNeEPsNnXUMd/5oaTJeAIAAAAAAAAfJLYAX39LlBgHOO247lCe31BTiHasY1UnsM2M/q8avgZneNhpoM2jtGkkURrbyWwHKa2/FpEVpFqSXEXV8LrteR9HCqrS1YrTkfaVum/Bjqt8xYlZpx8GFraQiy1guL2CVOsslc2H+3wa5rn1Gj6vZyVWOxpGGo7j0+g1CH2KY10rHx0ghpMV4AgAAAAAAAAAEFeHKLAMn2Cx505bAbj/bMPrKb4jOOcXmNcKr2GqPdrScWMzWg2tCi4Y29R6oVqpDi0qeDLVbZhjHeJn9NplWuKLY5ofClK1evgd0cylpWunwZarbOVh0PUwtayFORteSewpRYz9TFuVXwbZrn3lp+t4ZqpF8TaOTcde61Y8IR2Ljn029pDSKGhbQAAAAAAAAAAB5mtUAKslbKcHsAc97Lhual6jNzPMYRxnJ8RrhK7Bwl4DrSRPStn9B9P5WqVBpk2qmTO0g00YarfOTehDVIytbzKWVumEp8R/wBRfQro49K2S+CNVcjzUp6IyrSKFxb8vgUOx5tsVzmtjXNc+42OAw6i4vidGXH6Og4mzUILY0jk0fUo6IaEgAAAAAAAAAAAMAgr0+SYBn8tjlOL2A2BzmFWsnxGuMdeYzhJ7Ca5VY2uj8C60iWNukKqi1Qp6My1GuTO3WyMrG8q1FLQJD69cEVwuvMoomqlVqqMq0iONHlIUO04xuPTktjXLn3WzxFgopbHRlw+lai1pKMUaRzVdihofQAAAAAAAAAAAAPklqAU7qgpRewBmMxYJqWwKjCZaxUZPYTbNIKlLjIGkrxogPqSm1qTY0zV2hIi5ayrcZC4vr1zDhdeZTIsVKgk9WZWNZVq0o8pIUha01WJs/GxtmOT001thbKKWxtI4902pw0RbGpRkAAAAAAAAAAAAAAA8VI6oATZKgnF7AcYfN228thNJWOvYcZMGkqhOejGp8hV3BcXaFUmxtlcjU2JsWHVJJ4dQinK+werM7GkpzjKWskEiN6bTE2+y2Nsxx+mmltqaUUaxzWrcUUh9AAAAAAAAAAAAAAAAPklsAULynrFgbIZu21UtgVGEytFxkxNJWeuJNNjXEEar1Brldt6jCtsr1Ob0IrR7cmRU145EUli33kiFdabD09WipGW62+LppRRtlx7p7SWiLY1KMgAAAAAAAAAAAAAAAADAK9eOqYBnctbpxYKjBZu10ctgXKx19DSTE0lUYr2Brmr1tEVrbNMaUdiLWnXuS2ItCJvci0lm1fsiehqsNJaxLzWO23xklxRtmuXZ1SexbGpRkAAAAAAAAAAAAADVAHzkgDzKaQBXrVopPcAS5GtBxe4GxGbnB8gVGHyUlyYmkLYyXITSVftpLYVa5pjTmtCK1lfZzRFPqCUtyKcT20tJIi1XGkxVxxa3CaRrLZYy7Wi3Ns6cu8tBb3CaW5tK57lbjUTK6jj2pDJ9AAAAAAAA+OSQBFOtFfIEhneQXyPg6gnkYL5Dg6qV8vCP+QcHSq7zsFr7BwEOQz8Wn7CVIymUy6nruJcjLXt5yk9xLipCr7AuL1vWBcq/Tr7E2Lmkn8jZnYuV6jFsz00ielHRmGq1kNbKq4tEfR3LR4++46bmmdsN4aG0yK0W5vnbm1g0o36fybTTC5W6d1F/JcrOxYhVTKSlUkwD6AAB4nNJAFK5vIwT3HwrSO+zkKevsVIi6IrrtMIt+5Xyn6Krjt8Fr7j+S+iu57enr7/ALh8nNFNz2hy19yLGmSu4zsp6+xFbZhZcZGU9dyLWkijOu5MR8S0W2ygYUEwPq7T1FVSrVKDZnppFynSMdNsp409Dn06Mp6ezMrWnF+3uHHTcJpNyaW9+1pua52x15mNHJtfJvnbm35r9DKrbc3zpz6wZW+Ui9NzWVhYY0b6MvkpK3CvF/IBKpJgC+8ulCL3HCtZDN5yNJS9i5GWtOd5ztfFy0mbTLHW2LyHcJcn7mkwzuyat22bb9x/Bfas+yzk/wAhXK87SU8zKf8AkY6y6MaWoXzl8mGo6s1Iq2vyZ2NZUkHqxKX7aOugFw0oU9h9HF2lSJtXIuUaRnqtc5XadMw1W+Yl4mOm+YNDKtI9xloI+J4VmhyosSq6a+TTNY6y+/8ATcPk6cacvplLS7Aov8jpw4tm1l2SLa9jaRz2n9lm4T09h8H0c299Ga8i4fSDO5JU4S3KzEarlPas+480pG+cufenKs5nZynL2N85c+tMpdZOcpPc0kR9KbvZt+SuF9JKV3LXyK5VNGNteNabmOsN8ehrbXvjcw1h1Y9DKjdJ/JjrDoz6GFvUT0MrG00b2a10IrSQ5tqeyFauZX6VMi1pMrdKBna1mViKSMtNpH1tGdaR4c0RxXXz+VIXyPp8dzFfI/lF0iq30UvJcyy1ssu8oop7nRjLk9Nk9xnnCX5HZjLz/XYtu0OMl7nRMuPW2oxHatXFcx3JTbdYbPKoo+xFjSaJ+15TjGe5eYW64v2jLOU5+x05jk3XPshdSnN7m0jC0qm22Unrykyh1JDVBwdWaVRoVyqbXaN018mdw1z6GVteeNzHWHRj1O7G6Ta3ObeHZ5+jTY6onoc2o7cXrQWujSMa6swwppEVrImjJIVU+uukTcn9Ial5FfJPwX9FWpkIr5D+ab6q1TKRXyVPJnfZUrZhL/IueTHXuX3GbW/sa58XPv8AQTXma119jbPk5N+5Fd5WTb3OnOHJv1U45Sal5NplzXZ7iMzNTj7BcibdL6xl5S4+xnqNs6ee4ZB++4YjTdcfz1zKdSW505jl3WYrJykzSMah/iZSX1UWMuvSosZde1SYy6kjFoXDmlijOSZFy1zs3sLhpo594dnl6NZirnxucfpl6fjtq7GrrFHJqPR86ZRqaIzbdR1bpRXkqZZ62X3GTUddzSebDXsWXGY013Lnkw1+gurZl77lzxYa/Qo1sxL6mk8WOv0KNbLSfyXPJhr9CjWyM38lzzYa9lCtdzfyXMMr6KdWrJlzLO6Qpy1HxPTbFykpoVOV0vqlSWsDPTfNHabhyc9wzG+3NcnBymzeObRW7Zt+C2VfVafoND2rT9Bk9K0/QaX3+p+gyH9X9AMK3aBUq1bQcZIz1G+NNFi5tNHJ6Zej47bDGzbijg9I9by0aSqaRMeN7opvrtxT3N8Zcnr6M/eX8tXudefN5/p7FNa8m29zaebl17KlSvN/JUwxvqgnObK+Wd9EUlJhxF0jdKTDhdeJUGBI3bsDfI2z18ADTHW75omqjo3V6OjgZ6bZf//Z'),
                        'id' => '3',
                    ],
                    'OrderEmployees' => [
                        'id' => '3',
                        'lft' => '3',
                    ],
                    'DepartmentExtension' => [
                        'id' => '2',
                        'lft' => '3',
                        'name' => 'Отдел связи',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Егоров Т.Г.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Егоров Т.Г.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Т.Г.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа связи №1',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        'id' => '2',
                    ],
                    'OrderEmployees' => [
                        'id' => '2',
                        'lft' => '4',
                    ],
                    'DepartmentExtension' => [
                        'id' => '2',
                        'lft' => '3',
                        'name' => 'Отдел связи',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Хвощинский В.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'В.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Начальник отдела',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        'id' => '7',
                    ],
                    'OrderEmployees' => [
                        'id' => '7',
                        'lft' => '11',
                    ],
                    'DepartmentExtension' => [
                        'id' => '3',
                        'lft' => '5',
                        'name' => 'Отдел информационных технологий',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Дементьева А.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Дементьева А.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'А.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Сектор автоматизированной обработки информации',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        'id' => '4',
                    ],
                    'OrderEmployees' => [
                        'id' => '4',
                        'lft' => '10',
                    ],
                    'DepartmentExtension' => [
                        'id' => '3',
                        'lft' => '5',
                        'name' => 'Отдел информационных технологий',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Козловская Е.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Козловская Е.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Е.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Заведующий сектором',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Сектор автоматизированной обработки информации',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        'id' => '6',
                    ],
                    'OrderEmployees' => [
                        'id' => '6',
                        'lft' => '12',
                    ],
                    'DepartmentExtension' => [
                        'id' => '3',
                        'lft' => '5',
                        'name' => 'Отдел информационных технологий',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Чижов Я.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Чижов Я.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Я.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер 1 категории',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа №1',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
                        'id' => '10',
                    ],
                    'OrderEmployees' => [
                        'id' => '10',
                        'lft' => '17',
                    ],
                    'DepartmentExtension' => [
                        'id' => '4',
                        'lft' => '7',
                        'name' => 'Отдел распределительных сетей',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Матвеев Р.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Матвеев Р.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Р.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа №3',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                        'id' => '5',
                    ],
                    'OrderEmployees' => [
                        'id' => '5',
                        'lft' => '7',
                    ],
                    'DepartmentExtension' => [
                        'id' => '4',
                        'lft' => '7',
                        'name' => 'Отдел распределительных сетей',
                    ],
                ],
                [
                    'Employee' => [
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Голубев Е.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Голубев Е.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Е.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Водитель',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                        CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAPAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQABgQEBAUEBgUFBgkGBQYJCwgGBggLDAoKCwoKDBAMDAwMDAwQDA4PEA8ODBMTFBQTExwbGxscHx8fHx8fHx8fHwEHBwcNDA0YEBAYGhURFRofHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8f/8AAEQgAyADIAwERAAIRAQMRAf/EAHAAAAEFAQEBAAAAAAAAAAAAAAUAAQMEBgIHCAEAAwEBAQAAAAAAAAAAAAAAAAECAwQFEAACAgICAQQDAQEBAQAAAAAAAQIDEQQhBTFBEiIyYRMGFFGBIxEBAQEBAQEBAAAAAAAAAAAAAAECERIDE//aAAwDAQACEQMRAD8A+qQBMAYAQAgBADNgEcrEgCvZsJeounIq27aXqLq5lVs3fyL0uYRf7fyL0fh1Hd/I/ReE8Nxf9H1Nys17SfqPqLlZhamNNiaMsjS6QB0AIAQGQA6EDgZACAEwBgBACAGbAIbLMAajfspepNqpA+/c/JNrTOVK3bb9SLptnCvO9si6azCP9zF6V4Or2vUPQ/N3Dba9SppF+a1Tu/kuaZa+Yjr7ifqXKx1gQpvTLlZWLUZZGl2gI4AhGQA6AHAyAEAJgDACAEwCKyeEBh+zfhPkm1UgPtbXnki1tnIdZsNvyZXTozhH72yLW8wdIi1cyf2i6rjiSDp+UUpND9FcFG9p+Sppnr5ruvttNcmmdOffzF9Tazjk2lc2si1FuUjSVhYtRlkaXYAgBCB0AOBkAIATAGAEAcyYBU2LMJiVIDbt/ki1tmAuxc22Y6rpxlX92WZWunOUsET1pIlSA+OsCNxJCOK9iF1UivJ4DouXVdrTLzphvAnp7HK5N86cm8DunflI3lcmsilU8o0Y2J0BHAEAOgBxGQAgBMAYATAIrJYQGG7duEyavMAd27lmOq6cQLsnlmOq68Q0GQ3kTwEriVMAfIBzJiNDZgmqirYJSL3YY4jUW9a3DRvmuT6ZHdG7wdGa4twc1p5SNY5tRdi+CkOgBADoQOBkAIATAGAGYBXvlhAcBt6zyZ6rbMANuzlmOq6sRQlPkxrqy7jNEtYljagU7VqEHX7EA45nakBq9l6/6I1ad6EfUX7VkIm1Pr2co0y59jvX2eDozXH9I0OnLhG8cmhGD4LZVIBEAOhGcDIAQAmAMAcyAKmzLhiqoA78/JlpvgA2p8sx06sKM7cMzroiN7KXqSuU3+z8iVEle3n1EuJ47HHkXVcRXbOF5Do4oXbnPkCQPbz6gTqGxkE1d1rOUaZZaH+vn4N8uT6RpdKXCN8uPYpW+DRjUiAjgDoRnAyAEAJgDAEc3wBqG3ZhMmrkZ/fs8mWq3xAHanyzCurMDbrGiK2kUrb2JUQf6JZFVxPRdLJNaSL1djwJfEWxY8AOBl1ksgViFTlkabFiqbBNgjqzeUXGWo0HX2eDbNc2402hZwjoy49wYpllGkc9ToZHEDoAcDIAQAmAcsAgunhMVVID7t+MkWts5ANy/LZjqunGQi+WcmVrozkPui2Q1kUrKmxL8o1Q8gqZWKaWiWki5CDSErji6vKAcUbaG2McRf52NNiWFLQk8XKItNDiLkY0rMNG2a595aPr7vB0Zri+mR7WnlI1jl1FyLKZugI6AziMgBACYBzLwAU9qWEya0yz3Y2tZMtV1YjP7NuWzHVdWcqjeTO1tmOHVkhtI4esn6AuZc/5l/wDkdRqSBXHeEhHxxNJgOI3SmA4X+df8GXC/QkIcdKGAibFnXnho0zWG8jvX28o6M1w/TLS6U8pG8cO4IwfBbGpAI4GcRkAIATAOZeACjtrhk1plm+zT5MdOz5s7st+5mGnXlDF8mdbZSxE2kd4QluJ4QGrzsSGEUr0Buf3piCSuaYGsRw0AKSQhxFIZWHqfJcYbg110uUb5cP1ajQfCOjLz/oK1+DRz1KBHEZwMgBACYBy/ABU2Y5iyavLPdlX5MtOv51mtuvEmYadeKqeGZV0ZqSMyW2Xf7OAaILbOAJRutGOqsrmA6aNryI+rdFgjXa58CN1KYGilIaa6q+xpGG6N9cuUb5cP1rU6C4R0Zef9BWvwaOepUBHEZwMgBACYBywCC6OUxVUBt+nKZnqN8Vm96jDZhqOzGgm2OGY10ZqJzwRXRmmdwNOoLLQK1UtnkZdV5MDKL5EazVPAj6twt4EfXbtAdc+7I4i1Y11lo0y596aDra+UdGY4vppptKOEjoy4t0Th4LYV2gI4G6EZACAEwBgCOaygED9unKZFjXNAN/W88GOo6caZ/bqw2Y6jqxoNteGZV0ZqtOwTSVBO4D6gnaMRE7QXDK0RpIXEpTwvEOpo25AdSwlllxnrQnpQy0bZjm3pp+tp8HRmOL6aaHVhhI2jk1V2KKZukAOBuhGQAgBMAYA5aAkF0MoVVKEb1GUzPUbZ0zXY04yYajqxoA2VhswsdWaH2MltKrzyJSGUWNURyiwXHOGIzrIkVLCTElPCY4Vq3RLLRpmMdaH+shlo3xHJ9NNZ11XCOjMce9DdMcI1jG1OgSdADgboRkAIATAGAGAnE1wAUNuvKZNi81muzq8mOo6cVlt6OGzn1HXihdnkyrozXHtyJpCdQ1xxKpAqI3UhG5cESmm8Aikp4KjO1b1bF7ka5jDdabqbFlHTiOTdbDrpL2o3jl1Rmt8FskiAOhA4G6QgQGQAmAMAICcsAq7KWGKnGc7SKwzPUbYrIdlhNnNqOzFBbJ4ZjXTmuY2olrK7/dEa5UcrogrqKV8QHUcr4klainegRaj/dyOMtVY178NG2XPutB1e6k1ydGXJutn1W7Fpcm8c9aPXuUkikLUWMOhB0gN0hAgMgBMAYAbIE5lJDCjtXJJiEZrtdmOHyZ6a5rIdjcm2c+3Vigd9nJhXVmqstjBDWVFPdx6grqCfYfkZ+kT7D8gXpz/ALs+oh6JbLYk2pq5NjjPVWYZRrlz7q3rbjhJcm+a5dVqOo7ZJpZOjNY1suu7KMorktI3TsxkvIEsxsTAJEwN2hGQAgBnIAjlYkAQz2Yr1AlW7eil5GQL2HaRSfIjZPs+1Tb5I0vNAb9n9jfJz7dGNKdsGzn06c6UNiElkzbSht85IFdUbbpL1GOof3ybAup6pSkIur+vTKTQiuhXW1HhcDjLWk9lHtiaZYa0G33uuRvlhqrGh2zhJcm+azrX9T3qSXyNJUtVpd3FpfIoDGt2cZY5AhKnajL1A1uE0xGkAOZSwAVb9hRQAM2uzjDPIEEbPexjn5DARt/0McP5ATPdj/QZz8hGzuz27nL7EVUd6uz+xrkx1FzQtVR74nPqNs7Q7PXvD4MrG+dge9puOeBcazQFsw9rY+H6QVxzIOF6FtHV97XAuJumj0esylwJF2MVdf7Y+CpGOtqXYVqEWaRlayPZW4kzbKAyG44y8msIW0e4lDHyNJSaDR/oWsfIfSaTrv6DLXyK6TWdZ2qsxyAaXUv9yQGvReUAQ3zwgAB2m77E+QDGdt3bg38gJld3+hll/IAD7HeyefkIBmx2spZ5AKa3W5eSaY11WxmS5M7B1tOsxOKMrDmhC7Vi4eDO5aZ2zPb1RimT5bT6MZ2NkYyZUwr9FKjYj7x+B7afppwk4kXCbtt+sqg4onyzuhO2uMYDkZ3TMd1NJSLkLrC9pb8maQAk7H7jSB3XsyXqV0LlG/KLXI+kPdZ2kvcuSpSb/wDn+wlL28jD0LqrvdBFAcrfABW25YiwDGf0Gw4xlyAeZd9vSUpciJkdrek5PkApS2ZP1EEcrWwM0LHkQGuqvxJE0m86XYXtjyTYkctvX6iLDlZHv9lJSCZVNPPu12373yaTB+wurcan5K8H7ano99+6PJnrJ+nofTbuYx5MbE2jF+yv1+SeEyXebP25KkNhuxtzJlwwuUuSw5UhhJCbTGQr1tr96HA9E/mbXmJUJ6h0sswiUGkp+owqbv0YBhf6R/GQieV9/J++QBktiXyYgr+4QLIGeL5ACOhbiSETZ9Pt4S5JqaPS2HKokmZ7tSkpDhsF2tUvczbJWhMK5e8uwpWi6aE/dEx0uV6B0znGCMKYvfsNV+SeBku52c55HDjJblmZMqKUW+SgbIEeMuRgU62XzQ4Hov8AMPmJcJ6n0f0iUGmp+owq730YBhP6T6yETyrv/vIQZHZ+zAKwgdAZ0ILerPEkBNN1N745JpVqtVucESlV7LT90HwPMK1iu30UpPg6MxFoJDU/+nguwpWn6TQy48GG2krb6On7K1wY2K6j7F+yDJ4bF9vdyx8VGb2JZkxmrsYMAPFPIwK9an70OE9F/mFzAuB6p0f0iUGmp+qGFXd+jEGF/pPrICeV9+vnIQZHZXyYBW9ogWABCNNS+QJoOqn8kKlW26tZghcTVvdqX62XmM7WI7uCTZ05jK0Bqgv2/wDpVgla/oa4/E59xrK2NFaVRhYvoL3MsRYuHGE7WfyY+LgFa8sFI8ADqGQCWurLGBfrafmhk9D/AJqvDiVA9P6RYhEoNJT9Rh//2Q=='),
                        'id' => '8',
                    ],
                    'OrderEmployees' => [
                        'id' => '8',
                        'lft' => '9',
                    ],
                    'DepartmentExtension' => [
                        'id' => '5',
                        'lft' => '9',
                        'name' => 'Автотранспортный отдел',
                    ],
                ],
            ],
            'emptyDepartmentName' => __('Non-staff personnel'),
            'pageHeader' => __('Gallery of employees')
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'gallery',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->excludeCommonAppVars($result);
            $this->assertData($expected, $result);
        }
    }

    /**
     * testTreeEmptyIdForUser method
     *
     * User role: user
     * @return void
     */
    public function testTreeEmptyIdForUser()
    {
        $userInfo = [
            'role' => USER_ROLE_USER,
            'prefix' => '',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'tree',
        ];
        $result = $this->testAction($url, $opt);
        $this->assertTrue(isset($result['isTreeDraggable']));
        $this->assertTrue(isset($result['headerMenuActions']));
        $this->assertFalse($result['isTreeDraggable']);
        $expected = [];
        $this->assertData($expected, $result['headerMenuActions']);
    }

    /**
     * testTreeEmptyIdForSecretary method
     *
     * User role: secretary
     * @return void
     */
    public function testTreeEmptyIdForSecretary()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_SECRETARY,
            'prefix' => 'secret',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'tree',
            'prefix' => 'secret',
            'secret' => true
        ];
        $result = $this->testAction($url, $opt);
        $this->assertTrue(isset($result['isTreeDraggable']));
        $this->assertTrue(isset($result['headerMenuActions']));
        $this->assertFalse($result['isTreeDraggable']);
        $expected = [];
        $this->assertData($expected, $result['headerMenuActions']);
    }

    /**
     * testTreeEmptyIdForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testTreeEmptyIdForHr()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'tree',
            'prefix' => 'hr',
            'hr' => true
        ];
        $result = $this->testAction($url, $opt);
        $this->assertTrue(isset($result['headerMenuActions']));
        $expected = [
            [
                'fas fa-sort-alpha-down',
                __('Order tree of employees'),
                ['controller' => 'employees', 'action' => 'order'],
                [
                    'title' => __('Order tree of employees by alphabet'),
                    'action-type' => 'confirm-post',
                    'data-confirm-msg' => __('Are you sure you wish to re-order tree of employees?')
                ]
            ]
        ];
        $this->assertData($expected, $result['headerMenuActions']);
    }

    /**
     * testTreeEmptyIdForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testTreeEmptyIdForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'tree',
            'prefix' => 'admin',
            'admin' => true,
        ];
        $result = $this->testAction($url, $opt);
        $this->assertTrue(isset($result['headerMenuActions']));
        $expected = [
            [
                 'fas fa-sync-alt',
                 __('Synchronizing information with LDAP server'),
                 ['controller' => 'employees', 'action' => 'sync'],
                 [
                     'title' => __('Synchronize information of employees with LDAP server'),
                     'data-toggle' => 'request-only',
                 ]
            ],
            [
                'fas fa-check',
                __('Check state tree of employees'),
                ['controller' => 'employees', 'action' => 'check'],
                [
                    'title' => __('Check state tree of employees'),
                    'data-toggle' => 'modal'
                ]
            ],
            [
                 'fas fa-pencil-alt',
                 __('Editing tree of employees'),
                 ['controller' => 'employees', 'action' => 'tree', 0, true],
                 ['title' => __('Editing tree of employees')]
            ],
            [
                'fas fa-sort-alpha-down',
                __('Order tree of employees'),
                ['controller' => 'employees', 'action' => 'order'],
                [
                    'title' => __('Order tree of employees by alphabet'),
                    'action-type' => 'confirm-post',
                    'data-confirm-msg' => __('Are you sure you wish to re-order tree of employees?')
                ]
            ]
        ];
        $this->assertData($expected, $result['headerMenuActions']);
    }

    /**
     * testTreeEmptyId method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testTreeEmptyId()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $expected = [
            'id' => null,
            'expandAll' => false,
            'pageHeader' => __('Tree view information of employees'),
            'employees' => [
                [
                    'SubordinateDb' => [
                        'id' => '1',
                        'parent_id' => null,
                        'lft' => '1',
                        'rght' => '2',
                    ],
                    'Employee' => [
                        'id' => '1',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Миронов В.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
                    ],
                    'children' => [],
                ],
                [
                    'SubordinateDb' => [
                        'id' => '3',
                        'parent_id' => null,
                        'lft' => '3',
                        'rght' => '6',
                    ],
                    'Employee' => [
                        'id' => '3',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                    ],
                    'children' => [
                        [
                            'SubordinateDb' => [
                                'id' => '2',
                                'parent_id' => '3',
                                'lft' => '4',
                                'rght' => '5',
                            ],
                            'Employee' => [
                                'id' => '2',
                                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Егоров Т.Г.',
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                            ],
                            'children' => [],
                        ],
                    ],
                ],
                [
                    'SubordinateDb' => [
                        'id' => '5',
                        'parent_id' => null,
                        'lft' => '7',
                        'rght' => '8',
                    ],
                    'Employee' => [
                        'id' => '5',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Матвеев Р.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                    ],
                    'children' => [],
                ],
                [
                    'SubordinateDb' => [
                        'id' => '8',
                        'parent_id' => null,
                        'lft' => '9',
                        'rght' => '16',
                    ],
                    'Employee' => [
                        'id' => '8',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Голубев Е.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Водитель',
                    ],
                    'children' => [
                        [
                            'SubordinateDb' => [
                                'id' => '4',
                                'parent_id' => '8',
                                'lft' => '10',
                                'rght' => '15',
                            ],
                            'Employee' => [
                                'id' => '4',
                                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Дементьева А.С.',
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                            ],
                            'children' => [
                                [
                                    'SubordinateDb' => [
                                        'id' => '7',
                                        'parent_id' => '4',
                                        'lft' => '11',
                                        'rght' => '14',
                                    ],
                                    'Employee' => [
                                        'id' => '7',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Начальник отдела',
                                    ],
                                    'children' => [
                                        [
                                            'SubordinateDb' => [
                                                'id' => '6',
                                                'parent_id' => '7',
                                                'lft' => '12',
                                                'rght' => '13',
                                            ],
                                            'Employee' => [
                                                'id' => '6',
                                                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Козловская Е.М.',
                                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Заведующий сектором',
                                            ],
                                            'children' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'SubordinateDb' => [
                        'id' => '10',
                        'parent_id' => null,
                        'lft' => '17',
                        'rght' => '18',
                    ],
                    'Employee' => [
                        'id' => '10',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Чижов Я.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер 1 категории',
                    ],
                    'children' => [],
                ],
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'tree',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->excludeCommonAppVars($result);
            $this->assertTrue(isset($result['isTreeDraggable']));
            $this->assertTrue(isset($result['headerMenuActions']));
            unset($result['isTreeDraggable']);
            unset($result['headerMenuActions']);
            $this->assertData($expected, $result);
        }
    }

    /**
     * testTreeForId method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testTreeForId()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $id = '8';
        $expected = [
            'id' => $id,
            'expandAll' => false,
            'pageHeader' => __('Tree view information of employees'),
            'employees' => [
                [
                    'SubordinateDb' => [
                        'id' => '8',
                        'parent_id' => null,
                        'lft' => '9',
                        'rght' => '16',
                    ],
                    'Employee' => [
                        'id' => '8',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Голубев Е.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Водитель',
                    ],
                    'children' => [
                        [
                            'SubordinateDb' => [
                                'id' => '4',
                                'parent_id' => '8',
                                'lft' => '10',
                                'rght' => '15',
                            ],
                            'Employee' => [
                                'id' => '4',
                                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Дементьева А.С.',
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                            ],
                            'children' => [
                                [
                                    'SubordinateDb' => [
                                        'id' => '7',
                                        'parent_id' => '4',
                                        'lft' => '11',
                                        'rght' => '14',
                                    ],
                                    'Employee' => [
                                        'id' => '7',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Начальник отдела',
                                    ],
                                    'children' => [
                                        [
                                            'SubordinateDb' => [
                                                'id' => '6',
                                                'parent_id' => '7',
                                                'lft' => '12',
                                                'rght' => '13',
                                            ],
                                            'Employee' => [
                                                'id' => '6',
                                                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Козловская Е.М.',
                                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Заведующий сектором',
                                            ],
                                            'children' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'tree',
                $id,
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->excludeCommonAppVars($result);
            $this->assertTrue(isset($result['isTreeDraggable']));
            $this->assertTrue(isset($result['headerMenuActions']));
            unset($result['isTreeDraggable']);
            unset($result['headerMenuActions']);
            $this->assertData($expected, $result);
        }
    }

    /**
     * testTreeForIdUseMoveForUserAndSecretary method
     *
     * User role: user, secretary
     * @return void
     */
    public function testTreeForIdUseMoveForUserAndSecretary()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $id = '8';
        $useMove = '1';
        $expected = [
            'id' => $id,
            'expandAll' => false,
            'pageHeader' => __('Tree view information of employees'),
            'headerMenuActions' => [],
            'employees' => [
                [
                    'SubordinateDb' => [
                        'id' => '8',
                        'parent_id' => null,
                        'lft' => '9',
                        'rght' => '16',
                    ],
                    'Employee' => [
                        'id' => '8',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Голубев Е.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Водитель',
                    ],
                    'children' => [
                        [
                            'SubordinateDb' => [
                                'id' => '4',
                                'parent_id' => '8',
                                'lft' => '10',
                                'rght' => '15',
                            ],
                            'Employee' => [
                                'id' => '4',
                                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Дементьева А.С.',
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                            ],
                            'children' => [
                                [
                                    'SubordinateDb' => [
                                        'id' => '7',
                                        'parent_id' => '4',
                                        'lft' => '11',
                                        'rght' => '14',
                                    ],
                                    'Employee' => [
                                        'id' => '7',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Начальник отдела',
                                    ],
                                    'children' => [
                                        [
                                            'SubordinateDb' => [
                                                'id' => '6',
                                                'parent_id' => '7',
                                                'lft' => '12',
                                                'rght' => '13',
                                            ],
                                            'Employee' => [
                                                'id' => '6',
                                                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Козловская Е.М.',
                                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Заведующий сектором',
                                            ],
                                            'children' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'tree',
                $id,
                $useMove,
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->excludeCommonAppVars($result);
            $this->assertTrue(isset($result['isTreeDraggable']));
            unset($result['isTreeDraggable']);
            $this->assertData($expected, $result);
        }
    }

    /**
     * testTreeForIdUseMoveForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testTreeForIdUseMoveForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $id = '8';
        $useMove = '1';
        $expected = [
            'id' => $id,
            'isTreeDraggable' => true,
            'expandAll' => true,
            'pageHeader' => __('Tree view information of employees'),
            'employees' => [
                [
                    'SubordinateDb' => [
                        'id' => '8',
                        'parent_id' => null,
                        'lft' => '9',
                        'rght' => '16',
                    ],
                    'Employee' => [
                        'id' => '8',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Голубев Е.В.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Водитель',
                        CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '9d6cf30f-a579-4cbc-8dd1-c92a43b65aaf',
                    ],
                    'children' => [
                        [
                            'SubordinateDb' => [
                                'id' => '4',
                                'parent_id' => '8',
                                'lft' => '10',
                                'rght' => '15',
                            ],
                            'Employee' => [
                                'id' => '4',
                                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Дементьева А.С.',
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'd4bd663f-37da-4737-bfd8-e6442e723722',
                            ],
                            'children' => [
                                [
                                    'SubordinateDb' => [
                                        'id' => '7',
                                        'parent_id' => '4',
                                        'lft' => '11',
                                        'rght' => '14',
                                    ],
                                    'Employee' => [
                                        'id' => '7',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Начальник отдела',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '8c149661-7215-47de-b40e-35320a1ea508',
                                    ],
                                    'children' => [
                                        [
                                            'SubordinateDb' => [
                                                'id' => '6',
                                                'parent_id' => '7',
                                                'lft' => '12',
                                                'rght' => '13',
                                            ],
                                            'Employee' => [
                                                'id' => '6',
                                                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Козловская Е.М.',
                                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Заведующий сектором',
                                                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '81817f32-44a7-4b4a-8eff-b837ba387077',
                                            ],
                                            'children' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'tree',
                $id,
                $useMove,
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->excludeCommonAppVars($result);
            $this->assertTrue(isset($result['headerMenuActions']));
            unset($result['headerMenuActions']);
            $this->assertData($expected, $result);
        }
    }

    /**
     * testMoveDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testMoveDenyNotHrAndAdmin()
    {
        $this->setExpectedException('MissingActionException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'move',
                'up',
                '3',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkIsNotAuthorized();
            $this->checkRedirect(true);
        }
    }

    /**
     * testMoveInvalidStepForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testMoveInvalidStepForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
        ];
        $id = 1;
        $direct = 'up';
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'move',
                $direct,
                $id,
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__d('view_extension', 'Error move record %d %s', $id, __d('view_extension_direct', $direct)));
            $this->checkRedirect(true);
        }
    }

    /**
     * testMoveSuccessForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testMoveSuccessForHr()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr',
        ];
        $opt = [
            'method' => 'GET',
        ];
        $id = 10;
        $direct = 'down';
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = '/hr/employees/move/' . $direct . '/' . $id;
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__d('view_extension', 'Error move record %d %s', $id, __d('view_extension_direct', $direct)), false, true);
        $this->checkRedirect(true);
    }

    /**
     * testMoveSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testMoveSuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'GET',
        ];
        $id = 10;
        $direct = 'down';
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = '/admin/employees/move/' . $direct . '/' . $id;
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__d('view_extension', 'Error move record %d %s', $id, __d('view_extension_direct', $direct)), false, true);
        $this->checkRedirect(true);
    }

    /**
     * testDropDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testDropDenyNotHrAndAdmin()
    {
        $this->setExpectedException('MissingActionException');
        $this->setAjaxRequest();
        $dropData = [
            0 => [
                [
                    'id' => '3'
                ],
                [
                    'id' => '2'
                ],
                [
                    'id' => '5'
                ],
                [
                    'id' => '8'
                ],
                [
                    'id' => '4'
                ],
                [
                    'id' => '7'
                ],
                [
                    'id' => '6'
                ],
                [
                    'id' => '10'
                ],
                [
                    'id' => '1'
                ],
            ]
        ];
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'target' => 1,
                'parent' => null,
                'tree' => json_encode($dropData),
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'drop',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkIsNotAuthorized();
            $this->checkRedirect(true);
        }
        $this->resetAjaxRequest();
    }

    /**
     * testDropInvalidDataForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testDropInvalidDataForHrAndAdmin()
    {
        $this->setAjaxRequest();
        $dropData = [
            'Some data'
        ];
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'target' => 0,
                'parent' => null,
                'tree' => json_encode($dropData),
            ],
            'return' => 'contents',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'drop',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $result = json_decode($result, true);
            $expected = [
                'result' => false
            ];
            $this->assertData($expected, $result);
        }
        $this->resetAjaxRequest();
    }

    /**
     * testDropSuccessForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testDropSuccessForHr()
    {
        $this->setAjaxRequest();
        $dropData = [
            0 => [
                [
                    'id' => '3'
                ],
                [
                    'id' => '2'
                ],
                [
                    'id' => '5'
                ],
                [
                    'id' => '8'
                ],
                [
                    'id' => '4'
                ],
                [
                    'id' => '7'
                ],
                [
                    'id' => '6'
                ],
                [
                    'id' => '10'
                ],
                [
                    'id' => '1'
                ],
            ]
        ];
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'target' => 1,
                'parent' => null,
                'tree' => json_encode($dropData),
            ],
            'return' => 'contents',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = '/hr/employees/drop.json';
        $result = $this->testAction($url, $opt);
        $result = json_decode($result, true);
        $expected = [
            'result' => true
        ];
        $this->assertData($expected, $result);
        $this->resetAjaxRequest();
    }

    /**
     * testDropSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testDropSuccessForAdmin()
    {
        $this->setAjaxRequest();
        $dropData = [
            0 => [
                [
                    'id' => '3'
                ],
                [
                    'id' => '2'
                ],
                [
                    'id' => '5'
                ],
                [
                    'id' => '8'
                ],
                [
                    'id' => '4'
                ],
                [
                    'id' => '7'
                ],
                [
                    'id' => '6'
                ],
                [
                    'id' => '10'
                ],
                [
                    'id' => '1'
                ],
            ]
        ];
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'target' => 1,
                'parent' => null,
                'tree' => json_encode($dropData),
            ],
            'return' => 'contents',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = '/admin/employees/drop.json';
        $result = $this->testAction($url, $opt);
        $result = json_decode($result, true);
        $expected = [
            'result' => true
        ];
        $this->assertData($expected, $result);
        $this->resetAjaxRequest();
    }

    /**
     * testCheckDenyNotAdmin method
     *
     * User role: user, secretary, human resources
     * @return void
     */
    public function testCheckDenyNotAdmin()
    {
        $this->setExpectedException('MissingActionException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'check',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkIsNotAuthorized();
            $this->checkRedirect(true);
        }
    }

    /**
     * testCheckUnsuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testCheckUnsuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $modelSubordinateDb = ClassRegistry::init('CakeLdap.SubordinateDb');
        $modelSubordinateDb->id = 2;
        $result = (bool)$modelSubordinateDb->saveField('rght', null);
        $this->assertTrue($result);

        $url = '/admin/employees/check';
        $result = $this->testAction($url, $opt);
        $this->excludeCommonAppVars($result);
        $expected = [
            'pageHeader' => __('Checking state tree of employees'),
            'headerMenuActions' => [
                [
                    'fas fa-redo-alt',
                    __('Recovery state of tree'),
                    ['controller' => 'employees', 'action' => 'recover'],
                    [
                        'title' => __('Recovery state of tree'),
                        'data-toggle' => 'request-only',
                    ]
                ]
            ],
            'treeState' => [
                [
                    'index',
                    5,
                    'missing',
                ],
                [
                    'node',
                    '2',
                    'has invalid left or right values',
                ]
            ],
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testCheckSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testCheckSuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = '/admin/employees/check';
        $result = $this->testAction($url, $opt);
        $this->excludeCommonAppVars($result);
        $expected = [
            'pageHeader' => __('Checking state tree of employees'),
            'headerMenuActions' => [],
            'treeState' => true,
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testOrderDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testOrderDenyNotHrAndAdmin()
    {
        $this->setExpectedException('MissingActionException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
        ];
        $opt = [
            'method' => 'POST',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'order',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkIsNotAuthorized();
            $this->checkRedirect(true);
        }
    }

    /**
     * testOrderGetForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testOrderGetForHrAndAdmin()
    {
        $this->setExpectedException('MethodNotAllowedException');
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'order',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
    }

    /**
     * testOrderPostSuccessForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testOrderPostSuccessForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'order',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__d('cake_ldap', 'Order tree of employees put in queue...'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testRecoverDenyNotAdmin method
     *
     * User role: user, secretary, human resources
     * @return void
     */
    public function testRecoverDenyNotHrAndAdmin()
    {
        $this->setExpectedException('MissingActionException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'recover',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkIsNotAuthorized();
            $this->checkRedirect(true);
        }
    }

    /**
     * testRecoverSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testRecoverSuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'GET',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = '/admin/employees/recover';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__d('cake_ldap', 'Recovery tree of employees put in queue...'));
        $this->checkRedirect(true);
    }

    /**
     * testDeletePhotoDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testDeletePhotoDenyNotHrAndAdmin()
    {
        $this->setExpectedException('MissingActionException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'delete_photo',
                '81817f32-44a7-4b4a-8eff-b837ba387077',
                '1',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkIsNotAuthorized();
            $this->checkRedirect(true);
        }
    }

    /**
     * testDeletePhotoGetForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testDeletePhotoGetForHrAndAdmin()
    {
        $this->setExpectedException('MethodNotAllowedException');
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'delete_photo',
                '81817f32-44a7-4b4a-8eff-b837ba387077',
                '1',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkIsNotAuthorized();
            $this->checkRedirect(true);
        }
    }

    /**
     * testDeletePhotoPostEmptyGuidForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testDeletePhotoPostEmptyGuidForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'delete_photo',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid GUID for employee'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testDeletePhotoPostInvalidGuidForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testDeletePhotoPostInvalidGuidForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'delete_photo',
                '9ad6bfc5-a7af-4865-b73a-78a40476207d',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid GUID for employee'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testDeletePhotoPostSuccessForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testDeletePhotoPostSuccessForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'delete_photo',
                '0400f8f5-6cba-4f1e-8471-fa6e73415673',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__(
                'Deferred saving with an deleted employee photo was created.<br />Information on LDAP server will be updated by queue.<br />Information in phonebook will be updated %s after processing.',
                CakeTime::timeAgoInWords(strtotime('+' . DEFERRED_SAVE_SYNC_DELAY . ' second'), ['accuracy' => ['second' => 'minute']])
            ));
            $this->checkRedirect(true);
        }
    }

    /**
     * testDeletePhotoPostSuccessForHrAndAdminForceDeferred method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testDeletePhotoPostSuccessForHrAndAdminForceDeferred()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'delete_photo',
                '81817f32-44a7-4b4a-8eff-b837ba387077',
                '1',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Deferred saving with an deleted employee photo was created.<br />Information will be updated after approval by the administrator.'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testDeletePhotoPostSuccessForHrAndAdminKeepInternalFlag method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testDeletePhotoPostSuccessForHrAndAdminKeepInternalFlag()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'delete_photo',
                '1dde2cdc-5264-4286-9273-4a88b230237c',
                '1',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__(
                'Deferred saving with an deleted employee photo was created.<br />Information on LDAP server will be updated by queue.<br />Information in phonebook will be updated %s after processing.',
                CakeTime::timeAgoInWords(strtotime('+' . DEFERRED_SAVE_SYNC_DELAY . ' second'), ['accuracy' => ['second' => 'minute']])
            ));
            $this->checkRedirect(true);
        }
    }

    /**
     * testUploadGet method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testUploadGet()
    {
        $this->setExpectedException('MethodNotAllowedException');
        $this->setAjaxRequest();
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $url = [
                'controller' => 'employees',
                'action' => 'upload',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
        $this->resetAjaxRequest();
    }

    /**
     * testUploadPostNotAjax method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testUploadPostNotAjax()
    {
        $this->setExpectedException('BadRequestException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
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
                'action' => 'upload',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
    }

    /**
     * testUploadPostNotJson method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testUploadPostNotJson()
    {
        $this->setExpectedException('BadRequestException');
        $this->setAjaxRequest();
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
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
                'action' => 'upload',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
        $this->resetAjaxRequest();
    }

    /**
     * testUploadPostEmptyData method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testUploadPostEmptyData()
    {
        $this->setExpectedException('NotFoundException');
        $this->setAjaxRequest();
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
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
                'action' => 'upload',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
        $this->resetAjaxRequest();
    }

    /**
     * testUploadPostInvalidGuid method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testUploadPostInvalidGuid()
    {
        $this->setExpectedException('NotFoundException');
        $this->setAjaxRequest();
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'EmployeePhoto' => [
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'd2079171-89eb-4ca2-be07-7a88ea49ce3c'
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
                'action' => 'upload',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
        $this->resetAjaxRequest();
    }

    /**
     * testUploadPostInvalidFormat method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testUploadPostInvalidFormat()
    {
        $this->setAjaxRequest();
        $this->prepareUploadTest();

        $pathTempUploadDir = $this->_getUploadTempDir();
        $uploadFile = $this->createTestPhotoFile($pathTempUploadDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, false);
        $this->assertTrue(is_string($uploadFile));

        $uploadFileName = basename($uploadFile);
        $fileSize = filesize($uploadFile);
        $this->assertTrue($fileSize > 0);

        $_FILES = [
            'files' => [
                'name' => $uploadFileName,
                'type' => 'image/gif',
                'tmp_name' => $uploadFile,
                'error' => 0,
                'size' => $fileSize,
            ],
        ];

        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'return' => 'contents',
            'data' => [
                'EmployeePhoto' => [
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '8c149661-7215-47de-b40e-35320a1ea508',
                    'force_deferred' => false,
                ]
            ]
        ];
        $expected = [
            'files' => [
                [
                    'name' => $uploadFileName,
                    'size' => $fileSize,
                    'type' => 'image/gif',
                    'error' => 'Filetype not allowed',
                ]
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'upload',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $result = json_decode($result, true);
            $this->assertData($expected, $result);
        }
        $this->resetAjaxRequest();
    }

    /**
     * testUploadPostSuccessForceDeferred method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testUploadPostSuccessForceDeferred()
    {
        $this->setAjaxRequest();
        $pathTempUploadDir = $this->_getUploadTempDir();
        $uploadFile = $this->createTestPhotoFile($pathTempUploadDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, true);
        $this->prepareUploadTest();
        $this->assertTrue(is_string($uploadFile));

        $uploadFileName = basename($uploadFile);
        $fileSize = filesize($uploadFile);
        $this->assertTrue($fileSize > 0);

        $_FILES = [
            'files' => [
                'name' => $uploadFileName,
                'type' => 'image/jpeg',
                'tmp_name' => $uploadFile,
                'error' => 0,
                'size' => $fileSize,
            ],
        ];

        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'return' => 'contents',
            'data' => [
                'EmployeePhoto' => [
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '8c149661-7215-47de-b40e-35320a1ea508',
                    'force_deferred' => true,
                ]
            ]
        ];
        $expected = [
            'files' => [
                [
                    'name' => $uploadFileName,
                    'size' => $fileSize,
                    'type' => 'image/jpeg',
                    'url' => '',
                    'deleteUrl' => '?file=' . $uploadFileName,
                    'deleteType' => 'DELETE',
                ]
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $result = $this->_clearImportDir();
            $this->assertTrue($result);
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'upload',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $result = json_decode($result, true);
            $this->assertData($expected, $result);
            $this->checkFlashMessage(__('Deferred saving with an updated employee photo was created.<br />Information will be updated after approval by the administrator.'));
        }
        $this->resetAjaxRequest();
    }

    /**
     * testUploadPostSuccessForUserAndSecretary method
     *
     * User role: user, secretary
     * @return void
     */
    public function testUploadPostSuccessForUserAndSecretary()
    {
        $this->setAjaxRequest();
        $pathTempUploadDir = $this->_getUploadTempDir();
        $uploadFile = $this->createTestPhotoFile($pathTempUploadDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, true);
        $this->prepareUploadTest();
        $this->assertTrue(is_string($uploadFile));

        $uploadFileName = basename($uploadFile);
        $fileSize = filesize($uploadFile);
        $this->assertTrue($fileSize > 0);

        $_FILES = [
            'files' => [
                'name' => $uploadFileName,
                'type' => 'image/jpeg',
                'tmp_name' => $uploadFile,
                'error' => 0,
                'size' => $fileSize,
            ],
        ];

        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
        ];
        $opt = [
            'method' => 'POST',
            'return' => 'contents',
            'data' => [
                'EmployeePhoto' => [
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '8c149661-7215-47de-b40e-35320a1ea508',
                    'force_deferred' => false,
                ]
            ]
        ];
        $expected = [
            'files' => [
                [
                    'name' => $uploadFileName,
                    'size' => $fileSize,
                    'type' => 'image/jpeg',
                    'url' => '',
                    'deleteUrl' => '?file=' . $uploadFileName,
                    'deleteType' => 'DELETE',
                ]
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $result = $this->_clearImportDir();
            $this->assertTrue($result);
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'upload',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $result = json_decode($result, true);
            $this->assertData($expected, $result);
            $this->checkFlashMessage(__('Deferred saving with an updated employee photo was created.<br />Information will be updated after approval by the administrator.'));
        }
        $this->resetAjaxRequest();
    }

    /**
     * testUploadPostSuccessForHrAndAdmin method
     *
     * User role: secretary, human resources, admin
     * @return void
     */
    public function testUploadPostSuccessForHrAndAdmin()
    {
        $this->setAjaxRequest();
        $pathTempUploadDir = $this->_getUploadTempDir();
        $uploadFile = $this->createTestPhotoFile($pathTempUploadDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, true);
        $this->prepareUploadTest();
        $this->assertTrue(is_string($uploadFile));

        $uploadFileName = basename($uploadFile);
        $fileSize = filesize($uploadFile);
        $this->assertTrue($fileSize > 0);

        $_FILES = [
            'files' => [
                'name' => $uploadFileName,
                'type' => 'image/jpeg',
                'tmp_name' => $uploadFile,
                'error' => 0,
                'size' => $fileSize,
            ],
        ];

        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'return' => 'contents',
            'data' => [
                'EmployeePhoto' => [
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '0400f8f5-6cba-4f1e-8471-fa6e73415673',
                    'force_deferred' => false,
                ]
            ]
        ];
        $expected = [
            'files' => [
                [
                    'name' => $uploadFileName,
                    'size' => $fileSize,
                    'type' => 'image/jpeg',
                    'url' => '',
                    'deleteUrl' => '?file=' . $uploadFileName,
                    'deleteType' => 'DELETE',
                ]
            ]
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $result = $this->_clearImportDir();
            $this->assertTrue($result);
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'upload',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $result = json_decode($result, true);
            $this->assertData($expected, $result);
            $this->checkFlashMessage(__(
                'Deferred saving with an updated employee photo was created.<br />Information on LDAP server will be updated by queue.<br />Information in phonebook will be updated %s after processing.',
                CakeTime::timeAgoInWords(strtotime('+' . DEFERRED_SAVE_SYNC_DELAY . ' second'), ['accuracy' => ['second' => 'minute']])
            ));
        }
        $this->resetAjaxRequest();
    }

    /**
     * testManagersGet method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testManagersGet()
    {
        $this->setExpectedException('MethodNotAllowedException');
        $this->setAjaxRequest();
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $url = [
                'controller' => 'employees',
                'action' => 'managers',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
        $this->resetAjaxRequest();
    }

    /**
     * testManagersPostNotAjax method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testManagersPostNotAjax()
    {
        $this->setExpectedException('BadRequestException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
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
                'action' => 'managers',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
    }

    /**
     * testManagersPostNotJson method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testManagersPostNotJson()
    {
        $this->setExpectedException('BadRequestException');
        $this->setAjaxRequest();
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
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
                'action' => 'managers',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
        $this->resetAjaxRequest();
    }

    /**
     * testManagersPostEmptyData method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testManagersPostEmptyData()
    {
        $this->setAjaxRequest();
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'return' => 'contents',
        ];
        $expected = [];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'managers',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $result = json_decode($result, true);
            $this->assertData($expected, $result);
        }
        $this->resetAjaxRequest();
    }

    /**
     * testManagersPostSuccess method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testManagersPostSuccess()
    {
        $this->setAjaxRequest();
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'q' => ' ма ',
                'dn' => 'CN=Марчук А.М.,OU=Пользователи,DC=fabrikam,DC=com'
            ],
            'return' => 'contents',
        ];
        $expected = [
            [
                'value' => 'CN=Матвеев Р.М.,OU=Пользователи,DC=fabrikam,DC=com',
                'text' => 'Матвеев Р.М. - Ведущий инженер'
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
                'action' => 'managers',
                'ext' => 'json',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $result = json_decode($result, true);
            $this->assertData($expected, $result);
        }
        $this->resetAjaxRequest();
    }

    /**
     * testExportForUserAndSecretary method
     *
     * User role: user, secretary
     * @return void
     */
    public function testExportForUserAndSecretary()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'export',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->assertTrue(isset($result['headerMenuActions']));
            $expected = [];
            $this->assertData($expected, $result['headerMenuActions']);
        }
    }

    /**
     * testExportForHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testExportForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'export',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->assertTrue(isset($result['headerMenuActions']));
            $expected = [
                [
                    'fas fa-sync-alt',
                    __('Refresh all files'),
                    ['controller' => 'employees', 'action' => 'generate'],
                    [
                        'title' => __('Refresh all exported files'),
                        'data-toggle' => 'request-only',
                    ]
                ]
            ];
            $this->assertData($expected, $result['headerMenuActions']);
        }
    }

    /**
     * testExport method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testExport()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $expected = [
            'exportInfo' => [
                [
                    'generateType' => 'alph',
                    'viewType' => 'pdf',
                    'extendViewState' => true,
                    'downloadFileName' => __('Directory') . ' ' . __('by alphabet') . ' ' . __('full'),
                    'fileExists' => true,
                    'fileCreate' => 1483261200,
                    'fileExt' => 'pdf',
                    'fileType' => 'PDF',
                ],
                [
                    'generateType' => 'alph',
                    'viewType' => 'pdf',
                    'extendViewState' => false,
                    'downloadFileName' => __('Directory') . ' ' . __('by alphabet'),
                    'fileExists' => false,
                    'fileCreate' => null,
                    'fileExt' => 'pdf',
                    'fileType' => 'PDF',
                ],
                [
                    'generateType' => 'depart',
                    'viewType' => 'pdf',
                    'extendViewState' => true,
                    'downloadFileName' => __('Directory') . ' ' . __('by department') . ' ' . __('full'),
                    'fileExists' => false,
                    'fileCreate' => null,
                    'fileExt' => 'pdf',
                    'fileType' => 'PDF',
                ],
                [
                    'generateType' => 'depart',
                    'viewType' => 'pdf',
                    'extendViewState' => false,
                    'downloadFileName' => __('Directory') . ' ' . __('by department'),
                    'fileExists' => false,
                    'fileCreate' => null,
                    'fileExt' => 'pdf',
                    'fileType' => 'PDF',
                ],
                [
                    'generateType' => 'alph',
                    'viewType' => 'excel',
                    'extendViewState' => true,
                    'downloadFileName' => __('Directory') . ' ' . __('by alphabet') . ' ' . __('full'),
                    'fileExists' => false,
                    'fileCreate' => null,
                    'fileExt' => 'xlsx',
                    'fileType' => 'Excel',
                ],
                [
                    'generateType' => 'alph',
                    'viewType' => 'excel',
                    'extendViewState' => false,
                    'downloadFileName' => __('Directory') . ' ' . __('by alphabet'),
                    'fileExists' => false,
                    'fileCreate' => null,
                    'fileExt' => 'xlsx',
                    'fileType' => 'Excel',
                ],
                [
                    'generateType' => 'depart',
                    'viewType' => 'excel',
                    'extendViewState' => true,
                    'downloadFileName' => __('Directory') . ' ' . __('by department') . ' ' . __('full'),
                    'fileExists' => false,
                    'fileCreate' => null,
                    'fileExt' => 'xlsx',
                    'fileType' => 'Excel',
                ],
                [
                    'generateType' => 'depart',
                    'viewType' => 'excel',
                    'extendViewState' => false,
                    'downloadFileName' => __('Directory') . ' ' . __('by department'),
                    'fileExists' => true,
                    'fileCreate' => 1483261290,
                    'fileExt' => 'xlsx',
                    'fileType' => 'Excel',
                ],
            ],
            'pageHeader' => __('Index of phone book files')
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController(true);
            $url = [
                'controller' => 'employees',
                'action' => 'export',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $result = $this->testAction($url, $opt);
            $this->excludeCommonAppVars($result);
            $this->assertTrue(isset($result['headerMenuActions']));
            unset($result['headerMenuActions']);
            $this->assertData($expected, $result);
        }
    }

    /**
     * testDownloadNormalView method
     *
     * User role: user, secretary, human resources, admin
     * @return void
     */
    public function testDownloadNormalView()
    {
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController(true);
            $url = [
                'controller' => 'employees',
                'action' => 'download',
                GENERATE_FILE_DATA_TYPE_DEPART,
                'ext' => 'xlsx'
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $fileName = __('Directory') . ' ' . __('by department') . '.xlsx';
            $this->checkDownloadFile($fileName);
        }
    }

    /**
     * testDownloadExtendViewForUser method
     *
     * User role: user
     * @return void
     */
    public function testDownloadExtendViewForUser()
    {
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $userInfo = [
            'role' => USER_ROLE_USER,
            'prefix' => '',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController(true);
        $url = [
            'controller' => 'employees',
            'action' => 'download',
            GENERATE_FILE_DATA_TYPE_ALPH,
            '1',
            'ext' => 'pdf'
        ];
        if (!empty($userPrefix)) {
            $url['prefix'] = $userPrefix;
            $url[$userPrefix] = true;
        }
        $this->testAction($url, $opt);
        $fileName = __('Directory') . ' ' . __('by alphabet') . '.pdf';
        $this->checkFlashMessage(__('Generation file "%s" put in queue...', $fileName));
        $this->checkRedirect(true);
    }

    /**
     * testDownloadExtendViewForNotUser method
     *
     * User role: secretary, human resources, admin
     * @return void
     */
    public function testDownloadExtendViewForNotUser()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->_generateMockedController(true);
            $url = [
                'controller' => 'employees',
                'action' => 'download',
                GENERATE_FILE_DATA_TYPE_ALPH,
                '1',
                'ext' => 'pdf'
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $fileName = __('Directory') . ' ' . __('by alphabet') . ' ' . __('full') . '.pdf';
            $this->checkDownloadFile($fileName);
        }
    }

    /**
     * testGenerateDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testGenerateDenyNotHrAndAdmin()
    {
        $this->setExpectedException('MissingActionException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'generate',
                GENERATE_FILE_VIEW_TYPE_PDF,
                GENERATE_FILE_DATA_TYPE_ALPH,
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkIsNotAuthorized();
            $this->checkRedirect(true);
        }
    }

    /**
     * testGenerateSuccessForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testGenerateSuccessForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
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
            $this->_generateMockedController();
            $url = [
                'controller' => 'employees',
                'action' => 'generate',
                GENERATE_FILE_VIEW_TYPE_EXCEL,
                GENERATE_FILE_DATA_TYPE_ALL,
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Generation of exported files put in queue...'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testGenerateInvalidViewForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testGenerateInvalidViewForHr()
    {
        $this->setExpectedException('InternalErrorException');
        $opt = [
            'method' => 'GET',
        ];
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'generate',
            'BAD_VIEW',
            GENERATE_FILE_DATA_TYPE_ALL,
            'prefix' => 'hr',
            'hr' => true,
        ];
        $this->testAction($url, $opt);
    }

    /**
     * testGenerateInvalidTypeForAdmin method
     *
     * User role: human resources
     * @return void
     */
    public function testGenerateInvalidTypeForAdmin()
    {
        $this->setExpectedException('InternalErrorException');
        $opt = [
            'method' => 'GET',
        ];
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->_generateMockedController();
        $url = [
            'controller' => 'employees',
            'action' => 'generate',
            GENERATE_FILE_VIEW_TYPE_EXCEL,
            'BAD_TYPE',
            'prefix' => 'admin',
            'admin' => true,
        ];
        $this->testAction($url, $opt);
    }

    /**
     * Return path to upload temp directory.
     *
     * @return string Return path to upload temp directory
     */
    protected function _getUploadTempDir()
    {
        $tmpDir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        if (!empty($tmpDir) && (mb_substr($tmpDir, -1) !== DS)) {
            $tmpDir .= DS;
        }

        return $tmpDir;
    }

    /**
     * Cleanup the import directory from files
     *
     * @return bool Success
     */
    protected function _clearImportDir()
    {
        $Folder = new Folder($this->_pathImportDir, true);
        $dirContents = $Folder->read(false, false, true);
        list(, $files) = $dirContents;
        $result = true;
        foreach ($files as $file) {
            $oFile = new File($file);
            if (!$oFile->delete()) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Generate mocked EmployeeController.
     *
     * @param bool $createExportFiles If True, create export files.
     * @return bool Success
     */
    protected function _generateMockedController($createExportFiles = false)
    {
        if (!$this->generateMockedController()) {
            return false;
        }

        $this->Controller->Components->unload('CakeTheme.Upload');
        $this->Controller->Upload = $this->Controller->Components->load('CakeTheme.Upload', ['uploadDir' => $this->_pathImportDir]);
        $this->Controller->Employee->pathExportDir = $this->_pathExportDir;

        if (!isset($_SERVER)) {
            $_SERVER = [];
        }

        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = 'somehost.local';
        }

        if (!isset($_SERVER['SERVER_PORT'])) {
            $_SERVER['SERVER_PORT'] = '80';
        }

        if (!$createExportFiles) {
            return false;
        }

        $filesInfo = [
            [
                'timestamp' => mktime(12, 0, 0, 1, 1, 2017),
                'fileName' => $this->Controller->Employee->expandTypeExportToFilename(GENERATE_FILE_DATA_TYPE_ALPH, true, true),
                'ext' => 'pdf',
            ],
            [
                'timestamp' => mktime(12, 1, 30, 1, 1, 2017),
                'fileName' => $this->Controller->Employee->expandTypeExportToFilename(GENERATE_FILE_DATA_TYPE_DEPART, false, true),
                'ext' => 'xlsx',
            ]
        ];
        foreach ($filesInfo as $fileInfo) {
            $this->assertFalse(empty($fileInfo['fileName']));
            $oFile = new File($this->Controller->Employee->pathExportDir . $fileInfo['fileName'] . '.' . $fileInfo['ext'], true);
            $this->assertTrue($oFile->exists());
            $this->assertTrue(touch($oFile->pwd(), $fileInfo['timestamp']));
        }

        return true;
    }
}
