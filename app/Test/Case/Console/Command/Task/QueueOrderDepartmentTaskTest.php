<?php
/**
 * QueueOrderDepartmentTask Test Case
 *
 */
App::uses('AppCakeTestCase', 'Test');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('Hash', 'Utility');
App::uses('QueueOrderDepartmentTask', 'Console/Command/Task');

/**
 * QueueOrderDepartmentTaskTest class
 *
 */
class QueueOrderDepartmentTaskTest extends AppCakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.department_extension',
        'plugin.cake_ldap.department',
        'plugin.queue.queued_task'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);

        $this->_targetObject = $this->getMock(
            'QueueOrderDepartmentTask',
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
     * testRunTreeBroken
     *
     * @return void
     */
    public function testRunTreeBroken()
    {
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);
        $this->_targetObject = $this->getMock(
            'QueueOrderDepartmentTask',
            ['in', '_stop'],
            [$out, $out, $in]
        );

        $this->_targetObject->DepartmentExtension->id = 4;
        $result = (bool)$this->_targetObject->DepartmentExtension->saveField('rght', null);
        $this->assertTrue($result);

        $this->_targetObject->initialize();
        $this->_targetObject->QueuedTask->initConfig();
        $this->_targetObject->QueuedTask->createJob('OrderDepartment', null, null, 'order');
        $capabilities = [
            'OrderDepartment' => [
                'name' => 'OrderDepartment',
                'timeout' => REORDER_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT,
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
        $expected = '0';
        $this->assertData($expected, $progress);

        $failureMessage = Hash::get($taskInfo, 'QueuedTask.failure_message');
        $expected = __('Tree of departments is broken. Perform a restore.');
        $this->assertData($expected, $failureMessage);
    }

    /**
     * testRunInternalDeferredSaveLengthQueue
     *
     * @return void
     */
    public function testRunInternalDeferredSaveLengthQueue()
    {
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);
        $this->_targetObject = $this->getMock(
            'QueueOrderDepartmentTask',
            ['in', 'out', '_stop'],
            [$out, $out, $in]
        );
        $this->_targetObject->initialize();
        $this->_targetObject->QueuedTask->initConfig();
        $this->_targetObject->QueuedTask->createJob('OrderDepartment', null, null, 'order');
        $this->_targetObject->QueuedTask->createJob('OrderDepartment', null, null, 'order');
        $capabilities = [
            'OrderDepartment' => [
                'name' => 'OrderDepartment',
                'timeout' => REORDER_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT,
                'retries' => 2
            ]
        ];
        $jobInfo = $this->_targetObject->QueuedTask->requestJob($capabilities);
        $id = $jobInfo['id'];
        $data = unserialize($jobInfo['data']);
        $this->_targetObject->expects($this->at(4))->method('out')->with(__('Found order task in queue: %d. Skipped.', 1));
        $this->_targetObject->run($data, $id);
    }

    /**
     * testRunSuccess
     *
     * @return void
     */
    public function testRunSuccess()
    {
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);
        $this->_targetObject = $this->getMock(
            'QueueOrderDepartmentTask',
            ['in', '_stop'],
            [$out, $out, $in]
        );
        $this->_targetObject->initialize();
        $this->_targetObject->QueuedTask->initConfig();
        $this->_targetObject->QueuedTask->createJob('OrderDepartment', null, null, 'order');
        $capabilities = [
            'OrderDepartment' => [
                'name' => 'OrderDepartment',
                'timeout' => REORDER_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT,
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

        $result = $this->_targetObject->DepartmentExtension->verify();
        $this->assertTrue($result);

        $result = $this->_targetObject->DepartmentExtension->generateTreeList();
        $expected = [
            5 => 'Автотранспортный отдел',
            3 => 'Отдел информационных технологий',
            4 => 'Отдел распределительных сетей',
            2 => 'Отдел связи',
            6 => 'Охрана Труда',
            7 => 'Строительный отдел',
            1 => 'Управление инженерных изысканий',
        ];
        $this->assertData($expected, $result);
    }
}
