<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('View', 'View');
App::uses('Helper', 'View');
App::uses('DeferredHelper', 'View/Helper');

/**
 * DeferredHelper Test Case
 */
class DeferredHelperTest extends AppCakeTestCase
{

    /**
     * Object of model `Deferred`
     *
     * @var object
     */
    protected $_modelDeferred = null;

    /**
     * Code of the current language of the user interface.
     *
     * @var string
     */
    protected $_uiLcid = null;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.deferred',
        'plugin.cake_ldap.department',
        'plugin.cake_ldap.employee',
        'plugin.cake_ldap.employee_ldap',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $View = new View();
        $this->_targetObject = new DeferredHelper($View);
        $this->_modelDeferred = ClassRegistry::init('Deferred');
        $this->_uiLcid = Configure::read('Config.language');
        Configure::write('Config.language', 'eng');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        if (!empty($this->_uiLcid)) {
            Configure::write('Config.language', $this->_uiLcid);
        }
        unset($this->_uiLcid);
        unset($this->_targetObject);
        unset($this->_modelDeferred);

        parent::tearDown();
    }

    /**
     * testGetDeferredInfoEmptyData method
     *
     * @return void
     */
    public function testGetDeferredInfoEmptyData()
    {
        $deferredSave = [];
        $fieldsLabel = $this->_modelDeferred->Employee->getListFieldsLabel([], false);
        $fieldsConfig = $this->_modelDeferred->Employee->getFieldsConfig();
        $result = $this->_targetObject->getDeferredInfo($deferredSave, $deferredSave, $fieldsLabel, $fieldsConfig);
        $expected = __d('view_extension', '&lt;None&gt;');
        $this->assertData($expected, $result);
    }

    /**
     * testGetDeferredInfoEmptyChangedData method
     *
     * @return void
     */
    public function testGetDeferredInfoEmptyChangedData()
    {
        $deferredSave = [
            'data' => [
                'changed' => [
                    'EmployeeEdit' => [
                        CAKE_LDAP_LDAP_DISTINGUISHED_NAME => '',
                    ],
                ],
                'current' => [
                    'EmployeeEdit' => [
                        CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Дементьева А.С.,OU=20-02,OU=ОИТ,OU=Пользователи,DC=fabrikam,DC=com',
                    ]
                ]
            ]
        ];
        $fieldsLabel = $this->_modelDeferred->Employee->getListFieldsLabel([], false);
        $fieldsConfig = $this->_modelDeferred->Employee->getFieldsConfig();
        $result = $this->_targetObject->getDeferredInfo($deferredSave['data']['changed']['EmployeeEdit'], $deferredSave['data']['current']['EmployeeEdit'], $fieldsLabel, $fieldsConfig);
        $expected = __d('view_extension', '&lt;None&gt;');
        $this->assertData($expected, $result);
    }

    /**
     * testGetDeferredInfoValidDataEmptyPhoto method
     *
     * @return void
     */
    public function testGetDeferredInfoValidDataEmptyPhoto()
    {
        $deferredSave = $this->_modelDeferred->get(2, false, null);
        $fieldsLabel = $this->_modelDeferred->Employee->getListFieldsLabel([], false);
        $fieldsConfig = $this->_modelDeferred->Employee->getFieldsConfig();
        $result = $this->_targetObject->getDeferredInfo($deferredSave['Deferred']['data']['changed']['EmployeeEdit'], $deferredSave['Deferred']['data']['current']['EmployeeEdit'], $fieldsLabel, $fieldsConfig);
        $expected = '<dl class="dl-horizontal list-compact"><dt>' . __d('app_ldap_field_name', 'Subdivision') . ':</dt><dd>' . __d('view_extension', '&lt;None&gt;') . ' &rarr; Группа №1</dd><dt>' . __d('app_ldap_field_name', 'Position') . ':</dt><dd>Зам. начальника отдела - главный специалист &rarr; Ведущий инженер</dd><dt>' . __d('app_ldap_field_name', 'Photo') . ':</dt><dd><img src="data:image/jpeg;base64,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" class="img-thumbnail img-responsive" style="max-width:200px;min-height:200px;max-height:200px;"> &rarr; <img src="data:image/jpeg;base64,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" class="img-thumbnail img-responsive" style="max-width:200px;min-height:200px;max-height:200px;"></dd></dl>';
        $this->assertData($expected, $result);
    }

    /**
     * testGetDeferredInfoValidDataChangeManager method
     *
     * @return void
     */
    public function testGetDeferredInfoValidDataChangeManager()
    {
        $deferredSave = $this->_modelDeferred->get(3, false, null);
        $fieldsLabel = $this->_modelDeferred->Employee->getListFieldsLabel([], false);
        $fieldsConfig = $this->_modelDeferred->Employee->getFieldsConfig();
        $result = $this->_targetObject->getDeferredInfo($deferredSave['Deferred']['data']['changed']['EmployeeEdit'], $deferredSave['Deferred']['data']['current']['EmployeeEdit'], $fieldsLabel, $fieldsConfig);
        $expected = '<dl class="dl-horizontal list-compact"><dt>' . __d('app_ldap_field_name', 'E-mail') . ':</dt><dd><a href="mailto:a.dementeva@fabrikam.com">a.dementeva@fabrikam.com</a> &rarr; <a href="mailto:a.s.dementeva@fabrikam.com">a.s.dementeva@fabrikam.com</a></dd><dt>' . __d('app_ldap_field_name', 'Office room') . ':</dt><dd>123 &rarr; 216</dd><dt>' . __d('app_ldap_field_name', 'Manager') . ':</dt><dd><a href="/employees/view/8" data-toggle="modal-popover" class="popup-link text-nowrap" target="_blank" data-popover-placement="auto top" data-modal-title="' . __d('view_extension', 'Detail information') . '">Голубев Е.В.</a> - Водитель &rarr; ' . __d('view_extension', '&lt;None&gt;') . '</dd></dl>';
        $this->assertData($expected, $result);
    }

    /**
     * testGetDeferredInfoValidDataChangeDepartmentAndTel method
     *
     * @return void
     */
    public function testGetDeferredInfoValidDataChangeDepartmentAndTel()
    {
        $deferredSave = $this->_modelDeferred->get(5, false, null);
        $fieldsLabel = $this->_modelDeferred->Employee->getListFieldsLabel([], false);
        $fieldsConfig = $this->_modelDeferred->Employee->getFieldsConfig();
        $result = $this->_targetObject->getDeferredInfo($deferredSave['Deferred']['data']['changed']['EmployeeEdit'], $deferredSave['Deferred']['data']['current']['EmployeeEdit'], $fieldsLabel, $fieldsConfig);
        $expected = '<dl class="dl-horizontal list-compact"><dt>' . __d('app_ldap_field_name', 'Landline telephone') . ':</dt><dd><abbr title="Minsk" data-toggle="tooltip">8 017 100-00-02</abbr> &rarr; <ul class="list-unstyled list-compact"><li><abbr title="Minsk" data-toggle="tooltip">8 017 100-00-02</abbr></li><li><abbr title="Minsk" data-toggle="tooltip">8 017 100-00-07</abbr></li></ul></dd><dt>' . __d('app_ldap_field_name', 'Personal mobile telephone') . ':</dt><dd>' . __d('view_extension', '&lt;None&gt;') . ' &rarr; <abbr title="Velcom" data-toggle="tooltip">8 029 100-00-08</abbr></dd><dt>' . __d('app_ldap_field_name', 'Department') . ':</dt><dd>ОС &rarr; ОРС</dd></dl>';
        $this->assertData($expected, $result);
    }
}
