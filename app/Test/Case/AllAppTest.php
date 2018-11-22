<?php
/**
 * This file is the console shell task file of the application.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Test.Case
 */

/**
 * A class to contain test cases and run them with shared fixtures
 *
 * @package app.Test.Case
 */
class AllAppTest extends CakeTestSuite {

/**
 * Create test suite.
 *
 * @return object An object of `CakeTestSuite`.
 */
	public static function suite() {
		$suite = new CakeTestSuite('All App tests');
		$path = dirname(__FILE__);
		$suite->addTestFile($path . DS . 'AllConsoleTestsTest.php');
		$suite->addTestFile($path . DS . 'AllControllerTestsTest.php');
		$suite->addTestFile($path . DS . 'AllBehaviorTestsTest.php');
		$suite->addTestFile($path . DS . 'AllModelTestsTest.php');
		$suite->addTestFile($path . DS . 'AllHelperTestsTest.php');
		$suite->addTestFile($path . DS . 'AllViewTestsTest.php');
		$suite->addTestFile($path . DS . 'AllUtilityTestsTest.php');

		return $suite;
	}
}
