<?php
App::uses('AppCakeTestCase', 'CakeSettingsApp.Test');
App::uses('SettingBase', 'CakeSettingsApp.Model');
App::uses('Folder', 'Utility');

/**
 * SettingBaseTest Test Case
 *
 */
class SettingBaseTest extends AppCakeTestCase
{

    /**
     * Path to marker file.
     *
     * @var string
     */

    protected $_markerFile = TMP . 'tests' . DS . 'test_settings_configured.txt';

    /**
     * Path to application configuration file.
     *
     * @var string
     */
    protected $_pathAppCfg = TMP . 'tests' . DS . 'Config' . DS;

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
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $oFolder = new Folder($this->_pathAppCfg, true);
        $oFolder->create($this->_pathAppCfg);
        if (file_exists($this->_markerFile)) {
            unlink($this->_markerFile);
        }

        $this->_targetObject = ClassRegistry::init('CakeSettingsApp.SettingBase');
        $this->_targetObject->pathAppCfg = $this->_pathAppCfg;
        $this->_targetObject->markerFile = $this->_markerFile;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        $Folder = new Folder($this->_pathAppCfg);
        $Folder->delete();
        if (file_exists($this->_markerFile)) {
            unlink($this->_markerFile);
        }

        parent::tearDown();
    }

    /**
     * testValidGroup method
     *
     * @return void
     */
    public function testValidGroup()
    {
        $params = [
            [
                ['AdminGroupMember' => null], // $data
            ],
            [
                ['AdminGroupMember' => 'CN=Web.Admin,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com'], // $data
            ],
            [
                ['AdminGroupMember' => 'CN=Web.Test,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com'], // $data
            ],
        ];
        $expected = [
            false,
            true,
            false,
        ];

        $this->runClassMethodGroup('validGroup', $params, $expected);
    }

    /**
     * testValidEmail method
     *
     * @return void
     */
    public function testValidEmail()
    {
        $params = [
            [
                ['EmailContact' => ['test@mail.com']], // $data
            ],
            [
                ['EmailContact' => 'test'], // $data
            ],
            [
                ['EmailContact' => 'test@mail.com'], // $data
            ],
            [
                ['EmailContact' => 'test@mail.com' . CAKE_SETTINGS_APP_SMTP_EMAIL_DELIM . 'new@mail.by'], // $data
            ]
        ];
        $expected = [
            false,
            false,
            true,
            true,
        ];

        $this->runClassMethodGroup('validEmail', $params, $expected);
    }

    /**
     * testValidHost method
     *
     * @return void
     */
    public function testValidHost()
    {
        $data = [
            [
            ],
            [
                $this->_targetObject->alias => [
                    'EmailSmtphost' => '127.0.0.1',
                    'EmailSmtpport' => '3306',
                ]
            ],
        ];
        $expected = [
            true,
            true,
        ];

        foreach ($data as $i => $dataItem) {
            $this->_targetObject->data = $dataItem;
            $result = $this->_targetObject->validHost();
            $this->assertEquals($expected[$i], $result);
        }
    }

    /**
     * testValidPasswords method
     *
     * @return void
     */
    public function testValidPasswords()
    {
        $result = $this->_targetObject->validPasswords(['EmailSmtppassword' => '']);
        $this->assertFalse($result);

        $this->_targetObject->set(['EmailSmtppassword_confirm' => '']);
        $result = $this->_targetObject->validPasswords(['EmailSmtppassword' => 'test']);
        $this->assertFalse($result);

        $this->_targetObject->set(['EmailSmtppassword_confirm' => 'ok']);
        $result = $this->_targetObject->validPasswords(['EmailSmtppassword' => 'test']);
        $this->assertFalse($result);

        $this->_targetObject->set(['EmailSmtppassword_confirm' => 'tEst']);
        $result = $this->_targetObject->validPasswords(['EmailSmtppassword' => 'test']);
        $this->assertFalse($result);

        $this->_targetObject->set(['EmailSmtppassword_confirm' => 'test']);
        $result = $this->_targetObject->validPasswords(['EmailSmtppassword' => 'test']);
        $this->assertTrue($result);
    }

    /**
     * testGetPathAppCfg method
     *
     * @return void
     */
    public function testGetPathAppCfg()
    {
        $result = $this->_targetObject->getPathAppCfg();
        $expected = $this->_pathAppCfg;
        $this->assertData($expected, $result);
    }

    /**
     * testGetPathMarkerFile method
     *
     * @return void
     */
    public function testGetPathMarkerFile()
    {
        $result = $this->_targetObject->getPathMarkerFile();
        $expected = $this->_markerFile;
        $this->assertData($expected, $result);
    }

    /**
     * testGetDefaultConfigShort method
     *
     * @return void
     */
    public function testGetDefaultConfigShort()
    {
        Configure::write('CakeSettingsApp.configAcLimit', false);
        Configure::write('CakeSettingsApp.configADsearch', false);
        Configure::write('CakeSettingsApp.configSMTP', false);
        Configure::write('CakeSettingsApp.configExtAuth', false);
        Configure::delete('CakeSettingsApp.schema');
        $result = $this->_targetObject->getDefaultConfig();
        $expected = [
            $this->_targetObject->alias => [
                'EmailContact' => '',
                'EmailSubject' => '',
                'ManagerGroupMember' => '',
                'AdminGroupMember' => '',
                'Language' => 'eng',
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testGetDefaultConfigFull method
     *
     * @return void
     */
    public function testGetDefaultConfigFull()
    {
        $result = $this->_targetObject->getDefaultConfig();
        $expected = [
            $this->_targetObject->alias => [
                'EmailContact' => '',
                'EmailSubject' => '',
                'Company' => '',
                'SearchBase' => '',
                'AutocompleteLimit' => false,
                'ExternalAuth' => '',
                'EmailSmtphost' => '',
                'EmailSmtpport' => '',
                'EmailSmtpuser' => '',
                'EmailSmtppassword' => '',
                'EmailNotifyUser' => '',
                'ManagerGroupMember' => '',
                'AdminGroupMember' => '',
                'Language' => 'eng',
                'CountryCode' => 'BY',
                'ReadOnlyFields' => ''
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testGetConfig method
     *
     * @return void
     */
    public function testGetConfig()
    {
        $params = [
            [
                null, // $key
            ],
            [
                'Language', // $key
            ],
            [
                'Company', // $key
            ],
            [
                'Test', // $key
            ],
            [
                'ReadOnlyFields', // $key
            ],
        ];
        $expected = [
            [
                $this->_targetObject->alias => [
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
                    'EmailSmtpuser' => '',
                    'EmailSmtppassword' => '',
                    'EmailNotifyUser' => true,
                    'CountryCode' => 'US',
                    'ReadOnlyFields' => [
                        'objectguid'
                    ],
                    'Language' => 'eng'
                ],
            ],
            'eng',
            'Test ORG',
            null,
            ['objectguid'],
        ];
        $this->runClassMethodGroup('getConfig', $params, $expected);
    }

    /**
     * testGetAuthGroupsList method
     *
     * @return void
     */
    public function testGetAuthGroupsList()
    {
        $result = $this->_targetObject->getAuthGroupsList();
        $expected = [
            CAKE_SETTINGS_APP_TEST_USER_ROLE_EXTENDED => 'ManagerGroupMember',
            CAKE_SETTINGS_APP_TEST_USER_ROLE_ADMIN => 'AdminGroupMember'
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testGetAuthPrefixesList method
     *
     * @return void
     */
    public function testGetAuthPrefixesList()
    {
        $result = $this->_targetObject->getAuthPrefixesList();
        $expected = [
            CAKE_SETTINGS_APP_TEST_USER_ROLE_EXTENDED => 'manager',
            CAKE_SETTINGS_APP_TEST_USER_ROLE_ADMIN => 'admin'
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testGetAuthRoleName method
     *
     * @return void
     */
    public function testGetAuthRoleName()
    {
        $params = [
            [
                null, // $role
            ],
            [
                'a', // $role
            ],
            [
                '2', // $role
            ],
            [
                5, // $role
            ],
        ];
        $expected = [
            '',
            '',
            'manager',
            'administrator',
        ];

        $this->runClassMethodGroup('getAuthRoleName', $params, $expected);
    }

    /**
     * testGetUiLangsList method
     *
     * @return void
     */
    public function testGetUiLangsList()
    {
        $result = $this->_targetObject->getUiLangsList();
        $expected = [
            'US' => 'English',
            'RU' => 'Русский язык'
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testGetCountryCodeByLanguage method
     *
     * @return void
     */
    public function testGetCountryCodeByLanguage()
    {
        $params = [
            [
                null, // $lang
            ],
            [
                'bad', // $lang
            ],
            [
                'rus', // $lang
            ],
            [
                'eng', // $lang
            ],
        ];
        $expected = [
            '',
            '',
            'RU',
            'US',
        ];

        $this->runClassMethodGroup('getCountryCodeByLanguage', $params, $expected);
    }

    /**
     * testGetLanguageByCountryCode method
     *
     * @return void
     */
    public function testGetLanguageByCountryCode()
    {
        $params = [
            [
                null, // $country
            ],
            [
                'bad', // $country
            ],
            [
                'RU', // $country
            ],
            [
                'US', // $country
            ],
        ];
        $expected = [
            '',
            '',
            'rus',
            'eng',
        ];

        $this->runClassMethodGroup('getLanguageByCountryCode', $params, $expected);
    }

    /**
     * testGetFullSchemaShort method
     *
     * @return void
     */
    public function testGetFullSchemaShort()
    {
        Configure::write('CakeSettingsApp.configAcLimit', false);
        Configure::write('CakeSettingsApp.configADsearch', false);
        Configure::write('CakeSettingsApp.configSMTP', false);
        Configure::write('CakeSettingsApp.configExtAuth', false);
        Configure::delete('CakeSettingsApp.schema');
        $result = $this->_targetObject->getFullSchema();
        $expected = [
            'EmailContact' => [
                'type' => 'string',
                'default' => ''
            ],
            'EmailSubject' => [
                'type' => 'string',
                'default' => ''
            ],
            'Language' => [
                'type' => 'string',
                'default' => 'eng'
            ],
            'ManagerGroupMember' => [
                'type' => 'string',
                'default' => ''
            ],
            'AdminGroupMember' => [
                'type' => 'string',
                'default' => ''
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetFullSchema method
     *
     * @return void
     */
    public function testGetFullSchema()
    {
        $result = $this->_targetObject->getFullSchema();
        $expected = [
            'EmailContact' => [
                'type' => 'string',
                'default' => ''
            ],
            'EmailSubject' => [
                'type' => 'string',
                'default' => ''
            ],
            'Company' => [
                'type' => 'string',
                'default' => ''
            ],
            'SearchBase' => [
                'type' => 'string',
                'default' => ''
            ],
            'AutocompleteLimit' => [
                'type' => 'integer',
                'default' => false
            ],
            'ExternalAuth' => [
                'type' => 'boolean',
                'default' => ''
            ],
            'EmailSmtphost' => [
                'type' => 'string',
                'default' => ''
            ],
            'EmailSmtpport' => [
                'type' => 'integer',
                'default' => ''
            ],
            'EmailSmtpuser' => [
                'type' => 'string',
                'default' => ''
            ],
            'EmailSmtppassword' => [
                'type' => 'string',
                'default' => ''
            ],
            'EmailNotifyUser' => [
                'type' => 'boolean',
                'default' => ''
            ],
            'Language' => [
                'type' => 'string',
                'default' => 'eng'
            ],
            'ManagerGroupMember' => [
                'type' => 'string',
                'default' => ''
            ],
            'AdminGroupMember' => [
                'type' => 'string',
                'default' => ''
            ],
            'CountryCode' => [
                'type' => 'string',
                'default' => 'BY'
            ],
            'ReadOnlyFields' => [
                'type' => 'string',
                'default' => ''
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testSaveEmptyData method
     *
     * @return void
     */
    public function testSaveEmptyData()
    {
        $result = $this->_targetObject->save();
        $this->assertFalse($result);
    }

    /**
     * testSaveInvalidData method
     *
     * @return void
     */
    public function testSaveInvalidData()
    {
        $result = $this->_targetObject->save('test');
        $this->assertFalse($result);

        $result = $this->_targetObject->save(['bad_data' => 'some value']);
        $this->assertFalse($result);
    }

    /**
     * testSaveInvalidEmail method
     *
     * @return void
     */
    public function testSaveInvalidEmail()
    {
        $data = $this->_targetObject->getConfig();
        $data[$this->_targetObject->alias]['EmailContact'] = 'bad_email';
        $result = $this->_targetObject->save($data);
        $this->assertFalse($result);
    }

    /**
     * testSaveInvalidAutocompleteLimit method
     *
     * @return void
     */
    public function testSaveInvalidAutocompleteLimit()
    {
        $data = $this->_targetObject->getConfig();
        $data[$this->_targetObject->alias]['AutocompleteLimit'] = 1;
        $result = $this->_targetObject->save($data);
        $this->assertFalse($result);
    }

    /**
     * testSaveInvalidEmailSmtppassword method
     *
     * @return void
     */
    public function testSaveInvalidEmailSmtppassword()
    {
        $data = $this->_targetObject->getConfig();
        $data[$this->_targetObject->alias]['EmailSmtppassword'] = 'some pass';
        $data[$this->_targetObject->alias]['EmailSmtppassword_confirm'] = 'bad confirm';
        $result = $this->_targetObject->save($data);
        $this->assertFalse($result);
    }

    /**
     * testSaveSucess method
     *
     * @return void
     */
    public function testSaveSucess()
    {
        $data = $this->_targetObject->getConfig();
        $data[$this->_targetObject->alias]['EmailNotifyUser'] = false;
        $data[$this->_targetObject->alias]['EmailSmtphost'] = '';
        $data[$this->_targetObject->alias]['EmailSmtpport'] = '25';
        $data[$this->_targetObject->alias]['EmailSmtppassword_confirm'] = $data[$this->_targetObject->alias]['EmailSmtppassword'];
        $data[$this->_targetObject->alias]['CountryCode'] = 'RU';
        $data[$this->_targetObject->alias]['ReadOnlyFields'] = [
            'objectguid',
            'dn'
        ];
        $result = $this->_targetObject->save($data);
        $this->assertData($data, $result);
        if (!$result) {
            return;
        }

        $result = $this->_targetObject->getConfig();
        unset($data[$this->_targetObject->alias]['EmailSmtppassword_confirm']);
        $this->assertEquals($data, $result);

        $result = Configure::read('ExtConfig.AC');
        $expected = 5;
        $this->assertEquals($expected, $result);
    }

    /**
     * testIsAuthGroupConfigured method
     *
     * @return void
     */
    public function testIsAuthGroupConfigured()
    {
        $result = $this->_targetObject->isAuthGroupConfigured();
        $this->assertTrue($result);

        if ($result) {
            $this->assertTrue(file_exists($this->_targetObject->markerFile));
        }
    }

    /**
     * testProcessGroupAction method
     *
     * @return void
     */
    public function testProcessGroupAction()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $params = [
            [
                null, // $groupAction
                null, // $conditions
            ],
            [
                'bad-action', // $groupAction
                [
                    'ExtendQueuedTask.id' => [1]
                ], // $conditions
            ],
            [
                'group-data-del', // $groupAction
                [
                    'ExtendQueuedTask.id' => [2]
                ], // $conditions
            ],
        ];
        $expected = [
            null,
            false,
            true,
        ];
        $this->runClassMethodGroup('processGroupAction', $params, $expected);
    }

    /**
     * testGetListTaskState method
     *
     * @return void
     */
    public function testGetListTaskState()
    {
        $result = $this->_targetObject->getListTaskState();
        $expected = [
            'NOT_READY' => __d('cake_settings_app', 'Not ready'),
            'NOT_STARTED' => __d('cake_settings_app', 'Not started'),
            'IN_PROGRESS' => __d('cake_settings_app', 'In progress'),
            'COMPLETED' => __d('cake_settings_app', 'Completed'),
            'FAILED' => __d('cake_settings_app', 'Failed'),
            'UNKNOWN' => __d('cake_settings_app', 'Unknown'),
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetListBarStateClass method
     *
     * @return void
     */
    public function testGetListBarStateClass()
    {
        $result = $this->_targetObject->getListBarStateClass();
        $expected = [
            'NOT_READY' => 'progress-bar-warning',
            'NOT_STARTED' => 'progress-bar-success progress-bar-striped',
            'IN_PROGRESS' => 'progress-bar-info',
            'COMPLETED' => 'progress-bar-success',
            'FAILED' => 'progress-bar-danger',
            'UNKNOWN' => 'progress-bar-danger progress-bar-striped',
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testGetBarStateInfo method
     *
     * @return void
     */
    public function testGetBarStateInfo()
    {
        $this->skipIf(!CakePlugin::loaded('Queue'), "Plugin 'Queue' is not loaded");

        $result = $this->_targetObject->getBarStateInfo();
        $expected = [
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
        ];
        $this->assertData($expected, $result);
    }
}
