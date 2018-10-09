<?php
/**
 * This file is the console shell task file of the plugin.
 *
 * CakeExtendTest: Extended test tools for CakePHP.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.Test.Case
 */

/**
 * A class to contain test cases and run them with shared fixtures
 *
 * @package plugin.Test.Case
 */
class AllCakeTCPDFTest extends CakeTestSuite {

/**
 * Create test suite.
 *
 * @return object An object of `CakeTestSuite`.
 */
	public static function suite() {
		$suite = new CakeTestSuite('All CakeTCPDF tests');
		$path = dirname(__FILE__);
		$suite->addTestDirectory($path . DS . 'View');

		return $suite;
	}
}
