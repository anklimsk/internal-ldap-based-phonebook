<?php
App::uses('AppControllerTestCase', 'Test');
App::uses('LogsController', 'Controller');
App::uses('CakeTime', 'Utility');

/**
 * LogsController Test Case
 */
class LogsControllerTest extends AppControllerTestCase
{

    /**
     * Target Controller name
     *
     * @var string
     */
    public $targetController = 'Logs';

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
        'plugin.cake_ldap.othertelephone',
        'plugin.cake_ldap.othermobile',
        'plugin.queue.queued_task',
    ];

    /**
     * testIndexDenyNotAdmin method
     *
     * User role: user, secretary, human resources
     * @return void
     */
    public function testIndexDenyNotAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'logs',
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
     * User role: admin
     * @return void
     */
    public function testIndexForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $result = $this->testAction('/admin/logs/index', $opt);
        $this->excludeCommonAppVars($result);
        $expected = [
            'logs' => [
                [
                    'Log' => [
                        'id' => '4',
                        'user_id' => '7',
                        'employee_id' => '8',
                        'data' => false,
                        'created' => '2017-11-29 14:55:08',
                    ],
                    'Employee' => [
                        'id' => '8',
                        'block' => false,
                        'name' => 'Голубев Е.В.',
                        'title' => 'Водитель',
                    ],
                    'User' => [
                        'id' => '7',
                        'block' => false,
                        'name' => 'Хвощинский В.В.',
                        'title' => 'Начальник отдела',
                    ],
                ],
                [
                    'Log' => [
                        'id' => '3',
                        'user_id' => '6',
                        'employee_id' => '4',
                        'data' => [
                            'changed' => [
                                'EmployeeEdit' => [
                                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '216',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'a.s.dementeva@fabrikam.com',
                                ],
                            ],
                            'current' => [
                                'EmployeeEdit' => [
                                    CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => '123',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => 'a.dementeva@fabrikam.com',
                                ]
                            ],
                        ],
                        'created' => '2017-11-21 16:33:29',
                    ],
                    'Employee' => [
                        'id' => '4',
                        'block' => false,
                        'name' => 'Дементьева А.С.',
                        'title' => 'Инженер',
                    ],
                    'User' => [
                        'id' => '6',
                        'block' => false,
                        'name' => 'Козловская Е.М.',
                        'title' => 'Заведующий сектором',
                    ],
                ],
                [
                    'Log' => [
                        'id' => '2',
                        'user_id' => '6',
                        'employee_id' => '3',
                        'data' => [
                            'changed' => [
                                'EmployeeEdit' => [
                                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Суханова Л.Б.,OU=ОС,OU=Пользователи,DC=fabrikam,DC=com',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий инженер',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => 'Группа №1',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
                                ],
                            ],
                            'current' => [
                                'EmployeeEdit' => [
                                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Зам. начальника отдела - главный специалист',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => '',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
                                ],
                            ],
                        ],
                        'created' => '2017-11-08 08:06:41',
                    ],
                    'Employee' => [
                        'id' => '3',
                        'block' => false,
                        'name' => 'Суханова Л.Б.',
                        'title' => 'Зам. начальника отдела - главный специалист',
                    ],
                    'User' => [
                        'id' => '6',
                        'block' => false,
                        'name' => 'Козловская Е.М.',
                        'title' => 'Заведующий сектором',
                    ],
                ],
                [
                    'Log' => [
                        'id' => '1',
                        'user_id' => '6',
                        'employee_id' => '1',
                        'data' => [
                            'changed' => [
                                'EmployeeEdit' => [
                                    CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Геолог',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
                                ],
                            ],
                            'current' => [
                                'EmployeeEdit' => [
                                    CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
                                    CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
                                ],
                            ],
                        ],
                        'created' => '2017-11-01 11:04:02',
                    ],
                    'Employee' => [
                        'id' => '1',
                        'block' => false,
                        'name' => 'Миронов В.М.',
                        'title' => 'Ведущий геолог',
                    ],
                    'User' => [
                        'id' => '6',
                        'block' => false,
                        'name' => 'Козловская Е.М.',
                        'title' => 'Заведующий сектором',
                    ],
                ],
            ],
            'groupActions' => [
                GROUP_ACTION_LOG_DELETE => __('Delete data group'),
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
            'pageHeader' => __('Index of logs'),
            'headerMenuActions' => [
                [
                    'fas fa-trash-alt',
                    __('Clear logs'),
                    ['controller' => 'logs', 'action' => 'clear'],
                    [
                        'title' => __('Clear logs'),
                        'action-type' => 'confirm-post',
                        'data-confirm-msg' => __('Are you sure you wish to clear logs?'),
                    ]
                ]
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
                    CakeText::truncate(__('Logs'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                    [
                        'plugin' => null,
                        'controller' => 'logs',
                        'action' => 'index'
                    ]
                ],
                __('Index')
            ]
        ];
        $this->assertData($expected, $result);
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
                        'Log' => [
                            'id' => '1'
                        ]
                    ]
                ]
            ],
            'method' => 'POST',
            'return' => 'result',
        ];
        $this->testAction('/admin/logs/index', $opt);
        $flashMsgPcre = '/' . __('Selected tasks has been processed.') . '|' . __('Selected tasks could not be processed. Please, try again.') . '/';
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
                    'action' => GROUP_ACTION_LOG_DELETE,
                ],
                'FilterData' => [
                    'BAD_CONDITIONS'
                ]
            ],
            'method' => 'POST',
            'return' => 'result',
        ];
        $result = $this->testAction('/admin/logs/index', $opt);
        $flashMsgPcre = '/' . __('Selected tasks has been processed.') . '|' . __('Selected tasks could not be processed. Please, try again.') . '/';
        $this->checkFlashMessage($flashMsgPcre, true, true);
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
                    'action' => GROUP_ACTION_LOG_DELETE,
                ],
                'FilterData' => [
                    [
                        'Log' => [
                            'id' => ['1', '3']
                        ]
                    ]
                ]
            ],
            'method' => 'POST',
            'return' => 'result',
        ];
        $result = $this->testAction('/admin/logs/index', $opt);
        $this->checkFlashMessage(__('Selected tasks has been processed.'));

        $result = $this->Controller->Log->find('list', ['recursive' => -1]);
        $expected = [
            2 => '2',
            4 => '4',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testViewDenyNotAdmin method
     *
     * User role: user, secretary, human resources
     * @return void
     */
    public function testViewDenyNotAdmin()
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
            $this->generateMockedController();
            $url = [
                'controller' => 'logs',
                'action' => 'view',
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
     * testViewEmptyIdForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testViewEmptyIdForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'method' => 'GET',
            'return' => 'result',
        ];
        $this->testAction('/admin/logs/view', $opt);
        $this->checkFlashMessage(__('Invalid ID for record of log'));
        $this->checkRedirect(true);
    }

    /**
     * testViewInvalidIdForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testViewInvalidIdForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'method' => 'GET',
            'return' => 'result',
        ];
        $this->testAction('/admin/logs/view/1000', $opt);
        $this->checkFlashMessage(__('Invalid ID for record of log'));
        $this->checkRedirect(true);
    }

    /**
     * testViewSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testViewSuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $result = $this->testAction('/admin/logs/view/1', $opt);
        $this->excludeCommonAppVars($result);
        $expected = [
            'log' => [
                'Log' => [
                    'id' => '1',
                    'user_id' => '6',
                    'employee_id' => '1',
                    'data' => [
                        'changed' => [
                            'EmployeeEdit' => [
                                CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com',
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Геолог',
                                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
                            ],
                        ],
                        'current' => [
                            'EmployeeEdit' => [
                                CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
                                CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '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',
                            ],
                        ],
                    ],
                    'created' => '2017-11-01 11:04:02',
                ],
                'Employee' => [
                    'id' => '1',
                    'block' => false,
                    'name' => 'Миронов В.М.',
                    'title' => 'Ведущий геолог',
                ],
                'User' => [
                    'id' => '6',
                    'block' => false,
                    'name' => 'Козловская Е.М.',
                    'title' => 'Заведующий сектором',
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
            'pageHeader' => __('Information of log record'),
            'headerMenuActions' => [
                [
                    'fas fa-undo-alt',
                    __('Restore data from log'),
                    ['controller' => 'logs', 'action' => 'restore', '1'],
                    [
                        'title' => __('Restore data from log'), 'action-type' => 'confirm-post',
                        'data-confirm-msg' => __('Are you sure you wish to restore this data from log?'),
                    ]
                ],
                [
                    'far fa-trash-alt',
                    __('Delete record of log'),
                    ['controller' => 'logs', 'action' => 'delete', '1'],
                    [
                        'title' => __('Delete record of log'), 'action-type' => 'confirm-post',
                        'data-confirm-msg' => __('Are you sure you wish to delete this record of log?'),
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
                    CakeText::truncate('Миронов В.М.', CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                    [
                        '1',
                        'plugin' => null,
                        'controller' => 'employees',
                        'action' => 'view'
                    ]
                ],
                [
                    CakeText::truncate(__('Logs'), CAKE_THEME_BREADCRUMBS_TEXT_LIMIT),
                    [
                        '1',
                        'plugin' => null,
                        'controller' => 'logs',
                        'action' => 'view'
                    ]
                ],
                __('Viewing'),
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testDeleteDenyNotAdmin method
     *
     * User role: user, secretary, human resources
     * @return void
     */
    public function testDeleteDenyNotAdmin()
    {
        $this->setExpectedException('MissingActionException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
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
                'controller' => 'logs',
                'action' => 'delete',
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
     * testDeleteEmptyIdForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testDeleteEmptyIdForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'method' => 'POST',
            'return' => 'result',
        ];
        $this->testAction('/admin/logs/delete', $opt);
        $this->checkFlashMessage(__('Invalid ID for record of log'));
        $this->checkRedirect(true);
    }

    /**
     * testDeleteInvalidIdForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testDeleteInvalidIdForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'method' => 'POST',
            'return' => 'result',
        ];
        $this->testAction('/admin/logs/delete/1000', $opt);
        $this->checkFlashMessage(__('Invalid ID for record of log'));
        $this->checkRedirect(true);
    }

    /**
     * testDeleteValidIdGetForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testDeleteValidIdGetForAdmin()
    {
        $this->setExpectedException('MethodNotAllowedException');
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'method' => 'GET',
            'return' => 'result',
        ];
        $this->testAction('/admin/logs/delete/1', $opt);
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
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $mocks = [
            'components' => [
                'Security',
            ]
        ];
        $this->generateMockedController($mocks);
        $opt = [
            'method' => 'POST',
            'return' => 'vars',
        ];
        $this->testAction('/admin/logs/delete/2', $opt);
        $this->checkFlashMessage(__('The log record has been deleted.'));

        $result = $this->Controller->Log->find('list', ['recursive' => -1]);
        $expected = [
            1 => '1',
            3 => '3',
            4 => '4',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testRestoreDenyNotAdmin method
     *
     * User role: user, secretary, human resources
     * @return void
     */
    public function testRestoreDenyNotAdmin()
    {
        $this->setExpectedException('MissingActionException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
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
                'controller' => 'logs',
                'action' => 'restore',
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
     * testRestoreEmptyIdForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testRestoreEmptyIdForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'method' => 'POST',
            'return' => 'result',
        ];
        $this->testAction('/admin/logs/restore', $opt);
        $this->checkFlashMessage(__('Invalid ID for record of log'));
        $this->checkRedirect(true);
    }

    /**
     * testRestoreInvalidIdForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testRestoreInvalidIdForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'method' => 'POST',
            'return' => 'result',
        ];
        $this->testAction('/admin/logs/restore/1000', $opt);
        $this->checkFlashMessage(__('Invalid ID for record of log'));
        $this->checkRedirect(true);
    }

    /**
     * testRestoreValidIdGetForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testRestoreValidIdGetForAdmin()
    {
        $this->setExpectedException('MethodNotAllowedException');
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'method' => 'GET',
            'return' => 'result',
        ];
        $this->testAction('/admin/logs/restore/1', $opt);
    }

    /**
     * testRestoreSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testRestoreSuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $mocks = [
            'components' => [
                'Security',
            ]
        ];
        $this->generateMockedController($mocks);
        $opt = [
            'method' => 'POST',
            'return' => 'vars',
        ];
        $this->testAction('/admin/logs/restore/2', $opt);
        $this->checkFlashMessage(__(
            'Deferred saving with an restored employee information was created.<br />Information on LDAP server will be updated by queue.<br />Information in phonebook will be updated %s after processing.',
            CakeTime::timeAgoInWords(strtotime('+' . DEFERRED_SAVE_SYNC_DELAY . ' second'), ['accuracy' => ['second' => 'minute']])
        ));
    }

    /**
     * testClearDenyNotAdmin method
     *
     * User role: user, secretary, human resources
     * @return void
     */
    public function testClearDenyNotAdmin()
    {
        $this->setExpectedException('MissingActionException');
        $userRoles = [
            USER_ROLE_USER => '',
            USER_ROLE_USER | USER_ROLE_SECRETARY => 'secret',
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
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
                'controller' => 'logs',
                'action' => 'clear',
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
     * testClearGetForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testClearGetForAdmin()
    {
        $this->setExpectedException('MethodNotAllowedException');
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $opt = [
            'method' => 'GET',
            'return' => 'result',
        ];
        $this->testAction('/admin/logs/clear', $opt);
    }

    /**
     * testClearSuccessForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testClearSuccessForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $this->applyUserInfo($userInfo);
        $mocks = [
            'components' => [
                'Security',
            ]
        ];
        $this->generateMockedController($mocks);
        $opt = [
            'method' => 'POST',
            'return' => 'vars',
        ];
        $this->testAction('/admin/logs/clear', $opt);
        $this->checkFlashMessage(__('The logs has been cleared.'));

        $result = $this->Controller->Log->find('count', ['recursive' => -1]);
        $expected = 0;
        $this->assertData($expected, $result);
    }
}
