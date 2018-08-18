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
class AllCakeSearchInfoTest extends CakeTestSuite
{

    /**
     * Create test suite.
     *
     * @return object An object of `CakeTestSuite`.
     */
    public static function suite()
    {
        $suite = new CakeTestSuite('All CakeSearchInfo tests');
        $path = dirname(__FILE__);
        $suite->addTestDirectory($path . DS . 'Controller' . DS . 'Component');
        $suite->addTestDirectory($path . DS . 'Controller');
        $suite->addTestDirectory($path . DS . 'Model');
        $suite->addTestDirectory($path . DS . 'View' . DS . 'Helper');

        return $suite;
    }
}
