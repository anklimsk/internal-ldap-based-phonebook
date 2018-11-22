<?php
/**
 * QueueOrderEmployeeTask Test Case
 *
 */
App::uses('AppCakeTestCase', 'Test');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('Hash', 'Utility');
App::uses('QueueDeferredSaveTask', 'Console/Command/Task');

/**
 * QueueDeferredTaskTest class
 *
 */
class QueueDeferredSaveTaskTest extends AppCakeTestCase {

/**
 * Object of model `EmployeeEdit`
 *
 * @var object
 */
	protected $_modelEmployeeEdit = null;

/**
 * Object of model `Log`
 *
 * @var object
 */
	protected $_modelLog = null;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		'core.cake_session',
		'app.deferred',
		'app.log',
		'plugin.cake_ldap.employee',
		'plugin.cake_ldap.employee_ldap',
		'plugin.cake_ldap.department',
		'plugin.cake_ldap.othertelephone',
		'plugin.cake_ldap.othermobile',
		'plugin.queue.queued_task',
	];

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		$this->setDefaultUserInfo($this->userInfo);
		parent::setUp();
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);

		$this->_targetObject = $this->getMock(
			'QueueDeferredSaveTask',
			['in', 'out', 'err', 'hr', '_stop'],
			[$out, $out, $in]
		);
		$this->_modelEmployeeEdit = ClassRegistry::init('EmployeeEdit');
		$this->_modelLog = ClassRegistry::init('Log');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->_modelEmployeeEdit);

		parent::tearDown();
	}

/**
 * testRunInternalDeferredSaveLengthQueue
 *
 * @return void
 */
	public function testRunInternalDeferredSaveLengthQueue() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'QueueDeferredSaveTask',
			['in', 'out', '_stop'],
			[$out, $out, $in]
		);
		$taskParam = [
			'conditions' => ['Deferred.id >' => 0],
			'approve' => true,
			'userId' => 1,
			'internal' => true,
		];
		$this->_targetObject->initialize();
		$this->_targetObject->QueuedTask->initConfig();
		$this->_targetObject->QueuedTask->createJob('DeferredSave', $taskParam, null, 'deferred');
		$this->_targetObject->QueuedTask->createJob('DeferredSave', $taskParam, null, 'deferred');
		$capabilities = [
			'DeferredSave' => [
				'name' => 'DeferredSave',
				'timeout' => DEFERRED_SAVE_GROUP_PROCESS_TIME_LIMIT,
				'retries' => 2
			]
		];
		$jobInfo = $this->_targetObject->QueuedTask->requestJob($capabilities);
		$id = $jobInfo['id'];
		$data = unserialize($jobInfo['data']);
		$this->_targetObject->expects($this->at(4))->method('out')->with(__('Found processing internal deferred saves task in queue: %d. Skipped.', 1));
		$this->_targetObject->run($data, $id);
	}

