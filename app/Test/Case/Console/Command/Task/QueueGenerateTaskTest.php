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
		'app.last_processed',
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
 * Run job from queue tasks
 *
 * @param string $view Type of export view: PDF, Excel or All
 * @param string $type Export type
 * @param bool $forceUpdate Flag of forced update files
 * @param int $timeout Timeout of job
 * @param bool $checkProgress Flag of checking progress task
 * @return void
 */
	protected function _runJob($view = null, $type = null, $forceUpdate = false, $timeout = null, $checkProgress = true) {
		$taskParam = compact('view', 'type', 'forceUpdate');
		$this->_targetObject->QueuedTask->createJob('Generate', $taskParam, null, 'export');
		$capabilities = [
			'Generate' => [
				'name' => 'Generate',
				'timeout' => $timeout,
				'retries' => 2
			]
		];
		$jobInfo = $this->_targetObject->QueuedTask->requestJob($capabilities);
		$id = $jobInfo['id'];
		$data = unserialize($jobInfo['data']);
		$this->_targetObject->run($data, $id);
		$taskInfo = $this->_targetObject->QueuedTask->read(null, $id);
		$this->assertTrue(is_array($taskInfo));

		if (!$checkProgress) {
			return;
		}

		$progress = Hash::get($taskInfo, 'QueuedTask.progress');
		$expected = '1';
		$this->assertData($expected, $progress);

		$failureMessage = Hash::get($taskInfo, 'QueuedTask.failure_message');
		if ($forceUpdate) {
			$this->assertTrue(empty($failureMessage));
		} elseif (!empty($failureMessage)) {
			$this->assertRegExp('/' . __('Update file "%s" is not required', '.+') . '/', $failureMessage);
		}
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
		$this->_targetObject->initialize();
		$this->_targetObject->QueuedTask->initConfig();
		$this->_targetObject->expects($this->at(4))->method('out')->with(__('Found generating task in queue: %d. Skipped.', 1));

		$view = GENERATE_FILE_VIEW_TYPE_ALL;
		$type = GENERATE_FILE_DATA_TYPE_ALL;
		$forceUpdate = false;
		$timeout = (PDF_GENERATE_TIME_LIMIT + EXCEL_GENERATE_TIME_LIMIT) * 2;
		$taskParam = compact('view', 'type', 'forceUpdate');
		$this->_targetObject->QueuedTask->createJob('Generate', $taskParam, null, 'export');
		$this->_runJob($view, $type, $forceUpdate, $timeout, false);
	}

/**
 * Return list of file names for export.
 *
 * @param string $type Export type
 * @return array Return array list of file names for export.
 */
	protected function _getListFileNames($type = null) {
		$listFiles = [];
		$extendView = [false, true];
		foreach ($extendView as $extendViewState) {
			$listFiles[] = $this->_targetObject->Employee->expandTypeExportToFilename($type, $extendViewState, true);
		}

		return $listFiles;
	}

/**
 * Check exported files
 *
 * @param array &$filesInfo Cache of modifications date for files
 * @param string $type Export type
 * @param string $fileExt File extension
 * @param bool $forceUpdate Flag of forced update files
 * @return void
 */
	protected function _checkFiles(array &$filesInfo, $type = null, $fileExt = '', $forceUpdate = false) {
		$filesName = $this->_getListFileNames($type);
		foreach ($filesName as $fileName) {
			$this->assertFalse(empty($fileName));
			$filePath = $this->_targetObject->Employee->pathExportDir . $fileName . $fileExt;
			$oFile = new File($filePath, false);
			$this->assertTrue($oFile->exists());
			$this->assertTrue($oFile->size() > 0);
			$fileChangedTimestamp = filemtime($filePath);
			if (!isset($filesInfo[$fileName])) {
				$filesInfo[$fileName] = $fileChangedTimestamp;
			} else {
				if (!$forceUpdate) {
					$result = ($filesInfo[$fileName] === $fileChangedTimestamp);
				} else {
					$result = ($filesInfo[$fileName] < $fileChangedTimestamp);
				}
				$this->assertTrue($result);
			}
		}
	}

/**
 * testRunViewTypePdfDataTypeAlphForceUpdate
 *
 * @return void
 */
	public function testRunViewTypePdfDataTypeAlphForceUpdate() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'QueueGenerateTask',
			['in', '_stop'],
			[$out, $out, $in]
		);
		$this->_targetObject->initialize();
		$this->_targetObject->QueuedTask->initConfig();

		$view = GENERATE_FILE_VIEW_TYPE_PDF;
		$type = GENERATE_FILE_DATA_TYPE_ALPH;
		$forceUpdate = true;
		$timeout = PDF_GENERATE_TIME_LIMIT * 2;
		$this->_runJob($view, $type, $forceUpdate, $timeout);

		$filesInfo = [];
		$fileExt = '.pdf';
		$this->_checkFiles($filesInfo, $type, $fileExt, $forceUpdate);

		$this->_runJob($view, $type, $forceUpdate, $timeout);
		$this->_checkFiles($filesInfo, $type, $fileExt, $forceUpdate);
	}

/**
 * testRunViewTypeExcelDataTypeDepartWithoutUpdate
 *
 * @return void
 */
	public function testRunViewTypeExcelDataTypeDepartWithoutUpdate() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'QueueGenerateTask',
			['in', '_stop'],
			[$out, $out, $in]
		);
		$this->_targetObject->initialize();
		$this->_targetObject->QueuedTask->initConfig();

		$view = GENERATE_FILE_VIEW_TYPE_EXCEL;
		$type = GENERATE_FILE_DATA_TYPE_DEPART;
		$forceUpdate = false;
		$timeout = EXCEL_GENERATE_TIME_LIMIT * 2;
		$this->_runJob($view, $type, $forceUpdate, $timeout);

		$filesInfo = [];
		$fileExt = '.xlsx';
		$this->_checkFiles($filesInfo, $type, $fileExt, $forceUpdate);

		$this->_runJob($view, $type, $forceUpdate, $timeout);
		$this->_checkFiles($filesInfo, $type, $fileExt, $forceUpdate);
		clearstatcache();
		$time = time();
		while (time() < $time + 1) {
			usleep(100000);
		}

		CakeTestSuiteDispatcher::time(true);
		$modelLastProcessed = ClassRegistry::init('LastProcessed');
		$this->assertTrue($modelLastProcessed->setLastProcessed(LAST_PROCESSED_EMPLOYEE, 1));
		$this->_runJob($view, $type, $forceUpdate, $timeout);
		$this->_checkFiles($filesInfo, $type, $fileExt, true);
	}
}
