<?php
/**
 * This file is the model file of the application. Used for
 *  creation configuration for application tour.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Model
 */

App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');
App::uses('Router', 'Routing');
App::uses('UserInfo', 'CakeLdap.Utility');

/**
 * The model is used for creation configuration for application tour.
 *  variables for View.
 *
 * @package app.Model
 */
class Tour extends AppModel
{

    /**
     * Name of the model.
     *
     * @var string
     * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#name
     */
    public $name = 'Tour';

    /**
     * The name of the DataSource connection that this Model uses
     *
     * @var string
     * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#usedbconfig
     */
    public $useDbConfig = false;

    /**
     * Custom database table name, or null/false if no table association is desired.
     *
     * @var string
     * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#usetable
     */
    public $useTable = false;

    /**
     * Return configuration for tour of the application
     *
     * @param array $userInfo Information about current logged in user.
     * @return array Return array configuration for tour.
     */
    public function getListSteps($userInfo = null)
    {
        $result = [];
        if (empty($userInfo)) {
            return $result;
        }

        $modelEmployee = ClassRegistry::init('Employee');
        $userInfoLib = new UserInfo();
        $defaultUserInfo = [
            'user' => null,
            'role' => null,
            'prefix' => null,
            'id' => null,
            'includedFields' => [
                CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => null
            ]
        ];
        $userInfo += $defaultUserInfo;
        extract($userInfo);
        $guid = Hash::get($includedFields, CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID);

        $urlHome = '/';
        if ($userInfoLib->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true, $userInfo)) {
            $result[] = [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-employees',
                'title' => __d('tour', 'Start using'),
                'content' => __d('tour', 'To start using the directory, synchronize the information with LDAP server.'),
                'onShown' => "if (parseInt($('#content ul.list-statistics li span#countEmployees').text(), 10) > 0) tour.next();",
            ];
        }
        $result[] = [
            'path' => $urlHome,
            'placement' => 'bottom',
            'element' => '#content #SearchQuery',
            'title' => __d('tour', 'Start using'),
            'content' => __d('tour', 'To search for information, enter your request in this field and click the button "%s".', '<span class="fas fa-search fa-lg"></span>'),
        ];
        if (!empty($user)) {
            $queryData = ['query' => $user];
            $targetModels = $modelEmployee->getSearchTargetModels($role);
            if (isset($targetModels[$modelEmployee->alias]['fields'][$modelEmployee->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME])) {
                $queryData['target'] = [$modelEmployee->alias . '.' . $modelEmployee->alias . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME];
            }

            $urlSearchEmployee = [
                'controller' => 'employees',
                'action' => 'search',
                'plugin' => null,
                '?' => http_build_query($queryData),
            ];
            if (!empty($prefix)) {
                $urlSearchEmployee['prefix'] = $prefix;
                $urlSearchEmployee[$prefix] = true;
            }
            $urlSearchEmployee = Router::url($urlSearchEmployee);
            $result[] = [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#header div.search-scope-filter',
                'title' => __d('tour', 'Refinement of the search result'),
                'content' => __d('tour', 'To refine the search result, use the "%s" button.', '<span class="fas fa-filter fa-lg"></span>'),
            ];
            $result[] = [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table thead tr:eq(0) th:has(a):eq(0)',
                'title' => __d('tour', 'Changes to the sorting order of table data'),
                'content' => __d('tour', 'To change the sort order of tabular data, click the corresponding column heading in the table. Clicking again will change the sort direction on the opposite.<br />To display the current sort directions use symbols "%s" and "%s" next to the name of the corresponding column.', '<span class="fas fa-long-arrow-alt-up fa-lg"></span>', '<span class="fas fa-long-arrow-alt-down fa-lg"></span>'),
            ];
            $result[] = [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td:has(a):eq(0)',
                'title' => __d('tour', 'View information about the employee'),
                'content' => __d('tour', 'To view brief information about an employee, hover this link. Clicking will open the details window for the employee.'),
            ];
            $result[] = [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-subordinates',
                'title' => __d('tour', 'Subordinate tree'),
                'content' => __d('tour', 'Use this button to view the subordinate employee tree.'),
            ];
            $result[] = [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change information about the employee use this button.'),
            ];
            $result[] = [
                'path' => $urlSearchEmployee,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-sync',
                'title' => __d('tour', 'Synchronizing information'),
                'content' => __d('tour', 'Use this button to synchronize the information of this employee with LDAP server.'),
            ];
        }

