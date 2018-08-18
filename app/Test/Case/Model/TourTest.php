<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('Tour', 'Model');
App::uses('Router', 'Routing');

/**
 * Tour Test Case
 */
class TourTest extends AppCakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.cake_session',
        'plugin.cake_ldap.employee',
        'plugin.cake_ldap.employee_ldap',
        'plugin.cake_ldap.department',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        $this->setDefaultUserInfo($this->userInfo);
        parent::setUp();

        $this->_targetObject = ClassRegistry::init('Tour');
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
     * testGetListStepsForUserEmptyIdAndEmptyGuid method
     *
     * @return void
     */
    public function testGetListStepsForUserEmptyIdAndEmptyGuid()
    {
        $user = 'Хвощинский В.В.';
        $urlHome = '/';
        $queryData = [
            'query' => $user,
            'target' => [
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            ]
        ];
        $urlSearchEmployee = [
            'controller' => 'employees',
            'action' => 'search',
            'plugin' => null,
            '?' => http_build_query($queryData),
        ];
        $urlSearchEmployee = Router::url($urlSearchEmployee);
        $urlTreeEmployees = [
            'controller' => 'employees',
            'action' => 'tree',
            'plugin' => null,
        ];
        $urlTreeEmployees = Router::url($urlTreeEmployees);
        $queryFilterData = [
            'data' => [
                'FilterData' => [
                    [
                        'Employee' => [
                            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => $user
                        ]
                    ]
                ]
            ]
        ];
        $urlGallery = [
            'controller' => 'employees',
            'action' => 'gallery',
            'plugin' => null,
            '?' => http_build_query($queryFilterData),
        ];
        $urlGallery = Router::url($urlGallery);
        $urlExport = [
            'controller' => 'employees',
            'action' => 'export',
            'plugin' => null,
        ];
        $urlExport = Router::url($urlExport);

        $expected = [
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#content #SearchQuery',
                'title' => __d('tour', 'Start using'),
                'content' => __d('tour', 'To search for information, enter your request in this field and click the button "%s".', '<span class="fas fa-search fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#header div.search-scope-filter',
                'title' => __d('tour', 'Refinement of the search result'),
                'content' => __d('tour', 'To refine the search result, use the "%s" button.', '<span class="fas fa-filter fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table thead tr:eq(0) th:has(a):eq(0)',
                'title' => __d('tour', 'Changes to the sorting order of table data'),
                'content' => __d('tour', 'To change the sort order of tabular data, click the corresponding column heading in the table. Clicking again will change the sort direction on the opposite.<br />To display the current sort directions use symbols "%s" and "%s" next to the name of the corresponding column.', '<span class="fas fa-long-arrow-alt-up fa-lg"></span>', '<span class="fas fa-long-arrow-alt-down fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td:has(a):eq(0)',
                'title' => __d('tour', 'View information about the employee'),
                'content' => __d('tour', 'To view brief information about an employee, hover this link. Clicking will open the details window for the employee.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-subordinates',
                'title' => __d('tour', 'Subordinate tree'),
                'content' => __d('tour', 'Use this button to view the subordinate employee tree.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change information about the employee use this button.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-sync',
                'title' => __d('tour', 'Synchronizing information'),
                'content' => __d('tour', 'Use this button to synchronize the information of this employee with LDAP server.'),
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-employees',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item for the following:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Search for employee information</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee submission tree</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee gallery</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Save a local copy of the directory.</li></ul>'),
            ],
            [
                'path' => $urlTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Editing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronize information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Checking the status of the subordinate employee tree.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row',
                'title' => __d('tour', 'Table data filter'),
                'content' => __d('tour', 'To select tabular data, use the filter.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row code[data-toggle="filter-conditions"]',
                'title' => __d('tour', 'Table data filter'),
                'content' => __d('tour', 'Current filter condition.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.show-filter-btn',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'Use this button to display or hide the filter controls.'),
                'onNext' => "if ($('#content tr.filter-controls-row:visible').length == 0) $('#content button.show-filter-btn').trigger('click');",
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row input[name^="data[FilterData]"]:eq(0)',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'To create a filter condition, enter the required data in the input field of the corresponding column in the table.'),
                'onPrev' => "if ($('#content tr.filter-controls-row:visible').length > 0) $('#content button.show-filter-btn').trigger('click');",
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row button:has(:contains(=)):eq(0)',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'Use this button to select one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>=</i> - is equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&gt;</i> - more;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&ge;</i> - is greater than or equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&lt;<i></i> - less;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&le;</i> - is less than or equal;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&ne;<i></i> - is not equal.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row th.filter-action',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'If you want to add or delete filter lines, use these buttons.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row .filter-condition',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'Using this button, you can combine several filter lines with one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&amp;&amp;</i> - AND;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;||&nbsp;</i> - OR;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;!&nbsp;</i> - NOT.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.filter-apply',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'To apply the current filter, use this button.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.filter-clear',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'To clean the filter, use this button.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row a[href$=".prt"]',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'Use this button to print current tabular data.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu to update all the files in the directory.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-generate',
                'title' => __d('tour', 'Updating the directory file'),
                'content' => __d('tour', 'Use this button to update the directory file.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-download',
                'title' => __d('tour', 'Saving a directory file'),
                'content' => __d('tour', 'To save a local copy of the directory, use this button.'),
            ],
        ];

        $userInfo = [
            'role' => USER_ROLE_USER,
            'prefix' => '',
            'id' => '',
            'includedFields' => [
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => ''
            ],
        ] + $this->userInfo;
        $result = $this->_targetObject->getListSteps($userInfo);
        $this->assertData($expected, $result);
    }

    /**
     * testGetListStepsForUser method
     *
     * @return void
     */
    public function testGetListStepsForUser()
    {
        $user = 'Хвощинский В.В.';
        $urlHome = '/';
        $queryData = [
            'query' => $user,
            'target' => [
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            ]
        ];
        $urlSearchEmployee = [
            'controller' => 'employees',
            'action' => 'search',
            'plugin' => null,
            '?' => http_build_query($queryData),
        ];
        $urlSearchEmployee = Router::url($urlSearchEmployee);
        $urlViewEmployee = [
            'controller' => 'employees',
            'action' => 'view',
            'plugin' => null,
            '7',
        ];
        $urlViewEmployee = Router::url($urlViewEmployee);
        $urlEditEmployee = [
            'controller' => 'employees',
            'action' => 'edit',
            'plugin' => null,
            '8c149661-7215-47de-b40e-35320a1ea508',
        ];
        $urlEditEmployee = Router::url($urlEditEmployee);
        $urlTreeEmployees = [
            'controller' => 'employees',
            'action' => 'tree',
            'plugin' => null,
        ];
        $urlTreeEmployees = Router::url($urlTreeEmployees);
        $queryFilterData = [
            'data' => [
                'FilterData' => [
                    [
                        'Employee' => [
                            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => $user
                        ]
                    ]
                ]
            ]
        ];
        $urlGallery = [
            'controller' => 'employees',
            'action' => 'gallery',
            'plugin' => null,
            '?' => http_build_query($queryFilterData),
        ];
        $urlGallery = Router::url($urlGallery);
        $urlExport = [
            'controller' => 'employees',
            'action' => 'export',
            'plugin' => null,
        ];
        $urlExport = Router::url($urlExport);

        $expected = [
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#content #SearchQuery',
                'title' => __d('tour', 'Start using'),
                'content' => __d('tour', 'To search for information, enter your request in this field and click the button "%s".', '<span class="fas fa-search fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#header div.search-scope-filter',
                'title' => __d('tour', 'Refinement of the search result'),
                'content' => __d('tour', 'To refine the search result, use the "%s" button.', '<span class="fas fa-filter fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table thead tr:eq(0) th:has(a):eq(0)',
                'title' => __d('tour', 'Changes to the sorting order of table data'),
                'content' => __d('tour', 'To change the sort order of tabular data, click the corresponding column heading in the table. Clicking again will change the sort direction on the opposite.<br />To display the current sort directions use symbols "%s" and "%s" next to the name of the corresponding column.', '<span class="fas fa-long-arrow-alt-up fa-lg"></span>', '<span class="fas fa-long-arrow-alt-down fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td:has(a):eq(0)',
                'title' => __d('tour', 'View information about the employee'),
                'content' => __d('tour', 'To view brief information about an employee, hover this link. Clicking will open the details window for the employee.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-subordinates',
                'title' => __d('tour', 'Subordinate tree'),
                'content' => __d('tour', 'Use this button to view the subordinate employee tree.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change information about the employee use this button.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-sync',
                'title' => __d('tour', 'Synchronizing information'),
                'content' => __d('tour', 'Use this button to synchronize the information of this employee with LDAP server.'),
            ],
            [
                'path' => $urlViewEmployee,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Editing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronize information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Viewing the subordinate employee tree.</li></ul>'),
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content ul.nav-tabs li:has(a[href="#employeeInfo"])',
                'title' => __d('tour', 'Tabs for information input forms'),
                'content' => __d('tour', 'This tab contains employee information fields.'),
                'onNext' => "$('#content ul.nav-tabs a[href=\"#employeePhoto\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content ul.nav-tabs li:has(a[href="#employeePhoto"])',
                'title' => __d('tour', 'Tabs for information input forms'),
                'content' => __d('tour', 'This tab contains information about the employee\'s photo.'),
                'onPrev' => "$('#content ul.nav-tabs a[href=\"#employeeInfo\"]').trigger('click');",
                'onNext' => "$('#content ul.nav-tabs a[href=\"#employeeInfo\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content form.form-tabs div.progress',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'This progress bar shows the degree of filling of the data. Can accept from several colors: <ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-danger"><strong>Red</strong></span> - means that are not filled all required fields.</li><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-warning"><strong>Orange</strong></span> - is not filled in the required fields (allowed retention of information);</li><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-success"><strong>Green</strong></span> - filled in all the fields.</li></ul>'),
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content form.form-tabs .tabbable ul.nav',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'To select a group of fields for entering employee information, use the appropriate tab.'),
                'onPrev' => "$('#content ul.nav-tabs a[href=\"#employeePhoto\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'top',
                'element' => '#content form.form-tabs :submit',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'To save the information entered in the form fields, use this button.'),
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-employees',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item for the following:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Search for employee information</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee submission tree</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee gallery</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Save a local copy of the directory.</li></ul>')
            ],
            [
                'path' => $urlTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Editing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronize information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Checking the status of the subordinate employee tree.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row',
                'title' => __d('tour', 'Table data filter'),
                'content' => __d('tour', 'To select tabular data, use the filter.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row code[data-toggle="filter-conditions"]',
                'title' => __d('tour', 'Table data filter'),
                'content' => __d('tour', 'Current filter condition.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.show-filter-btn',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'Use this button to display or hide the filter controls.'),
                'onNext' => "if ($('#content tr.filter-controls-row:visible').length == 0) $('#content button.show-filter-btn').trigger('click');",
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row input[name^="data[FilterData]"]:eq(0)',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'To create a filter condition, enter the required data in the input field of the corresponding column in the table.'),
                'onPrev' => "if ($('#content tr.filter-controls-row:visible').length > 0) $('#content button.show-filter-btn').trigger('click');",
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row button:has(:contains(=)):eq(0)',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'Use this button to select one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>=</i> - is equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&gt;</i> - more;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&ge;</i> - is greater than or equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&lt;<i></i> - less;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&le;</i> - is less than or equal;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&ne;<i></i> - is not equal.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row th.filter-action',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'If you want to add or delete filter lines, use these buttons.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row .filter-condition',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'Using this button, you can combine several filter lines with one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&amp;&amp;</i> - AND;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;||&nbsp;</i> - OR;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;!&nbsp;</i> - NOT.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.filter-apply',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'To apply the current filter, use this button.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.filter-clear',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'To clean the filter, use this button.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row a[href$=".prt"]',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'Use this button to print current tabular data.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu to update all the files in the directory.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-generate',
                'title' => __d('tour', 'Updating the directory file'),
                'content' => __d('tour', 'Use this button to update the directory file.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-download',
                'title' => __d('tour', 'Saving a directory file'),
                'content' => __d('tour', 'To save a local copy of the directory, use this button.'),
            ],
        ];

        $userInfo = [
            'role' => USER_ROLE_USER,
            'prefix' => ''
        ] + $this->userInfo;
        $result = $this->_targetObject->getListSteps($userInfo);
        $this->assertData($expected, $result);
    }

    /**
     * testGetListStepsForSecretary method
     *
     * @return void
     */
    public function testGetListStepsForSecretary()
    {
        $user = 'Хвощинский В.В.';
        $urlHome = '/';
        $queryData = [
            'query' => $user,
            'target' => [
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            ]
        ];
        $urlSearchEmployee = [
            'controller' => 'employees',
            'action' => 'search',
            'plugin' => null,
            'prefix' => 'secret',
            'secret' => true,
            '?' => http_build_query($queryData),
        ];
        $urlSearchEmployee = Router::url($urlSearchEmployee);
        $urlViewEmployee = [
            'controller' => 'employees',
            'action' => 'view',
            'plugin' => null,
            '7',
            'prefix' => 'secret',
            'secret' => true,
        ];
        $urlViewEmployee = Router::url($urlViewEmployee);
        $urlEditEmployee = [
            'controller' => 'employees',
            'action' => 'edit',
            'plugin' => null,
            '8c149661-7215-47de-b40e-35320a1ea508',
            'prefix' => 'secret',
            'secret' => true,
        ];
        $urlEditEmployee = Router::url($urlEditEmployee);
        $urlTreeEmployees = [
            'controller' => 'employees',
            'action' => 'tree',
            'plugin' => null,
            'prefix' => 'secret',
            'secret' => true,
        ];
        $urlTreeEmployees = Router::url($urlTreeEmployees);
        $queryFilterData = [
            'data' => [
                'FilterData' => [
                    [
                        'Employee' => [
                            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => $user
                        ]
                    ]
                ]
            ]
        ];
        $urlGallery = [
            'controller' => 'employees',
            'action' => 'gallery',
            'plugin' => null,
            'prefix' => 'secret',
            'secret' => true,
            '?' => http_build_query($queryFilterData),
        ];
        $urlGallery = Router::url($urlGallery);
        $urlExport = [
            'controller' => 'employees',
            'action' => 'export',
            'plugin' => null,
            'prefix' => 'secret',
            'secret' => true,
        ];
        $urlExport = Router::url($urlExport);

        $expected = [
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#content #SearchQuery',
                'title' => __d('tour', 'Start using'),
                'content' => __d('tour', 'To search for information, enter your request in this field and click the button "%s".', '<span class="fas fa-search fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#header div.search-scope-filter',
                'title' => __d('tour', 'Refinement of the search result'),
                'content' => __d('tour', 'To refine the search result, use the "%s" button.', '<span class="fas fa-filter fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table thead tr:eq(0) th:has(a):eq(0)',
                'title' => __d('tour', 'Changes to the sorting order of table data'),
                'content' => __d('tour', 'To change the sort order of tabular data, click the corresponding column heading in the table. Clicking again will change the sort direction on the opposite.<br />To display the current sort directions use symbols "%s" and "%s" next to the name of the corresponding column.', '<span class="fas fa-long-arrow-alt-up fa-lg"></span>', '<span class="fas fa-long-arrow-alt-down fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td:has(a):eq(0)',
                'title' => __d('tour', 'View information about the employee'),
                'content' => __d('tour', 'To view brief information about an employee, hover this link. Clicking will open the details window for the employee.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-subordinates',
                'title' => __d('tour', 'Subordinate tree'),
                'content' => __d('tour', 'Use this button to view the subordinate employee tree.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change information about the employee use this button.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-sync',
                'title' => __d('tour', 'Synchronizing information'),
                'content' => __d('tour', 'Use this button to synchronize the information of this employee with LDAP server.'),
            ],
            [
                'path' => $urlViewEmployee,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Editing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronize information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Viewing the subordinate employee tree.</li></ul>'),
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content ul.nav-tabs li:has(a[href="#employeeInfo"])',
                'title' => __d('tour', 'Tabs for information input forms'),
                'content' => __d('tour', 'This tab contains employee information fields.'),
                'onNext' => "$('#content ul.nav-tabs a[href=\"#employeePhoto\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content ul.nav-tabs li:has(a[href="#employeePhoto"])',
                'title' => __d('tour', 'Tabs for information input forms'),
                'content' => __d('tour', 'This tab contains information about the employee\'s photo.'),
                'onPrev' => "$('#content ul.nav-tabs a[href=\"#employeeInfo\"]').trigger('click');",
                'onNext' => "$('#content ul.nav-tabs a[href=\"#employeeInfo\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content form.form-tabs div.progress',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'This progress bar shows the degree of filling of the data. Can accept from several colors: <ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-danger"><strong>Red</strong></span> - means that are not filled all required fields.</li><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-warning"><strong>Orange</strong></span> - is not filled in the required fields (allowed retention of information);</li><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-success"><strong>Green</strong></span> - filled in all the fields.</li></ul>'),
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content form.form-tabs .tabbable ul.nav',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'To select a group of fields for entering employee information, use the appropriate tab.'),
                'onPrev' => "$('#content ul.nav-tabs a[href=\"#employeePhoto\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'top',
                'element' => '#content form.form-tabs :submit',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'To save the information entered in the form fields, use this button.'),
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-employees',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item for the following:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Search for employee information</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee submission tree</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee gallery</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Save a local copy of the directory.</li></ul>'),
            ],
            [
                'path' => $urlTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Editing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronize information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Checking the status of the subordinate employee tree.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row',
                'title' => __d('tour', 'Table data filter'),
                'content' => __d('tour', 'To select tabular data, use the filter.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row code[data-toggle="filter-conditions"]',
                'title' => __d('tour', 'Table data filter'),
                'content' => __d('tour', 'Current filter condition.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.show-filter-btn',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'Use this button to display or hide the filter controls.'),
                'onNext' => "if ($('#content tr.filter-controls-row:visible').length == 0) $('#content button.show-filter-btn').trigger('click');",
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row input[name^="data[FilterData]"]:eq(0)',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'To create a filter condition, enter the required data in the input field of the corresponding column in the table.'),
                'onPrev' => "if ($('#content tr.filter-controls-row:visible').length > 0) $('#content button.show-filter-btn').trigger('click');",
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row button:has(:contains(=)):eq(0)',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'Use this button to select one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>=</i> - is equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&gt;</i> - more;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&ge;</i> - is greater than or equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&lt;<i></i> - less;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&le;</i> - is less than or equal;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&ne;<i></i> - is not equal.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row th.filter-action',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'If you want to add or delete filter lines, use these buttons.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row .filter-condition',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'Using this button, you can combine several filter lines with one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&amp;&amp;</i> - AND;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;||&nbsp;</i> - OR;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;!&nbsp;</i> - NOT.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.filter-apply',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'To apply the current filter, use this button.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.filter-clear',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'To clean the filter, use this button.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row a[href$=".prt"]',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'Use this button to print current tabular data.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu to update all the files in the directory.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-generate',
                'title' => __d('tour', 'Updating the directory file'),
                'content' => __d('tour', 'Use this button to update the directory file.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-download',
                'title' => __d('tour', 'Saving a directory file'),
                'content' => __d('tour', 'To save a local copy of the directory, use this button.'),
            ],
        ];

        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_SECRETARY,
            'prefix' => 'secret'
        ] + $this->userInfo;
        $result = $this->_targetObject->getListSteps($userInfo);
        $this->assertData($expected, $result);
    }

    /**
     * testGetListStepsForHr method
     *
     * @return void
     */
    public function testGetListStepsForHr()
    {
        $user = 'Хвощинский В.В.';
        $urlHome = '/';
        $queryData = [
            'query' => $user,
            'target' => [
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            ]
        ];
        $urlSearchEmployee = [
            'controller' => 'employees',
            'action' => 'search',
            'plugin' => null,
            'prefix' => 'hr',
            'hr' => true,
            '?' => http_build_query($queryData),
        ];
        $urlSearchEmployee = Router::url($urlSearchEmployee);
        $urlViewEmployee = [
            'controller' => 'employees',
            'action' => 'view',
            'plugin' => null,
            '7',
            'prefix' => 'hr',
            'hr' => true,
        ];
        $urlViewEmployee = Router::url($urlViewEmployee);
        $urlEditEmployee = [
            'controller' => 'employees',
            'action' => 'edit',
            'plugin' => null,
            '8c149661-7215-47de-b40e-35320a1ea508',
            'prefix' => 'hr',
            'hr' => true,
        ];
        $urlEditEmployee = Router::url($urlEditEmployee);
        $urlTreeEmployees = [
            'controller' => 'employees',
            'action' => 'tree',
            'plugin' => null,
            'prefix' => 'hr',
            'hr' => true,
        ];
        $urlTreeEmployees = Router::url($urlTreeEmployees);
        $urlEditTreeEmployees = [
            'controller' => 'employees',
            'action' => 'tree',
            'plugin' => null,
            '0',
            '1',
            'prefix' => 'hr',
            'hr' => true,
        ];
        $urlEditTreeEmployees = Router::url($urlEditTreeEmployees);
        $queryFilterData = [
            'data' => [
                'FilterData' => [
                    [
                        'Employee' => [
                            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => $user
                        ]
                    ]
                ]
            ]
        ];
        $urlGallery = [
            'controller' => 'employees',
            'action' => 'gallery',
            'plugin' => null,
            'prefix' => 'hr',
            'hr' => true,
            '?' => http_build_query($queryFilterData),
        ];
        $urlGallery = Router::url($urlGallery);
        $urlExport = [
            'controller' => 'employees',
            'action' => 'export',
            'plugin' => null,
            'prefix' => 'hr',
            'hr' => true,
        ];
        $urlExport = Router::url($urlExport);
        $urlDepartments = [
            'controller' => 'departments',
            'action' => 'index',
            'plugin' => null,
            'prefix' => 'hr',
            'hr' => true,
        ];
        $urlDepartments = Router::url($urlDepartments);
        $urlDeferred = [
            'controller' => 'deferred',
            'action' => 'index',
            'plugin' => null,
            'prefix' => 'hr',
            'hr' => true,
        ];
        $urlDeferred = Router::url($urlDeferred);

        $expected = [
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-employees',
                'title' => __d('tour', 'Start using'),
                'content' => __d('tour', 'To start using the directory, synchronize the information with LDAP server.'),
                'onShown' => "if (parseInt($('#content ul.list-statistics li span#countEmployees').text(), 10) > 0) tour.next();",
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#content #SearchQuery',
                'title' => __d('tour', 'Start using'),
                'content' => __d('tour', 'To search for information, enter your request in this field and click the button "%s".', '<span class="fas fa-search fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#header div.search-scope-filter',
                'title' => __d('tour', 'Refinement of the search result'),
                'content' => __d('tour', 'To refine the search result, use the "%s" button.', '<span class="fas fa-filter fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table thead tr:eq(0) th:has(a):eq(0)',
                'title' => __d('tour', 'Changes to the sorting order of table data'),
                'content' => __d('tour', 'To change the sort order of tabular data, click the corresponding column heading in the table. Clicking again will change the sort direction on the opposite.<br />To display the current sort directions use symbols "%s" and "%s" next to the name of the corresponding column.', '<span class="fas fa-long-arrow-alt-up fa-lg"></span>', '<span class="fas fa-long-arrow-alt-down fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td:has(a):eq(0)',
                'title' => __d('tour', 'View information about the employee'),
                'content' => __d('tour', 'To view brief information about an employee, hover this link. Clicking will open the details window for the employee.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-subordinates',
                'title' => __d('tour', 'Subordinate tree'),
                'content' => __d('tour', 'Use this button to view the subordinate employee tree.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change information about the employee use this button.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-sync',
                'title' => __d('tour', 'Synchronizing information'),
                'content' => __d('tour', 'Use this button to synchronize the information of this employee with LDAP server.'),
            ],
            [
                'path' => $urlViewEmployee,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Editing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronize information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Viewing the subordinate employee tree.</li></ul>'),
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content ul.nav-tabs li:has(a[href="#employeeInfo"])',
                'title' => __d('tour', 'Tabs for information input forms'),
                'content' => __d('tour', 'This tab contains employee information fields.'),
                'onNext' => "$('#content ul.nav-tabs a[href=\"#employeePhoto\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content ul.nav-tabs li:has(a[href="#employeePhoto"])',
                'title' => __d('tour', 'Tabs for information input forms'),
                'content' => __d('tour', 'This tab contains information about the employee\'s photo.'),
                'onPrev' => "$('#content ul.nav-tabs a[href=\"#employeeInfo\"]').trigger('click');",
                'onNext' => "$('#content ul.nav-tabs a[href=\"#employeeInfo\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content form.form-tabs div.progress',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'This progress bar shows the degree of filling of the data. Can accept from several colors: <ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-danger"><strong>Red</strong></span> - means that are not filled all required fields.</li><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-warning"><strong>Orange</strong></span> - is not filled in the required fields (allowed retention of information);</li><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-success"><strong>Green</strong></span> - filled in all the fields.</li></ul>'),
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content form.form-tabs .tabbable ul.nav',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'To select a group of fields for entering employee information, use the appropriate tab.'),
                'onPrev' => "$('#content ul.nav-tabs a[href=\"#employeePhoto\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'top',
                'element' => '#content form.form-tabs :submit',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'To save the information entered in the form fields, use this button.'),
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-employees',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item for the following:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Search for employee information</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee submission tree</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee gallery</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Save a local copy of the directory.</li></ul>'),
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-departments',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item to service a list of the full department names.'),
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-deferred',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item to service the deferred saves of employee information.'),
            ],
            [
                'path' => $urlTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Editing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronize information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Checking the status of the subordinate employee tree.</li></ul>'),
            ],
            [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operation menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronizing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Checks the status of the subordinate employee tree.</li></ul>'),
            ],
            [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content button[data-toggle-icons]:eq(0)',
                'title' => __d('tour', 'Controls of moving employee'),
                'content' => __d('tour', 'Use this button to display or hide the controls of moving employee.'),
                'onNext' => "if ($('#content .controls-move-employee:visible').length == 0) $('#content button[data-toggle-icons]:eq(0)').trigger('click');",
            ],
            [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #employee-tree li a[data-toggle="drag"]:eq(0)',
                'title' => __d('tour', 'Change employee position'),
                'content' => __d('tour', 'To change the position of an employee by dragging, use this button. The employee\'s position is used to determine the order of employees in the exported files of the directory.'),
                'onPrev' => "if ($('#content .controls-move-employee:visible').length > 0) $('#content .controls-move-employee:visible').prev('button[data-toggle-icons]').trigger('click');",
            ],
            [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #employee-tree li a[data-toggle="move"]:eq(0)',
                'title' => __d('tour', 'Change employee position'),
                'content' => __d('tour', 'Use the buttons "%s", "%s", "%s" and "%s" for an exact change position of the employee.', '<span class="fas fa-angle-double-up fa-lg"></span>', '<span class="fas fa-angle-up fa-lg"></span>', '<span class="fas fa-angle-down fa-lg"></span>', '<span class="fas fa-angle-double-down fa-lg"></span>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row',
                'title' => __d('tour', 'Table data filter'),
                'content' => __d('tour', 'To select tabular data, use the filter.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row code[data-toggle="filter-conditions"]',
                'title' => __d('tour', 'Table data filter'),
                'content' => __d('tour', 'Current filter condition.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.show-filter-btn',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'Use this button to display or hide the filter controls.'),
                'onNext' => "if ($('#content tr.filter-controls-row:visible').length == 0) $('#content button.show-filter-btn').trigger('click');",
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row input[name^="data[FilterData]"]:eq(0)',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'To create a filter condition, enter the required data in the input field of the corresponding column in the table.'),
                'onPrev' => "if ($('#content tr.filter-controls-row:visible').length > 0) $('#content button.show-filter-btn').trigger('click');",
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row button:has(:contains(=)):eq(0)',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'Use this button to select one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>=</i> - is equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&gt;</i> - more;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&ge;</i> - is greater than or equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&lt;<i></i> - less;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&le;</i> - is less than or equal;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&ne;<i></i> - is not equal.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row th.filter-action',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'If you want to add or delete filter lines, use these buttons.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row .filter-condition',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'Using this button, you can combine several filter lines with one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&amp;&amp;</i> - AND;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;||&nbsp;</i> - OR;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;!&nbsp;</i> - NOT.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.filter-apply',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'To apply the current filter, use this button.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.filter-clear',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'To clean the filter, use this button.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row a[href$=".prt"]',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'Use this button to print current tabular data.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu to update all the files in the directory.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-generate',
                'title' => __d('tour', 'Updating the directory file'),
                'content' => __d('tour', 'Use this button to update the directory file.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-download',
                'title' => __d('tour', 'Saving a directory file'),
                'content' => __d('tour', 'To save a local copy of the directory, use this button.'),
            ],
            [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Adding new department;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Arranging the list of departments alphabetically.</li></ul>'),
            ],
            [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a[data-toggle="drag"]',
                'title' => __d('tour', 'Change the position of the department'),
                'content' => __d('tour', 'To change the position of the department by dragging, use this button. The position of the department is used to determine the order of departments in the exported directory files alphabetically.'),
            ],
            [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a[data-toggle="move"]:eq(0)',
                'title' => __d('tour', 'Change the position of the department'),
                'content' => __d('tour', 'Use the buttons "%s", "%s", "%s" and "%s" for an exact change positions of the department.', '<span class="fas fa-angle-double-up fa-lg"></span>', '<span class="fas fa-angle-up fa-lg"></span>', '<span class="fas fa-angle-down fa-lg"></span>', '<span class="fas fa-angle-double-down fa-lg"></span>'),
            ],
            [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change the full name of the department or rename department, use this button.'),
            ],
            [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-delete',
                'title' => __d('tour', 'Deleting information'),
                'content' => __d('tour', 'Use this button to delete department.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change the deferred save information, use this button.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-delete',
                'title' => __d('tour', 'Deleting information'),
                'content' => __d('tour', 'Use this button to delete information about deferred save.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-approve',
                'title' => __d('tour', 'Approval of change of information'),
                'content' => __d('tour', 'To approve the change of information, use this button. The creator of this deferred save will receive a notification about the approval of the information being changed.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-reject',
                'title' => __d('tour', 'Rejection of change of information'),
                'content' => __d('tour', 'To reject the change of information, use this button. The creator of this deferred save will receive a notification of rejection of the changed information.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'top',
                'element' => '#content table tfoot td button[data-toggle="btn-action-select-all"]',
                'title' => __d('tour', 'Select / deselect all records'),
                'content' => __d('tour', 'To select or deselect all records, use this button.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'top',
                'element' => '#content table tfoot td button[value="group-action"]',
                'title' => __d('tour', 'Performing an action for a record group'),
                'content' => __d('tour', 'To perform an action on the group of records selected in the filter, use this button.')
            ],
        ];

        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_HUMAN_RESOURCES,
            'prefix' => 'hr'
        ] + $this->userInfo;
        $result = $this->_targetObject->getListSteps($userInfo);
        $this->assertData($expected, $result);
    }

    /**
     * testGetListStepsForAdmin method
     *
     * @return void
     */
    public function testGetListStepsForAdmin()
    {
        $user = 'Хвощинский В.В.';
        $urlHome = '/';
        $queryData = [
            'query' => $user,
            'target' => [
                'Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            ]
        ];
        $urlSearchEmployee = [
            'controller' => 'employees',
            'action' => 'search',
            'plugin' => null,
            'prefix' => 'admin',
            'admin' => true,
            '?' => http_build_query($queryData),
        ];
        $urlSearchEmployee = Router::url($urlSearchEmployee);
        $urlViewEmployee = [
            'controller' => 'employees',
            'action' => 'view',
            'plugin' => null,
            '7',
            'prefix' => 'admin',
            'admin' => true,
        ];
        $urlViewEmployee = Router::url($urlViewEmployee);
        $urlEditEmployee = [
            'controller' => 'employees',
            'action' => 'edit',
            'plugin' => null,
            '8c149661-7215-47de-b40e-35320a1ea508',
            'prefix' => 'admin',
            'admin' => true,
        ];
        $urlEditEmployee = Router::url($urlEditEmployee);
        $urlTreeEmployees = [
            'controller' => 'employees',
            'action' => 'tree',
            'plugin' => null,
            'prefix' => 'admin',
            'admin' => true,
        ];
        $urlTreeEmployees = Router::url($urlTreeEmployees);
        $urlEditTreeEmployees = [
            'controller' => 'employees',
            'action' => 'tree',
            'plugin' => null,
            '0',
            '1',
            'prefix' => 'admin',
            'admin' => true,
        ];
        $urlEditTreeEmployees = Router::url($urlEditTreeEmployees);
        $queryFilterData = [
            'data' => [
                'FilterData' => [
                    [
                        'Employee' => [
                            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => $user
                        ]
                    ]
                ]
            ]
        ];
        $urlGallery = [
            'controller' => 'employees',
            'action' => 'gallery',
            'plugin' => null,
            'prefix' => 'admin',
            'admin' => true,
            '?' => http_build_query($queryFilterData),
        ];
        $urlGallery = Router::url($urlGallery);
        $urlExport = [
            'controller' => 'employees',
            'action' => 'export',
            'plugin' => null,
            'prefix' => 'admin',
            'admin' => true,
        ];
        $urlExport = Router::url($urlExport);
        $urlDepartments = [
            'controller' => 'departments',
            'action' => 'index',
            'plugin' => null,
            'prefix' => 'admin',
            'admin' => true,
        ];
        $urlDepartments = Router::url($urlDepartments);
        $urlDeferred = [
            'controller' => 'deferred',
            'action' => 'index',
            'plugin' => null,
            'prefix' => 'admin',
            'admin' => true,
        ];
        $urlDeferred = Router::url($urlDeferred);

        $expected = [
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-employees',
                'title' => __d('tour', 'Start using'),
                'content' => __d('tour', 'To start using the directory, synchronize the information with LDAP server.'),
                'onShown' => "if (parseInt($('#content ul.list-statistics li span#countEmployees').text(), 10) > 0) tour.next();",
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#content #SearchQuery',
                'title' => __d('tour', 'Start using'),
                'content' => __d('tour', 'To search for information, enter your request in this field and click the button "%s".', '<span class="fas fa-search fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#header div.search-scope-filter',
                'title' => __d('tour', 'Refinement of the search result'),
                'content' => __d('tour', 'To refine the search result, use the "%s" button.', '<span class="fas fa-filter fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table thead tr:eq(0) th:has(a):eq(0)',
                'title' => __d('tour', 'Changes to the sorting order of table data'),
                'content' => __d('tour', 'To change the sort order of tabular data, click the corresponding column heading in the table. Clicking again will change the sort direction on the opposite.<br />To display the current sort directions use symbols "%s" and "%s" next to the name of the corresponding column.', '<span class="fas fa-long-arrow-alt-up fa-lg"></span>', '<span class="fas fa-long-arrow-alt-down fa-lg"></span>'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td:has(a):eq(0)',
                'title' => __d('tour', 'View information about the employee'),
                'content' => __d('tour', 'To view brief information about an employee, hover this link. Clicking will open the details window for the employee.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-subordinates',
                'title' => __d('tour', 'Subordinate tree'),
                'content' => __d('tour', 'Use this button to view the subordinate employee tree.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change information about the employee use this button.'),
            ],
            [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-sync',
                'title' => __d('tour', 'Synchronizing information'),
                'content' => __d('tour', 'Use this button to synchronize the information of this employee with LDAP server.'),
            ],
            [
                'path' => $urlViewEmployee,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Editing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronize information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Viewing the subordinate employee tree.</li></ul>'),
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content ul.nav-tabs li:has(a[href="#employeeInfo"])',
                'title' => __d('tour', 'Tabs for information input forms'),
                'content' => __d('tour', 'This tab contains employee information fields.'),
                'onNext' => "$('#content ul.nav-tabs a[href=\"#employeePhoto\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content ul.nav-tabs li:has(a[href="#employeePhoto"])',
                'title' => __d('tour', 'Tabs for information input forms'),
                'content' => __d('tour', 'This tab contains information about the employee\'s photo.'),
                'onPrev' => "$('#content ul.nav-tabs a[href=\"#employeeInfo\"]').trigger('click');",
                'onNext' => "$('#content ul.nav-tabs a[href=\"#employeeInfo\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content form.form-tabs div.progress',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'This progress bar shows the degree of filling of the data. Can accept from several colors: <ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-danger"><strong>Red</strong></span> - means that are not filled all required fields.</li><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-warning"><strong>Orange</strong></span> - is not filled in the required fields (allowed retention of information);</li><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-success"><strong>Green</strong></span> - filled in all the fields.</li></ul>'),
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content form.form-tabs .tabbable ul.nav',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'To select a group of fields for entering employee information, use the appropriate tab.'),
                'onPrev' => "$('#content ul.nav-tabs a[href=\"#employeePhoto\"]').trigger('click');",
            ],
            [
                'path' => $urlEditEmployee,
                'placement' => 'top',
                'element' => '#content form.form-tabs :submit',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'To save the information entered in the form fields, use this button.'),
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-employees',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item for the following:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Search for employee information</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee submission tree</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee gallery</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Save a local copy of the directory.</li></ul>'),
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-departments',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item to service a list of the full department names.'),
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-deferred',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item to service the deferred saves of employee information.'),
            ],
            [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-settings',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item to change application settings.'),
            ],
            [
                'path' => $urlTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Editing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronize information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Checking the status of the subordinate employee tree.</li></ul>'),
            ],
            [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operation menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronizing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Checks the status of the subordinate employee tree.</li></ul>'),
            ],
            [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content button[data-toggle-icons]:eq(0)',
                'title' => __d('tour', 'Controls of moving employee'),
                'content' => __d('tour', 'Use this button to display or hide the controls of moving employee.'),
                'onNext' => "if ($('#content .controls-move-employee:visible').length == 0) $('#content button[data-toggle-icons]:eq(0)').trigger('click');",
            ],
            [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #employee-tree li a[data-toggle="drag"]:eq(0)',
                'title' => __d('tour', 'Change employee position'),
                'content' => __d('tour', 'To change the position of an employee by dragging, use this button. The employee\'s position is used to determine the order of employees in the exported files of the directory.'),
                'onPrev' => "if ($('#content .controls-move-employee:visible').length > 0) $('#content .controls-move-employee:visible').prev('button[data-toggle-icons]').trigger('click');",
            ],
            [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #employee-tree li a[data-toggle="move"]:eq(0)',
                'title' => __d('tour', 'Change employee position'),
                'content' => __d('tour', 'Use the buttons "%s", "%s", "%s" and "%s" for an exact change position of the employee.', '<span class="fas fa-angle-double-up fa-lg"></span>', '<span class="fas fa-angle-up fa-lg"></span>', '<span class="fas fa-angle-down fa-lg"></span>', '<span class="fas fa-angle-double-down fa-lg"></span>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row',
                'title' => __d('tour', 'Table data filter'),
                'content' => __d('tour', 'To select tabular data, use the filter.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row code[data-toggle="filter-conditions"]',
                'title' => __d('tour', 'Table data filter'),
                'content' => __d('tour', 'Current filter condition.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.show-filter-btn',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'Use this button to display or hide the filter controls.'),
                'onNext' => "if ($('#content tr.filter-controls-row:visible').length == 0) $('#content button.show-filter-btn').trigger('click');",
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row input[name^="data[FilterData]"]:eq(0)',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'To create a filter condition, enter the required data in the input field of the corresponding column in the table.'),
                'onPrev' => "if ($('#content tr.filter-controls-row:visible').length > 0) $('#content button.show-filter-btn').trigger('click');",
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row button:has(:contains(=)):eq(0)',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'Use this button to select one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>=</i> - is equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&gt;</i> - more;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&ge;</i> - is greater than or equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&lt;<i></i> - less;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&le;</i> - is less than or equal;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&ne;<i></i> - is not equal.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-controls-row th.filter-action',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'If you want to add or delete filter lines, use these buttons.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row .filter-condition',
                'title' => __d('tour', 'Elements of formation of filter conditions'),
                'content' => __d('tour', 'Using this button, you can combine several filter lines with one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&amp;&amp;</i> - AND;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;||&nbsp;</i> - OR;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;!&nbsp;</i> - NOT.</li></ul>'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.filter-apply',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'To apply the current filter, use this button.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row button.filter-clear',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'To clean the filter, use this button.'),
            ],
            [
                'path' => $urlGallery,
                'placement' => 'bottom',
                'element' => '#content tr.filter-header-row a[href$=".prt"]',
                'title' => __d('tour', 'Table data filter controls'),
                'content' => __d('tour', 'Use this button to print current tabular data.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu to update all the files in the directory.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-generate',
                'title' => __d('tour', 'Updating the directory file'),
                'content' => __d('tour', 'Use this button to update the directory file.'),
            ],
            [
                'path' => $urlExport,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-download',
                'title' => __d('tour', 'Saving a directory file'),
                'content' => __d('tour', 'To save a local copy of the directory, use this button.'),
            ],
            [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Adding new department;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Arranging the list of departments alphabetically.</li></ul>'),
            ],
            [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a[data-toggle="drag"]',
                'title' => __d('tour', 'Change the position of the department'),
                'content' => __d('tour', 'To change the position of the department by dragging, use this button. The position of the department is used to determine the order of departments in the exported directory files alphabetically.'),
            ],
            [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a[data-toggle="move"]:eq(0)',
                'title' => __d('tour', 'Change the position of the department'),
                'content' => __d('tour', 'Use the buttons "%s", "%s", "%s" and "%s" for an exact change positions of the department.', '<span class="fas fa-angle-double-up fa-lg"></span>', '<span class="fas fa-angle-up fa-lg"></span>', '<span class="fas fa-angle-down fa-lg"></span>', '<span class="fas fa-angle-double-down fa-lg"></span>'),
            ],
            [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change the full name of the department or rename department, use this button.'),
            ],
            [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-delete',
                'title' => __d('tour', 'Deleting information'),
                'content' => __d('tour', 'Use this button to delete department.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change the deferred save information, use this button.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-delete',
                'title' => __d('tour', 'Deleting information'),
                'content' => __d('tour', 'Use this button to delete information about deferred save.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-approve',
                'title' => __d('tour', 'Approval of change of information'),
                'content' => __d('tour', 'To approve the change of information, use this button. The creator of this deferred save will receive a notification about the approval of the information being changed.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-reject',
                'title' => __d('tour', 'Rejection of change of information'),
                'content' => __d('tour', 'To reject the change of information, use this button. The creator of this deferred save will receive a notification of rejection of the changed information.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'top',
                'element' => '#content table tfoot td button[data-toggle="btn-action-select-all"]',
                'title' => __d('tour', 'Select / deselect all records'),
                'content' => __d('tour', 'To select or deselect all records, use this button.'),
            ],
            [
                'path' => $urlDeferred,
                'placement' => 'top',
                'element' => '#content table tfoot td button[value="group-action"]',
                'title' => __d('tour', 'Performing an action for a record group'),
                'content' => __d('tour', 'To perform an action on the group of records selected in the filter, use this button.')
            ],
        ];

        $userInfo = [
            'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
            'prefix' => 'admin'
        ] + $this->userInfo;
        $result = $this->_targetObject->getListSteps($userInfo);
        $this->assertData($expected, $result);
    }
}
