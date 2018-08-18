<?php
/**
 * QueueRenameDepartmentTask Test Case
 *
 */
App::uses('AppCakeTestCase', 'Test');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('Hash', 'Utility');
App::uses('QueueRenameDepartmentTask', 'Console/Command/Task');

/**
 * QueueRenameDepartmentTaskTest class
 *
 */
class QueueRenameDepartmentTaskTest extends AppCakeTestCase
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
        $this->setDefaultUserInfo($this->userInfo);
        parent::setUp();
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);

        $this->_targetObject = $this->getMock(
            'QueueRenameDepartmentTask',
            ['in', 'out', 'err', 'hr', '_stop'],
            [$out, $out, $in]
        );
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * testRunExistsNewDepartment
     *
     * @return void
     */
    public function testRunExistsNewDepartment()
    {
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);

        $oldName = 'ОС';
        $newName = 'ОИТ';
        $userRole = USER_ROLE_USER;
        $userId = 2;
        $taskParam = compact('oldName', 'newName', 'userRole', 'userId');
        $this->_targetObject->initialize();
        $this->_targetObject->QueuedTask->initConfig();
        $this->_targetObject->QueuedTask->createJob('RenameDepartment', $taskParam, null, 'change');
        $capabilities = [
            'RenameDepartment' => [
                'name' => 'RenameDepartment',
                'timeout' => TASK_RENAME_DEPARTMENT_TIME_LIMIT,
                'retries' => 2
            ]
        ];
        $jobInfo = $this->_targetObject->QueuedTask->requestJob($capabilities);
        $id = $jobInfo['id'];
        $data = unserialize($jobInfo['data']);
        $this->_targetObject->run($data, $id);
        $taskInfo = $this->_targetObject->QueuedTask->read(null, $id);
        $this->assertTrue(is_array($taskInfo));

        $progress = Hash::get($taskInfo, 'QueuedTask.progress');
        $expected = '1';
        $this->assertData($expected, $progress);

        $failureMessage = Hash::get($taskInfo, 'QueuedTask.failure_message');
        $expected = __('Error on renaming department from "%s" to "%s"', $oldName, $newName);
        $this->assertData($expected, $failureMessage);
    }

    /**
     * testRunValidParam
     *
     * @return void
     */
    public function testRunValidParam()
    {
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);

        $oldName = 'ОС';
        $newName = 'Отдел связи';
        $userRole = USER_ROLE_USER;
        $userId = 2;
        $taskParam = compact('oldName', 'newName', 'userRole', 'userId');
        $this->_targetObject->initialize();
        $this->_targetObject->QueuedTask->initConfig();
        $this->_targetObject->QueuedTask->createJob('RenameDepartment', $taskParam, null, 'change');
        $capabilities = [
            'RenameDepartment' => [
                'name' => 'RenameDepartment',
                'timeout' => TASK_RENAME_DEPARTMENT_TIME_LIMIT,
                'retries' => 2
            ]
        ];
        $jobInfo = $this->_targetObject->QueuedTask->requestJob($capabilities);
        $id = $jobInfo['id'];
        $data = unserialize($jobInfo['data']);
        $this->_targetObject->run($data, $id);
        $taskInfo = $this->_targetObject->QueuedTask->read(null, $id);
        $this->assertTrue(is_array($taskInfo));

        $progress = Hash::get($taskInfo, 'QueuedTask.progress');
        $expected = '1';
        $this->assertData($expected, $progress);

        $failureMessage = Hash::get($taskInfo, 'QueuedTask.failure_message');
        $expected = null;
        $this->assertData($expected, $failureMessage);

        $modelDeferred = ClassRegistry::init('Deferred');
        $modelDeferred->recursive = 0;
        $result = $modelDeferred->find('all', ['conditions' => ['Employee.department_id' => 2]]);
        $this->assertFalse(empty($result));
        $this->assertTrue(is_array($result));

        $departments = Hash::extract($result, '{n}.Deferred.data.changed.EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT);
        $expected = [
            'Отдел связи',
            'Отдел связи',
        ];
        $this->assertData($expected, $departments);
    }
}
