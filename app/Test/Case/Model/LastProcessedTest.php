<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('LastProcessed', 'Model');

/**
 * LastProcessed Test Case
 */
class LastProcessedTest extends AppCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		'app.last_processed'
	];

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->_targetObject = ClassRegistry::init('LastProcessed');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->_targetObject);

		parent::tearDown();
	}

/**
 * testSetLastProcessed method
 *
 * @return void
 */
	public function testSetLastProcessed() {
		$params = [
			[
				null, // $id
				null, // $lastProcessedId
			], // Params for step 1
			[
				10, // $id
				null, // $lastProcessedId
			], // Params for step 2
			[
				null, // $id
				2, // $lastProcessedId
			], // Params for step 3
			[
				1, // $id
				0, // $lastProcessedId
			], // Params for step 4
			[
				1, // $id
				3, // $lastProcessedId
			], // Params for step 5
			[
				1, // $id
				2, // $lastProcessedId
			], // Params for step 6
			[
				2, // $id
				1, // $lastProcessedId
			], // Params for step 7
		];
		$expected = [
			false, // Result of step 1
			false, // Result of step 2
			false, // Result of step 3
			false, // Result of step 4
			true, // Result of step 5
			true, // Result of step 6
			true, // Result of step 7
		];
		$this->runClassMethodGroup('setLastProcessed', $params, $expected);
	}

/**
 * testGetLastProcessed method
 *
 * @return void
 */
	public function testGetLastProcessed() {
		$params = [
			[
				null, // $id
			], // Params for step 1
			[
				100, // $id
			], // Params for step 2
			[
				LAST_PROCESSED_EMPLOYEE, // $id
			], // Params for step 3
		];
		$expected = [
			false, // Result of step 1
			false, // Result of step 2
			'5', // Result of step 3
		];
		$this->runClassMethodGroup('getLastProcessed', $params, $expected);
	}

/**
 * testGetLastUpdate method
 *
 * @return void
 */
	public function testGetLastUpdate() {
		$params = [
			[
				null, // $id
			], // Params for step 1
			[
				100, // $id
			], // Params for step 2
			[
				LAST_PROCESSED_DEFERRED_SAVE, // $id
			], // Params for step 3
		];
		$expected = [
			false, // Result of step 1
			false, // Result of step 2
			'2017-11-16 12:00:01', // Result of step 3
		];
		$this->runClassMethodGroup('getLastUpdate', $params, $expected);
	}
}
