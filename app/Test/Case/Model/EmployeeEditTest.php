<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('EmployeeEdit', 'Model');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('CakeNumber', 'Utility');

/**
 * EmployeeEdit Test Case
 */
class EmployeeEditTest extends AppCakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.cake_session',
        'app.deferred',
        'app.log',
        'plugin.cake_ldap.department',
        'plugin.cake_ldap.employee',
        'plugin.cake_ldap.employee_ldap',
        'plugin.cake_ldap.othermobile',
        'plugin.cake_ldap.othertelephone',
        'plugin.queue.queued_task',
    ];

    /**
     * Path to import directory
     *
     * @var string
     */
    protected $_pathImportDir = TMP . 'tests' . DS . 'import' . DS;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        $this->setDefaultUserInfo($this->userInfo);
        parent::setUp();

        $this->_targetObject = ClassRegistry::init('EmployeeEdit');
        $oFolder = new Folder($this->_pathImportDir, true);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->_targetObject);
        $Folder = new Folder($this->_pathImportDir);
        $Folder->delete();

        parent::tearDown();
    }

    /**
     * testBeforeDelete method
     *
     * @return void
     */
    public function testBeforeDelete()
    {
        $result = $this->_targetObject->delete(2);
        $this->assertFalse($result);
    }

    /**
     * testAfterSave method
     *
     * @return void
     */
    public function testAfterSave()
    {
        $data = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '0010b7b8-d69a-4365-81ca-5f975584fe5c',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Егоров Т.Г.',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Егоров Т. Г.',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Т. Г.',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'егоров ',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'тИмофей',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'ГЕННАДЬЕВИЧ',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа связи №2',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'отдел связи',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '261',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '8 029 123-45-67',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '504',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 't.g.egorov@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0500',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1631',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'НовТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '2000-01-01',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '602261',
            ]
        ];

        $expected = array_merge_recursive($data, ['EmployeeEdit' => ['id' => '11']]);
        $result = $this->_targetObject->save($data);
        $this->assertData($expected, $result);

        $modelLog = ClassRegistry::init('Log');
        $findOpt = [
            'conditions' => ['Log.employee_id' => 2],
            'recursive' => -1,
        ];

        $log = $modelLog->find('first', $findOpt);
        $this->assertTrue(isset($log['Log']['created']));
        unset($log['Log']['created']);
        $expected = [
            'Log' => [
                'id' => '5',
                'user_id' => '7',
                'employee_id' => '2',
                'data' => [
                    'changed' => [
                        'EmployeeEdit' => [
                            CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Т. Г.',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'егоров ',
                            CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'тИмофей',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'ГЕННАДЬЕВИЧ',
                            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                            CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа связи №2',
                            CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'отдел связи',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '8 029 123-45-67',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 't.g.egorov@fabrikam.com',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => '',
                            CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0500',
                            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '2000-01-01',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '602261',
                            'id' => '11',
                            CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                        ],
                    ],
                    'current' => [
                        'EmployeeEdit' => [
                            CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Т.Г.',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Егоров',
                            CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Тимофей',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Геннадьевич',
                            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                            CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа связи №1',
                            CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 't.egorov@fabrikam.com',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                            CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0390',
                            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-07-27',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501261',
                            'id' => '2',
                        ]
                    ]
                ]
            ]
        ];
        $this->assertData($expected, $log);
    }

    /**
     * testCreateValidationRulesUser method
     *
     * @return void
     */
    public function testCreateValidationRulesUser()
    {
        $userRole = USER_ROLE_USER;
        $expected = 9;
        $result = $this->_targetObject->createValidationRules(['userRole' => $userRole]);
        $this->assertTrue($result, __d('test', 'User role: %d', $userRole));

        $validator = $this->_targetObject->validator();
        $result = $validator->count();
        $this->assertData($expected, $result, __d('test', 'User role: %d', $userRole));
    }

    /**
     * testCreateValidationRulesNotUser method
     *
     * @return void
     */
    public function testCreateValidationRulesNotUser()
    {
        $userRoles = [
            USER_ROLE_SECRETARY,
            USER_ROLE_HUMAN_RESOURCES,
            USER_ROLE_ADMIN,
        ];
        $expected = 9;
        foreach ($userRoles as $userRole) {
            $this->_targetObject->clear();
            $result = $this->_targetObject->createValidationRules(['userRole' => $userRole]);
            $this->assertTrue($result, __d('test', 'User role: %d', $userRole));

            $validator = $this->_targetObject->validator();
            $result = $validator->count();
            $this->assertData($expected, $result, __d('test', 'User role: %d', $userRole));
        }
    }

    /**
     * testValidTelephonenumber method
     *
     * @return void
     */
    public function testValidTelephonenumber()
    {
        $params = [
            [
                null, // $data
                false, // $isMobileNumber
            ], // Params for step 1
            [
                'bad_number', // $data
                false, // $isMobileNumber
            ], // Params for step 2
            [
                '+37529', // $data
                false, // $isMobileNumber
            ], // Params for step 3
            [
                '+375291234567', // $data
                false, // $isMobileNumber
            ], // Params for step 4
            [
                '+375 17 223-45-67', // $data
                false, // $isMobileNumber
            ], // Params for step 5
            [
                '+37517', // $data
                true, // $isMobileNumber
            ], // Params for step 6
            [
                '+375172234567', // $data
                true, // $isMobileNumber
            ], // Params for step 7
            [
                ['8 029 123-45-67'], // $data
                true, // $isMobileNumber
            ], // Params for step 8
        ];
        $expected = [
            true, // Result of step 1
            false, // Result of step 2
            false, // Result of step 3
            false, // Result of step 4
            true, // Result of step 5
            false, // Result of step 6
            false, // Result of step 7
            true, // Result of step 8
        ];
        $this->runClassMethodGroup('validTelephonenumber', $params, $expected);
    }

    /**
     * testValidatesUser method
     *
     * @return void
     */
    public function testValidatesUser()
    {
        $result = $this->addExtendedFields([CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER, CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER]);
        $this->assertTrue($result);
        $data = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '203',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375291000001',
                    '+37517',
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375172000001',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    '+375172000003',
                    '+37529',
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '507',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'bad.email@somedomain',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0216',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1602',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-16',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501203',
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => '',
            ]
        ];

        $this->_targetObject->set($data);
        $result = $this->_targetObject->validates(['userRole' => USER_ROLE_USER]);
        $this->assertFalse($result);

        $result = $this->_targetObject->validationErrors;
        $expected = [
            CAKE_LDAP_LDAP_DISTINGUISHED_NAME => [
                __d('cake_ldap_validation_errors', 'Incorrect distinguished name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                __d('cake_ldap_validation_errors', 'This field must contain a valid local telephone number.')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                __d('cake_ldap_validation_errors', 'This field must contain a valid mobile telephone number.')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                __d('cake_ldap_validation_errors', 'This field must contain a valid mobile telephone number.')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                __d('cake_ldap_validation_errors', 'Incorrect E-mail address')
            ],
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testValidatesNotUser method
     *
     * @return void
     */
    public function testValidatesNotUser()
    {
        $result = $this->addExtendedFields([CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER, CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER]);
        $this->assertTrue($result);
        $data = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '203',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375291000001',
                    '+37517',
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375172000001',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    '+375172000003',
                    '+37529',
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '507',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'bad.email@somedomain',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0216',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1602',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-16',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501203',
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => '',
            ]
        ];

        $userRoles = [
            USER_ROLE_SECRETARY,
            USER_ROLE_HUMAN_RESOURCES,
            USER_ROLE_ADMIN,
        ];
        $expected = [
            CAKE_LDAP_LDAP_DISTINGUISHED_NAME => [
                __d('cake_ldap_validation_errors', 'Incorrect distinguished name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                __d('cake_ldap_validation_errors', 'This field must contain a valid local telephone number.')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                __d('cake_ldap_validation_errors', 'This field must contain a valid mobile telephone number.')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                __d('cake_ldap_validation_errors', 'This field must contain a valid mobile telephone number.')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                __d('cake_ldap_validation_errors', 'Incorrect E-mail address')
            ],
        ];
        foreach ($userRoles as $userRole) {
            $this->_targetObject->clear();
            $this->_targetObject->set($data);
            $result = $this->_targetObject->validates(['userRole' => $userRole]);
            $this->assertFalse($result, __d('test', 'User role: %d', $userRole));

            $result = $this->_targetObject->validationErrors;
            $this->assertData($expected, $result, __d('test', 'User role: %d', $userRole));
        }
    }

    /**
     * testGetDnEmployee method
     *
     * @return void
     */
    public function testGetDnEmployee()
    {
        $params = [
            [
                null, // $guid
            ], // Params for step 1
            [
                'bad_guid', // $guid
            ], // Params for step 2
            [
                '25698d14-e2a5-409f-a1a2-86190a906f63', // $guid
            ], // Params for step 3
            [
                '0010b7b8-d69a-4365-81ca-5f975584fe5c', // $guid
            ], // Params for step 4
        ];
        $expected = [
            false, // Result of step 1
            false, // Result of step 2
            false, // Result of step 3
            'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com', // Result of step 4
        ];
        $this->runClassMethodGroup('getDnEmployee', $params, $expected);
    }

    /**
     * testGet method
     *
     * @return void
     */
    public function testGet()
    {
        $shortInfo = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '203',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375171000003',
                    '+375171000004',
                    '+375171000005',
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000001',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '507',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'l.suhanova@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501203',
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
            ]
        ];
        $fullInfoLdap = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '203',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375171000003',
                    '+375171000004',
                    '+375171000005',
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000001',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '507',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'l.suhanova@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0216',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1602',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-16',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501203',
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
            ]
        ];
        $fullInfoDb = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '203',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000001',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '507',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'l.suhanova@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0216',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1602',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-16',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501203',
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375171000003',
                    '+375171000004',
                    '+375171000005',
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
            ]
        ];
        $params = [
            [
                null, // $guid
                null, // $userRole
                false, // $useLdap
            ], // Params for step 1
            [
                'bad_dn', // $guid
                null, // $userRole
                false, // $useLdap
            ], // Params for step 2
            [
                'CN=Some user,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com', // $guid
                null, // $userRole
                false, // $useLdap
            ], // Params for step 3
            [
                'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com', // $guid
                null, // $userRole
                true, // $useLdap
            ], // Params for step 4
            [
                '3a01fed4-b981-43a8-afd4-e0b1387b3a28', // $guid
                USER_ROLE_SECRETARY, // $userRole
                false, // $useLdap
            ], // Params for step 5
            [
                'dd518c55-35ce-4a5c-85c5-b5fb762220bf', // $guid
                USER_ROLE_SECRETARY, // $userRole
                false, // $useLdap
            ], // Params for step 6
            [
                'dd518c55-35ce-4a5c-85c5-b5fb762220bf', // $guid
                USER_ROLE_HUMAN_RESOURCES, // $userRole
                true, // $useLdap
            ], // Params for step 7
            [
                'dd518c55-35ce-4a5c-85c5-b5fb762220bf', // $guid
                USER_ROLE_ADMIN, // $userRole
                true, // $useLdap
            ], // Params for step 8
        ];
        $expected = [
            false, // Result of step 1
            [], // Result of step 2
            [], // Result of step 3
            $shortInfo, // Result of step 4
            [], // Result of step 5
            $fullInfoDb, // Result of step 6
            $fullInfoLdap, // Result of step 7
            $fullInfoLdap, // Result of step 8
        ];
        $this->runClassMethodGroup('get', $params, $expected);
    }

    /**
     * testGetListReadOnlyFields method
     *
     * @return void
     */
    public function testGetListReadOnlyFields()
    {
        $result = $this->_targetObject->getListReadOnlyFields();
        $expected = [
            'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
            'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
            'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
            'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY,
            'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetLimitPhotoSize method
     *
     * @return void
     */
    public function testGetLimitPhotoSize()
    {
        $result = $this->_targetObject->getLimitPhotoSize();
        $expected = UPLOAD_FILE_SIZE_LIMIT;
        $this->assertData($expected, $result);
    }

    /**
     * testGetAcceptFileTypes method
     *
     * @return void
     */
    public function testGetAcceptFileTypes()
    {
        $params = [
            [
                false, // $returnServer
            ], // Params for step 1
            [
                true, // $returnServer
            ], // Params for step 2
        ];
        $expected = [
            UPLOAD_FILE_TYPES_CLIENT, // Result of step 1
            UPLOAD_FILE_TYPES_SERVER, // Result of step 2
        ];
        $this->runClassMethodGroup('getAcceptFileTypes', $params, $expected);
    }

    /**
     * testGetLimitLinesMultipleValue method
     *
     * @return void
     */
    public function testGetLimitLinesMultipleValue()
    {
        $result = $this->_targetObject->getLimitLinesMultipleValue();
        $expected = 4;
        $this->assertData($expected, $result);
    }

    /**
     * testGetListManagers method
     *
     * @return void
     */
    public function testGetListManagers()
    {
        $this->_targetObject->id = 3;
        $result = $this->_targetObject->saveField(CAKE_LDAP_LDAP_ATTRIBUTE_TITLE, '');
        $expected = [
            'EmployeeEdit' => [
                'id' => 3,
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => ''
            ]
        ];
        $this->assertData($expected, $result);

        $result = $this->_targetObject->getListManagers();
        $expected = [
            'CN=Голубев Е.В.,OU=АТО,OU=Пользователи,DC=fabrikam,DC=com' => 'Голубев Е.В. - Водитель',
            'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com' => 'Дементьева А.С. - Инженер',
            'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com' => 'Егоров Т.Г. - Ведущий инженер',
            'CN=Козловская Е.М.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com' => 'Козловская Е.М. - Заведующий сектором',
            'CN=Марчук А.М.,OU=Пользователи,DC=fabrikam,DC=com' => 'Марчук А.М. - Ведущий инженер по охране труда',
            'CN=Матвеев Р.М.,OU=Пользователи,DC=fabrikam,DC=com' => 'Матвеев Р.М. - Ведущий инженер',
            'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com' => 'Миронов В.М. - Ведущий геолог',
            'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com' => 'Суханова Л.Б.',
            'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com' => 'Хвощинский В.В. - Начальник отдела',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetListFieldsLabel method
     *
     * @return void
     */
    public function testGetListFieldsLabel()
    {
        $shortInfo = [
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
        ];
        $shortInfoAlt = [
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
        ];
        $fullInfo = [
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
        ];
        $fullInfoAlt = [
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
        ];
        $params = [
            [
                false, // $useAlternative
                null, // $userRole
            ], // Params for step 1
            [
                false, // $useAlternative
                USER_ROLE_SECRETARY, // $userRole
            ], // Params for step 2
            [
                false, // $useAlternative
                USER_ROLE_HUMAN_RESOURCES, // $userRole
            ], // Params for step 3
            [
                false, // $useAlternative
                USER_ROLE_ADMIN, // $userRole
            ], // Params for step 4
            [
                true, // $useAlternative
                null, // $userRole
            ], // Params for step 5
            [
                true, // $useAlternative
                USER_ROLE_SECRETARY, // $userRole
            ], // Params for step 6
            [
                true, // $useAlternative
                USER_ROLE_HUMAN_RESOURCES, // $userRole
            ], // Params for step 7
            [
                true, // $useAlternative
                USER_ROLE_ADMIN, // $userRole
            ], // Params for step 8
        ];
        $expected = [
            $shortInfo, // Result of step 1
            $fullInfo, // Result of step 2
            $fullInfo, // Result of step 3
            $fullInfo, // Result of step 4
            $shortInfoAlt, // Result of step 5
            $fullInfoAlt, // Result of step 6
            $fullInfoAlt, // Result of step 7
            $fullInfoAlt, // Result of step 8
        ];
        $this->runClassMethodGroup('getListFieldsLabel', $params, $expected);
    }

    /**
     * testPrepareDataForSaveInvalidData method
     *
     * @return void
     */
    public function testPrepareDataForSaveInvalidData()
    {
        $data = ['EmployeeEdit' => ['Bad Data']];
        $result = $this->_targetObject->prepareDataForSave($data);
        $this->assertFalse($result);
    }

    /**
     * testPrepareDataForSaveValidData method
     *
     * @return void
     */
    public function testPrepareDataForSaveValidData()
    {
        $cfgPath = PROJECT_CONFIG_NAME . '.ReadOnlyFields';
        $excludeReadOnlyFields = [CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME];
        $readOnlyFields = Configure::read($cfgPath);
        $readOnlyFields = unserialize($readOnlyFields);
        $readOnlyFields[] = CAKE_LDAP_LDAP_ATTRIBUTE_MAIL;
        $readOnlyFields = array_values(array_diff($readOnlyFields, $excludeReadOnlyFields));
        $result = Configure::write($cfgPath, serialize($readOnlyFields));
        $this->assertTrue($result);

        $data = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => '  ',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => ' ',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => '  сУХановА',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => '  лариса  ',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => '	БОРИСОВНА ',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'глаВНЫй СпЕЦИАЛИСт',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'отдел связи',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '203',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375 29 500-00-02',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '507',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'l.suhanova@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0001',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '5002',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'НовТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1997-01-12',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '805203',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '8 017 100-00-03',
                    '8 017 100-00-05'
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    '8(029)100-00-01'
                ],
            ]
        ];
        $result = $this->_targetObject->prepareDataForSave($data);
        $this->assertTrue($result);

        $expected = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Главный специалист',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'Отдел связи',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '203',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000002',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '507',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0001',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '5002',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1997-01-12',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '805203',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375171000003',
                    '+375171000005'
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    '+375291000001'
                ],
            ]
        ];
        $this->assertData($expected, $data);
    }

    /**
     * testSaveInformationEmptyDataUser method
     *
     * User role: user.
     * @return void
     */
    public function testSaveInformationEmptyDataUser()
    {
        $result = $this->_targetObject->saveInformation(null, null, false, false);
        $this->assertFalse($result);
    }

    /**
     * testSaveInformationInvalidDataUser method
     *
     * User role: user.
     * @return void
     */
    public function testSaveInformationInvalidDataUser()
    {
        $data = ['EmployeeEdit' => ['Bad Data']];
        $result = $this->_targetObject->saveInformation($data, null, false, false);
        $this->assertFalse($result);
    }

    /**
     * testSaveInformationInvalidData method
     *
     * @return void
     */
    public function testSaveInformationInvalidData()
    {
        $data = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Главный специалист',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'Отдел связи',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '203',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375172000002',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '507',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'badEmail@somedomain',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0001',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '5002',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1997-01-12',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '805203',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375291000001',
                    '+375295000002'
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    '+375173000001'
                ],
            ]
        ];
        $params = [
            [
                $data, // $data
                null, // $userRole
                true, // $validate
                true, // $includeExistsDeferredSaveInfo
            ], // Params for step 1
            [
                $data, // $data
                USER_ROLE_SECRETARY, // $userRole
                true, // $validate
                true, // $includeExistsDeferredSaveInfo
            ], // Params for step 2
            [
                $data, // $data
                USER_ROLE_HUMAN_RESOURCES, // $userRole
                true, // $validate
                true, // $includeExistsDeferredSaveInfo
            ], // Params for step 3
            [
                $data, // $data
                USER_ROLE_ADMIN, // $userRole
                true, // $validate
                true, // $includeExistsDeferredSaveInfo
            ], // Params for step 4
        ];
        $expected = [
            false, // Result of step 1
            false, // Result of step 2
            false, // Result of step 3
            false, // Result of step 4
        ];
        $this->runClassMethodGroup('saveInformation', $params, $expected);
    }

    /**
     * testSaveInformationValidDataKeepInternalFlag method
     *
     * @return void
     */
    public function testSaveInformationValidDataKeepInternalFlag()
    {
        $data = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Главный специалист',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'Отдел связи',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '203',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000002',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '507',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'l.suhanova@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0001',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '5002',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'НовТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1997-01-12',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '805203',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375171000003',
                    '+375171000005'
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    '+375291000001'
                ],
            ]
        ];
        $params = [
            [
                $data, // $data
                null, // $userRole
                false, // $validate
                true, // $includeExistsDeferredSaveInfo
            ], // Params for step 1
            [
                $data, // $data
                USER_ROLE_SECRETARY, // $userRole
                false, // $validate
                true, // $includeExistsDeferredSaveInfo
            ], // Params for step 2
            [
                $data, // $data
                USER_ROLE_HUMAN_RESOURCES, // $userRole
                false, // $validate
                true, // $includeExistsDeferredSaveInfo
            ], // Params for step 3
            [
                $data, // $data
                USER_ROLE_ADMIN, // $userRole
                false, // $validate
                true, // $includeExistsDeferredSaveInfo
            ], // Params for step 4
        ];
        $expected = [
            null, // Result of step 1
            null, // Result of step 2
            null, // Result of step 3
            null, // Result of step 4
        ];
        $this->runClassMethodGroup('saveInformation', $params, $expected);
    }

    /**
     * testSaveInformationValidDataUser method
     *
     * User role: user.
     * @return void
     */
    public function testSaveInformationValidDataUser()
    {
        $data = [
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
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '327',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000004',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '314',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'v.hvoshchinskiy@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0386',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1304',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-08',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501321',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375171000008',
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    '+375291234567'
                ],
            ]
        ];
        $result = $this->_targetObject->saveInformation($data, null, true, true);
        $this->assertNull($result);
    }

    /**
     * testSaveInformationValidDataSecretary method
     *
     * User role: secretary.
     * @return void
     */
    public function testSaveInformationValidDataSecretary()
    {
        $data = [
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
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '327',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000004',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '314',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'v.hvoshchinskiy@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0386',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1304',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-08',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501321',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375171000008',
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    '+375291234567'
                ],
            ]
        ];
        $result = $this->_targetObject->saveInformation($data, USER_ROLE_SECRETARY, true, true);
        $this->assertNull($result);
    }

    /**
     * testSaveInformationValidDataHr method
     *
     * User role: human resources.
     * @return void
     */
    public function testSaveInformationValidDataHr()
    {
        $data = [
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
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '327',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000004',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '314',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'v.hvoshchinskiy@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAPAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQABgQEBAUEBgUFBgkGBQYJCwgGBggLDAoKCwoKDBAMDAwMDAwQDA4PEA8ODBMTFBQTExwbGxscHx8fHx8fHx8fHwEHBwcNDA0YEBAYGhURFRofHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8f/8AAEQgAyADIAwERAAIRAQMRAf/EAHEAAAIDAQEBAQAAAAAAAAAAAAAFAwQGAgcBCAEBAAMBAQAAAAAAAAAAAAAAAAECAwQFEAACAQMEAgICAwEBAAAAAAAAAQIDBAURISIGMRJBMkIUURMWYVIRAQEBAQEBAQAAAAAAAAAAAAABAhEDEhP/2gAMAwEAAhEDEQA/AP1SAAfGgPmgHSQAAAAAB8ctAI5VkvkCGd3FfJAhd/BfJI+LIQ/kCSF7B/IE8LiL+QJYzTA61AAAAAAAAAAAAAAAAAAAAA5lLQCncXUYJ7gJ7zLwhryIQR3fZIR15EdCyr2uKf3HRxHtkdfuOi5b9og2uY6HNnn4T05APLXIxmluSkxpVlJEiZPUAAAAAAAAAAAAAAAAAA+SegFK6uFFPcgZrLZVQUtyOjD5nsDj7aSK9OMbkex1NXyI6cJa3Yquv2HTiFdiq6/YdOLtr2aomuQ6jjSYrtEtVrMno3OF7Cp+vIt0bTHZBVIrckOaVRSRKUyAAAAAAAAAAAAAAAAAhrT0QCDK3frF7lalg85kJP2SZS1MjC5SrVqNlLpeZZy6tq0m/JX6W+C+pj6zfhj6T8Inj6y+GR9I+HKtq0H8k/SLhes61anJeS00pctjgspOMopsvKpY9LwGTclHcvENvYXHtFEpMoPVEjoAAAAAAAAAAAAAA+SewFG8qaRZAyOauHpLcpatIxWQjKpJmOtNc5LXiXUfgyum0y+/5z2/Ejq3y5fVtfxHTjifVdvoOnFK46w1+JH0fJbWwMoP6lppS4S2drOlNbGmdMtYbbr9eUXFam2axsejYevrCJpENDRlqiRKAAAAAAAAAAAAAAczewCnIT0iytTGQyrcmzHVa5hL+p7z8HPqujOTG0xkXpsZ9acM6WKhp4HUVMsRD/yWV6+SxENPqQdUrnDQafEravCS+wsd+JHV/klrYv0l4Nc6ZawvYuk4TR0Zrm3lu8LJ+sTeMa1Nu9YoshYAAAAAAAAAAAAAAI6r2ICbIvZlNVeRl72Gsmc+66MZQUaK9jm1XTnJrbQikivVuGFJxJlUsWIepbrOx04x0CIrV4x0K1pkqu6UWmUbSEd3bR1exfNNZRWtDSaOnFcnplrMRHRI6cuXUae2+qLqLKJAAAAAAAAAAAAABDWexFTCe/3TMtVtmM/cw5M5d11YyhitGc1rpzlPGtoR1b5TU7r/AKW6pcrdK6/6T1lcJncbE9R8qte5RFq8yXXFwnqVraQurS9mImx3bQ1kjoxXN6ZaXGQ00OvNcW40Nv8AVGsY1ZRKAAAAAAAABqAagAAwK9Z7Favkovfk59104hPXjuzl3XXiK0oGFbxFJSCRH2TJQsUptEdV4mdV6E9R8qteo2OpkUark2SvEPq2wlctIcka4rDcaPHrRI68Vw+kPaHhG8c1WV4LKgAAAAAAAPgAB9QA/AFestilaZKbteTn26vMqqx3OTTryj/q1M+L9H62paRW6cu20+BwlH9WhSryhxIShnS1LQRStiyOuP1tH4CerFvS0aL5Z6PLGPg6sOP0OqHhHRHJpZXguoAAAAAAAA+AAH1ADAhqrYrV8ld3Hyc+46vOldWO5y6jrzXMEjNarEYLQtGdcTikRUxWqNIpW2Yic0VW46joy0VqRU00WUridNEnX2lBammYrqm9lHwdWI5PSm9FbG8cuk6LKAAAAAAAAAD4B9AAI6i2Iq0L7qOzMdRvik9wtGzl3HXioI1EmY1qnhVWhHUccVauxFq2cl9etozO1vnKt+xuR1p8p6NfUmVTWVyFVaF5WNj5KaZeKpKG8jbEZbpzZx8HViOTdNKS2No56lJVAAAAAAAAAAEAJAHM/BCVC6aSZTUaZpDe1Umzm3HVjRZK6Sl5OXTpykp3afyZ2tZkVK+q8letM5Ua9Uq3zlVdR6kL8TUq2hKmsrUbnReS0rHWXSuU35NssNGFnL2aOnEcu6f2cdkdWY5NUygti7KuyUAAAAAAAAAAAAACKrPRAJsjdxhF7lamVkcplIpvc59x0YpJLKpy8nHt3edWrfIJ/Jz105XI3PsvJXrfMR1J6hrIgkyVh/b6kK1xO9UfktHPt8o5BOXk6cRx+mmhxVypNbnXiOLemssWnFHRI57TGHgso6AAAAAAAAAAAA1QHEppAULy6jGL3CGNzuVUFLcikrz7K5pub5HPtv5lUMo3Lyce3d5mlnkvG5zWOvNOra+UktyvG2dLsaykvIazT5KaJT9Kle4UU9wrdE95kfXXc0zHN6aU6OX0qeTq844PXTXYDKKTjudmY4t6ehYq6UoLc0Z9O6ck0SJAAAAAAAAABvQDiVRICvVuox+Qjpfc5OEU9yeK2s7lc5GMXyJ4rdPPuwZxP20kVsTKw15kHUqPc5fR1eQt6kmcW678QxoV5R0Mm0pna5Fprcji80b2+TWnkcXm008jHTyOJ+y67v8AVPcnit2z9/eSeu5pmMN6Kf3pRqeTq844vStR1/L+s46yOvMcWq9RwGXjKEeRpxTrYWl7GUVuFpV+FZNEJSKWoS+gAAAADegEFWqooILbvIRgnuTxW0gv87GGvItxS6ZjJdmS15E8UumRy3ZtVLkTxT6YzJ5p1JPkU1F80so3P9k/Jx+rv8T/AB9L2SODb0vMydrL12Rn1pYgkpwZeKV3C8nH5JOpf35v5HD6cTrTmSjqjdQk0y0U0RXc3CTOnzcnomxuVdOotzsxHDut/gOxqPryNeMvpvsV2KMlHkOLTTT2WXhNLkV4vNG1C7jJeSFpVyE00Ql2EgAA4qS0QCnIXXpF7kxS1jM1mfT25F5GWtMJl+xNOXIvIxumRyOflJvkW4pdM7e5ecteQ4dJ6985S8meo0xVnH3Gs1ucfrHoeNbfCSjJRPP9I9PyrUUraM4HO6Ve5xv8ItKrcl1THyT8Fuq3L5Cwnr4J6rxZhYtLdE9RxSv6ShBl8s9Mbl6qjJnX5xx+tI43zhU8nbiPP3T3GZuUGuRtIxtbHE9mkvXmOE02mI7Nr68iti802uJzaqJciljWaamzulNLcq0lMYS1RCzoAAhrvSLCGYzddxhItGenl/Zb+SctzWOfVecZbIycpbl4xtZ25u5NvclBdXrNkJilOo9SlaZWbK59Zrc5vTLs89Nng8nGPrqzh9MPR8ttzjsnTlFbnNrDtzsz/vpTXkp8r9Ryp0pDiX2NCkTxWo7iVKEWXkZ6rLZu+hGMtzfGXN6aef5e89pS3Ozzy4PTRDKs3I6sxx6q1b3MotbmkY04sslOLW5KGoxGampR5EWJlei9czEpOPIpY1zXpuEu3OMdzOt81p6EtYoq0icJAFe5+rCKyPYZcJF4z08i7TUftM0jm084yVR+7NGNJ6r1YQq1CFoqVERV5USrODMtRtjRhZZZ02tzn3h2ee2mx3ZfVLkc+vN1Z9T+27OmlyMr5Np6r9PskH+RX81/1T/6KGn2H5l9C++7HH1ekjTPmy16Mhl83768joxhy72y91dOpJ7nTnLj3pXjuzWRz2p6eqLKLdGbTJQcY64kpLcD0Xq1zL2huUrTL2DrdVuETOujLb2j4oq1i0QkAV7n6sIY/sX0kXjPTyHtP2maRz6ecZFc2XY0rqRJVVqiCVWqiFoX3EtClaZUpXMovyZ2N86TUcnOL8lLlrNmFDNzX5Fbhaei5T7BNfkV/Nb9Uv8Aop6fYfmn9Va4z05L7EzCt9CyvkZTfk0mWOtI6dVyZrIx1VykizKp4olCWHklBnj3zRA9E6q+UCta5ey9Y+kDOt8t1afRFWsXCEgCvc/VhDH9i+ki0Z6eRdoXKZrHPp53kI82XjGllSBKqrViEqVdELQrun5K1pCus9yrSIVJkcW67jVkhw6kVeX8kcOj9iX8jieuJV5P5HDojUbZKtW7eXgtFKZ0HsSzq1FEoSRjuEGWPXNBL0Tqq5QK1pl7L1j6RM63y3Vp9EVaxcISAK9z9WEVkOwrhIvGenkvZ4cpGkc+nn9/T5s0jGldSmFVWrTCS+5jsyFoT3a8la0hXVjuVXRegS6UAgOIS+OIHPqB9igLdv5RKtNbb4JUq/ThsSqnhTJQZWFPmgPQerQ5QK1pl7H1lcImVdGW5tPqirWLZCQBBcfVhDJ5+OsJF4z08p7LT5SNcubbBX9Hmy8Y0sqUiVVOvT2ISVXcdmRV4S3Ud2VaQvnT3IXcqkB9/qCA6QHDphLl0wl9VMCxQhuFaa2sPBZSmlGlsSoswpEoMrGlzQTG+6xT0lEpWmXr3W48ImVdGW2tPqirWLZCQBBX+rCKy2dXCReM9PL+xw5SNcubbC31LkzSMKWVaQQoXFPZhMJr2PkrV4SXK3ZVpFOUNwsFTCH1UwB0wOXTIS4dMJCpgWKFPclFNbSn4JjOm1ClsWVq3TpEqmNjS5ohMbvrdPlEpWuXrPXVwiZ10ZbO1+qKNYtEJf/Z'),
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0386',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1304',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-08',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501321',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375171000008',
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                    '+375291234567'
                ],
            ]
        ];
        $result = $this->_targetObject->saveInformation($data, USER_ROLE_HUMAN_RESOURCES, true, true);
        $this->assertTrue($result);
    }

    /**
     * testSaveInformationValidDataAdmin method
     *
     * User role: admin.
     * @return void
     */
    public function testSaveInformationValidDataAdmin()
    {
        $data = [
            'EmployeeEdit' => [
                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Суханова Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'Л.Б.',
                CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Суханова',
                CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Лариса',
                CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Борисовна',
                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Главный специалист',
                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '204',
                CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000001',
                CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '501',
                CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'l.suhanova@fabrikam.com',
                CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0216',
                CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1602',
                CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-16',
                CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501203',
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                    '+375171000003',
                    '+375171000004',
                    '+375171000005'
                ],
                CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
            ]
        ];
        $result = $this->_targetObject->saveInformation($data, USER_ROLE_ADMIN, true, false);
        $this->assertTrue($result);
    }

    /**
     * testResizePhotoInvalidFilePath method
     *
     * @return void
     */
    public function testResizePhotoInvalidFilePath()
    {
        $result = $this->_targetObject->resizePhoto('test', PHOTO_WIDTH, PHOTO_HEIGHT);
        $this->assertFalse($result);
    }

    /**
     * testResizePhotoInvalidFormat method
     *
     * @return void
     */
    public function testResizePhotoInvalidFormat()
    {
        $imageFile = $this->createTestPhotoFile($this->_pathImportDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, false);
        $this->assertTrue(is_string($imageFile));
        $result = $this->_targetObject->resizePhoto($imageFile, PHOTO_WIDTH, PHOTO_HEIGHT);
        $this->assertFalse($result);
    }

    /**
     * testResizePhotoValidFormatGreatSize method
     *
     * @return void
     */
    public function testResizePhotoValidFormatGreatSize()
    {
        $imageFile = $this->createTestPhotoFile($this->_pathImportDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, true);
        $this->assertTrue(is_string($imageFile));
        $result = $this->_targetObject->resizePhoto($imageFile, PHOTO_WIDTH, PHOTO_HEIGHT, '80');
        $this->assertTrue($result);

        list($width, $height) = getimagesize($imageFile);
        $this->assertSame(PHOTO_WIDTH, $width);
        $this->assertSame(PHOTO_HEIGHT, $height);
    }

    /**
     * testResizePhotoValidFormatLessSize method
     *
     * @return void
     */
    public function testResizePhotoValidFormatLessSize()
    {
        $imageFile = $this->createTestPhotoFile($this->_pathImportDir, PHOTO_WIDTH - 10, PHOTO_HEIGHT - 10, true);
        $this->assertTrue(is_string($imageFile));
        $result = $this->_targetObject->resizePhoto($imageFile, PHOTO_WIDTH, PHOTO_HEIGHT, 50);
        $this->assertTrue($result);

        list($width, $height) = getimagesize($imageFile);
        $this->assertSame(PHOTO_WIDTH, $width);
        $this->assertSame(PHOTO_HEIGHT, $height);
    }

    /**
     * testResizePhotoValidFormat method
     *
     * @return void
     */
    public function testResizePhotoValidFormat()
    {
        $imageFile = $this->createTestPhotoFile($this->_pathImportDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT - 10, true);
        $this->assertTrue(is_string($imageFile));
        $result = $this->_targetObject->resizePhoto($imageFile, PHOTO_WIDTH, PHOTO_HEIGHT, null);
        $this->assertTrue($result);

        list($width, $height) = getimagesize($imageFile);
        $this->assertSame(PHOTO_WIDTH, $width);
        $this->assertSame(PHOTO_HEIGHT, $height);
    }

    /**
     * testUpdatePhotoEmptyPathUser method
     *
     * User role: user.
     * @return void
     */
    public function testUpdatePhotoEmptyPathUser()
    {
        $result = $this->_targetObject->updatePhoto('0010b7b8-d69a-4365-81ca-5f975584fe5c', null, UPLOAD_FILE_SIZE_LIMIT, null, false);
        $expected = __('Invalid file for update.');
        $this->assertData($expected, $result);
    }

    /**
     * testUpdatePhotoInvalidPathUser method
     *
     * User role: user.
     * @return void
     */
    public function testUpdatePhotoInvalidPathUser()
    {
        $result = $this->_targetObject->updatePhoto('0010b7b8-d69a-4365-81ca-5f975584fe5c', TMP, UPLOAD_FILE_SIZE_LIMIT, null, false);
        $expected = __('Invalid file for update.');
        $this->assertData($expected, $result);
    }

    /**
     * testUpdatePhotoInvalidFileSizeUser method
     *
     * User role: user.
     * @return void
     */
    public function testUpdatePhotoInvalidFileSizeUser()
    {
        $maxFileSize = 1;
        $imageFile = $this->createTestPhotoFile($this->_pathImportDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, true, false);
        $this->assertTrue(is_string($imageFile));
        $oFile = new File($imageFile, true);
        $result = $this->_targetObject->updatePhoto('0010b7b8-d69a-4365-81ca-5f975584fe5c', $imageFile, $maxFileSize, null);
        $fileSize = $oFile->size();
        $expected = __(
            'File size is %s. Limit - %s.',
            CakeNumber::toReadableSize($fileSize),
            CakeNumber::toReadableSize($maxFileSize)
        );
        $this->assertData($expected, $result);
    }

    /**
     * testUpdatePhotoEmptyGuidUser method
     *
     * User role: user.
     * @return void
     */
    public function testUpdatePhotoEmptyGuidUser()
    {
        $this->setExpectedException('NotFoundException');
        $imageFile = $this->createTestPhotoFile($this->_pathImportDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, true, false);
        $this->assertTrue(is_string($imageFile));
        $this->_targetObject->updatePhoto(null, $imageFile, UPLOAD_FILE_SIZE_LIMIT, null);
    }

    /**
     * testUpdatePhotoInvalidGuidUser method
     *
     * User role: user.
     * @return void
     */
    public function testUpdatePhotoInvalidGuidUser()
    {
        $this->setExpectedException('NotFoundException');
        $imageFile = $this->createTestPhotoFile($this->_pathImportDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, true, false);
        $this->assertTrue(is_string($imageFile));
        $this->_targetObject->updatePhoto('85540b34-6d90-4263-8426-5c846caf96d7', $imageFile, UPLOAD_FILE_SIZE_LIMIT, null);
    }

    /**
     * testUpdatePhotoInvalidFormatUser method
     *
     * User role: user.
     * @return void
     */
    public function testUpdatePhotoInvalidFormatUser()
    {
        $imageFile = $this->createTestPhotoFile($this->_pathImportDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, false, false);
        $this->assertTrue(is_string($imageFile));
        $result = $this->_targetObject->updatePhoto('0010b7b8-d69a-4365-81ca-5f975584fe5c', $imageFile, UPLOAD_FILE_SIZE_LIMIT, null);
        $expected = __('The uploaded photo is not a jpeg');
        $this->assertData($expected, $result);
    }

    /**
     * testUpdatePhotoValidFileUserUseDb method
     *
     * User role: User.
     * @return void
     */
    public function testUpdatePhotoValidFileUserUseDb()
    {
        $imageFile = $this->createTestPhotoFile($this->_pathImportDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, true, false);
        $guid = '0010b7b8-d69a-4365-81ca-5f975584fe5c';
        $this->assertTrue(is_string($imageFile));
        $result = $this->_targetObject->updatePhoto($guid, $imageFile, UPLOAD_FILE_SIZE_LIMIT, null);
        $this->assertNull($result);
        $modelDeferred = ClassRegistry::init('Deferred');
        $findOpt = [
            'conditions' => ['Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => $guid],
            'recursive' => 0,
        ];

        $deferredSave = $modelDeferred->find('first', $findOpt);
        $this->assertTrue(isset($deferredSave['Deferred']['internal']));
        $this->assertTrue(isset($deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]));
        $this->assertTrue(isset($deferredSave['Deferred']['data']['current']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]));
        $this->assertFalse($deferredSave['Deferred']['internal']);
        $this->assertTrue($deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO] !== $deferredSave['Deferred']['data']['current']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]);
    }

    /**
     * testUpdatePhotoValidFileUserUseLdap method
     *
     * User role: User.
     * @return void
     */
    public function testUpdatePhotoValidFileUserUseLdap()
    {
        $imageFile = $this->createTestPhotoFile($this->_pathImportDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT + 10, true, true);
        $guid = 'd4bd663f-37da-4737-bfd8-e6442e723722';
        $this->assertTrue(is_string($imageFile));
        $result = $this->_targetObject->updatePhoto($guid, $imageFile, UPLOAD_FILE_SIZE_LIMIT, null);
        $this->assertNull($result);
        $modelDeferred = ClassRegistry::init('Deferred');
        $findOpt = [
            'conditions' => ['Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => $guid],
            'recursive' => 0,
        ];

        $deferredSave = $modelDeferred->find('first', $findOpt);
        $this->assertTrue(isset($deferredSave['Deferred']['internal']));
        $this->assertTrue(isset($deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]));
        $this->assertTrue(isset($deferredSave['Deferred']['data']['current']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]));
        $this->assertFalse($deferredSave['Deferred']['internal']);
        $this->assertTrue($deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO] !== $deferredSave['Deferred']['data']['current']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]);
    }

    /**
     * testUpdatePhotoValidFile method
     *
     * User role: Secretary, Human resources, Administrator.
     * @return void
     */
    public function testUpdatePhotoValidFile()
    {
        $imageFile = $this->createTestPhotoFile($this->_pathImportDir, PHOTO_WIDTH + 10, PHOTO_HEIGHT - 10, true);
        $this->assertTrue(is_string($imageFile));
        $params = [
            [
                '0400f8f5-6cba-4f1e-8471-fa6e73415673', // $guid
                $imageFile, // $fileName
                UPLOAD_FILE_SIZE_LIMIT, // $maxFileSize
                null, // $userRole
                false, // $useLdap
            ], // Params for step 1
            [
                '8c149661-7215-47de-b40e-35320a1ea508', // $guid
                $imageFile, // $fileName
                UPLOAD_FILE_SIZE_LIMIT, // $maxFileSize
                USER_ROLE_SECRETARY, // $userRole
                false, // $useLdap
            ], // Params for step 2
            [
                '9d6cf30f-a579-4cbc-8dd1-c92a43b65aaf', // $guid
                $imageFile, // $fileName
                UPLOAD_FILE_SIZE_LIMIT, // $maxFileSize
                USER_ROLE_HUMAN_RESOURCES, // $userRole
                false, // $useLdap
            ], // Params for step 3
            [
                '971327c0-0863-4c83-8e57-91007b506e5d', // $guid
                $imageFile, // $fileName
                UPLOAD_FILE_SIZE_LIMIT, // $maxFileSize
                USER_ROLE_ADMIN, // $userRole
                false, // $useLdap
            ], // Params for step 4
        ];
        $expected = [
            null, // Result of step 1
            null, // Result of step 2
            true, // Result of step 3
            true, // Result of step 4
        ];
        $this->runClassMethodGroup('updatePhoto', $params, $expected);
    }

    /**
     * testClearPhotoEmptyGuidUser method
     *
     * User role: user.
     * @return void
     */
    public function testClearPhotoEmptyGuidUser()
    {
        $this->setExpectedException('NotFoundException');
        $result = $this->_targetObject->clearPhoto(null, null, false);
    }

    /**
     * testClearPhotoInvalidGuidUser method
     *
     * User role: user.
     * @return void
     */
    public function testClearPhotoInvalidGuidUser()
    {
        $this->setExpectedException('NotFoundException');
        $result = $this->_targetObject->clearPhoto('3eb2666a-a9e7-44dc-9ce8-e0c2ea90f308', null, false);
    }

    /**
     * testClearPhotoValidParamUser method
     *
     * User role: User.
     * @return void
     */
    public function testClearPhotoValidParamUser()
    {
        $guid = '0400f8f5-6cba-4f1e-8471-fa6e73415673';
        $result = $this->_targetObject->clearPhoto($guid, null, false);
        $this->assertNull($result);
        $modelDeferred = ClassRegistry::init('Deferred');
        $findOpt = [
            'conditions' => ['Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => $guid],
            'recursive' => 0,
        ];

        $deferredSave = $modelDeferred->find('first', $findOpt);
        $this->assertTrue(isset($deferredSave['Deferred']['internal']));
        $this->assertTrue(isset($deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]));
        $this->assertFalse($deferredSave['Deferred']['internal']);
        $this->assertEmpty($deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]);
    }

    /**
     * testClearPhotoValidParamUserKeepIntarnalFlag method
     *
     * User role: User.
     * @return void
     */
    public function testClearPhotoValidParamUserKeepIntarnalFlag()
    {
        $guid = '1dde2cdc-5264-4286-9273-4a88b230237c';
        $result = $this->_targetObject->clearPhoto($guid, null, false);
        $this->assertTrue($result);
        $modelDeferred = ClassRegistry::init('Deferred');
        $findOpt = [
            'conditions' => ['Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => $guid],
            'recursive' => 0,
        ];

        $deferredSave = $modelDeferred->find('first', $findOpt);
        $this->assertTrue(isset($deferredSave['Deferred']['internal']));
        $this->assertTrue(isset($deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]));
        $this->assertTrue($deferredSave['Deferred']['internal']);
        $this->assertEmpty($deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO]);
    }

    /**
     * testClearPhotoValidParam method
     *
     * User role: Secretary, Human resources, Administrator.
     * @return void
     */
    public function testClearPhotoValidParam()
    {
        $params = [
            [
                '0400f8f5-6cba-4f1e-8471-fa6e73415673', // $guid
                null, // $userRole
                false, // $useLdap
            ], // Params for step 1
            [
                '8c149661-7215-47de-b40e-35320a1ea508', // $guid
                USER_ROLE_SECRETARY, // $userRole
                true, // $useLdap
            ], // Params for step 2
            [
                '9d6cf30f-a579-4cbc-8dd1-c92a43b65aaf', // $guid
                USER_ROLE_HUMAN_RESOURCES, // $userRole
                false, // $useLdap
            ], // Params for step 3
            [
                'b3ec524a-69d0-4fce-b9c2-3b59956cfa25', // $guid
                USER_ROLE_ADMIN, // $userRole
                true, // $useLdap
            ], // Params for step 4
        ];
        $expected = [
            null, // Result of step 1
            null, // Result of step 2
            true, // Result of step 3
            true, // Result of step 4
        ];
        $this->runClassMethodGroup('clearPhoto', $params, $expected);
    }

    /**
     * testChangeManagerEmptyEmployeeDnUser method
     *
     * User role: user.
     * @return void
     */
    public function testChangeManagerEmptyEmployeeDnUser()
    {
        $this->setExpectedException('NotFoundException');
        $result = $this->_targetObject->changeManager(null, 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com', null, false);
    }

    /**
     * testChangeManagerInvalidEmployeeDnUser method
     *
     * User role: user.
     * @return void
     */
    public function testChangeManagerInvalidEmployeeDnUser()
    {
        $this->setExpectedException('NotFoundException');
        $result = $this->_targetObject->changeManager('CN=Some user,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com', 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com', null, false);
    }

    /**
     * testChangeManagerEqualsUser method
     *
     * User role: user.
     * @return void
     */
    public function testChangeManagerEqualsUser()
    {
        $employeeDn = 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com';
        $result = $this->_targetObject->changeManager($employeeDn, $employeeDn, null, false);
        $this->assertFalse($result);
    }

    /**
     * testChangeManagerValidParamHrKeepInternalFlag method
     *
     * User role: Human resources.
     * @return void
     */
    public function testChangeManagerValidParamHrKeepInternalFlag()
    {
        $employeeDn = 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com';
        $employeeGuid = 'd4bd663f-37da-4737-bfd8-e6442e723722';
        $managerDn = 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com';
        $result = $this->_targetObject->changeManager($employeeDn, $managerDn, USER_ROLE_HUMAN_RESOURCES, false);
        $this->assertNull($result);
        $modelDeferred = ClassRegistry::init('Deferred');
        $findOpt = [
            'conditions' => ['Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => $employeeGuid],
            'recursive' => 0,
        ];

        $deferredSave = $modelDeferred->find('first', $findOpt);
        $this->assertTrue(isset($deferredSave['Deferred']['internal']));
        $this->assertTrue(isset($deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER]));
        $this->assertFalse($deferredSave['Deferred']['internal']);
        $this->assertData($managerDn, $deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER]);
    }

    /**
     * testChangeManagerValidParam method
     *
     * User role: Secretary, Human resources, Administrator.
     * @return void
     */
    public function testChangeManagerValidParam()
    {
        $managerDn = 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com';
        $params = [
            [
                'CN=Матвеев Р.М.,OU=Пользователи,DC=fabrikam,DC=com', // $employeeDn
                $managerDn, // $managerDn
                null, // $userRole
                false, // $useLdap
            ], // Params for step 1
            [
                'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com', // $employeeDn
                null, // $managerDn
                USER_ROLE_SECRETARY, // $userRole
                true, // $useLdap
            ], // Params for step 2
            [
                'CN=Голубев Е.В.,OU=АТО,OU=Пользователи,DC=fabrikam,DC=com', // $employeeDn
                $managerDn, // $managerDn
                USER_ROLE_HUMAN_RESOURCES, // $userRole
                false, // $useLdap
            ], // Params for step 3
            [
                'CN=Марчук А.М.,OU=Пользователи,DC=fabrikam,DC=com', // $employeeDn
                $managerDn, // $managerDn
                USER_ROLE_ADMIN, // $userRole
                true, // $useLdap
            ], // Params for step 4
        ];
        $expected = [
            false, // Result of step 1
            null, // Result of step 2
            true, // Result of step 3
            true, // Result of step 4
        ];
        $this->runClassMethodGroup('changeManager', $params, $expected);
    }

    /**
     * testChangeDepartmentEmptyEmployeeDnUser method
     *
     * User role: user.
     * @return void
     */
    public function testChangeDepartmentEmptyEmployeeDnUser()
    {
        $this->setExpectedException('NotFoundException');
        $result = $this->_targetObject->changeDepartment(null, 'Отдел связи', null, false);
    }

    /**
     * testChangeDepartmentInvalidEmployeeDnUser method
     *
     * User role: user.
     * @return void
     */
    public function testChangeDepartmentInvalidEmployeeDnUser()
    {
        $this->setExpectedException('NotFoundException');
        $result = $this->_targetObject->changeDepartment('CN=Some user,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com', 'Отдел связи', null, false);
    }

    /**
     * testChangeDepartmentEqualsUser method
     *
     * User role: user.
     * @return void
     */
    public function testChangeDepartmentEqualsUser()
    {
        $employeeDn = 'CN=Матвеев Р.М.,OU=Пользователи,DC=fabrikam,DC=com';
        $result = $this->_targetObject->changeDepartment($employeeDn, 'ОРС', null, false);
        $this->assertFalse($result);
    }

    /**
     * testChangeDepartmentValidParamHrKeepInternalFlag method
     *
     * User role: Human resources.
     * @return void
     */
    public function testChangeDepartmentValidParamHrKeepInternalFlag()
    {
        $employeeDn = 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com';
        $employeeGuid = 'd4bd663f-37da-4737-bfd8-e6442e723722';
        $departmentName = 'Отдел связи';
        $result = $this->_targetObject->changeDepartment($employeeDn, $departmentName, USER_ROLE_HUMAN_RESOURCES, false);
        $this->assertNull($result);
        $modelDeferred = ClassRegistry::init('Deferred');
        $findOpt = [
            'conditions' => ['Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => $employeeGuid],
            'recursive' => 0,
        ];

        $deferredSave = $modelDeferred->find('first', $findOpt);
        $this->assertTrue(isset($deferredSave['Deferred']['internal']));
        $this->assertTrue(isset($deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT]));
        $this->assertFalse($deferredSave['Deferred']['internal']);
        $this->assertData($departmentName, $deferredSave['Deferred']['data']['changed']['EmployeeEdit'][CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT]);
    }

    /**
     * testChangeDepartmentValidParam method
     *
     * User role: Secretary, Human resources, Administrator.
     * @return void
     */
    public function testChangeDepartmentValidParam()
    {
        $departmentName = 'Отдел связи';
        $params = [
            [
                'CN=Матвеев Р.М.,OU=Пользователи,DC=fabrikam,DC=com', // $employeeDn
                $departmentName, // $departmentName
                null, // $userRole
                false, // $useLdap
            ], // Params for step 1
            [
                'CN=Хвощинский В.В.,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com', // $employeeDn
                null, // $departmentName
                USER_ROLE_SECRETARY, // $userRole
                true, // $useLdap
            ], // Params for step 2
            [
                'CN=Голубев Е.В.,OU=АТО,OU=Пользователи,DC=fabrikam,DC=com', // $employeeDn
                $departmentName, // $departmentName
                USER_ROLE_HUMAN_RESOURCES, // $userRole
                false, // $useLdap
            ], // Params for step 3
            [
                'CN=Марчук А.М.,OU=Пользователи,DC=fabrikam,DC=com', // $employeeDn
                $departmentName, // $departmentName
                USER_ROLE_ADMIN, // $userRole
                true, // $useLdap
            ], // Params for step 4
        ];
        $expected = [
            null, // Result of step 1
            null, // Result of step 2
            true, // Result of step 3
            true, // Result of step 4
        ];
        $this->runClassMethodGroup('changeDepartment', $params, $expected);
    }

    /**
     * testGetListEmployeesByDepartmentName method
     *
     * @return void
     */
    public function testGetListEmployeesByDepartmentName()
    {
        $params = [
            [
                null, // $name
                10, // $limit
            ], // Params for step 1
            [
                'BAD_DEPART', // $name
                null, // $limit
            ], // Params for step 2
            [
                'ОС', // $name
                null, // $limit
            ], // Params for step 3
            [
                'ОИТ', // $name
                2, // $limit
            ], // Params for step 4
        ];
        $expected = [
            [], // Result of step 1
            [], // Result of step 2
            [
                'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com'
            ], // Result of step 3
            [
                'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                'CN=Козловская Е.М.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
            ], // Result of step 4
        ];
        $this->runClassMethodGroup('getListEmployeesByDepartmentName', $params, $expected);
    }

    /**
     * testGetListFieldsInputMask method
     *
     * @return void
     */
    public function testGetListFieldsInputMask()
    {
        $result = $this->_targetObject->getListFieldsInputMask();
        $expected = [
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
                'data-inputmask-greedy' => 'false'
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
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetListFieldsInputTooltip method
     *
     * @return void
     */
    public function testGetListFieldsInputTooltip()
    {
        $result = $this->_targetObject->getListFieldsInputTooltip();
        $expected = [
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
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetListManagersByQuery method
     *
     * @return void
     */
    public function testGetListManagersByQuery()
    {
        $params = [
            [
                null, // $query
                null, // $excludeDn
            ], // Params for step 1
            [
                ' Ма ', // $query
                null, // $excludeDn
            ], // Params for step 2
            [
                ' Ма ', // $query
                'CN=Марчук А.М.,OU=Пользователи,DC=fabrikam,DC=com', // $excludeDn
            ], // Params for step 3
        ];
        $expected = [
            [], // Result of step 1
            [
                [
                    'value' => 'CN=Марчук А.М.,OU=Пользователи,DC=fabrikam,DC=com',
                    'text' => 'Марчук А.М. - Ведущий инженер по охране труда'
                ],
                [
                    'value' => 'CN=Матвеев Р.М.,OU=Пользователи,DC=fabrikam,DC=com',
                    'text' => 'Матвеев Р.М. - Ведущий инженер'
                ]
            ], // Result of step 2
            [
                [
                    'value' => 'CN=Матвеев Р.М.,OU=Пользователи,DC=fabrikam,DC=com',
                    'text' => 'Матвеев Р.М. - Ведущий инженер'
                ]
            ], // Result of step 3
        ];
        $this->runClassMethodGroup('getListManagersByQuery', $params, $expected);
    }
}