        if (!empty($id)) {
            $urlViewEmployee = [
                'controller' => 'employees',
                'action' => 'view',
                'plugin' => null,
                $id,
            ];
            if (!empty($prefix)) {
                $urlViewEmployee['prefix'] = $prefix;
                $urlViewEmployee[$prefix] = true;
            }
            $urlViewEmployee = Router::url($urlViewEmployee);
            $result[] = [
                'path' => $urlViewEmployee,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Editing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronize information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Viewing the subordinate employee tree.</li></ul>'),
            ];
        }

        if (!empty($guid)) {
            $urlEditEmployee = [
                'controller' => 'employees',
                'action' => 'edit',
                'plugin' => null,
                $guid,
            ];
            if (!empty($prefix)) {
                $urlEditEmployee['prefix'] = $prefix;
                $urlEditEmployee[$prefix] = true;
            }
            $urlEditEmployee = Router::url($urlEditEmployee);
            $result[] = [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content ul.nav-tabs li:has(a[href="#employeeInfo"])',
                'title' => __d('tour', 'Tabs for information input forms'),
                'content' => __d('tour', 'This tab contains employee information fields.'),
                'onNext' => "$('#content ul.nav-tabs a[href=\"#employeePhoto\"]').trigger('click');",
            ];
            $result[] = [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content ul.nav-tabs li:has(a[href="#employeePhoto"])',
                'title' => __d('tour', 'Tabs for information input forms'),
                'content' => __d('tour', 'This tab contains information about the employee\'s photo.'),
                'onPrev' => "$('#content ul.nav-tabs a[href=\"#employeeInfo\"]').trigger('click');",
                'onNext' => "$('#content ul.nav-tabs a[href=\"#employeeInfo\"]').trigger('click');",
            ];
            $result[] = [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content form.form-tabs div.progress',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'This progress bar shows the degree of filling of the data. Can accept from several colors: <ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-danger"><strong>Red</strong></span> - means that are not filled all required fields.</li><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-warning"><strong>Orange</strong></span> - is not filled in the required fields (allowed retention of information);</li><li><span class="fa-li"><i class="fas fa-check"></i></span><span class="bg-success"><strong>Green</strong></span> - filled in all the fields.</li></ul>'),
            ];
            $result[] = [
                'path' => $urlEditEmployee,
                'placement' => 'bottom',
                'element' => '#content form.form-tabs .tabbable ul.nav',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'To select a group of fields for entering employee information, use the appropriate tab.'),
                'onPrev' => "$('#content ul.nav-tabs a[href=\"#employeePhoto\"]').trigger('click');",
            ];
            $result[] = [
                'path' => $urlEditEmployee,
                'placement' => 'top',
                'element' => '#content form.form-tabs :submit',
                'title' => __d('tour', 'Elements of the information input form'),
                'content' => __d('tour', 'To save the information entered in the form fields, use this button.'),
            ];
        }

        $result[] = [
            'path' => $urlHome,
            'placement' => 'bottom',
            'element' => '#header ul.nav > li > a.app-tour-main-menu-employees',
            'title' => __d('tour', 'Elements of the main menu'),
            'content' => __d('tour', 'Use this menu item for the following:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Search for employee information</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee submission tree</li><li><span class="fa-li"><i class="fas fa-check"></i></span>View employee gallery</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Save a local copy of the directory.</li></ul>'),
        ];

        if ($userInfoLib->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true, $userInfo)) {
            $result[] = [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-departments',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item to service a list of the full department names.'),
            ];
            $result[] = [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-deferred',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item to service the deferred saves of employee information.'),
            ];
        }
        if ($userInfoLib->checkUserRole(USER_ROLE_ADMIN, true, $userInfo)) {
            $result[] = [
                'path' => $urlHome,
                'placement' => 'bottom',
                'element' => '#header ul.nav > li > a.app-tour-main-menu-settings',
                'title' => __d('tour', 'Elements of the main menu'),
                'content' => __d('tour', 'Use this menu item to change application settings.'),
            ];
        }

        $urlTreeEmployees = [
            'controller' => 'employees',
            'action' => 'tree',
            'plugin' => null,
        ];
        if (!empty($prefix)) {
            $urlTreeEmployees['prefix'] = $prefix;
            $urlTreeEmployees[$prefix] = true;
        }
        $urlTreeEmployees = Router::url($urlTreeEmployees);
        $result[] = [
            'path' => $urlTreeEmployees,
            'placement' => 'bottom',
            'element' => '#content #pageHeaderMenu',
            'title' => __d('tour', 'Operation menu'),
            'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Editing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronize information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Checking the status of the subordinate employee tree.</li></ul>'),
        ];

        if ($userInfoLib->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true, $userInfo)) {
            $urlEditTreeEmployees = [
                'controller' => 'employees',
                'action' => 'tree',
                'plugin' => null,
                '0',
                '1',
            ];
            if (!empty($prefix)) {
                $urlEditTreeEmployees['prefix'] = $prefix;
                $urlEditTreeEmployees[$prefix] = true;
            }
            $urlEditTreeEmployees = Router::url($urlEditTreeEmployees);
            $result[] = [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operation menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Synchronizing information;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Checks the status of the subordinate employee tree.</li></ul>'),
            ];
            $result[] = [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content button[data-toggle-icons]:eq(0)',
                'title' => __d('tour', 'Controls of moving employee'),
                'content' => __d('tour', 'Use this button to display or hide the controls of moving employee.'),
                'onNext' => "if ($('#content .controls-move-employee:visible').length == 0) $('#content button[data-toggle-icons]:eq(0)').trigger('click');",
            ];
            $result[] = [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #employee-tree li a[data-toggle="drag"]:eq(0)',
                'title' => __d('tour', 'Change employee position'),
                'content' => __d('tour', 'To change the position of an employee by dragging, use this button. The employee\'s position is used to determine the order of employees in the exported files of the directory.'),
                'onPrev' => "if ($('#content .controls-move-employee:visible').length > 0) $('#content .controls-move-employee:visible').prev('button[data-toggle-icons]').trigger('click');",
            ];
            $result[] = [
                'path' => $urlEditTreeEmployees,
                'placement' => 'bottom',
                'element' => '#content #employee-tree li a[data-toggle="move"]:eq(0)',
                'title' => __d('tour', 'Change employee position'),
                'content' => __d('tour', 'Use the buttons "%s", "%s", "%s" and "%s" for an exact change position of the employee.', '<span class="fas fa-angle-double-up fa-lg"></span>', '<span class="fas fa-angle-up fa-lg"></span>', '<span class="fas fa-angle-down fa-lg"></span>', '<span class="fas fa-angle-double-down fa-lg"></span>'),
            ];
        }

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
        if (!empty($prefix)) {
            $urlGallery['prefix'] = $prefix;
            $urlGallery[$prefix] = true;
        }
        $urlGallery = Router::url($urlGallery);
        $result[] = [
            'path' => $urlGallery,
            'placement' => 'bottom',
            'element' => '#content tr.filter-header-row',
            'title' => __d('tour', 'Table data filter'),
            'content' => __d('tour', 'To select tabular data, use the filter.'),
        ];
        $result[] = [
            'path' => $urlGallery,
            'placement' => 'bottom',
            'element' => '#content tr.filter-header-row code[data-toggle="filter-conditions"]',
            'title' => __d('tour', 'Table data filter'),
            'content' => __d('tour', 'Current filter condition.'),
        ];
        $result[] = [
            'path' => $urlGallery,
            'placement' => 'bottom',
            'element' => '#content tr.filter-header-row button.show-filter-btn',
            'title' => __d('tour', 'Table data filter controls'),
            'content' => __d('tour', 'Use this button to display or hide the filter controls.'),
            'onNext' => "if ($('#content tr.filter-controls-row:visible').length == 0) $('#content button.show-filter-btn').trigger('click');",
        ];
        $result[] = [
            'path' => $urlGallery,
            'placement' => 'bottom',
            'element' => '#content tr.filter-controls-row input[name^="data[FilterData]"]:eq(0)',
            'title' => __d('tour', 'Elements of formation of filter conditions'),
            'content' => __d('tour', 'To create a filter condition, enter the required data in the input field of the corresponding column in the table.'),
            'onPrev' => "if ($('#content tr.filter-controls-row:visible').length > 0) $('#content button.show-filter-btn').trigger('click');",
        ];
        $result[] = [
            'path' => $urlGallery,
            'placement' => 'bottom',
            'element' => '#content tr.filter-controls-row button:has(:contains(=)):eq(0)',
            'title' => __d('tour', 'Elements of formation of filter conditions'),
            'content' => __d('tour', 'Use this button to select one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>=</i> - is equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&gt;</i> - more;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&ge;</i> - is greater than or equal to;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&lt;<i></i> - less;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&le;</i> - is less than or equal;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>&ne;<i></i> - is not equal.</li></ul>'),
        ];
        $result[] = [
            'path' => $urlGallery,
            'placement' => 'bottom',
            'element' => '#content tr.filter-controls-row th.filter-action',
            'title' => __d('tour', 'Elements of formation of filter conditions'),
            'content' => __d('tour', 'If you want to add or delete filter lines, use these buttons.'),
        ];
        $result[] = [
            'path' => $urlGallery,
            'placement' => 'bottom',
            'element' => '#content tr.filter-header-row .filter-condition',
            'title' => __d('tour', 'Elements of formation of filter conditions'),
            'content' => __d('tour', 'Using this button, you can combine several filter lines with one of the following logical conditions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&amp;&amp;</i> - AND;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;||&nbsp;</i> - OR;</li><li><span class="fa-li"><i class="fas fa-check"></i></span><i>&nbsp;!&nbsp;</i> - NOT.</li></ul>'),
        ];
        $result[] = [
            'path' => $urlGallery,
            'placement' => 'bottom',
            'element' => '#content tr.filter-header-row button.filter-apply',
            'title' => __d('tour', 'Table data filter controls'),
            'content' => __d('tour', 'To apply the current filter, use this button.'),
        ];
        $result[] = [
            'path' => $urlGallery,
            'placement' => 'bottom',
            'element' => '#content tr.filter-header-row button.filter-clear',
            'title' => __d('tour', 'Table data filter controls'),
            'content' => __d('tour', 'To clean the filter, use this button.'),
        ];
        $result[] = [
            'path' => $urlGallery,
            'placement' => 'bottom',
            'element' => '#content tr.filter-header-row a[href$=".prt"]',
            'title' => __d('tour', 'Table data filter controls'),
            'content' => __d('tour', 'Use this button to print current tabular data.'),
        ];

        $urlExport = [
            'controller' => 'employees',
            'action' => 'export',
            'plugin' => null,
        ];
        if (!empty($prefix)) {
            $urlExport['prefix'] = $prefix;
            $urlExport[$prefix] = true;
        }
        $urlExport = Router::url($urlExport);
        $result[] = [
            'path' => $urlExport,
            'placement' => 'bottom',
            'element' => '#content #pageHeaderMenu',
            'title' => __d('tour', 'Operation menu'),
            'content' => __d('tour', 'Use the operations menu to update all the files in the directory.'),
        ];
        $result[] = [
            'path' => $urlExport,
            'placement' => 'bottom',
            'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-generate',
            'title' => __d('tour', 'Updating the directory file'),
            'content' => __d('tour', 'Use this button to update the directory file.'),
        ];
        $result[] = [
            'path' => $urlExport,
            'placement' => 'bottom',
            'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-download',
            'title' => __d('tour', 'Saving a directory file'),
            'content' => __d('tour', 'To save a local copy of the directory, use this button.'),
        ];
        if ($userInfoLib->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true, $userInfo)) {
            $urlDepartments = [
                'controller' => 'departments',
                'action' => 'index',
                'plugin' => null,
            ];
            if (!empty($prefix)) {
                $urlDepartments['prefix'] = $prefix;
                $urlDepartments[$prefix] = true;
            }
            $urlDepartments = Router::url($urlDepartments);
            $result[] = [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content #pageHeaderMenu',
                'title' => __d('tour', 'Operation menu'),
                'content' => __d('tour', 'Use the operations menu for the following actions:<ul class="fa-ul"><li><span class="fa-li"><i class="fas fa-check"></i></span>Adding new department;</li><li><span class="fa-li"><i class="fas fa-check"></i></span>Arranging the list of departments alphabetically.</li></ul>'),
            ];
            $result[] = [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a[data-toggle="drag"]',
                'title' => __d('tour', 'Change the position of the department'),
                'content' => __d('tour', 'To change the position of the department by dragging, use this button. The position of the department is used to determine the order of departments in the exported directory files alphabetically.'),
            ];
            $result[] = [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a[data-toggle="move"]:eq(0)',
                'title' => __d('tour', 'Change the position of the department'),
                'content' => __d('tour', 'Use the buttons "%s", "%s", "%s" and "%s" for an exact change positions of the department.', '<span class="fas fa-angle-double-up fa-lg"></span>', '<span class="fas fa-angle-up fa-lg"></span>', '<span class="fas fa-angle-down fa-lg"></span>', '<span class="fas fa-angle-double-down fa-lg"></span>'),
            ];
            $result[] = [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody td.action:eq(0) a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change the full name of the department or rename department, use this button.'),
            ];
            $result[] = [
                'path' => $urlDepartments,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-delete',
                'title' => __d('tour', 'Deleting information'),
                'content' => __d('tour', 'Use this button to delete department.'),
            ];

            $urlDeferred = [
                'controller' => 'deferred',
                'action' => 'index',
                'plugin' => null,
            ];
            if (!empty($prefix)) {
                $urlDeferred['prefix'] = $prefix;
                $urlDeferred[$prefix] = true;
            }
            $urlDeferred = Router::url($urlDeferred);
            $result[] = [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-edit',
                'title' => __d('tour', 'Editing of information'),
                'content' => __d('tour', 'To change the deferred save information, use this button.'),
            ];
            $result[] = [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-delete',
                'title' => __d('tour', 'Deleting information'),
                'content' => __d('tour', 'Use this button to delete information about deferred save.'),
            ];
            $result[] = [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-approve',
                'title' => __d('tour', 'Approval of change of information'),
                'content' => __d('tour', 'To approve the change of information, use this button. The creator of this deferred save will receive a notification about the approval of the information being changed.'),
            ];
            $result[] = [
                'path' => $urlDeferred,
                'placement' => 'bottom',
                'element' => '#content table tbody tr:eq(0) td.action a.app-tour-btn-reject',
                'title' => __d('tour', 'Rejection of change of information'),
                'content' => __d('tour', 'To reject the change of information, use this button. The creator of this deferred save will receive a notification of rejection of the changed information.'),
            ];
            $result[] = [
                'path' => $urlDeferred,
                'placement' => 'top',
                'element' => '#content table tfoot td button[data-toggle="btn-action-select-all"]',
                'title' => __d('tour', 'Select / deselect all records'),
                'content' => __d('tour', 'To select or deselect all records, use this button.'),
            ];
            $result[] = [
                'path' => $urlDeferred,
                'placement' => 'top',
                'element' => '#content table tfoot td button[value="group-action"]',
                'title' => __d('tour', 'Performing an action for a record group'),
                'content' => __d('tour', 'To perform an action on the group of records selected in the filter, use this button.')
            ];
        }

        return $result;
    }
}
