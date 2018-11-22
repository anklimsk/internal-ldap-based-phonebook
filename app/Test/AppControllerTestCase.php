<?php
/**
 * This file is the application level ExtendControllerTestCase class.
 *
 * CakeExtendTest: Extended test tools for CakePHP.
 * @copyright Copyright 2016-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Test
 */

App::uses('ExtendControllerTestCase', 'CakeExtendTest.Test');
App::uses('AppTestTrait', 'Test');
App::uses('Employee', 'Model');
App::uses('Department', 'Model');
require_once App::pluginPath('CakeLdap') . 'Test' . DS . 'Model' . DS . 'modelsFixt.php';
require_once App::pluginPath('CakeSettingsApp') . 'Test' . DS . 'Model' . DS . 'modelsFixt.php';

/**
 * Application level ControllerTestCase class
 *
 */
class AppControllerTestCase extends ExtendControllerTestCase {

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
		$this->setDefaultUserInfo($this->userInfo);
		parent::setUp();

		$path = __DIR__ . DS;
		$this->applyTestConfig($path);
		//Configure::write('Config.language', 'eng');
	}

/**
 * teardown any static object changes and restore them.
 *
 * Actions:
 * - Restore configuration;
 *
 * @return void
 */
	public function tearDown() {
		unset($this->_userInfo);

		parent::tearDown();
	}

/**
 * Excluding common application variables from result.
 *
 * @param array &$data Data for processing.
 * @return void.
 */
	public function excludeCommonAppVars(&$data) {
		$this->assertTrue(is_array($data));

		$commonVars = [
			'additionalCssFiles',
			'additionalJsFiles',
			'search_targetFields',
			'search_targetFieldsSelected',
			'search_querySearchMinLength',
			'search_targetDeep',
			'uiLcid2',
			'uiLcid3',
			'isExternalAuth',
			'emailContact',
			'emailSubject',
			'showSearchForm',
			'countDeferredSaves',
			'useNavbarContainerFluid',
			'search_urlActionSearch',
			'projectName',
			'pageTitlePrefix',
			'pageTitlePostfix',
		];
		$commonVars = array_flip($commonVars);
		$result = array_diff_key($commonVars, $data);
		$this->assertEmpty($result);

		$data = array_diff_key($data, $commonVars);
	}
}
