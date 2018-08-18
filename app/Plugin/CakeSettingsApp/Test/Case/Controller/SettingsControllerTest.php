<?php
App::uses('AppControllerTestCase', 'CakeSettingsApp.Test');
App::uses('SettingsController', 'CakeSettingsApp.Controller');
App::uses('Model', 'Model');

/**
 * SettingsController Test Case
 *
 */
class SettingsControllerTest extends AppControllerTestCase
{

    /**
     * Target Controller name
     *
     * @var string
     */
    public $targetController = 'CakeSettingsApp.Settings';

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.cake_session',
        'plugin.cake_settings_app.ldap',
        'plugin.cake_settings_app.queued_task',
    ];

    /**
     * testIndex method
     *
     * Method: GET
     * @return void
     */
    public function testIndexGet()
    {
        $this->_generateMockedController();

        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $result = $this->testAction('/cake_settings_app/settings/index', $opt);
        $expected = [
            'groupList' => [
                'CN=Web.Admin,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com' => 'Web.Admin',
                'CN=Web.Extend,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com' => 'Web.Extend',
            ],
            'containerList' => [
                'OU=Группы,DC=fabrikam,DC=com',
                'OU=Компьютеры,DC=fabrikam,DC=com'
            ],
            'configUIlangs' => true,
            'configSMTP' => true,
            'configAcLimit' => true,
            'configADsearch' => true,
            'configExtAuth' => true,
            'authGroups' => [
                CAKE_SETTINGS_APP_TEST_USER_ROLE_EXTENDED => [
                    'field' => 'ManagerGroupMember',
                    'name' => 'manager',
                    'prefix' => 'manager'
                ],
                CAKE_SETTINGS_APP_TEST_USER_ROLE_ADMIN => [
                    'field' => 'AdminGroupMember',
                    'name' => 'administrator',
                    'prefix' => 'admin'
                ]
            ],
            'languages' => [
                'US' => 'English',
                'RU' => 'Русский язык'
            ],
            'varsExt' => [
                'countries' => [
                    'BY' => 'Belarus',
                    'RU' => 'Russia',
                    'US' => 'United States'
                ]
            ],
            'pageHeader' => __d('cake_settings_app', 'Application settings'),
            'uiLcid2' => 'en',
            'uiLcid3' => 'eng'
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testIndex method
     *
     * Method: POST
     * Save: success
     * @return void
     */
    public function testIndexSuccessPost()
    {
        $this->_generateMockedController(true);
        $opt = [
            'data' => [
                'Setting' => [
                    'EmailContact' => 'adm@fabrikam.com',
                    'EmailSubject' => 'Test msg',
                    'Company' => 'Test ORG',
                    'SearchBase' => '',
                    'AutocompleteLimit' => '5',
                    'ExternalAuth' => false,
                    'AdminGroupMember' => 'CN=Web.Admin,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com',
                    'ManagerGroupMember' => 'CN=Web.Manager,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com',
                    'EmailSmtphost' => 'localhost',
                    'EmailSmtpport' => '25',
                    'EmailSmtpuser' => 'usr',
                    'EmailSmtppassword' => 'test',
                    'EmailSmtppassword_confirm' => 'test',
                    'EmailNotifyUser' => true,
                    'Language' => 'US',
                    'CountryCode' => 'RU'
                ],
            ],
            'method' => 'POST',
        ];
        $this->testAction('/cake_settings_app/settings/index', $opt);
        $this->checkFlashMessage(__d('cake_settings_app', 'Application settings has been saved.'));
        $this->checkRedirect('/settings');
    }

    /**
     * testIndex method
     *
     * Method: POST
     * Save: Unsuccess
     * @return void
     */
    public function testIndexUnsuccessPostMsg()
    {
        $this->_generateMockedController(false);

        $opt = [
            'data' => [
                'Setting' => [
                    'EmailContact' => 'adm@fabrikam.com',
                    'EmailSubject' => 'Test msg',
                    'Company' => 'Test ORG',
                    'SearchBase' => '',
                    'AutocompleteLimit' => '5',
                    'ExternalAuth' => false,
                    'AdminGroupMember' => 'TEST',
                    'ManagerGroupMember' => 'CN=Web.Manager,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com',
                    'EmailSmtphost' => 'localhost',
                    'EmailSmtpport' => '25',
                    'EmailSmtpuser' => 'usr',
                    'EmailSmtppassword' => 'test',
                    'EmailSmtppassword_confirm' => 'test',
                    'EmailNotifyUser' => true,
                    'Language' => 'eng'
                ],
            ],
            'method' => 'POST',
        ];
        $result = $this->testAction('/cake_settings_app/settings/index', $opt);
        $this->checkFlashMessage(__d('cake_settings_app', 'Unable to save application settings.'));
    }

    /**
     * testQueueGet method
     *
     * Method: GET
     * @return void
     */
    public function testQueueGet()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $this->_generateMockedController();
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $result = $this->testAction('/cake_settings_app/settings/queue', $opt);
        $expected = [
            'queue' => [
                [
                    'ExtendQueuedTask' => [
                        'id' => '2',
                        'jobtype' => 'Sync',
                        'created' => '2016-09-26 08:28:15',
                        'fetched' => null,
                        'progress' => null,
                        'completed' => null,
                        'reference' => null,
                        'failed' => '0',
                        'failure_message' => null,
                        'status' => 'NOT_STARTED'
                    ]
                ],
                [
                    'ExtendQueuedTask' => [
                        'id' => '1',
                        'jobtype' => 'Sync',
                        'created' => '2016-09-26 08:27:17',
                        'fetched' => '2016-09-26 08:28:01',
                        'progress' => '1.00',
                        'completed' => '2016-09-26 08:28:05',
                        'reference' => null,
                        'failed' => '0',
                        'failure_message' => null,
                        'status' => 'COMPLETED',
                    ]
                ]
            ],
            'groupActions' => [
                'group-data-del' => __d('cake_settings_app', 'Delete selected tasks')
            ],
            'taskStateList' => [
                'NOT_READY' => __d('cake_settings_app', 'Not ready'),
                'NOT_STARTED' => __d('cake_settings_app', 'Not started'),
                'IN_PROGRESS' => __d('cake_settings_app', 'In progress'),
                'COMPLETED' => __d('cake_settings_app', 'Completed'),
                'FAILED' => __d('cake_settings_app', 'Failed'),
                'UNKNOWN' => __d('cake_settings_app', 'Unknown'),
            ],
            'stateData' => [
                [
                    'stateName' => __d('cake_settings_app', 'Completed'),
                    'stateId' => 'COMPLETED',
                    'amount' => 1,
                    'stateUrl' => [
                        'controller' => 'settings',
                        'action' => 'queue',
                        'plugin' => 'cake_settings_app',
                        '?' => [
                            'data[FilterData][0][ExtendQueuedTask][status]' => 'COMPLETED',
                        ]
                    ],
                    'class' => 'progress-bar-success'
                ],
                [
                    'stateName' => __d('cake_settings_app', 'Not started'),
                    'stateId' => 'NOT_STARTED',
                    'amount' => 1,
                    'stateUrl' => [
                        'controller' => 'settings',
                        'action' => 'queue',
                        'plugin' => 'cake_settings_app',
                        '?' => [
                            'data[FilterData][0][ExtendQueuedTask][status]' => 'NOT_STARTED',
                        ]
                    ],
                    'class' => 'progress-bar-success progress-bar-striped',
                ]
            ],
            'usePost' => true,
            'pageHeader' => __d('cake_settings_app', 'Queue of tasks'),
            'headerMenuActions' => [
                [
                    'fas fa-trash-alt',
                    __d('cake_settings_app', 'Clear queue of tasks'),
                    ['controller' => 'settings', 'action' => 'clear', 'plugin' => 'cake_settings_app', 'prefix' => false],
                    [
                        'title' => __d('cake_settings_app', 'Clear queue of tasks'),
                        'action-type' => 'confirm-post',
                        'data-confirm-msg' => __d('cake_settings_app', 'Are you sure you wish to clear queue of tasks?'),
                    ]
                ]
            ],
            'uiLcid2' => 'en',
            'uiLcid3' => 'eng'
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testQueueGetUseFilter method
     *
     * Method: GET
     * @return void
     */
    public function testQueueGetUseFilter()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $this->_generateMockedController();
        $opt = [
            'method' => 'GET',
            'return' => 'vars',
        ];
        $data = http_build_query(
            [
                'data' => [
                    'FilterData' => [
                        [
                            'ExtendQueuedTask' => [
                                'id' => [2]
                            ]
                        ]
                    ],
                ]
            ]
        );
        $result = $this->testAction('/cake_settings_app/settings/queue?' . $data, $opt);
        $expected = [
            'queue' => [
                [
                    'ExtendQueuedTask' => [
                        'id' => '2',
                        'jobtype' => 'Sync',
                        'created' => '2016-09-26 08:28:15',
                        'fetched' => null,
                        'progress' => null,
                        'completed' => null,
                        'reference' => null,
                        'failed' => '0',
                        'failure_message' => null,
                        'status' => 'NOT_STARTED'
                    ]
                ],
            ],
            'groupActions' => [
                'group-data-del' => __d('cake_settings_app', 'Delete selected tasks')
            ],
            'taskStateList' => [
                'NOT_READY' => __d('cake_settings_app', 'Not ready'),
                'NOT_STARTED' => __d('cake_settings_app', 'Not started'),
                'IN_PROGRESS' => __d('cake_settings_app', 'In progress'),
                'COMPLETED' => __d('cake_settings_app', 'Completed'),
                'FAILED' => __d('cake_settings_app', 'Failed'),
                'UNKNOWN' => __d('cake_settings_app', 'Unknown'),
            ],
            'stateData' => [],
            'usePost' => false,
            'pageHeader' => __d('cake_settings_app', 'Queue of tasks'),
            'headerMenuActions' => [
                [
                    'fas fa-trash-alt',
                    __d('cake_settings_app', 'Clear queue of tasks'),
                    ['controller' => 'settings', 'action' => 'clear', 'plugin' => 'cake_settings_app', 'prefix' => false],
                    [
                        'title' => __d('cake_settings_app', 'Clear queue of tasks'),
                        'action-type' => 'confirm-post',
                        'data-confirm-msg' => __d('cake_settings_app', 'Are you sure you wish to clear queue of tasks?'),
                    ]
                ]
            ],
            'uiLcid2' => 'en',
            'uiLcid3' => 'eng'
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testQueuePostInvalid method
     *
     * Method: POST
     * Group process: invalid data
     * @return void
     */
    public function testQueuePostInvalid()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $this->_generateMockedController();
        $opt = [
            'method' => 'POST',
            'return' => 'vars',
            'data' => [
                'FilterData' => [
                    [
                        'ExtendQueuedTask' => [
                            'id' => [2]
                        ]
                    ]
                ],
                'FilterGroup' => [
                    'action' => 'bad-action'
                ]
            ],
        ];
        $this->checkFlashMessage(__d('cake_settings_app', 'Selected tasks has been deleted.'), false, true);
        $this->checkFlashMessage(__d('cake_settings_app', 'Selected tasks could not be deleted. Please, try again.'), false, true);
    }

    /**
     * testQueuePostSuccessMsg method
     *
     * Method: POST
     * Group process: success
     * @return void
     */
    public function testQueuePostSuccessMsg()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $this->_generateMockedController();
        $opt = [
            'method' => 'POST',
            'data' => [
                'FilterData' => [
                    [
                        'ExtendQueuedTask' => [
                            'id' => [2]
                        ]
                    ]
                ],
                'FilterGroup' => [
                    'action' => 'group-data-del'
                ]
            ],
        ];
        $result = $this->testAction('/cake_settings_app/settings/queue', $opt);
        $this->checkFlashMessage(__d('cake_settings_app', 'Selected tasks has been deleted.'));
    }

    /**
     * testQueuePostSuccessVars method
     *
     * Method: POST
     * Group process: success
     * @return void
     */
    public function testQueuePostSuccess()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $this->_generateMockedController();
        $opt = [
            'method' => 'POST',
            'return' => 'vars',
            'data' => [
                'FilterData' => [
                    [
                        'ExtendQueuedTask' => [
                            'id' => [2]
                        ]
                    ]
                ],
                'FilterGroup' => [
                    'action' => 'group-data-del'
                ]
            ],
        ];
        $result = $this->testAction('/cake_settings_app/settings/queue', $opt);
        $expected = [
            'queue' => [
                [
                    'ExtendQueuedTask' => [
                        'id' => '1',
                        'jobtype' => 'Sync',
                        'created' => '2016-09-26 08:27:17',
                        'fetched' => '2016-09-26 08:28:01',
                        'progress' => '1.00',
                        'completed' => '2016-09-26 08:28:05',
                        'reference' => null,
                        'failed' => '0',
                        'failure_message' => null,
                        'status' => 'COMPLETED',
                    ]
                ]
            ],
            'groupActions' => [
                'group-data-del' => __d('cake_settings_app', 'Delete selected tasks')
            ],
            'taskStateList' => [
                'NOT_READY' => __d('cake_settings_app', 'Not ready'),
                'NOT_STARTED' => __d('cake_settings_app', 'Not started'),
                'IN_PROGRESS' => __d('cake_settings_app', 'In progress'),
                'COMPLETED' => __d('cake_settings_app', 'Completed'),
                'FAILED' => __d('cake_settings_app', 'Failed'),
                'UNKNOWN' => __d('cake_settings_app', 'Unknown'),
            ],
            'stateData' => [
                [
                    'stateName' => __d('cake_settings_app', 'Completed'),
                    'stateId' => 'COMPLETED',
                    'amount' => 1,
                    'stateUrl' => [
                        'controller' => 'settings',
                        'action' => 'queue',
                        'plugin' => 'cake_settings_app',
                        '?' => [
                            'data[FilterData][0][ExtendQueuedTask][status]' => 'COMPLETED',
                        ]
                    ],
                    'class' => 'progress-bar-success'
                ],
            ],
            'usePost' => true,
            'pageHeader' => __d('cake_settings_app', 'Queue of tasks'),
            'headerMenuActions' => [
                [
                    'fas fa-trash-alt',
                    __d('cake_settings_app', 'Clear queue of tasks'),
                    ['controller' => 'settings', 'action' => 'clear', 'plugin' => 'cake_settings_app', 'prefix' => false],
                    [
                        'title' => __d('cake_settings_app', 'Clear queue of tasks'),
                        'action-type' => 'confirm-post',
                        'data-confirm-msg' => __d('cake_settings_app', 'Are you sure you wish to clear queue of tasks?'),
                    ]
                ]
            ],
            'uiLcid2' => 'en',
            'uiLcid3' => 'eng'
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testDelete method
     *
     * Method: GET
     * @return void
     */
    public function testDeleteGet()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $this->_generateMockedController();
        $opt = [
            'method' => 'GET'
        ];
        $this->setExpectedException('MethodNotAllowedException');
        $this->testAction('/cake_settings_app/settings/delete', $opt);
    }

    /**
     * testDelete method
     *
     * Method: POST
     * ID: invalid
     * @return void
     */
    public function testDeleteInvalidIdPost()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $this->_generateMockedController();
        $opt = [
            'method' => 'POST'
        ];
        $data = http_build_query(
            [
                'jobtype' => 'Sync',
                'created' => '2016-09-26 00:00:00',
                'failed' => '0',
                'status' => 'NOT_STARTED'
            ]
        );
        $this->testAction('/cake_settings_app/settings/delete?' . $data, $opt);
        $this->checkFlashMessage(__d('cake_settings_app', 'The task could not be deleted. Please, try again.'));
    }

    /**
     * testDelete method
     *
     * Method: POST
     * Delete: success
     * @return void
     */
    public function testDeleteSuccessPost()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $this->_generateMockedController();
        $opt = [
            'method' => 'POST'
        ];
        $data = http_build_query(
            [
                'jobtype' => 'Sync',
                'created' => '2016-09-26 08:28:15',
                'failed' => '0',
                'status' => 'NOT_STARTED'
            ]
        );
        $this->testAction('/cake_settings_app/settings/delete?' . $data, $opt);
        $this->checkFlashMessage(__d('cake_settings_app', 'The task has been deleted.'));
    }

    /**
     * testClear method
     *
     * Method: GET
     * @return void
     */
    public function testClearGet()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $this->_generateMockedController();
        $opt = [
            'method' => 'GET'
        ];
        $this->setExpectedException('MethodNotAllowedException');
        $this->testAction('/cake_settings_app/settings/clear', $opt);
    }

    /**
     * testClear method
     *
     * Method: POST
     * Delete: success
     * @return void
     */
    public function testClearSuccessPost()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $this->_generateMockedController();
        $opt = [
            'method' => 'POST'
        ];
        $this->testAction('/cake_settings_app/settings/clear', $opt);
        $this->checkFlashMessage(__d('cake_settings_app', 'The task queue has been cleared.'));
    }

    /**
     * Generate mocked SettingsController.
     *
     * @param mixed $saveResult Result of call Setting::save().
     * @return bool Success
     */
    protected function _generateMockedController($saveResult = true)
    {
        $mocks = [
            'components' => [
                'Security',
                'Auth',
            ],
            'models' => [
                'CakeSettingsApp.Setting' => ['save'],
                'CakeSettingsApp.Ldap' => [
                    'getGroupList',
                    'getTopLevelContainerList'
                ],
            ],
        ];
        if (!$this->generateMockedController($mocks)) {
            return false;
        }

        $this->Controller->Setting->expects($this->any())
            ->method('save')
            ->will($this->returnValue($saveResult));
        $this->Controller->Ldap->expects($this->any())
            ->method('getGroupList')
            ->will($this->returnValue([
                'CN=Web.Admin,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com' => 'Web.Admin',
                'CN=Web.Extend,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com' => 'Web.Extend'
            ]));
        $this->Controller->Ldap->expects($this->any())
            ->method('getTopLevelContainerList')
            ->will($this->returnValue([
                'OU=Группы,DC=fabrikam,DC=com',
                'OU=Компьютеры,DC=fabrikam,DC=com'
            ]));

        return true;
    }
}
