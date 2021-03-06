<?php
/**
 * This file is the application level ExtendCakeTestCase class.
 *
 * CakeExtendTest: Extended test tools for CakePHP.
 * @copyright Copyright 2016-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Test
 */

App::uses('ExtendCakeTestCase', 'CakeExtendTest.Test');
App::uses('AppTestTrait', 'Test');
App::uses('Employee', 'Model');
App::uses('Department', 'Model');
require_once App::pluginPath('CakeLdap') . 'Test' . DS . 'Model' . DS . 'modelsFixt.php';
require_once App::pluginPath('CakeSettingsApp') . 'Test' . DS . 'Model' . DS . 'modelsFixt.php';

/**
 * Application level CakeTestCase class
 *
 */
class AppCakeTestCase extends ExtendCakeTestCase {

	use AppTestTrait;

/**
 * Setup the test case, backup the static object values so they can be restored.
 * Specifically backs up the contents of Configure and paths in App if they have
 * not already been backed up.
 *
 * Actions:
 * - Write test configuration.
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$path = __DIR__ . DS;
		$this->applyTestConfig($path);
		//Configure::write('Config.language', 'eng');
	}

/**
 * teardown any static object changes and restore them.
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
	}
}
