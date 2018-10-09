<?php
App::uses('AppControllerTestCase', 'Test');
App::uses('DeferredController', 'Controller');
App::uses('CakeText', 'Utility');

/**
 * DeferredController Test Case
 */
class DeferredControllerTest extends AppControllerTestCase
{

    /**
     * Target Controller name
     *
     * @var string
     */
    public $targetController = 'Deferred';

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.cake_session',
        'app.deferred',
        'app.department_extension',
        'app.log',
        'plugin.cake_ldap.department',
        'plugin.cake_ldap.employee',
        'plugin.cake_ldap.employee_ldap',
        'plugin.cake_ldap.othermobile',
        'plugin.cake_ldap.othertelephone',
        'plugin.queue.queued_task',
    ];

    /**
     * testIndexDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testIndexDenyNotHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'index',
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
     * testIndex method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testIndexForHrAndAdmin()
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
            'deferredSaves' => [
                [
                    'Deferred' => [
                        'id' => '2',
                        'employee_id' => '3',
                        'data' => [
                            'changed' => [
                                'EmployeeEdit' => [
                                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа №1',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
                                ]
                            ],
                            'current' => [
                                'EmployeeEdit' => [
                                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
                                ]
                            ]
                        ],
                        'created' => '2017-11-16 10:12:44',
                        'modified' => '2017-11-16 10:12:44',
                    ],
                    'Employee' => [
                        'id' => '3',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                        CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'l.suhanova@fabrikam.com',
                        CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
                    ],
                ],
                [
                    'Deferred' => [
                        'id' => '3',
                        'employee_id' => '4',
                        'data' => [
                            'changed' => [
                                'EmployeeEdit' => [
                                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '216',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'a.s.dementeva@fabrikam.com',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => '',
                                ]
                            ],
                            'current' => [
                                'EmployeeEdit' => [
                                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '123',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'a.dementeva@fabrikam.com',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => [
                                        'id' => '8',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Голубев Е.В.,OU=АТО,OU=Пользователи,DC=fabrikam,DC=com',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Голубев Е.В.',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Водитель',
                                    ]
                                ]
                            ]
                        ],
                        'created' => '2017-11-16 12:31:28',
                        'modified' => '2017-11-16 12:31:28',
                    ],
                    'Employee' => [
                        'id' => '4',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Дементьева А.С.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                        CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'a.dementeva@fabrikam.com',
                        CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'd4bd663f-37da-4737-bfd8-e6442e723722',
                    ],
                ],
                [
                    'Deferred' => [
                        'id' => '4',
                        'employee_id' => '6',
                        'data' => false,
                        'created' => '2017-11-16 14:48:22',
                        'modified' => '2017-11-16 14:48:22',
                    ],
                    'Employee' => [
                        'id' => '6',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Козловская Е.М.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Заведующий сектором',
                        CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'e.kozlovskaya@fabrikam.com',
                        CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '81817f32-44a7-4b4a-8eff-b837ba387077',
                    ],
                ],
                [
                    'Deferred' => [
                        'id' => '5',
                        'employee_id' => '2',
                        'data' => [
                            'changed' => [
                                'EmployeeEdit' => [
                                        CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОРС',
                                        CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => ['+375171000002', '+375171000007'],
                                        CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => ['+375291000008'],

                                    ]
                                ],
                            'current' => [
                                'EmployeeEdit' => [
                                    CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => ['+375171000002'],
                                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
                                ]
                            ]
                        ],
                        'created' => '2017-11-17 09:33:19',
                        'modified' => '2017-11-17 09:33:19',
                    ],
                    'Employee' => [
                        'id' => '2',
                        CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Егоров Т.Г.',
                        CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                        CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 't.egorov@fabrikam.com',
                        CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => '0010b7b8-d69a-4365-81ca-5f975584fe5c',
                    ],
                ],
            ],
            'groupActions' => [
                GROUP_ACTION_DEFERRED_SAVE_DELETE => __('Delete data group'),
                GROUP_ACTION_DEFERRED_SAVE_APPROVE => __('Approve data group'),
                GROUP_ACTION_DEFERRED_SAVE_REJECT => __('Reject data group'),
            ],
            'fieldsLabel' => [
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('cake_ldap_field_name', 'Surname'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('cake_ldap_field_name', 'Given name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('cake_ldap_field_name', 'Middle name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('cake_ldap_field_name', 'E-mail'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('cake_ldap_field_name', 'SIP telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
                'Othertelephone.{n}.value' => __d('app_ldap_field_name', 'Landline telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('cake_ldap_field_name', 'Mobile telephone'),
                'Othermobile.{n}.value' => __d('app_ldap_field_name', 'Personal mobile telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('cake_ldap_field_name', 'Office room'),
                'Department.value' => __d('cake_ldap_field_name', 'Department'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('cake_ldap_field_name', 'Subdivision'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('cake_ldap_field_name', 'Position'),
                'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __d('cake_ldap_field_name', 'Manager'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('cake_ldap_field_name', 'Birthday'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('cake_ldap_field_name', 'Computer'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('cake_ldap_field_name', 'Employee ID'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => __d('cake_ldap_field_name', 'GUID'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => __d('cake_ldap_field_name', 'Distinguished name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('cake_ldap_field_name', 'Photo'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => __d('cake_ldap_field_name', 'Company name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => __d('cake_ldap_field_name', 'Initials'),
                'Employee.block' => __d('cake_ldap_field_name', 'Block'),
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
            'pageHeader' => __('Index of deferred saves'),
            'breadCrumbs' => [
                [
                    CakeText::truncate(__('Employees'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                    [
                        'plugin' => null,
                        'controller' => 'employees',
                        'action' => 'index'
                    ]
                ],
                [
                    CakeText::truncate(__('Deferred saves'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                    [
                        'plugin' => null,
                        'controller' => 'deferred',
                        'action' => 'index'
                    ]
                ],
                __('Index'),
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
                'controller' => 'deferred',
                'action' => 'index',
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
     * testIndexBadGroupActionPostForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testIndexBadGroupActionPostForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'data' => [
                'FilterGroup' => [
                    'action' => 'BAD_ACTION',
                ],
                'FilterData' => [
                    [
                        'Deferred' => [
                            'id' => '2'
                        ]
                    ]
                ]
            ],
            'method' => 'POST',
            'return' => 'result',
        ];
        $this->testAction('/admin/deferred/index', $opt);
        $flashMsgPcre = '/' . __('Processing of selected tasks put in queue...') . '|' . __('Selected tasks has been processed.') . '|' . __('Selected tasks could not be processed. Please, try again.') . '/';
        $this->checkFlashMessage($flashMsgPcre, true, true);
    }

    /**
     * testIndexGroupActionBadConditionsPostForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testIndexGroupActionBadConditionsPostForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'data' => [
                'FilterGroup' => [
                    'action' => GROUP_ACTION_DEFERRED_SAVE_REJECT,
                ],
                'FilterData' => [
                    'BAD_CONDITIONS'
                ]
            ],
            'method' => 'POST',
            'return' => 'result',
        ];
        $result = $this->testAction('/admin/deferred/index', $opt);
        $flashMsgPcre = '/' . __('Processing of selected tasks put in queue...') . '|' . __('Selected tasks has been processed.') . '|' . __('Selected tasks could not be processed. Please, try again.') . '/';
        $this->checkFlashMessage($flashMsgPcre, true, true);
    }

    /**
     * testIndexGroupActionPostSuccessForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testIndexGroupActionPostSuccessForHr()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'data' => [
                'FilterGroup' => [
                    'action' => GROUP_ACTION_DEFERRED_SAVE_DELETE,
                ],
                'FilterData' => [
                    [
                        'Deferred' => [
                            'id' => ['1', '2']
                        ]
                    ]
                ]
            ],
            'method' => 'POST',
            'return' => 'result',
        ];
        $result = $this->testAction('/hr/deferred/index', $opt);
        $this->checkFlashMessage(__('Selected tasks has been processed.'));

        $result = $this->Controller->Deferred->find('list', ['recursive' => -1]);
        $expected = [
            3 => '3',
            4 => '4',
            5 => '5',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testIndexGroupActionPostSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testIndexGroupActionPostSuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'data' => [
                'FilterGroup' => [
                    'action' => GROUP_ACTION_DEFERRED_SAVE_REJECT,
                ],
                'FilterData' => [
                    [
                        'Deferred' => [
                            'id' => ['2', '3']
                        ]
                    ]
                ]
            ],
            'method' => 'POST',
            'return' => 'result',
        ];
        $result = $this->testAction('/admin/deferred/index', $opt);
        $this->checkFlashMessage(__('Processing of selected tasks put in queue...'));
    }

    /**
     * testViewDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testViewDenyNotHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'view',
                '1'
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
     * testViewEmptyIdForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testViewEmptyIdForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'view',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for deferred save'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testViewInvalidIdForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testViewInvalidIdForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'view',
                '1000',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for deferred save'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testViewSuccessForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testViewSuccessForHrAndAdmin()
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
            'deferredSave' => [
                'Deferred' => [
                    'id' => '2',
                    'employee_id' => '3',
                    'internal' => false,
                    'data' => [
                        'changed' => [
                            'EmployeeEdit' => [
                                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа №1',
                                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
                            ]
                        ],
                        'current' => [
                            'EmployeeEdit' => [
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                                CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
                            ]
                        ]
                    ],
                    'created' => '2017-11-16 10:12:44',
                    'modified' => '2017-11-16 10:12:44',
                ],
                'Employee' => [
                    'id' => '3',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Суханова Л.Б.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'l.suhanova@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
                ],
            ],
            'fieldsLabel' => [
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => __dx('app_ldap_field_name', 'employee', 'Name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => __d('cake_ldap_field_name', 'Surname'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => __d('cake_ldap_field_name', 'Given name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => __d('cake_ldap_field_name', 'Middle name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => __d('cake_ldap_field_name', 'E-mail'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => __d('cake_ldap_field_name', 'SIP telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => __d('app_ldap_field_name', 'Internal telephone'),
                'Othertelephone.{n}.value' => __d('app_ldap_field_name', 'Landline telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => __d('cake_ldap_field_name', 'Mobile telephone'),
                'Othermobile.{n}.value' => __d('app_ldap_field_name', 'Personal mobile telephone'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => __d('cake_ldap_field_name', 'Office room'),
                'Department.value' => __d('cake_ldap_field_name', 'Department'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => __d('cake_ldap_field_name', 'Subdivision'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => __d('cake_ldap_field_name', 'Position'),
                'Manager.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => __d('cake_ldap_field_name', 'Manager'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => __d('cake_ldap_field_name', 'Birthday'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => __d('cake_ldap_field_name', 'Computer'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => __d('cake_ldap_field_name', 'Employee ID'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => __d('cake_ldap_field_name', 'GUID'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => __d('cake_ldap_field_name', 'Distinguished name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => __d('cake_ldap_field_name', 'Photo'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => __d('cake_ldap_field_name', 'Company name'),
                'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => __d('cake_ldap_field_name', 'Initials'),
                'Employee.block' => __d('cake_ldap_field_name', 'Block'),
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
            'pageHeader' => __('Information of deferred save'),
            'headerMenuActions' => [
                [
                    'fas fa-pencil-alt',
                    __('Edit deferred save'),
                    ['controller' => 'deferred', 'action' => 'edit', '2'],
                    ['title' => __('Editing information of this deferred save')]
                ],
                [
                    'far fa-trash-alt',
                    __('Delete deferred save'),
                    ['controller' => 'deferred', 'action' => 'delete', '2'],
                    [
                        'title' => __('Delete deferred save'), 'action-type' => 'confirm-post',
                        'data-confirm-msg' => __('Are you sure you wish to delete this deferred save?')
                    ]
                ],
                [
                    'fas fa-check',
                    __('Approve deferred save data'),
                    ['controller' => 'deferred', 'action' => 'approve', '2'],
                    [
                        'title' => __('Approve deferred save data'), 'action-type' => 'confirm-post',
                        'data-confirm-msg' => __('Are you sure you wish to approve this deferred save?'),
                    ]
                ],
                [
                    'fas fa-times',
                    __('Reject deferred save data'),
                    ['controller' => 'deferred', 'action' => 'reject', '2'],
                    [
                        'title' => __('Reject deferred save data'), 'action-type' => 'confirm-post',
                        'data-confirm-msg' => __('Are you sure you wish to reject this deferred save?'),
                    ]
                ],
            ],
            'breadCrumbs' => [
                [
                    CakeText::truncate(__('Employees'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                    [
                        'plugin' => null,
                        'controller' => 'employees',
                        'action' => 'index'
                    ]
                ],
                [
                    CakeText::truncate('Суханова Л.Б.', CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                    [
                        '3',
                        'plugin' => null,
                        'controller' => 'employees',
                        'action' => 'view'
                    ]
                ],
                [
                    CakeText::truncate(__('Deferred saves'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                    [
                        '2',
                        'plugin' => null,
                        'controller' => 'deferred',
                        'action' => 'view'
                    ]
                ],
                __('Viewing'),
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
                'controller' => 'deferred',
                'action' => 'view',
                '2',
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
     * testEditDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testEditDenyNotHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'edit',
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
     * testEditEmptyIdForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testEditEmptyIdForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'edit',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for deferred save'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testEditInvalidIdForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testEditInvalidIdForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'edit',
                '1000',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for deferred save'));
            $this->checkRedirect(true);
        }
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
            'id' => '2',
            'dn' => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
            'guid' => 'dd518c55-35ce-4a5c-85c5-b5fb762220bf',
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
            'employeePhoto' => base64_decode('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'),
            'changedFields' => [
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION,
                'EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO,
            ],
            'forceDeferred' => true,
            'pageHeader' => __('Editing deferred save'),
            'breadCrumbs' => [
                [
                    CakeText::truncate(__('Employees'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                    [
                        'plugin' => null,
                        'controller' => 'employees',
                        'action' => 'index'
                    ]
                ],
                [
                    CakeText::truncate('Суханова Л.Б.', CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                    [
                        '3',
                        'plugin' => null,
                        'controller' => 'employees',
                        'action' => 'view'
                    ]
                ],
                [
                    CakeText::truncate(__('Deferred saves'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                    [
                        '2',
                        'plugin' => null,
                        'controller' => 'deferred',
                        'action' => 'view'
                    ]
                ],
                __('Editing'),
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
                'controller' => 'deferred',
                'action' => 'edit',
                '2',
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
     * testEditPostBadDataForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testEditPostBadDataForHrAndAdmin()
    {
        $userRoles = [
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'edit',
                '2'
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('The deferred save not be saved. Please, try again.'));
        }
    }

    /**
     * testEditPostInvalidDataForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testEditPostInvalidDataForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
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
                'controller' => 'deferred',
                'action' => 'edit',
                '2',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('The deferred save not be saved. Please, try again.'));
        }
    }

    /**
     * testEditPostValidDataNotChangedForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testEditPostValidDataNotChangedForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'EmployeeEdit' => [
                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => 'd4bd663f-37da-4737-bfd8-e6442e723722',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Дементьева А.С.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => 'Дементьева А.С.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => 'А.С.',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => 'Дементьева',
                    CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => 'Анна',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => 'Сергеевна',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Инженер',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Сектор автоматизированной обработки информации',
                    CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОИТ',
                    CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => '247',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '123',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'a.dementeva@fabrikam.com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Голубев Е.В.,OU=АТО,OU=Пользователи,DC=fabrikam,DC=com',
                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '',
                    CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => 'ТестОрг',
                    CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1991-11-07',
                    CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501247',
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [],
                    CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => ['+375291000003'],
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
                'controller' => 'deferred',
                'action' => 'edit',
                '3',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('The deferred save not be saved. Please, try again.'));
        }
    }

    /**
     * testEditPostValidDataForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testEditPostValidDataForHr()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
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
            ]
        ];
        $id = 2;
        $expected = [
            'Deferred' => [
                'id' => '2',
                'employee_id' => '3',
                'internal' => false,
                'data' => [
                    'changed' => [
                        'EmployeeEdit' => [
                            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Главный специалист',
                            CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                            CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'Отдел связи',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000002',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0001',
                            CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '5002',
                            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1997-01-12',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '805203',
                            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                                '+375171000003',
                                '+375171000005',
                            ],
                            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                                '+375291000001'
                            ],
                            CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                        ],
                    ],
                    'current' => [
                        'EmployeeEdit' => [
                            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                            CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                            CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000001',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => null,
                            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0216',
                            CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1602',
                            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-16',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501203',
                            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                                '+375171000003',
                                '+375171000005',
                                '+375171000004',
                            ],
                            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
                        ],
                    ],
                ],
                'created' => '2017-11-16 10:12:44',
            ]
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/hr/deferred/edit/' . $id;
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The deferred save has been saved.'));
        $this->checkRedirect(true);
        $this->Controller->Deferred->recursive = -1;
        $result = $this->Controller->Deferred->read(null, $id);
        $this->assertTrue(isset($result['Deferred']['modified']));
        unset($result['Deferred']['modified']);

        $this->assertData($expected, $result);
    }

    /**
     * testEditPostValidDataForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testEditPostValidDataForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
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
            ]
        ];
        $id = 2;
        $expected = [
            'Deferred' => [
                'id' => '2',
                'employee_id' => '3',
                'internal' => false,
                'data' => [
                    'changed' => [
                        'EmployeeEdit' => [
                            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Главный специалист',
                            CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                            CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'Отдел связи',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000002',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => 'CN=Егоров Т.Г.,OU=14-01,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0001',
                            CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '5002',
                            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1997-01-12',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '805203',
                            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                                '+375171000003',
                                '+375171000005',
                            ],
                            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                                '+375291000001'
                            ],
                            CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                        ],
                    ],
                    'current' => [
                        'EmployeeEdit' => [
                            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                            CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
                            CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => 'ОС',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => '+375295000001',
                            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => null,
                            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => 'pc0216',
                            CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => '1602',
                            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => '1996-08-16',
                            CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => '501203',
                            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                                '+375171000003',
                                '+375171000005',
                                '+375171000004',
                            ],
                            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
                        ],
                    ],
                ],
                'created' => '2017-11-16 10:12:44',
            ]
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/admin/deferred/edit/' . $id;
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The deferred save has been saved.'));
        $this->checkRedirect(true);
        $this->Controller->Deferred->recursive = -1;
        $result = $this->Controller->Deferred->read(null, $id);
        $this->assertTrue(isset($result['Deferred']['modified']));
        unset($result['Deferred']['modified']);

        $this->assertData($expected, $result);
    }

    /**
     * testDeleteDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testDeleteDenyNotHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'delete',
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
     * testDeleteEmptyIdForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testDeleteEmptyIdForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'delete',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for deferred save'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testDeleteInvalidIdForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testDeleteInvalidIdForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'delete',
                '1000',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for deferred save'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testDeleteValidIdGetForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testDeleteValidIdGetForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'delete',
                '4',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
    }

    /**
     * testDeleteSuccessForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testDeleteSuccessForHr()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr'
        ];
        $opt = [
            'method' => 'POST',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/hr/deferred/delete/3';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The deferred save has been deleted.'));
        $result = $this->Controller->Deferred->find('list', ['recursive' => -1]);
        $expected = [
            1 => '1',
            2 => '2',
            4 => '4',
            5 => '5',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testDeleteSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testDeleteSuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin'
        ];
        $opt = [
            'method' => 'POST',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/admin/deferred/delete/2';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The deferred save has been deleted.'));
        $result = $this->Controller->Deferred->find('list', ['recursive' => -1]);
        $expected = [
            1 => '1',
            3 => '3',
            4 => '4',
            5 => '5',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testApproveDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testApproveDenyNotHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'approve',
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
     * testApproveEmptyIdForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testApproveEmptyIdForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'approve',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for deferred save'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testApproveInvalidIdForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testApproveInvalidIdForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'approve',
                '1000',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for deferred save'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testApproveValidIdGetForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testApproveValidIdGetForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'approve',
                '4',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
    }

    /**
     * testApproveSuccessForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testApproveSuccessForHr()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr'
        ];
        $opt = [
            'method' => 'POST',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/hr/deferred/approve/3';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The deferred save has been %s.', __('approved')));
        $result = $this->Controller->Deferred->find('list', ['recursive' => -1]);
        $expected = [
            1 => '1',
            2 => '2',
            4 => '4',
            5 => '5',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testApproveSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testApproveSuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin'
        ];
        $opt = [
            'method' => 'POST',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/admin/deferred/approve/2';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The deferred save has been %s.', __('approved')));
        $result = $this->Controller->Deferred->find('list', ['recursive' => -1]);
        $expected = [
            1 => '1',
            3 => '3',
            4 => '4',
            5 => '5',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testRejectDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testRejectDenyNotHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'reject',
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
     * testRejectEmptyIdForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testRejectEmptyIdForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'reject',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for deferred save'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testRejectInvalidIdForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testRejectInvalidIdForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'reject',
                '1000',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for deferred save'));
            $this->checkRedirect(true);
        }
    }

    /**
     * testRejectValidIdGetForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testRejectValidIdGetForHrAndAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'deferred',
                'action' => 'reject',
                '4',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
        }
    }

    /**
     * testRejectSuccessForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testRejectSuccessForHr()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr'
        ];
        $opt = [
            'method' => 'POST',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/hr/deferred/reject/3';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The deferred save has been %s.', __('rejected')));
        $result = $this->Controller->Deferred->find('list', ['recursive' => -1]);
        $expected = [
            1 => '1',
            2 => '2',
            4 => '4',
            5 => '5',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testRejectSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testRejectSuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin'
        ];
        $opt = [
            'method' => 'POST',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/admin/deferred/reject/2';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The deferred save has been %s.', __('rejected')));
        $result = $this->Controller->Deferred->find('list', ['recursive' => -1]);
        $expected = [
            1 => '1',
            3 => '3',
            4 => '4',
            5 => '5',
        ];
        $this->assertData($expected, $result);
    }
}
