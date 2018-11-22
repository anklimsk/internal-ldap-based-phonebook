<?php
/**
 * GenerateTask Test Case
 *
 */
App::uses('AppCakeTestCase', 'Test');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('GenerateTask', 'Console/Command/Task');

/**
 * GenerateTaskTest class
 *
 */
class GenerateTaskTest extends AppCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		'plugin.queue.queued_task',
	];

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);

		$this->_targetObject = $this->getMock(
			'GenerateTask',
			['in', 'out', 'err', 'hr', '_stop'],
			[$out, $out, $in]
		);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
	}

/**
 * testExecuteEmptyParam
 *
 * @return void
 */
	public function testExecuteEmptyParam() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'GenerateTask',
			['in', 'out', '_stop'],
			[$out, $out, $in]
		);

		$this->_targetObject->expects($this->at(0))->method('out')->with('<error>' . __("Empty parameters. Run this task with parameter '-h' or '--help'") . '</error>');
		$this->_targetObject->execute();
	}

/**
 * testExecuteInvalidParamView
 *
 * @return void
 */
	public function testExecuteInvalidParamView() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'GenerateTask',
			['in', 'out', '_stop'],
			[$out, $out, $in]
		);

		$this->_targetObject->args = ['bad', GENERATE_FILE_DATA_TYPE_ALPH];
		$this->_targetObject->expects($this->at(0))->method('out')->with('<error>' . __("Invalid parameters. Run this task with parameter '-h' or '--help'") . '</error>');
		$this->_targetObject->execute();
	}

/**
 * testExecuteInvalidParamType
 *
 * @return void
 */
	public function testExecuteInvalidParamType() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'GenerateTask',
			['in', 'out', '_stop'],
			[$out, $out, $in]
		);

		$this->_targetObject->args = [GENERATE_FILE_VIEW_TYPE_PDF, 'bad'];
		$this->_targetObject->expects($this->at(0))->method('out')->with('<error>' . __("Invalid parameters. Run this task with parameter '-h' or '--help'") . '</error>');
		$this->_targetObject->execute();
	}

/**
 * testExecuteSuccess
 *
 * @return void
 */
	public function testExecuteSuccess() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'GenerateTask',
			['in', 'out', '_stop'],
			[$out, $out, $in]
		);

		$this->_targetObject->args = [GENERATE_FILE_VIEW_TYPE_PDF, GENERATE_FILE_DATA_TYPE_ALPH];
		$this->_targetObject->expects($this->at(1))->method('out')->with(__('Generate %s files set in queue successfully.', 'Pdf'));
		$this->_targetObject->execute();
	}
}
