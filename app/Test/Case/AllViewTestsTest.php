<?php
/**
 * This file is the console shell task file of the application.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.Test.Case
 */

/**
 * A class to contain test cases and run them with shared fixtures
 *
 * @package app.Test.Case
 */
class AllViewTestsTest extends CakeTestSuite
{

    /**
     * Create test suite.
     *
     * @return object An object of `CakeTestSuite`.
     */
    public static function suite()
    {
        $suite = new CakeTestSuite('All View tests');
        $path = dirname(__FILE__);
        $suite->addTestDirectory($path . DS . 'View');

        return $suite;
    }
}
