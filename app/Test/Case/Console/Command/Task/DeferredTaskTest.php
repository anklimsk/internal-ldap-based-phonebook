<?php
/**
 * DeferredTask Test Case
 *
 */
App::uses('AppCakeTestCase', 'Test');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('DeferredTask', 'Console/Command/Task');

/**
 * DeferredTaskTest class
 *
 */
class DeferredTaskTest extends AppCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		'app.deferred',
		'app.last_processed',
		'plugin.cake_settings_app.ldap',
		'plugin.cake_ldap.department',
		'plugin.cake_ldap.employee',
		'plugin.cake_ldap.employee_ldap',
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
			'DeferredTask',
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
 * testExecute
 *
 * @return void
 */
	public function testExecute() {
		$out = $this->getMock('ConsoleOutput', [], [], '', false);
		$in = $this->getMock('ConsoleInput', [], [], '', false);
		$this->_targetObject = $this->getMock(
			'DeferredTask',
			['in', 'out', '_stop'],
			[$out, $out, $in]
		);

		$this->_targetObject->expects($this->at(1))->method('out')->with(__('Checking new deferred saves complete successfully.'));
		$this->_targetObject->execute();

		$modelLastProcessed = ClassRegistry::init('LastProcessed');
		$result = $modelLastProcessed->getLastProcessed(LAST_PROCESSED_DEFERRED_SAVE);
		$expected = '5';
		$this->assertData($expected, $result);
	}
}
