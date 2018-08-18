<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('Employee', 'Model');
App::uses('CakeTime', 'Utility');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

/**
 * Employee Test Case
 */
class EmployeeTest extends AppCakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.cake_session',
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
        $this->setDefaultUserInfo($this->userInfo);
        parent::setUp();
        $oFolder = new Folder($this->_pathExportDir, true);
        $this->_targetObject = ClassRegistry::init('Employee');
        $this->_targetObject->pathExportDir = $this->_pathExportDir;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        $Folder = new Folder($this->_targetObject->pathExportDir);
        $Folder->delete();
        unset($this->_targetObject);
        parent::tearDown();
    }

    /**
     * testAfterDelete method
     *
     * @return void
     */
    public function testAfterDelete()
    {
        $result = $this->_targetObject->getNumberOf();
        $expected = 9;
        $this->assertData($expected, $result);

        $result = $this->_targetObject->delete(2);
        $this->assertTrue($result);

        $result = $this->_targetObject->getNumberOf();
        $expected = 8;
        $this->assertData($expected, $result);
    }

    /**
     * testGetFieldsConfig method
     *
     * @return void
     */
    public function testGetFieldsConfig()
    {
        $result = $this->_targetObject->getFieldsConfig();
        $expected = [
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
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetNumberOf method
     *
     * @return void
     */
    public function testGetNumberOf()
    {
        $result = $this->_targetObject->getNumberOf();
        $expected = 9;
        $this->assertData($expected, $result);
    }

    /**
     * testGetSearchTargetModels method
     *
     * @return void
     */
    public function testGetSearchTargetModels()
    {
        $shortInfo = [
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
        ];
        $fullInfo = [
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
        ];

        $params = [
            [
                null, // $userRole
            ], // Params for step 1
            [
                USER_ROLE_SECRETARY, // $userRole
            ], // Params for step 2
            [
                USER_ROLE_HUMAN_RESOURCES, // $userRole
            ], // Params for step 3
            [
                USER_ROLE_ADMIN, // $userRole
            ], // Params for step 4
        ];
        $expected = [
            $shortInfo, // Result of step 1
            $fullInfo, // Result of step 2
            $fullInfo, // Result of step 3
            $fullInfo, // Result of step 4
        ];
        $this->runClassMethodGroup('getSearchTargetModels', $params, $expected);
    }

    /**
     * testGetSearchIncludeFields method
     *
     * @return void
     */
    public function testGetSearchIncludeFields()
    {
        $shortInfo = [
           'Employee' => [
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
                'Department.id',
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
            ]
        ];
        $fullInfo = [
           'Employee' => [
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
                'Manager.id',
                'Department.id',
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
            ]
        ];

        $params = [
            [
                null, // $userRole
            ], // Params for step 1
            [
                USER_ROLE_SECRETARY, // $userRole
            ], // Params for step 2
            [
                USER_ROLE_HUMAN_RESOURCES, // $userRole
            ], // Params for step 3
            [
                USER_ROLE_ADMIN, // $userRole
            ], // Params for step 4
        ];
        $expected = [
            $shortInfo, // Result of step 1
            $fullInfo, // Result of step 2
            $fullInfo, // Result of step 3
            $fullInfo, // Result of step 4
        ];
        $this->runClassMethodGroup('getSearchIncludeFields', $params, $expected);
    }

    /**
     * testGetListExtendedFields method
     *
     * @return void
     */
    public function testGetListExtendedFields()
    {
        $result = $this->_targetObject->getListExtendedFields();
        $expected = [
            CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION,
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER,
            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER,
            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER,
            CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID,
            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY,
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetListExcludeFieldsLdap method
     *
     * @return void
     */
    public function testGetListExcludeFieldsLdap()
    {
        $shortInfo = [
            CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION,
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER,
            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER,
            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER,
            CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID,
            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY,
        ];
        $fullInfo = [];

        $params = [
            [
                null, // $userRole
            ], // Params for step 1
            [
                USER_ROLE_SECRETARY, // $userRole
            ], // Params for step 2
            [
                USER_ROLE_HUMAN_RESOURCES, // $userRole
            ], // Params for step 3
            [
                USER_ROLE_ADMIN, // $userRole
            ], // Params for step 4
        ];
        $expected = [
            $shortInfo, // Result of step 1
            $fullInfo, // Result of step 2
            $fullInfo, // Result of step 3
            $fullInfo, // Result of step 4
        ];
        $this->runClassMethodGroup('getListExcludeFieldsLdap', $params, $expected);
    }

    /**
     * testGetListExcludeFieldsDb method
     *
     * @return void
     */
    public function testGetListExcludeFieldsDb()
    {
        $shortInfo = [
            CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION,
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER,
            'manager_id',
            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER,
            CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID,
            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY,
        ];
        $fullInfo = [
        ];

        $params = [
            [
                null, // $userRole
            ], // Params for step 1
            [
                USER_ROLE_SECRETARY, // $userRole
            ], // Params for step 2
            [
                USER_ROLE_HUMAN_RESOURCES, // $userRole
            ], // Params for step 3
            [
                USER_ROLE_ADMIN, // $userRole
            ], // Params for step 4
        ];
        $expected = [
            $shortInfo, // Result of step 1
            $fullInfo, // Result of step 2
            $fullInfo, // Result of step 3
            $fullInfo, // Result of step 4
        ];
        $this->runClassMethodGroup('getListExcludeFieldsDb', $params, $expected);
    }

    /**
     * testGetListReadOnlyFieldsLdap method
     *
     * @return void
     */
    public function testGetListReadOnlyFieldsLdap()
    {
        $params = [
            [
                null, // $modelName
            ], // Params for step 1
            [
                'EmployeeEdit', // $modelName
            ], // Params for step 2
        ];
        $expected = [
            [
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
                CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY,
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            ], // Result of step 1
            [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            ], // Result of step 2
        ];
        $this->runClassMethodGroup('getListReadOnlyFieldsLdap', $params, $expected);
    }

    /**
     * testGetListReadOnlyFieldsLdapEmptyCompanyName method
     *
     * @return void
     */
    public function testGetListReadOnlyFieldsLdapEmptyCompanyName()
    {
        Configure::write('Phonebook.Company', '');
        $result = $this->_targetObject->getListReadOnlyFieldsLdap();
        $expected = [
            CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
            CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
            CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
            CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetListExcludeFieldsLabel method
     *
     * @return void
     */
    public function testGetListExcludeFieldsLabel()
    {
        $shortInfo = [
            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION,
            'Othermobile.{n}.value',
            'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
            'Subordinate.{n}',
            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER,
            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID,
            'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY,
            'Employee.block',
        ];
        $fullInfo = [
            'Employee.block'
        ];

        $params = [
            [
                null, // $userRole
            ], // Params for step 1
            [
                USER_ROLE_SECRETARY, // $userRole
            ], // Params for step 2
            [
                USER_ROLE_HUMAN_RESOURCES, // $userRole
            ], // Params for step 3
            [
                USER_ROLE_ADMIN, // $userRole
            ], // Params for step 4
        ];
        $expected = [
            $shortInfo, // Result of step 1
            $fullInfo, // Result of step 2
            $fullInfo, // Result of step 3
            $fullInfo, // Result of step 4
        ];
        $this->runClassMethodGroup('getListExcludeFieldsLabel', $params, $expected);
    }

    /**
     * testGetLdapFieldsInfoForUserRole method
     *
     * @return void
     */
    public function testGetLdapFieldsInfoForUserRole()
    {
        $shortInfo = [
            CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => [
                'label' => __d('app_ldap_field_name', 'GUID'),
                'altLabel' => __d('app_ldap_field_name', 'GUID'),
                'priority' => 20,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect GUID of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                    'isUnique' => [
                        'rule' => ['isUnique'],
                        'message' => __d('cake_ldap_validation_errors', 'GUID of employee is not unique'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => [
                'label' => __d('app_ldap_field_name', 'Distinguished name'),
                'altLabel' => __d('app_ldap_field_name', 'Disting. name'),
                'priority' => 21,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect distinguished name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                    'isUnique' => [
                        'rule' => ['isUnique'],
                        'message' => __d('cake_ldap_validation_errors', 'Distinguished name of employee is not unique'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                'label' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'altLabel' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'priority' => 1,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect full name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                'label' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'altLabel' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'priority' => 2,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => '(a{2,} a.[ ]a.|a.[ ]a. a{2,})'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Display name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [
                'label' => __d('app_ldap_field_name', 'Initials'),
                'altLabel' => __d('app_ldap_field_name', 'Init.'),
                'priority' => 24,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a.[ ]a.'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Initials name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [
                'label' => __d('app_ldap_field_name', 'Surname'),
                'altLabel' => __d('app_ldap_field_name', 'Surn.'),
                'priority' => 3,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect last name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Surname of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => [
                'label' => __d('app_ldap_field_name', 'Given name'),
                'altLabel' => __d('app_ldap_field_name', 'Giv. name'),
                'priority' => 4,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect first name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Given name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => [
                'label' => __d('app_ldap_field_name', 'Middle name'),
                'altLabel' => __d('app_ldap_field_name', 'Mid. name'),
                'priority' => 5,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect middle name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Middle name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                'label' => __d('app_ldap_field_name', 'Position'),
                'altLabel' => __d('app_ldap_field_name', 'Pos.'),
                'priority' => 15,
                'truncate' => true,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect position of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Position of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => [
                'label' => __d('app_ldap_field_name', 'Department'),
                'altLabel' => __d('app_ldap_field_name', 'Depart.'),
                'priority' => 13,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Department of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                'label' => __d('app_ldap_field_name', 'Internal telephone'),
                'altLabel' => __d('app_ldap_field_name', 'Int. tel.'),
                'priority' => 8,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => '9{4}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Interoffice telephone of employee. Format: %s, where X - number from 0 to 9', 'XXXX')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                'label' => __d('app_ldap_field_name', 'Landline telephone'),
                'altLabel' => __d('app_ldap_field_name', 'Land. tel.'),
                'priority' => 9,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'phone'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Local telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                'label' => __d('app_ldap_field_name', 'Mobile telephone'),
                'altLabel' => __d('app_ldap_field_name', 'Mob. tel.'),
                'priority' => 10,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'phone'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Mobile telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                'label' => __d('app_ldap_field_name', 'Office room'),
                'altLabel' => __d('app_ldap_field_name', 'Office'),
                'priority' => 12,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => [
                    'data-inputmask-mask' => '(9{1,4}[a{1}])|(9{1,4}-9{1})',
                    'data-inputmask-greedy' => 'false'
                ],
                'tooltip' => __d('app_ldap_field_tooltip', 'Office room of employee. Format: %s, where X - number from 0 to 9, L - letter', 'X(L)')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                'label' => __d('app_ldap_field_name', 'E-mail'),
                'altLabel' => __d('app_ldap_field_name', 'E-mail'),
                'priority' => 6,
                'truncate' => false,
                'rules' => [
                    'email' => [
                        'rule' => ['email'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect E-mail address'),
                        'allowEmpty' => true,
                        'required' => false,
                        'last' => false,
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'email'],
                'tooltip' => __d('app_ldap_field_tooltip', 'E-mail of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => [
                'label' => __d('app_ldap_field_name', 'Photo'),
                'altLabel' => __d('app_ldap_field_name', 'Photo'),
                'priority' => 22,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'tooltip' => __d('app_ldap_field_tooltip', 'Photo of employee %dpx X %dpx in JPEG format', PHOTO_WIDTH, PHOTO_HEIGHT)
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => [
                'label' => __d('app_ldap_field_name', 'Company name'),
                'altLabel' => __d('app_ldap_field_name', 'Comp. name'),
                'priority' => 23,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'tooltip' => __d('app_ldap_field_tooltip', 'Company name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                'label' => __d('app_ldap_field_name', 'SIP telephone'),
                'altLabel' => __d('app_ldap_field_name', 'SIP tel.'),
                'priority' => 7,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => '9{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'SIP telephone. Format: %s, where X - number from 0 to 9', 'XX')
            ],
        ];
        $fullInfo = [
            CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => [
                'label' => __d('app_ldap_field_name', 'GUID'),
                'altLabel' => __d('app_ldap_field_name', 'GUID'),
                'priority' => 20,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect GUID of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                    'isUnique' => [
                        'rule' => ['isUnique'],
                        'message' => __d('cake_ldap_validation_errors', 'GUID of employee is not unique'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => [
                'label' => __d('app_ldap_field_name', 'Distinguished name'),
                'altLabel' => __d('app_ldap_field_name', 'Disting. name'),
                'priority' => 21,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect distinguished name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                    'isUnique' => [
                        'rule' => ['isUnique'],
                        'message' => __d('cake_ldap_validation_errors', 'Distinguished name of employee is not unique'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                'label' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'altLabel' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'priority' => 1,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect full name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                'label' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'altLabel' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'priority' => 2,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => '(a{2,} a.[ ]a.|a.[ ]a. a{2,})'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Display name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [
                'label' => __d('app_ldap_field_name', 'Initials'),
                'altLabel' => __d('app_ldap_field_name', 'Init.'),
                'priority' => 24,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a.[ ]a.'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Initials name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [
                'label' => __d('app_ldap_field_name', 'Surname'),
                'altLabel' => __d('app_ldap_field_name', 'Surn.'),
                'priority' => 3,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect last name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Surname of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => [
                'label' => __d('app_ldap_field_name', 'Given name'),
                'altLabel' => __d('app_ldap_field_name', 'Giv. name'),
                'priority' => 4,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect first name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Given name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => [
                'label' => __d('app_ldap_field_name', 'Middle name'),
                'altLabel' => __d('app_ldap_field_name', 'Mid. name'),
                'priority' => 5,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect middle name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Middle name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                'label' => __d('app_ldap_field_name', 'Position'),
                'altLabel' => __d('app_ldap_field_name', 'Pos.'),
                'priority' => 15,
                'truncate' => true,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect position of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Position of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => [
                'label' => __d('app_ldap_field_name', 'Subdivision'),
                'altLabel' => __d('app_ldap_field_name', 'Subdiv.'),
                'priority' => 14,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)\#\№]{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Subdivision of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => [
                'label' => __d('app_ldap_field_name', 'Department'),
                'altLabel' => __d('app_ldap_field_name', 'Depart.'),
                'priority' => 13,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Department of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                'label' => __d('app_ldap_field_name', 'Internal telephone'),
                'altLabel' => __d('app_ldap_field_name', 'Int. tel.'),
                'priority' => 8,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => '9{4}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Interoffice telephone of employee. Format: %s, where X - number from 0 to 9', 'XXXX')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                'label' => __d('app_ldap_field_name', 'Landline telephone'),
                'altLabel' => __d('app_ldap_field_name', 'Land. tel.'),
                'priority' => 9,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'phone'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Local telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                'label' => __d('app_ldap_field_name', 'Mobile telephone'),
                'altLabel' => __d('app_ldap_field_name', 'Mob. tel.'),
                'priority' => 10,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'phone'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Mobile telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                'label' => __d('app_ldap_field_name', 'Personal mobile telephone'),
                'altLabel' => __d('app_ldap_field_name', 'Person. mob. tel.'),
                'priority' => 11,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'phone'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Other mobile telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                'label' => __d('app_ldap_field_name', 'Office room'),
                'altLabel' => __d('app_ldap_field_name', 'Office'),
                'priority' => 12,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => [
                    'data-inputmask-mask' => '(9{1,4}[a{1}])|(9{1,4}-9{1})',
                    'data-inputmask-greedy' => 'false'
                ],
                'tooltip' => __d('app_ldap_field_tooltip', 'Office room of employee. Format: %s, where X - number from 0 to 9, L - letter', 'X(L)')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                'label' => __d('app_ldap_field_name', 'E-mail'),
                'altLabel' => __d('app_ldap_field_name', 'E-mail'),
                'priority' => 6,
                'truncate' => false,
                'rules' => [
                    'email' => [
                        'rule' => ['email'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect E-mail address'),
                        'allowEmpty' => true,
                        'required' => false,
                        'last' => false,
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'email'],
                'tooltip' => __d('app_ldap_field_tooltip', 'E-mail of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => [
                'label' => __d('app_ldap_field_name', 'Manager'),
                'altLabel' => __d('app_ldap_field_name', 'Manag.'),
                'priority' => 16,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'tooltip' => __d('app_ldap_field_tooltip', 'Manager of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => [
                'label' => __d('app_ldap_field_name', 'Photo'),
                'altLabel' => __d('app_ldap_field_name', 'Photo'),
                'priority' => 22,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'tooltip' => __d('app_ldap_field_tooltip', 'Photo of employee %dpx X %dpx in JPEG format', PHOTO_WIDTH, PHOTO_HEIGHT)
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => [
                'label' => __d('app_ldap_field_name', 'Computer'),
                'altLabel' => __d('app_ldap_field_name', 'Comp.'),
                'priority' => 18,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'tooltip' => __d('app_ldap_field_tooltip', 'Computer of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [
                'label' => __d('app_ldap_field_name', 'Employee ID'),
                'altLabel' => __d('app_ldap_field_name', 'Empl. ID'),
                'priority' => 19,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => '9{1,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Employee ID. Format: %s, where X - number from 0 to 9', 'X')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => [
                'label' => __d('app_ldap_field_name', 'Company name'),
                'altLabel' => __d('app_ldap_field_name', 'Comp. name'),
                'priority' => 23,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'tooltip' => __d('app_ldap_field_tooltip', 'Company name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => [
                'label' => __d('app_ldap_field_name', 'Birthday'),
                'altLabel' => __d('app_ldap_field_name', 'Birthd.'),
                'priority' => 17,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'yyyy-mm-dd'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Date of birthday. Format: %s, where YYYY - year, MM - month and DD - day', 'YYYY-MM-DD')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                'label' => __d('app_ldap_field_name', 'SIP telephone'),
                'altLabel' => __d('app_ldap_field_name', 'SIP tel.'),
                'priority' => 7,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => '9{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'SIP telephone. Format: %s, where X - number from 0 to 9', 'XX')
            ],
        ];

        $params = [
            [
                null, // $userRole
            ], // Params for step 1
            [
                USER_ROLE_SECRETARY, // $userRole
            ], // Params for step 2
            [
                USER_ROLE_HUMAN_RESOURCES, // $userRole
            ], // Params for step 3
            [
                USER_ROLE_ADMIN, // $userRole
            ], // Params for step 4
        ];
        $expected = [
            $shortInfo, // Result of step 1
            $fullInfo, // Result of step 2
            $fullInfo, // Result of step 3
            $fullInfo, // Result of step 4
        ];
        $this->runClassMethodGroup('getLdapFieldsInfoForUserRole', $params, $expected);
    }

    /**
     * testGetPaginatorOptions method
     *
     * @return void
     */
    public function testGetPaginatorOptions()
    {
        $shortInfo = [
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
        ];
        $fullInfo = [
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
        ];

        $params = [
            [
                null, // $userRole
            ], // Params for step 1
            [
                USER_ROLE_SECRETARY, // $userRole
            ], // Params for step 2
            [
                USER_ROLE_HUMAN_RESOURCES, // $userRole
            ], // Params for step 3
            [
                USER_ROLE_ADMIN, // $userRole
            ], // Params for step 4
        ];
        $expected = [
            $shortInfo, // Result of step 1
            $fullInfo, // Result of step 2
            $fullInfo, // Result of step 3
            $fullInfo, // Result of step 4
        ];
        $this->runClassMethodGroup('getPaginatorOptions', $params, $expected);
    }

    /**
     * testGetPaginatorOptionsGalleryNotAllowedUser method
     *
     * @return void
     */
    public function testGetPaginatorOptionsGalleryNotAllowedUser()
    {
        $result = $this->addExtendedFields(CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO);
        $this->assertTrue($result);

        $result = $this->_targetObject->getPaginatorOptionsGallery(null);
        $this->assertFalse($result);
    }

    /**
     * testGetPaginatorOptionsGallery method
     *
     * @return void
     */
    public function testGetPaginatorOptionsGallery()
    {
        $result = $this->addExtendedFields(CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT);
        $this->assertTrue($result);
        $shortInfo = [
            'page' => '1',
            'limit' => '20',
            'maxLimit' => '250',
            'order' => [
                'OrderEmployees.lft' => 'asc',
            ],
            'contain' => [
                'OrderEmployees',
            ],
            'fields' => [
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO,
                'Employee.id',
            ],
            'conditions' => [
                'Employee.block' => false,
            ]
        ];
        $fullInfo = [
            'page' => '1',
            'limit' => '20',
            'maxLimit' => '250',
            'order' => [
                'DepartmentExtension.lft' => 'asc',
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'asc',
                'OrderEmployees.lft' => 'asc',
            ],
            'contain' => [
                'DepartmentExtension',
                'OrderEmployees',
            ],
            'fields' => [
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION,
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO,
                'Employee.id',
            ],
            'conditions' => [
                'Employee.block' => false,
            ]
        ];

        $params = [
            [
                null, // $userRole
            ], // Params for step 1
            [
                USER_ROLE_SECRETARY, // $userRole
            ], // Params for step 2
            [
                USER_ROLE_HUMAN_RESOURCES, // $userRole
            ], // Params for step 3
            [
                USER_ROLE_ADMIN, // $userRole
            ], // Params for step 4
        ];
        $expected = [
            $shortInfo, // Result of step 1
            $fullInfo, // Result of step 2
            $fullInfo, // Result of step 3
            $fullInfo, // Result of step 4
        ];
        $this->runClassMethodGroup('getPaginatorOptionsGallery', $params, $expected);
    }

    /**
     * testGetListEmployeesManagerInfo method
     *
     * @return void
     */
    public function testGetListEmployeesManagerInfo()
    {
        $params = [
            [
                null, // $limit
            ], // Params for step 1
            [
                2, // $limit
            ], // Params for step 2
        ];
        $expected = [
            [
                'CN=Голубев Е.В.,OU=АТО,OU=Пользователи,DC=fabrikam,DC=com' => [
                    'id' => '8',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Голубев Е.В.,OU=АТО,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Голубев Е.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Водитель',
                ],
                'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com' => [
                    'id' => '4',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Дементьева А.С.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                ],
                'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com' => [
                    'id' => '2',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Егоров Т.Г.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                ],
                'CN=Козловская Е.М.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com' => [
                    'id' => '6',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Козловская Е.М.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Козловская Е.М.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Заведующий сектором',
                ],
                'CN=Матвеев Р.М.,OU=Пользователи,DC=fabrikam,DC=com' => [
                    'id' => '5',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Матвеев Р.М.,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Матвеев Р.М.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                ],
                'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com' => [
                    'id' => '1',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Миронов В.М.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
                ],
                'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com' => [
                    'id' => '3',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                ],
                'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com' => [
                    'id' => '7',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Начальник отдела',
                ],
                'CN=Чижов Я.С.,OU=09-02,OU=ОРС,OU=Пользователи,DC=fabrikam,DC=com' => [
                    'id' => '10',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Чижов Я.С.,OU=09-02,OU=ОРС,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Чижов Я.С.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер 1 категории',
                ],
            ], // Result of step 1
            [
                'CN=Голубев Е.В.,OU=АТО,OU=Пользователи,DC=fabrikam,DC=com' => [
                    'id' => '8',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Голубев Е.В.,OU=АТО,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Голубев Е.В.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Водитель',
                ],
                'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com' => [
                    'id' => '4',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Дементьева А.С.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                ],
            ], // Result of step 2
        ];
        $this->runClassMethodGroup('getListEmployeesManagerInfo', $params, $expected);
    }

    /**
     * testGetListBirthday method
     *
     * @return void
     */
    public function testGetListBirthday()
    {
        $timestamp = mktime(1, 0, 0, 1, 28);
        $date = date('Y-m-d', $timestamp);
        $this->_targetObject->id = 3;
        $result = $this->_targetObject->saveField(CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY, $date);
        $expected = [
            'Employee' => [
                'id' => 3,
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => $date,
            ]
        ];
        $this->assertData($expected, $result);

        $params = [
            [
                $timestamp, // $timestamp
                null, // $limit
            ], // Params for step 1
            [
                $timestamp, // $timestamp
                1, // $limit
            ], // Params for step 2
        ];
        $expected = [
            [
                [
                    'Employee' => [
                        'id' => '6',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Козловская Е.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Козловская',
                        CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Евгения',
                        CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Михайловна',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Козловская Е.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Е.М.',
                    ],
                    'Department' => [
                        'value' => 'ОИТ',
                        'id' => '3',
                        'block' => false
                    ]
                ],
                [
                    'Employee' => [
                        'id' => '3',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                        CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                        CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                    ],
                    'Department' => [
                        'value' => 'ОС',
                        'id' => '2',
                        'block' => false
                    ]
                ]
            ], // Result of step 1
            [
                [
                    'Employee' => [
                        'id' => '6',
                        CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Козловская Е.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Козловская',
                        CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Евгения',
                        CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Михайловна',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Козловская Е.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Е.М.',
                    ],
                    'Department' => [
                        'value' => 'ОИТ',
                        'id' => '3',
                        'block' => false
                    ]
                ]
            ], // Result of step 2
        ];
        $this->runClassMethodGroup('getListBirthday', $params, $expected);
    }

    /**
     * testExpandTypeExportToFilenameString method
     *
     * @return void
     */
    public function testExpandTypeExportToFilenameString()
    {
        $params = [
            [
                null, // $type
                false, // $extendView
                false, // $returnHash
            ], // Params for step 1
            [
                GENERATE_FILE_DATA_TYPE_ALPH, // $type
                false, // $extendView
                false, // $returnHash
            ], // Params for step 2
            [
                GENERATE_FILE_DATA_TYPE_DEPART, // $type
                true, // $extendView
                false, // $returnHash
            ], // Params for step 3
        ];
        $expected = [
            __('Directory'), // Result of step 1
            __('Directory') . ' ' . __('by alphabet'), // Result of step 2
            __('Directory') . ' ' . __('by department') . ' ' . __('full'), // Result of step 3
        ];
        $this->runClassMethodGroup('expandTypeExportToFilename', $params, $expected);
    }

    /**
     * testExpandTypeExportToFilenameHash method
     *
     * @return void
     */
    public function testExpandTypeExportToFilenameHash()
    {
        $result = $this->_targetObject->expandTypeExportToFilename(GENERATE_FILE_DATA_TYPE_ALPH, true, true);
        $this->assertTrue(ctype_xdigit($result));
    }

    /**
     * testGetEmptyDepartmentName method
     *
     * @return void
     */
    public function testGetEmptyDepartmentName()
    {
        $result = $this->_targetObject->getEmptyDepartmentName();
        $expected = __('Non-staff personnel');
        $this->assertData($expected, $result);
    }

    /**
     * testGetExportConfigEmptyType method
     *
     * @return void
     */
    public function testGetExportConfigEmptyType()
    {
        $result = $this->_targetObject->getExportConfig(null, true);
        $this->assertFalse($result);
    }

    /**
     * testGetExportConfigInvalidType method
     *
     * @return void
     */
    public function testGetExportConfigInvalidType()
    {
        $result = $this->_targetObject->getExportConfig('badType', true);
        $this->assertFalse($result);
    }

    /**
     * testGetExportConfigTypeAlphNotExtendView method
     *
     * @return void
     */
    public function testGetExportConfigTypeAlphNotExtendView()
    {
        $result = $this->_targetObject->getExportConfig(GENERATE_FILE_DATA_TYPE_ALPH, false);
        $this->assertTrue(is_array($result));

        $this->assertTrue(isset($result['fileName']));
        $this->assertTrue(ctype_xdigit($result['fileName']));
        unset($result['fileName']);

        $expected = [
            'titletext' => __('Directory of staff by alphabet'),
            'createDate' => __('Created %s', CakeTime::i18nFormat(time(), '%x')),
            'company' => 'ТестОрг',
            'header' => [
                __dx('app_ldap_field_name', 'employee', 'Name') . ' / ' . __d('app_ldap_field_name', 'E-mail'),
                __d('app_ldap_field_name', 'Department') . ' / ' . __d('app_ldap_field_name', 'Position'),
                __d('app_ldap_field_name', 'Int. tel.') . ' / ' . __d('app_ldap_field_name', 'SIP tel.'),
                __d('app_ldap_field_name', 'Land. tel.') . ' / ' . __d('app_ldap_field_name', 'Mob. tel.'),
                __d('app_ldap_field_name', 'Office room'),
            ],
            'width' => [
                50,
                65,
                20,
                25,
                20,
            ],
            'align' => [
                'L',
                'L',
                'C',
                'C',
                'C',
            ],
            'orientation' => 'P',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetExportConfigTypeAlphExtendView method
     *
     * @return void
     */
    public function testGetExportConfigTypeAlphExtendView()
    {
        $result = $this->_targetObject->getExportConfig(GENERATE_FILE_DATA_TYPE_ALPH, true);
        $this->assertTrue(is_array($result));

        $this->assertTrue(isset($result['fileName']));
        $this->assertTrue(ctype_xdigit($result['fileName']));
        unset($result['fileName']);

        $expected = [
            'titletext' => __('Directory of staff by alphabet'),
            'createDate' => __('Created %s', CakeTime::i18nFormat(time(), '%x')),
            'company' => 'ТестОрг',
            'header' => [
                __dx('app_ldap_field_name', 'employee', 'Name') . ' / ' . __d('app_ldap_field_name', 'E-mail'),
                __d('app_ldap_field_name', 'Department') . ' / ' . __d('app_ldap_field_name', 'Subdiv.') . ' / ' . __d('app_ldap_field_name', 'Position'),
                __d('app_ldap_field_name', 'Int. tel.') . ' / ' . __d('app_ldap_field_name', 'SIP tel.'),
                __d('app_ldap_field_name', 'Land. tel.') . ' / ' . __d('app_ldap_field_name', 'Mob. tel.'),
                __d('app_ldap_field_name', 'Office room'),
            ],
            'width' => [
                50,
                65,
                20,
                25,
                20,
            ],
            'align' => [
                'L',
                'L',
                'C',
                'C',
                'C',
            ],
            'orientation' => 'P',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetExportConfigTypeDepartNotExtendView method
     *
     * @return void
     */
    public function testGetExportConfigTypeDepartNotExtendView()
    {
        $result = $this->_targetObject->getExportConfig(GENERATE_FILE_DATA_TYPE_DEPART, false);
        $this->assertTrue(is_array($result));

        $this->assertTrue(isset($result['fileName']));
        $this->assertTrue(ctype_xdigit($result['fileName']));
        unset($result['fileName']);

        $expected = [
            'titletext' => __('Directory of staff by department'),
            'createDate' => __('Created %s', CakeTime::i18nFormat(time(), '%x')),
            'company' => 'ТестОрг',
            'header' => [
                __dx('app_ldap_field_name', 'employee', 'Name') . ' / ' . __d('app_ldap_field_name', 'E-mail'),
                __d('app_ldap_field_name', 'Position'),
                __d('app_ldap_field_name', 'Int. tel.') . ' / ' . __d('app_ldap_field_name', 'SIP tel.'),
                __d('app_ldap_field_name', 'Land. tel.') . ' / ' . __d('app_ldap_field_name', 'Mob. tel.'),
                __d('app_ldap_field_name', 'Office room'),
            ],
            'width' => [
                50,
                65,
                20,
                25,
                20,
            ],
            'align' => [
                'L',
                'L',
                'C',
                'C',
                'C',
            ],
            'orientation' => 'P',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetExportConfigTypeDepartExtendView method
     *
     * @return void
     */
    public function testGetExportConfigTypeDepartExtendView()
    {
        $result = $this->_targetObject->getExportConfig(GENERATE_FILE_DATA_TYPE_DEPART, true);
        $this->assertTrue(is_array($result));

        $this->assertTrue(isset($result['fileName']));
        $this->assertTrue(ctype_xdigit($result['fileName']));
        unset($result['fileName']);

        $expected = [
            'titletext' => __('Directory of staff by department'),
            'createDate' => __('Created %s', CakeTime::i18nFormat(time(), '%x')),
            'company' => 'ТестОрг',
            'header' => [
                __dx('app_ldap_field_name', 'employee', 'Name') . ' / ' . __d('app_ldap_field_name', 'E-mail'),
                __d('app_ldap_field_name', 'Position'),
                __d('app_ldap_field_name', 'Int. tel.') . ' / ' . __d('app_ldap_field_name', 'SIP tel.'),
                __d('app_ldap_field_name', 'Land. tel.') . ' / ' . __d('app_ldap_field_name', 'Mob. tel.'),
                __d('app_ldap_field_name', 'Office room'),
            ],
            'width' => [
                50,
                65,
                20,
                25,
                20,
            ],
            'align' => [
                'L',
                'L',
                'C',
                'C',
                'C',
            ],
            'orientation' => 'P',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetPathExportDirDefault method
     *
     * @return void
     */
    public function testGetPathExportDirDefault()
    {
        $result = $this->_targetObject->getPathExportDir();
        $expected = $this->_pathExportDir;
        $this->assertData($expected, $result);
    }

    /**
     * testGetExportData method
     *
     * @return void
     */
    public function testGetExportData()
    {
        $params = [
            [
                null, // $type
                false, // $extendView
                false, // $useFullDepartName
            ], // Params for step 1
            [
                'bad_type', // $type
                false, // $extendView
                false, // $useFullDepartName
            ], // Params for step 2
            [
                GENERATE_FILE_DATA_TYPE_ALPH, // $type
                false, // $extendView
                false, // $useFullDepartName
            ], // Params for step 3
            [
                GENERATE_FILE_DATA_TYPE_ALPH, // $type
                true, // $extendView
                false, // $useFullDepartName
            ], // Params for step 4
            [
                GENERATE_FILE_DATA_TYPE_DEPART, // $type
                false, // $extendView
                false, // $useFullDepartName
            ], // Params for step 5
            [
                GENERATE_FILE_DATA_TYPE_DEPART, // $type
                true, // $extendView
                false, // $useFullDepartName
            ], // Params for step 6
            [
                GENERATE_FILE_DATA_TYPE_DEPART, // $type
                true, // $extendView
                true, // $useFullDepartName
            ], // Params for step 7
        ];
        $expected = [
            false, // Result of step 1
            false, // Result of step 2
            [
                'Г' => [
                    [
                        '<b>Голубев</b><br />Егор Владимирович<br /><i>e.golubev@fabrikam.com</i>',
                        '<b>Автотранспортный отдел</b><br /><i>Водитель</i>',
                        '',
                        '8 029 500-00-05',
                        'Гараж',
                    ],
                ],
                'Д' => [
                    [
                        '<b>Дементьева</b><br />Анна Сергеевна<br /><i>a.dementeva@fabrikam.com</i>',
                        '<b>Отдел информационных технологий</b><br /><i>Инженер</i>',
                        '247,<br />501247',
                        '',
                        '123',
                    ],
                ],
                'Е' => [
                    [
                        '<b>Егоров</b><br />Тимофей Геннадьевич<br /><i>t.egorov@fabrikam.com</i>',
                        '<b>Отдел связи</b><br /><i>Ведущий инженер</i>',
                        '261,<br />501261',
                        '8 017 100-00-02',
                        '504',
                    ],
                ],
                'К' => [
                    [
                        '<b>Козловская</b><br />Евгения Михайловна<br /><i>e.kozlovskaya@fabrikam.com</i>',
                        '<b>Отдел информационных технологий</b><br /><i>Заведующий сектором</i>',
                        '302,<br />501302',
                        '8 017 100-00-07,<br />8 029 500-00-03',
                        '114',
                    ],
                ],
                'М' => [
                    [
                        '<b>Матвеев</b><br />Руслан Михайлович<br /><i>r.matveev@fabrikam.com</i>',
                        '<b>Отдел распределительных сетей</b><br /><i>Ведущий инженер</i>',
                        '292,<br />501292',
                        '8 017 100-00-06,<br />8 029 500-00-02',
                        '407',
                    ],
                    [
                        '<b>Миронов</b><br />Вячеслав Миронович<br /><i>v.mironov@fabrikam.com</i>',
                        '<b>Управление инженерных изысканий</b><br /><i>Ведущий геолог</i>',
                        '380,<br />50380',
                        '8 017 100-00-01',
                        '214',
                    ],
                ],
                'С' => [
                    [
                        '<b>Суханова</b><br />Лариса Борисовна<br /><i>l.suhanova@fabrikam.com</i>',
                        '<b>Отдел связи</b><br /><i>Зам. начальника отдела - главный специалист</i>',
                        '203,<br />501203',
                        '8 017 100-00-03,<br />8 017 100-00-04,<br />8 017 100-00-05,<br />8 029 500-00-01',
                        '507',
                    ],
                ],
                'Х' => [
                    [
                        '<b>Хвощинский</b><br />Виктор Владимирович<br /><i>v.hvoshchinskiy@fabrikam.com</i>',
                        '<b>Отдел информационных технологий</b><br /><i>Начальник отдела</i>',
                        '320,<br />501321',
                        '8 017 100-00-08,<br />8 017 100-00-09,<br />8 029 500-00-04',
                        '217',
                    ],
                ],
                'Ч' => [
                    [
                        '<b>Чижов</b><br />Ярослав Сергеевич<br /><i>y.chizhov@fabrikam.com</i>',
                        '<b>Отдел распределительных сетей</b><br /><i>Инженер 1 категории</i>',
                        '256,<br />501256',
                        '8 017 100-00-10,<br />8 029 500-00-06',
                        '410',
                    ],
                ],
            ], // Result of step 3
            [
                'Г' => [
                    [
                        '<b>Голубев</b><br />Егор Владимирович<br /><i>e.golubev@fabrikam.com</i>',
                        '<b>Автотранспортный отдел</b><br /><i>Водитель</i>',
                        '',
                        '8 029 500-00-05,<br />8 029 100-00-04',
                        'Гараж',
                    ],
                ],
                'Д' => [
                    [
                        '<b>Дементьева</b><br />Анна Сергеевна<br /><i>a.dementeva@fabrikam.com</i>',
                        '<b>Отдел информационных технологий</b><br /><b><i>Сектор автоматизированной обработки информации</i></b><br /><i>Инженер</i>',
                        '247,<br />501247',
                        '8 029 100-00-03',
                        '123',
                    ],
                ],
                'Е' => [
                    [
                        '<b>Егоров</b><br />Тимофей Геннадьевич<br /><i>t.egorov@fabrikam.com</i>',
                        '<b>Отдел связи</b><br /><b><i>Группа связи №1</i></b><br /><i>Ведущий инженер</i>',
                        '261,<br />501261',
                        '8 017 100-00-02',
                        '504',
                    ],
                ],
                'К' => [
                    [
                        '<b>Козловская</b><br />Евгения Михайловна<br /><i>e.kozlovskaya@fabrikam.com</i>',
                        '<b>Отдел информационных технологий</b><br /><b><i>Сектор автоматизированной обработки информации</i></b><br /><i>Заведующий сектором</i>',
                        '302,<br />501302',
                        '8 017 100-00-07,<br />8 029 500-00-03',
                        '114',
                    ],
                ],
                'М' => [
                    [
                        '<b>Матвеев</b><br />Руслан Михайлович<br /><i>r.matveev@fabrikam.com</i>',
                        '<b>Отдел распределительных сетей</b><br /><b><i>Группа №3</i></b><br /><i>Ведущий инженер</i>',
                        '292,<br />501292',
                        '8 017 100-00-06,<br />8 029 500-00-02',
                        '407',
                    ],
                    [
                        '<b>Миронов</b><br />Вячеслав Миронович<br /><i>v.mironov@fabrikam.com</i>',
                        '<b>Управление инженерных изысканий</b><br /><b><i>Геологический отдел (ГО)</i></b><br /><i>Ведущий геолог</i>',
                        '380,<br />50380',
                        '8 017 100-00-01,<br />8 029 100-00-01,<br />8 029 100-00-02',
                        '214',
                    ],
                ],
                'С' => [
                    [
                        '<b>Суханова</b><br />Лариса Борисовна<br /><i>l.suhanova@fabrikam.com</i>',
                        '<b>Отдел связи</b><br /><i>Зам. начальника отдела - главный специалист</i>',
                        '203,<br />501203',
                        '8 017 100-00-03,<br />8 017 100-00-04,<br />8 017 100-00-05,<br />8 029 500-00-01',
                        '507',
                    ],
                ],
                'Х' => [
                    [
                        '<b>Хвощинский</b><br />Виктор Владимирович<br /><i>v.hvoshchinskiy@fabrikam.com</i>',
                        '<b>Отдел информационных технологий</b><br /><i>Начальник отдела</i>',
                        '320,<br />501321',
                        '8 017 100-00-08,<br />8 017 100-00-09,<br />8 029 500-00-04',
                        '217',
                    ],
                ],
                'Ч' => [
                    [
                        '<b>Чижов</b><br />Ярослав Сергеевич<br /><i>y.chizhov@fabrikam.com</i>',
                        '<b>Отдел распределительных сетей</b><br /><b><i>Группа №1</i></b><br /><i>Инженер 1 категории</i>',
                        '256,<br />501256',
                        '8 017 100-00-10,<br />8 029 500-00-06',
                        '410',
                    ],
                ],
            ], // Result of step 4
            [
                '<b>УИЗ</b>' => [
                    '' => [
                        [
                            '<b>Миронов</b><br />Вячеслав Миронович<br /><i>v.mironov@fabrikam.com</i>',
                            '<i>Ведущий геолог</i>',
                            '380,<br />50380',
                            '8 017 100-00-01',
                            '214',
                        ],
                    ],
                ],
                '<b>ОС</b>' => [
                    '' => [
                        [
                            '<b>Суханова</b><br />Лариса Борисовна<br /><i>l.suhanova@fabrikam.com</i>',
                            '<i>Зам. начальника отдела - главный специалист</i>',
                            '203,<br />501203',
                            '8 017 100-00-03,<br />8 017 100-00-04,<br />8 017 100-00-05,<br />8 029 500-00-01',
                            '507',
                        ],
                        [
                            '<b>Егоров</b><br />Тимофей Геннадьевич<br /><i>t.egorov@fabrikam.com</i>',
                            '<i>Ведущий инженер</i>',
                            '261,<br />501261',
                            '8 017 100-00-02',
                            '504',
                        ],
                    ],
                ],
                '<b>ОИТ</b>' => [
                    '' => [
                        [
                            '<b>Хвощинский</b><br />Виктор Владимирович<br /><i>v.hvoshchinskiy@fabrikam.com</i>',
                            '<i>Начальник отдела</i>',
                            '320,<br />501321',
                            '8 017 100-00-08,<br />8 017 100-00-09,<br />8 029 500-00-04',
                            '217',
                        ],
                        [
                            '<b>Дементьева</b><br />Анна Сергеевна<br /><i>a.dementeva@fabrikam.com</i>',
                            '<i>Инженер</i>',
                            '247,<br />501247',
                            '',
                            '123',
                        ],
                        [
                            '<b>Козловская</b><br />Евгения Михайловна<br /><i>e.kozlovskaya@fabrikam.com</i>',
                            '<i>Заведующий сектором</i>',
                            '302,<br />501302',
                            '8 017 100-00-07,<br />8 029 500-00-03',
                            '114',
                        ],
                    ],
                ],
                '<b>ОРС</b>' => [
                    '' => [
                        [
                            '<b>Чижов</b><br />Ярослав Сергеевич<br /><i>y.chizhov@fabrikam.com</i>',
                            '<i>Инженер 1 категории</i>',
                            '256,<br />501256',
                            '8 017 100-00-10,<br />8 029 500-00-06',
                            '410',
                        ],
                        [
                            '<b>Матвеев</b><br />Руслан Михайлович<br /><i>r.matveev@fabrikam.com</i>',
                            '<i>Ведущий инженер</i>',
                            '292,<br />501292',
                            '8 017 100-00-06,<br />8 029 500-00-02',
                            '407',
                        ],
                    ],
                ],
                '<b>АТО</b>' => [
                    '' => [
                        [
                            '<b>Голубев</b><br />Егор Владимирович<br /><i>e.golubev@fabrikam.com</i>',
                            '<i>Водитель</i>',
                            '',
                            '8 029 500-00-05',
                            'Гараж',
                        ],
                    ],
                ],
            ], // Result of step 5
            [
                '<b>УИЗ</b>' => [
                    'Геологический отдел (ГО)' => [
                        [
                            '<b>Миронов</b><br />Вячеслав Миронович<br /><i>v.mironov@fabrikam.com</i>',
                            '<i>Ведущий геолог</i>',
                            '380,<br />50380',
                            '8 017 100-00-01,<br />8 029 100-00-01,<br />8 029 100-00-02',
                            '214',
                        ],
                    ],
                ],
                '<b>ОС</b>' => [
                    '' => [
                        [
                            '<b>Суханова</b><br />Лариса Борисовна<br /><i>l.suhanova@fabrikam.com</i>',
                            '<i>Зам. начальника отдела - главный специалист</i>',
                            '203,<br />501203',
                            '8 017 100-00-03,<br />8 017 100-00-04,<br />8 017 100-00-05,<br />8 029 500-00-01',
                            '507',
                        ],
                    ],
                    'Группа связи №1' => [
                        [
                            '<b>Егоров</b><br />Тимофей Геннадьевич<br /><i>t.egorov@fabrikam.com</i>',
                            '<i>Ведущий инженер</i>',
                            '261,<br />501261',
                            '8 017 100-00-02',
                            '504',
                        ],
                    ],
                ],
                '<b>ОИТ</b>' => [
                    '' => [
                        [
                            '<b>Хвощинский</b><br />Виктор Владимирович<br /><i>v.hvoshchinskiy@fabrikam.com</i>',
                            '<i>Начальник отдела</i>',
                            '320,<br />501321',
                            '8 017 100-00-08,<br />8 017 100-00-09,<br />8 029 500-00-04',
                            '217',
                        ],
                    ],
                    'Сектор автоматизированной обработки информации' => [
                        [
                            '<b>Дементьева</b><br />Анна Сергеевна<br /><i>a.dementeva@fabrikam.com</i>',
                            '<i>Инженер</i>',
                            '247,<br />501247',
                            '8 029 100-00-03',
                            '123',
                        ],
                        [
                            '<b>Козловская</b><br />Евгения Михайловна<br /><i>e.kozlovskaya@fabrikam.com</i>',
                            '<i>Заведующий сектором</i>',
                            '302,<br />501302',
                            '8 017 100-00-07,<br />8 029 500-00-03',
                            '114',
                        ],
                    ],
                ],
                '<b>ОРС</b>' => [
                    'Группа №1' => [
                        [
                            '<b>Чижов</b><br />Ярослав Сергеевич<br /><i>y.chizhov@fabrikam.com</i>',
                            '<i>Инженер 1 категории</i>',
                            '256,<br />501256',
                            '8 017 100-00-10,<br />8 029 500-00-06',
                            '410',
                        ],
                    ],
                    'Группа №3' => [
                        [
                            '<b>Матвеев</b><br />Руслан Михайлович<br /><i>r.matveev@fabrikam.com</i>',
                            '<i>Ведущий инженер</i>',
                            '292,<br />501292',
                            '8 017 100-00-06,<br />8 029 500-00-02',
                            '407',
                        ],
                    ],
                ],
                '<b>АТО</b>' => [
                    '' => [
                        [
                            '<b>Голубев</b><br />Егор Владимирович<br /><i>e.golubev@fabrikam.com</i>',
                            '<i>Водитель</i>',
                            '',
                            '8 029 500-00-05,<br />8 029 100-00-04',
                            'Гараж',
                        ],
                    ],
                ],
            ], // Result of step 6
            [
                '<b>Управление инженерных изысканий</b>' => [
                    'Геологический отдел (ГО)' => [
                        [
                            '<b>Миронов</b><br />Вячеслав Миронович<br /><i>v.mironov@fabrikam.com</i>',
                            '<i>Ведущий геолог</i>',
                            '380,<br />50380',
                            '8 017 100-00-01,<br />8 029 100-00-01,<br />8 029 100-00-02',
                            '214',
                        ],
                    ],
                ],
                '<b>Отдел связи</b>' => [
                    '' => [
                        [
                            '<b>Суханова</b><br />Лариса Борисовна<br /><i>l.suhanova@fabrikam.com</i>',
                            '<i>Зам. начальника отдела - главный специалист</i>',
                            '203,<br />501203',
                            '8 017 100-00-03,<br />8 017 100-00-04,<br />8 017 100-00-05,<br />8 029 500-00-01',
                            '507',
                        ],
                    ],
                    'Группа связи №1' => [
                        [
                            '<b>Егоров</b><br />Тимофей Геннадьевич<br /><i>t.egorov@fabrikam.com</i>',
                            '<i>Ведущий инженер</i>',
                            '261,<br />501261',
                            '8 017 100-00-02',
                            '504',
                        ],
                    ],
                ],
                '<b>Отдел информационных технологий</b>' => [
                    '' => [
                        [
                            '<b>Хвощинский</b><br />Виктор Владимирович<br /><i>v.hvoshchinskiy@fabrikam.com</i>',
                            '<i>Начальник отдела</i>',
                            '320,<br />501321',
                            '8 017 100-00-08,<br />8 017 100-00-09,<br />8 029 500-00-04',
                            '217',
                        ],
                    ],
                    'Сектор автоматизированной обработки информации' => [
                        [
                            '<b>Дементьева</b><br />Анна Сергеевна<br /><i>a.dementeva@fabrikam.com</i>',
                            '<i>Инженер</i>',
                            '247,<br />501247',
                            '8 029 100-00-03',
                            '123',
                        ],
                        [
                            '<b>Козловская</b><br />Евгения Михайловна<br /><i>e.kozlovskaya@fabrikam.com</i>',
                            '<i>Заведующий сектором</i>',
                            '302,<br />501302',
                            '8 017 100-00-07,<br />8 029 500-00-03',
                            '114',
                        ],
                    ],
                ],
                '<b>Отдел распределительных сетей</b>' => [
                    'Группа №1' => [
                        [
                            '<b>Чижов</b><br />Ярослав Сергеевич<br /><i>y.chizhov@fabrikam.com</i>',
                            '<i>Инженер 1 категории</i>',
                            '256,<br />501256',
                            '8 017 100-00-10,<br />8 029 500-00-06',
                            '410',
                        ],
                    ],
                    'Группа №3' => [
                        [
                            '<b>Матвеев</b><br />Руслан Михайлович<br /><i>r.matveev@fabrikam.com</i>',
                            '<i>Ведущий инженер</i>',
                            '292,<br />501292',
                            '8 017 100-00-06,<br />8 029 500-00-02',
                            '407',
                        ],
                    ],
                ],
                '<b>Автотранспортный отдел</b>' => [
                    '' => [
                        [
                            '<b>Голубев</b><br />Егор Владимирович<br /><i>e.golubev@fabrikam.com</i>',
                            '<i>Водитель</i>',
                            '',
                            '8 029 500-00-05,<br />8 029 100-00-04',
                            'Гараж',
                        ],
                    ],
                ],
            ], // Result of step 7
        ];
        $this->runClassMethodGroup('getExportData', $params, $expected);
    }

    /**
     * testGenerateExportTask method
     *
     * @return void
     */
    public function testGenerateExportTask()
    {
        $params = [
            [
                null, // $view
                null, // $type
                null, // $idTask
            ], // Params for step 1
            [
                'bad_view', // $view
                GENERATE_FILE_DATA_TYPE_ALPH, // $type
                null, // $idTask
            ], // Params for step 2
            [
                GENERATE_FILE_VIEW_TYPE_ALL, // $view
                null, // $type
                null, // $idTask
            ], // Params for step 3
            [
                GENERATE_FILE_VIEW_TYPE_ALL, // $view
                'badType', // $type
                null, // $idTask
            ], // Params for step 4
            [
                GENERATE_FILE_VIEW_TYPE_ALL, // $view
                GENERATE_FILE_DATA_TYPE_ALL, // $type
                null, // $idTask
            ], // Params for step 5
            [
                GENERATE_FILE_VIEW_TYPE_PDF, // $view
                GENERATE_FILE_DATA_TYPE_ALPH, // $type
                null, // $idTask
            ], // Params for step 6
            [
                GENERATE_FILE_VIEW_TYPE_PDF, // $view
                GENERATE_FILE_DATA_TYPE_DEPART, // $type
                null, // $idTask
            ], // Params for step 7
            [
                GENERATE_FILE_VIEW_TYPE_EXCEL, // $view
                GENERATE_FILE_DATA_TYPE_ALPH, // $type
                null, // $idTask
            ], // Params for step 8
            [
                GENERATE_FILE_VIEW_TYPE_EXCEL, // $view
                GENERATE_FILE_DATA_TYPE_DEPART, // $type
                null, // $idTask
            ], // Params for step 9
        ];
        $expected = [
            false, // Result of step 1
            false, // Result of step 2
            false, // Result of step 3
            false, // Result of step 4
            true, // Result of step 5
            true, // Result of step 6
            true, // Result of step 7
            true, // Result of step 8
            true, // Result of step 9
        ];
        $this->runClassMethodGroup('generateExportTask', $params, $expected);
    }

    /**
     * testPutExportTaskInvalidView method
     *
     * @return void
     */
    public function testPutExportTaskInvalidView()
    {
        $this->setExpectedException('InternalErrorException');
        $this->_targetObject->putExportTask('badView', GENERATE_FILE_DATA_TYPE_ALPH);
    }

    /**
     * testPutExportTaskInvalidType method
     *
     * @return void
     */
    public function testPutExportTaskInvalidType()
    {
        $this->setExpectedException('InternalErrorException');
        $this->_targetObject->putExportTask(GENERATE_FILE_VIEW_TYPE_PDF, 'bad_type');
    }

    /**
     * testPutExportTaskEmptyView method
     *
     * @return void
     */
    public function testPutExportTaskEmptyView()
    {
        $result = $this->_targetObject->putExportTask(null, GENERATE_FILE_DATA_TYPE_ALPH);
        $this->assertTrue(is_array($result));
        if (isset($result['ExtendQueuedTask']['created'])) {
            unset($result['ExtendQueuedTask']['created']);
        }
        $expected = [
            'ExtendQueuedTask' => [
                'failed' => '0',
                'jobtype' => 'Generate',
                'data' => serialize([
                        'view' => GENERATE_FILE_VIEW_TYPE_ALL,
                        'type' => GENERATE_FILE_DATA_TYPE_ALPH,
                    ]),
                'group' => 'export',
                'reference' => null,
                'id' => '1',
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testPutExportTaskEmptyType method
     *
     * @return void
     */
    public function testPutExportTaskEmptyType()
    {
        $result = $this->_targetObject->putExportTask(GENERATE_FILE_VIEW_TYPE_PDF, null);
        $this->assertTrue(is_array($result));
        if (isset($result['ExtendQueuedTask']['created'])) {
            unset($result['ExtendQueuedTask']['created']);
        }
        $expected = [
            'ExtendQueuedTask' => [
                'failed' => '0',
                'jobtype' => 'Generate',
                'data' => serialize([
                        'view' => GENERATE_FILE_VIEW_TYPE_PDF,
                        'type' => GENERATE_FILE_DATA_TYPE_ALL,
                    ]),
                'group' => 'export',
                'reference' => null,
                'id' => '1',
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testPutExportTaskValidParam method
     *
     * @return void
     */
    public function testPutExportTaskValidParam()
    {
        $result = $this->_targetObject->putExportTask(GENERATE_FILE_VIEW_TYPE_PDF, GENERATE_FILE_DATA_TYPE_ALPH);
        $this->assertTrue(is_array($result));
        if (isset($result['ExtendQueuedTask']['created'])) {
            unset($result['ExtendQueuedTask']['created']);
        }
        $expected = [
            'ExtendQueuedTask' => [
                'failed' => '0',
                'jobtype' => 'Generate',
                'data' => serialize([
                        'view' => GENERATE_FILE_VIEW_TYPE_PDF,
                        'type' => GENERATE_FILE_DATA_TYPE_ALPH,
                    ]),
                'group' => 'export',
                'reference' => null,
                'id' => '1',
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetExportInfo method
     *
     * @return void
     */
    public function testGetExportInfo()
    {
        $timestamp = mktime(12, 0, 0, 1, 1, 2017);
        $fileName = $this->_targetObject->expandTypeExportToFilename(GENERATE_FILE_DATA_TYPE_ALPH, true, true);
        $this->assertFalse(empty($fileName));
        $oFile = new File($this->_targetObject->pathExportDir . $fileName . '.pdf', true);
        $this->assertTrue($oFile->exists());
        $this->assertTrue(touch($oFile->pwd(), $timestamp));
        clearstatcache();
        $result = $this->_targetObject->getExportInfo();
        $expected = [
            [
                'generateType' => 'alph',
                'viewType' => 'pdf',
                'extendViewState' => true,
                'downloadFileName' => __('Directory') . ' ' . __('by alphabet') . ' ' . __('full'),
                'fileExists' => true,
                'fileCreate' => $timestamp,
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
                'fileExists' => false,
                'fileCreate' => null,
                'fileExt' => 'xlsx',
                'fileType' => 'Excel',
            ],
        ];
        $this->assertData($expected, $result);
    }
}
