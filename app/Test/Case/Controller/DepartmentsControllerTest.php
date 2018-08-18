<?php
App::uses('AppControllerTestCase', 'Test');
App::uses('DepartmentsController', 'Controller');

/**
 * DepartmentsController Test Case
 */
class DepartmentsControllerTest extends AppControllerTestCase
{

    /**
     * Target Controller name
     *
     * @var string
     */
    public $targetController = 'Departments';

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.cake_session',
        'app.deferred',
        'app.department_extension',
        'plugin.cake_ldap.department',
        'plugin.cake_ldap.employee',
        'plugin.cake_ldap.employee_ldap',
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
                'controller' => 'departments',
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
            'departments' => [
                [
                    'Department' => [
                        'id' => '1',
                        'value' => 'УИЗ',
                        'block' => false,
                    ],
                    'DepartmentExtension' => [
                        'id' => '1',
                        'department_id' => '1',
                        'name' => 'Управление инженерных изысканий',
                        'lft' => '1',
                        'rght' => '2',
                    ],
                ],
                [
                    'Department' => [
                        'id' => '2',
                        'value' => 'ОС',
                        'block' => false,
                    ],
                    'DepartmentExtension' => [
                        'id' => '2',
                        'department_id' => '2',
                        'name' => 'Отдел связи',
                        'lft' => '3',
                        'rght' => '4',
                    ],
                ],
                [
                    'Department' => [
                        'id' => '3',
                        'value' => 'ОИТ',
                        'block' => false,
                    ],
                    'DepartmentExtension' => [
                        'id' => '3',
                        'department_id' => '3',
                        'name' => 'Отдел информационных технологий',
                        'lft' => '5',
                        'rght' => '6',
                    ],
                ],
                [
                    'Department' => [
                        'id' => '4',
                        'value' => 'ОРС',
                        'block' => false,
                    ],
                    'DepartmentExtension' => [
                        'id' => '4',
                        'department_id' => '4',
                        'name' => 'Отдел распределительных сетей',
                        'lft' => '7',
                        'rght' => '8',
                    ],
                ],
                [
                    'Department' => [
                        'id' => '5',
                        'value' => 'АТО',
                        'block' => false,
                    ],
                    'DepartmentExtension' => [
                        'id' => '5',
                        'department_id' => '5',
                        'name' => 'Автотранспортный отдел',
                        'lft' => '9',
                        'rght' => '10',
                    ],
                ],
                [
                    'Department' => [
                        'id' => '6',
                        'value' => 'Охрана труда',
                        'block' => false,
                    ],
                    'DepartmentExtension' => [
                        'id' => '6',
                        'department_id' => '6',
                        'name' => 'Охрана Труда',
                        'lft' => '11',
                        'rght' => '12',
                    ],
                ],
                [
                    'Department' => [
                        'id' => '7',
                        'value' => 'СО',
                        'block' => true,
                    ],
                    'DepartmentExtension' => [
                        'id' => '7',
                        'department_id' => '7',
                        'name' => 'Строительный отдел',
                        'lft' => '13',
                        'rght' => '14',
                    ],
                ],
            ],
            'pageHeader' => __('Index of departments'),
            'headerMenuActions' => [
                [
                    'fas fa-plus',
                    __('Add department'),
                    ['controller' => 'departments', 'action' => 'add'],
                    [
                        'title' => __('Add department'),
                        'action-type' => 'modal',
                    ]
                ],
                [
                    'fas fa-sort-alpha-down',
                    __('Order list of departments'),
                    ['controller' => 'departments', 'action' => 'order'],
                    [
                        'title' => __('Order list of departments by alphabet'),
                        'action-type' => 'confirm-post',
                        'data-confirm-msg' => __('Are you sure you wish to re-order list of departments?')
                    ]
                ],
                'divider',
                [
                    'fas fa-check',
                    __('Check state list'),
                    ['controller' => 'departments', 'action' => 'check'],
                    [
                        'title' => __('Check state list of departments'),
                        'data-toggle' => 'modal'
                    ]
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
                'controller' => 'departments',
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
                'controller' => 'departments',
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
                'controller' => 'departments',
                'action' => 'view',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for department'));
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
                'controller' => 'departments',
                'action' => 'view',
                '1000',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for department'));
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
            'department' => [
                'Department' => [
                    'id' => '2',
                    'value' => 'ОС',
                    'block' => false,
                ],
                'DepartmentExtension' => [
                    'id' => '2',
                    'department_id' => '2',
                    'parent_id' => null,
                    'lft' => '3',
                    'rght' => '4',
                    'name' => 'Отдел связи',
                ],
            ],
            'pageHeader' => __('Information of department'),
            'headerMenuActions' => [
                [
                    'fas fa-pencil-alt',
                    __('Editing department'),
                    ['controller' => 'departments', 'action' => 'edit', '2'],
                    ['title' => __('Editing information of this department')]
                ],
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
                'controller' => 'departments',
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
     * testAddDenyNotHrAndAdmin method
     *
     * User role: user, secretary
     * @return void
     */
    public function testAddDenyNotHrAndAdmin()
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
                'controller' => 'departments',
                'action' => 'add',
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
     * testAddGetSuccessForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testAddGetSuccessForHrAndAdmin()
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
            'fieldInputMask' => [
                'data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}'
            ],
            'pageHeader' => __('Adding department'),
            'isAddAction' => true
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
                'action' => 'add',
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
     * testAddPostBadDataForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testAddPostBadDataForHrAndAdmin()
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
                'controller' => 'departments',
                'action' => 'add',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Department could not be saved. Please, try again.'));
        }
    }

    /**
     * testAddPostInvalidDataForHrAndAdmin method
     *
     * User role: human resources, admin
     * @return void
     */
    public function testAddPostInvalidDataForHrAndAdmin()
    {
        $userRoles = [
            USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES => 'hr',
            USER_ROLE_USER | USER_ROLE_ADMIN => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'Department' => [
                    'value' => '',
                ],
                'DepartmentExtension' => [
                    'name' => ''
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
                'controller' => 'departments',
                'action' => 'add',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Department could not be saved. Please, try again.'));
        }
    }

    /**
     * testAddPostValidDataExistsForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testAddPostValidDataExistsForHr()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'Department' => [
                    'value' => 'ОС',
                ],
                'DepartmentExtension' => [
                    'name' => 'Отдел линий связи'
                ]
            ]
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/hr/departments/add';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('Department could not be saved. Please, try again.'));
        $this->checkRedirect(true);
    }

    /**
     * testAddPostValidDataExistsForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testAddPostValidDataExistsForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'Department' => [
                    'value' => 'ОС',
                ],
                'DepartmentExtension' => [
                    'name' => 'Отдел линий связи'
                ]
            ]
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/admin/departments/add';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('Department could not be saved. Please, try again.'));
        $this->checkRedirect(true);
    }

    /**
     * testAddPostValidDataForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testAddPostValidDataForHr()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'Department' => [
                    'value' => 'New',
                ],
                'DepartmentExtension' => [
                    'name' => 'New department'
                ]
            ]
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/hr/departments/add';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('Department has been saved.'));
        $this->checkRedirect(true);
    }

    /**
     * testAddPostValidDataForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testAddPostValidDataForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'Department' => [
                    'value' => 'New',
                ],
                'DepartmentExtension' => [
                    'name' => 'New department'
                ]
            ]
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/admin/departments/add';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('Department has been saved.'));
        $this->checkRedirect(true);
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
                'controller' => 'departments',
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
                'controller' => 'departments',
                'action' => 'edit',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for department'));
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
                'controller' => 'departments',
                'action' => 'edit',
                '1000',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for department'));
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
            'fieldInputMask' => [
                'data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}'
            ],
            'pageHeader' => __('Editing department')
        ];
        foreach ($userRoles as $userRole => $userPrefix) {
            $userInfo = [
                'role' => $userRole,
                'prefix' => $userPrefix,
            ];
            $this->applyUserInfo($userInfo);
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
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
                'controller' => 'departments',
                'action' => 'edit',
                '2',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Department could not be saved. Please, try again.'));
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
                'Department' => [
                    'id' => 2,
                    'value' => '',
                    'block' => false,
                ],
                'DepartmentExtension' => [
                    'id' => 2,
                    'department_id' => 2,
                    'parent_id' => null,
                    'lft' => 3,
                    'rght' => 4,
                    'name' => ''
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
                'controller' => 'departments',
                'action' => 'edit',
                '2',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Department could not be saved. Please, try again.'));
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
                'Department' => [
                    'id' => 2,
                    'value' => 'ОС',
                    'block' => false,
                ],
                'DepartmentExtension' => [
                    'id' => 2,
                    'department_id' => 2,
                    'parent_id' => null,
                    'lft' => 3,
                    'rght' => 4,
                    'name' => 'Отдел линий связи'
                ]
            ]
        ];
        $id = 2;
        $expected = [
            'DepartmentExtension' => [
                'id' => '2',
                'department_id' => '2',
                'parent_id' => null,
                'lft' => '3',
                'rght' => '4',
                'name' => 'Отдел линий связи'
            ]
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/hr/departments/edit/' . $id;
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('Department has been saved.'));
        $this->checkRedirect(true);
        $this->Controller->Department->DepartmentExtension->recursive = -1;
        $result = $this->Controller->Department->DepartmentExtension->read(null, $id);
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
                'Department' => [
                    'id' => 2,
                    'value' => 'ОС',
                    'block' => false,
                ],
                'DepartmentExtension' => [
                    'id' => 2,
                    'department_id' => 2,
                    'parent_id' => null,
                    'lft' => 3,
                    'rght' => 4,
                    'name' => 'Отдел линий связи'
                ]
            ]
        ];
        $id = 2;
        $expected = [
            'DepartmentExtension' => [
                'id' => '2',
                'department_id' => '2',
                'parent_id' => null,
                'lft' => '3',
                'rght' => '4',
                'name' => 'Отдел линий связи'
            ]
        ];
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/admin/departments/edit/' . $id;
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('Department has been saved.'));
        $this->checkRedirect(true);
        $this->Controller->Department->DepartmentExtension->recursive = -1;
        $result = $this->Controller->Department->DepartmentExtension->read(null, $id);
        $this->assertData($expected, $result);
    }

    /**
     * testEditPostValidDataRenameDepartmentForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testEditPostValidDataRenameDepartmentForHr()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'Department' => [
                    'id' => 2,
                    'value' => 'ОЛС',
                    'block' => false,
                ],
                'DepartmentExtension' => [
                    'id' => 2,
                    'department_id' => 2,
                    'parent_id' => null,
                    'lft' => 3,
                    'rght' => 4,
                    'name' => 'Отдел линий связи'
                ]
            ]
        ];
        $id = 2;
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/hr/departments/edit/' . $id;
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('Renaming department from "%s" to "%s" put in queue...', 'ОС', 'ОЛС'));
        $this->checkRedirect(true);
    }

    /**
     * testEditPostValidDataRenameDepartmentForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testEditPostValidDataRenameDepartmentForAdmin()
    {
        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin',
        ];
        $opt = [
            'method' => 'POST',
            'data' => [
                'Department' => [
                    'id' => 2,
                    'value' => 'ОЛС',
                    'block' => false,
                ],
                'DepartmentExtension' => [
                    'id' => 2,
                    'department_id' => 2,
                    'parent_id' => null,
                    'lft' => 3,
                    'rght' => 4,
                    'name' => 'Отдел линий связи'
                ]
            ]
        ];
        $id = 2;
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/admin/departments/edit/' . $id;
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('Renaming department from "%s" to "%s" put in queue...', 'ОС', 'ОЛС'));
        $this->checkRedirect(true);
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
                'controller' => 'departments',
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
                'controller' => 'departments',
                'action' => 'delete',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for department'));
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
                'controller' => 'departments',
                'action' => 'delete',
                '1000',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Invalid ID for department'));
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
                'controller' => 'departments',
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
     * testDeleteUnsuccessNonBlockedForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testDeleteUnsuccessNonBlockedForHr()
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
        $url = '/hr/department/delete/2';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The department could not be deleted. Please, try again.'));
    }

    /**
     * testDeleteUnsuccessNonBlockedForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testDeleteUnsuccessNonBlockedorAdmin()
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
        $url = '/admin/department/delete/3';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The department could not be deleted. Please, try again.'));
    }

    /**
     * testDeleteSuccessBlockedForHr method
     *
     * User role: human resources
     * @return void
     */
    public function testDeleteSuccessBlockedForHr()
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
        $url = '/hr/department/delete/7';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The department has been deleted.'));
    }

    /**
     * testDeleteSuccessBlockedForAdmin method
     *
     * User role: admin
     * @return void
     */
    public function testDeleteSuccessBlockedForAdmin()
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
        $url = '/admin/department/delete/7';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('The department has been deleted.'));
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
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
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
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
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
        $id = 3;
        $direct = 'down';
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/hr/departments/move/' . $direct . '/' . $id;
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
        $id = 3;
        $direct = 'down';
        $this->applyUserInfo($userInfo);
        $this->generateMockedController();
        $url = '/admin/departments/move/' . $direct . '/' . $id;
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
                    'id' => '2'
                ],
                [
                    'id' => '3'
                ],
                [
                    'id' => '4'
                ],
                [
                    'id' => '5'
                ],
                [
                    'id' => '6'
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
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
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
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
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
                    'id' => '2'
                ],
                [
                    'id' => '3'
                ],
                [
                    'id' => '4'
                ],
                [
                    'id' => '5'
                ],
                [
                    'id' => '6'
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
        $this->generateMockedController();
        $url = '/hr/departments/drop.json';
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
                    'id' => '2'
                ],
                [
                    'id' => '1'
                ],
                [
                    'id' => '3'
                ],
                [
                    'id' => '4'
                ],
                [
                    'id' => '5'
                ],
                [
                    'id' => '6'
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
        $this->generateMockedController();
        $url = '/admin/departments/drop.json';
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
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
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
        $this->generateMockedController();
        $this->Controller->Department->DepartmentExtension->id = 2;
        $result = (bool)$this->Controller->Department->DepartmentExtension->saveField('rght', null);
        $this->assertTrue($result);

        $url = '/admin/departments/check';
        $result = $this->testAction($url, $opt);
        $this->excludeCommonAppVars($result);
        $expected = [
            'treeState' => [
                [
                    'index',
                    4,
                    'missing',
                ],
                [
                    'node',
                    '2',
                    'has invalid left or right values',
                ]
            ],
            'pageHeader' => __('Checking state list of departments'),
            'headerMenuActions' => [
                [
                    'fas fa-redo-alt',
                    __('Recovery state of list'),
                    ['controller' => 'departments', 'action' => 'recover'],
                    [
                        'title' => __('Recovery state of list'),
                        'data-toggle' => 'pjax',
                    ]
                ]
            ]
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
        $this->generateMockedController();
        $url = '/admin/departments/check';
        $result = $this->testAction($url, $opt);
        $this->excludeCommonAppVars($result);
        $expected = [
            'treeState' => true,
            'pageHeader' => __('Checking state list of departments'),
            'headerMenuActions' => [],
        ];
        $this->assertData($expected, $result);
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
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
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
        $this->generateMockedController();
        $url = '/admin/departments/recover';
        $this->testAction($url, $opt);
        $this->checkFlashMessage(__('Recovering list of departments put in queue...'));
        $this->checkRedirect(true);
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
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
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
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
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
            $this->generateMockedController();
            $url = [
                'controller' => 'departments',
                'action' => 'order',
            ];
            if (!empty($userPrefix)) {
                $url['prefix'] = $userPrefix;
                $url[$userPrefix] = true;
            }
            $this->testAction($url, $opt);
            $this->checkFlashMessage(__('Ordering list of departments put in queue...'));
            $this->checkRedirect(true);
        }
    }
}