/**
 * testRunInternalDeferredSave
 *
 * @return void
 */
	public function testRunInternalDeferredSave() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'QueueDeferredSaveTask',
			['in', '_stop'],
			[$out, $out, $in]
		);
		$recordId = 1;
		$result = $this->_prepareDeferredSave($recordId);
		$this->assertTrue($result);

		$taskParam = [
			'conditions' => ['Deferred.id' => $recordId],
			'approve' => true,
			'userId' => 7,
			'internal' => true,
		];
		$this->_targetObject->initialize();
		$this->_targetObject->QueuedTask->initConfig();
		$this->_targetObject->QueuedTask->createJob('DeferredSave', $taskParam, null, 'deferred');
		$capabilities = [
			'DeferredSave' => [
				'name' => 'DeferredSave',
				'timeout' => DEFERRED_SAVE_GROUP_PROCESS_TIME_LIMIT,
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

		$findOpt = ['conditions' => ['Deferred.internal' => true]];
		$result = $this->_targetObject->Deferred->find('count', $findOpt);
		$expected = 0;
		$this->assertData($expected, $result);

		$findOpt = [
			'conditions' => ['EmploeyeeEdit.id' => 1],
			'fields' => [
				CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
				CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO,
			]
		];
		$result = $this->_modelEmployeeEdit->find('first', $findOpt);
		$expected = [
			'EmployeeEdit' => [
				CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Геолог',
				CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
				CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com',
			]
		];
		$this->assertData($expected, $result);

		$findOpt = ['conditions' => ['Log.id' => 5]];
		$result = $this->_modelLog->find('first', $findOpt);
		$this->assertTrue(isset($result['Log']['created']));
		unset($result['Log']['created']);
		$expected = [
			'Log' => [
				'id' => '5',
				'user_id' => '7',
				'employee_id' => '1',
				'data' => [
					'changed' => [
						'EmployeeEdit' => [
							CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Геолог',
							CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => '',
							CAKE_LDAP_LDAP_DISTINGUISHED_NAME => 'CN=Миронов В.М.,OU=12-05,OU=УИЗ,OU=Пользователи,DC=fabrikam,DC=com',
						],
					],
					'current' => [
						'EmployeeEdit' => [
							CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
							CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => base64_decode('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'),
						],
					],
				],
			],
			'Employee' => [
				'id' => '1',
				'block' => false,
				CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Миронов В.М.',
				CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Ведущий геолог',
			],
			'User' => [
				'id' => '7',
				'block' => false,
				CAKE_LDAP_LDAP_ATTRIBUTE_NAME => 'Хвощинский В.В.',
				CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => 'Начальник отдела',
			]
		];
		$this->assertData($expected, $result);
	}

/**
 * testRunNotInternalDeferredSave
 *
 * @return void
 */
	public function testRunNotInternalDeferredSave() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'QueueDeferredSaveTask',
			['in', 'out', '_stop'],
			[$out, $out, $in]
		);
		$recordId = 2;
		$taskParam = [
			'conditions' => ['Deferred.id' => $recordId],
			'approve' => false,
			'userId' => 1,
			'internal' => false,
		];
		$this->_targetObject->initialize();
		$this->_targetObject->QueuedTask->initConfig();
		$this->_targetObject->QueuedTask->createJob('DeferredSave', $taskParam, null, 'deferred');
		$this->_targetObject->QueuedTask->createJob('DeferredSave', $taskParam, null, 'deferred');
		$capabilities = [
			'DeferredSave' => [
				'name' => 'DeferredSave',
				'timeout' => DEFERRED_SAVE_GROUP_PROCESS_TIME_LIMIT,
				'retries' => 2
			]
		];
		$jobInfo = $this->_targetObject->QueuedTask->requestJob($capabilities);
		$id = $jobInfo['id'];
		$data = unserialize($jobInfo['data']);
		$this->_targetObject->expects($this->any())->method('out')->with(new PHPUnit_Framework_Constraint_Not(__('Found processing internal deferred saves task in queue: %d. Skipped.', 1)));
		$this->_targetObject->run($data, $id);
		$taskInfo = $this->_targetObject->QueuedTask->read(null, $id);
		$this->assertTrue(is_array($taskInfo));

		$progress = Hash::get($taskInfo, 'QueuedTask.progress');
		$expected = '1';
		$this->assertData($expected, $progress);

		$failureMessage = Hash::get($taskInfo, 'QueuedTask.failure_message');
		$expected = null;
		$this->assertData($expected, $failureMessage);

		$findOpt = ['conditions' => ['Deferred.id' => $recordId]];
		$result = $this->_targetObject->Deferred->find('count', $findOpt);
		$expected = 0;
		$this->assertData($expected, $result);
	}

/**
 * Preparing data for saving
 *
 * Actions:
 *  - Adding field `ID` to changed data.
 *
 * @param int $id ID of record for prepare.
 * @return bool Success
 */
	protected function _prepareDeferredSave($id = null) {
		if (empty($id)) {
			return false;
		}

		$this->_targetObject->Deferred->recursive = -1;
		$deferredSave = $this->_targetObject->Deferred->read(null, $id);
		if (empty($deferredSave)) {
			return false;
		}

		$deferredSave['Deferred']['data']['changed']['EmployeeEdit']['id'] = $deferredSave['Deferred']['employee_id'];
		$this->_targetObject->Deferred->id = $id;

		return (bool)$this->_targetObject->Deferred->saveField('data', $deferredSave['Deferred']['data']);
	}
}
