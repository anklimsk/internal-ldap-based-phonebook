<?php
/**
 * QueueGenerateTask Test Case
 *
 */
App::uses('AppCakeTestCase', 'Test');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('Hash', 'Utility');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('QueueGenerateTask', 'Console/Command/Task');

/**
 * QueueGenerateTaskTest class
 *
 */
class QueueGenerateTaskTest extends AppCakeTestCase {

/**
 * Path to export directory
 *
 * @var string
 */
	protected $_pathExportDir = TMP . 'tests' . DS . 'export' . DS;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		'core.cake_session',
		'app.department_extension',
		'plugin.cake_ldap.employee',
		'plugin.cake_ldap.employee_ldap',
		'plugin.cake_ldap.department',
		'plugin.cake_ldap.othertelephone',
		'plugin.cake_ldap.othermobile',
		'plugin.cake_ldap.subordinate',
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
			'QueueGenerateTask',
			['in', 'out', 'err', 'hr', '_stop'],
			[$out, $out, $in]
		);
		$this->_targetObject->Employee->pathExportDir = $this->_pathExportDir;
		$oFolder = new Folder($this->_targetObject->Employee->pathExportDir, true);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		$Folder = new Folder($this->_targetObject->Employee->pathExportDir);
		$Folder->delete();
		parent::tearDown();
	}

/**
 * testRunViewTypeAllDataTypeAllLengthQueue
 *
 * @return void
 */
	public function testRunViewTypeAllDataTypeAllLengthQueue() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'QueueGenerateTask',
			['in', 'out', '_stop'],
			[$out, $out, $in]
		);
		$taskParam = [
			'view' => GENERATE_FILE_VIEW_TYPE_ALL,
			'type' => GENERATE_FILE_DATA_TYPE_ALL
		];
		$this->_targetObject->initialize();
		$this->_targetObject->QueuedTask->initConfig();
		$this->_targetObject->QueuedTask->createJob('Generate', $taskParam, null, 'export');
		$this->_targetObject->QueuedTask->createJob('Generate', $taskParam, null, 'export');
		$capabilities = [
			'Generate' => [
				'name' => 'Generate',
				'timeout' => (PDF_GENERATE_TIME_LIMIT + EXCEL_GENERATE_TIME_LIMIT) * 2,
				'retries' => 2
			]
		];
		$jobInfo = $this->_targetObject->QueuedTask->requestJob($capabilities);
		$id = $jobInfo['id'];
		$data = unserialize($jobInfo['data']);
		$this->_targetObject->expects($this->at(4))->method('out')->with(__('Found generating task in queue: %d. Skipped.', 1));
		$this->_targetObject->run($data, $id);
	}

/**
 * testRunViewTypePdfDataTypeAlph
 *
 * @return void
 */
	public function testRunViewTypePdfDataTypeAlph() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'QueueGenerateTask',
			['in', '_stop'],
			[$out, $out, $in]
		);
		$view = GENERATE_FILE_VIEW_TYPE_PDF;
		$type = GENERATE_FILE_DATA_TYPE_ALPH;
		$taskParam = compact('view', 'type');
		$this->_targetObject->initialize();
		$this->_targetObject->QueuedTask->initConfig();
		$this->_targetObject->QueuedTask->createJob('Generate', $taskParam, null, 'export');
		$capabilities = [
			'Generate' => [
				'name' => 'Generate',
				'timeout' => PDF_GENERATE_TIME_LIMIT * 2,
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

		$filesName = [
			$this->_targetObject->Employee->expandTypeExportToFilename($type, false, true),
			$this->_targetObject->Employee->expandTypeExportToFilename($type, true, true),
		];
		foreach ($filesName as $fileName) {
			$this->assertFalse(empty($fileName));
			$oFile = new File($this->_targetObject->Employee->pathExportDir . $fileName . '.pdf', false);
			$this->assertTrue($oFile->exists());
			$this->assertTrue($oFile->size() > 0);
		}
	}

/**
 * testRunViewTypeExcelDataTypeDepart
 *
 * @return void
 */
	public function testRunViewTypeExcelDataTypeDepart() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'QueueGenerateTask',
			['in', '_stop'],
			[$out, $out, $in]
		);
		$view = GENERATE_FILE_VIEW_TYPE_EXCEL;
		$type = GENERATE_FILE_DATA_TYPE_DEPART;
		$taskParam = compact('view', 'type');
		$this->_targetObject->initialize();
		$this->_targetObject->QueuedTask->initConfig();
		$this->_targetObject->QueuedTask->createJob('Generate', $taskParam, null, 'export');
		$capabilities = [
			'Generate' => [
				'name' => 'Generate',
				'timeout' => EXCEL_GENERATE_TIME_LIMIT * 2,
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

		$filesName = [
			$this->_targetObject->Employee->expandTypeExportToFilename($type, false, true),
			$this->_targetObject->Employee->expandTypeExportToFilename($type, true, true),
		];
		foreach ($filesName as $fileName) {
			$this->assertFalse(empty($fileName));
			$oFile = new File($this->_targetObject->Employee->pathExportDir . $fileName . '.xlsx', false);
			$this->assertTrue($oFile->exists());
			$this->assertTrue($oFile->size() > 0);
		}
	}
}
