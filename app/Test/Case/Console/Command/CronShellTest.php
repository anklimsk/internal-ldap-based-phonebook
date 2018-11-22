<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('CronShell', 'Console/Command');
App::uses('CakeText', 'Utility');

/**
 * CronShell Test Case
 *
 */
class CronShellTest extends AppCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [];

/**
 * setup test
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);

		$this->_targetObject = $this->getMock(
			'CronShell',
			['in', 'out', 'hr', 'err', 'createFile', '_stop', '_checkUnitTest'],
			[$out, $out, $in]
		);
		$this->_targetObject->initialize();
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
 * testMain method
 *
 * @return void
 */
	public function testMain() {
		$this->_targetObject->expects($this->at(3))->method('out')
			->with($this->stringContains(CakeText::toList(constsVals('SHELL_CRON_TASK_'), __('and'))));
		$this->_targetObject->main();
	}
}
