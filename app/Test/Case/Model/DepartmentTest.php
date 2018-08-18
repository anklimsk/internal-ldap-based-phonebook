<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('Department', 'Model');

/**
 * Department Test Case
 */
class DepartmentTest extends AppCakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'core.cake_session',
        'app.deferred',
        'app.department_extension',
        'plugin.cake_ldap.employee',
        'plugin.cake_ldap.employee_ldap',
        'plugin.cake_ldap.department',
        'plugin.cake_ldap.othertelephone',
        'plugin.queue.queued_task',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_targetObject = ClassRegistry::init('Department');
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
     * testAfterSaveInvalidData method
     *
     * @return void
     */
    public function testAfterSaveInvalidData()
    {
        $data = [
            $this->_targetObject->alias => [
                'value' => ''
            ]
        ];
        $this->_targetObject->create();
        $result = $this->_targetObject->save($data);
        $this->assertFalse($result);
    }

    /**
     * testAfterSaveValidData method
     *
     * @return void
     */
    public function testAfterSaveValidData()
    {
        $data = [
            $this->_targetObject->alias => [
                'value' => 'Some department',
                'block' => false,
            ]
        ];
        $this->_targetObject->create();
        $result = $this->_targetObject->save($data);
        $expected = [
            $this->_targetObject->alias => [
                'block' => false,
                'value' => 'Some department',
                'id' => '8'
            ]
        ];
        $this->assertData($expected, $result);

        $this->_targetObject->DepartmentExtension->recursive = -1;
        $result = $this->_targetObject->DepartmentExtension->read();
        $expected = [
            'DepartmentExtension' => [
                'id' => '8',
                'department_id' => '8',
                'parent_id' => null,
                'lft' => '15',
                'rght' => '16',
                'name' => 'Some department',
            ]
        ];
        $this->assertData($expected, $result);

        $result = $this->_targetObject->DepartmentExtension->verify();
        $this->assertTrue($result);

        $data = [
            $this->_targetObject->alias => [
                'id' => '8',
                'value' => 'New name of some department'
            ]
        ];
        $result = $this->_targetObject->save($data);
        $expected = [
            $this->_targetObject->alias => [
                'id' => '8',
                'value' => 'New name of some department',
            ]
        ];
        $this->assertData($expected, $result);

        $this->_targetObject->DepartmentExtension->recursive = -1;
        $result = $this->_targetObject->DepartmentExtension->read();
        $expected = [
            'DepartmentExtension' => [
                'id' => '8',
                'department_id' => '8',
                'parent_id' => null,
                'lft' => '15',
                'rght' => '16',
                'name' => 'Some department',
            ]
        ];
        $this->assertData($expected, $result);

        $result = $this->_targetObject->DepartmentExtension->verify();
        $this->assertTrue($result);
    }

    /**
     * testBeforeDeleteNonBlocked method
     *
     * @return void
     */
    public function testBeforeDeleteNonBlocked()
    {
        $result = $this->_targetObject->delete(2);
        $this->assertFalse($result);
    }

    /**
     * testBeforeDeleteBlocked method
     *
     * @return void
     */
    public function testBeforeDeleteBlocked()
    {
        $result = $this->_targetObject->delete(7);
        $this->assertTrue($result);
    }

    /**
     * testGet method
     *
     * @return void
     */
    public function testGet()
    {
        $params = [
            [
                null, // $id
            ], // Params for step 1
            [
                100, // $id
            ], // Params for step 2
            [
                2, // $id
            ], // Params for step 3
        ];
        $expected = [
            false, // Result of step 1
            [], // Result of step 2
            [
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
            ], // Result of step 3
        ];
        $this->runClassMethodGroup('get', $params, $expected);
    }

    /**
     * testGetListDepartmentsWithExtension method
     *
     * @return void
     */
    public function testGetListDepartmentsWithExtension()
    {
        $id = 2;
        $this->_targetObject->id = $id;
        $department = $this->_targetObject->field('value');
        $this->_targetObject->DepartmentExtension->id = $id;
        $result = $this->_targetObject->DepartmentExtension->saveField('name', $department);
        $expected = [
            'DepartmentExtension' => [
                'id' => $id,
                'name' => $department,
            ]
        ];
        $this->assertData($expected, $result);

        $params = [
            [
                null, // $limit
            ], // Params for step 1
            [
                3, // $limit
            ], // Params for step 2
        ];
        $expected = [
            [
                'АТО' => 'Автотранспортный отдел (АТО)',
                'ОС' => 'ОС',
                'ОИТ' => 'Отдел информационных технологий (ОИТ)',
                'ОРС' => 'Отдел распределительных сетей (ОРС)',
                'Охрана труда' => 'Охрана Труда (Охрана труда)',
                'СО' => 'Строительный отдел (СО)',
                'УИЗ' => 'Управление инженерных изысканий (УИЗ)',
            ], // Result of step 1
            [
                'АТО' => 'Автотранспортный отдел (АТО)',
                'ОС' => 'ОС',
                'ОИТ' => 'Отдел информационных технологий (ОИТ)',
            ], // Result of step 2
        ];
        $this->runClassMethodGroup('getListDepartmentsWithExtension', $params, $expected);
    }

    /**
     * testPutRenameDepartmentTaskEmptyOldDepartment method
     *
     * @return void
     */
    public function testPutRenameDepartmentTaskEmptyOldDepartment()
    {
        $result = $this->_targetObject->putRenameDepartmentTask('', 'ОС', USER_ROLE_USER, 2, false);
        $this->assertFalse($result);
    }

    /**
     * testPutRenameDepartmentTaskEmptyNewDepartment method
     *
     * @return void
     */
    public function testPutRenameDepartmentTaskEmptyNewDepartment()
    {
        $result = $this->_targetObject->putRenameDepartmentTask('ОС', '', USER_ROLE_USER, 2, false);
        $this->assertFalse($result);
    }

    /**
     * testPutRenameDepartmentTaskExistsNewDepartment method
     *
     * @return void
     */
    public function testPutRenameDepartmentTaskExistsNewDepartment()
    {
        $result = $this->_targetObject->putRenameDepartmentTask('ОС', 'ОИТ', USER_ROLE_USER, 2, false);
        $this->assertFalse($result);
    }

    /**
     * testPutRenameDepartmentTaskValidParam method
     *
     * @return void
     */
    public function testPutRenameDepartmentTaskValidParam()
    {
        $result = $this->_targetObject->putRenameDepartmentTask('ОС', 'Отдел связи', USER_ROLE_USER, 2, true);
        $this->assertTrue(is_array($result));
        if (isset($result['ExtendQueuedTask']['created'])) {
            unset($result['ExtendQueuedTask']['created']);
        }
        $expected = [
            'ExtendQueuedTask' => [
                'failed' => '0',
                'jobtype' => 'RenameDepartment',
                'data' => serialize([
                        'oldName' => 'ОС',
                        'newName' => 'Отдел связи',
                        'userRole' => USER_ROLE_USER,
                        'userId' => 2,
                        'useLdap' => true,
                    ]),
                'group' => 'change',
                'reference' => null,
                'id' => '1',
            ]
        ];
        $this->assertData($expected, $result);
    }

    /**
     * testPutRenameDepartmentTaskEmptyOldDepartment method
     *
     * @return void
     */
    public function testRenameDepartmentEmptyOldDepartment()
    {
        $result = $this->_targetObject->renameDepartment('', 'ОС', USER_ROLE_USER, 2, false);
        $this->assertFalse($result);
    }

    /**
     * testRenameDepartmentEmptyNewDepartment method
     *
     * @return void
     */
    public function testRenameDepartmentEmptyNewDepartment()
    {
        $result = $this->_targetObject->renameDepartment('ОС', '', USER_ROLE_USER, 2, false);
        $this->assertFalse($result);
    }

    /**
     * testRenameDepartmentExistsNewDepartment method
     *
     * @return void
     */
    public function testRenameDepartmentExistsNewDepartment()
    {
        $result = $this->_targetObject->renameDepartment('ОС', 'ОИТ', USER_ROLE_USER, 2, false);
        $this->assertFalse($result);
    }

    /**
     * testRenameDepartmentValidParamUseDb method
     *
     * @return void
     */
    public function testRenameDepartmentValidParamUseDb()
    {
        $result = $this->_targetObject->renameDepartment('ОС', 'Отдел связи', USER_ROLE_USER, 2, false);
        $this->assertTrue($result);
    }

    /**
     * testRenameDepartmentValidParamUseLdap method
     *
     * @return void
     */
    public function testRenameDepartmentValidParamUseLdap()
    {
        $result = $this->_targetObject->renameDepartment('Охрана Труда', 'ОТ', USER_ROLE_USER, 2, true);
        $this->assertTrue($result);
    }
}
