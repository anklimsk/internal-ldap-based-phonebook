<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('View', 'View');
App::uses('Helper', 'View');
App::uses('PhonenumberHelper', 'View/Helper');

/**
 * PhonenumberHelper Test Case
 */
class PhonenumberHelperTest extends AppCakeTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $View = new View();
        $this->_targetObject = new PhonenumberHelper($View);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->_targetObject);

        parent::tearDown();
    }

    /**
     * testFormat method
     *
     * @return void
     */
    public function testFormat()
    {
        $params = [
            [
                '', // $phoneNumber
                'BY', // $countryCode
                'E164', // $format
            ], // Params for step 1
            [
                '+375296', // $phoneNumber
                'BY', // $countryCode
                'NATIONAL', // $format
            ], // Params for step 2
            [
                '+375 29 123-45-67', // $phoneNumber
                'BY', // $countryCode
                'NATIONAL', // $format
            ], // Params for step 3
            [
                '8 029 123-45-67', // $phoneNumber
                'BY', // $countryCode
                'INTERNATIONAL', // $format
            ], // Params for step 4
            [
                '+375 17 234-56-78', // $phoneNumber
                'bad', // $countryCode
                'NATIONAL', // $format
            ], // Params for step 5
        ];
        $expected = [
            '', // Result of step 1
            '296', // Result of step 2
            '8 029 123-45-67', // Result of step 3
            '+375 29 123-45-67', // Result of step 4
            '8 017 234-56-78', // Result of step 5
        ];
        $this->runClassMethodGroup('format', $params, $expected);
    }

    /**
     * testGetNameForNumber method
     *
     * @return void
     */
    public function testGetNameForNumber()
    {
        $params = [
            [
                '', // $phoneNumber
                'BY', // $countryCode
                'BY', // $languageCode
            ], // Params for step 1
            [
                '+375296', // $phoneNumber
                'BY', // $countryCode
                'BY', // $languageCode
            ], // Params for step 2
            [
                '+375 29 523-45-67', // $phoneNumber
                'BY', // $countryCode
                'RU', // $languageCode
            ], // Params for step 3
            [
                '+375 17 234-56-78', // $phoneNumber
                'BY', // $countryCode
                'RU', // $languageCode
            ], // Params for step 4
            [
                '8 029 123-45-67', // $phoneNumber
                'BY', // $countryCode
                'bad', // $languageCode
            ], // Params for step 5
            [
                '+375 17 234-56-78', // $phoneNumber
                'bad', // $countryCode
                'RU', // $languageCode
            ], // Params for step 6
            [
                '+375 29 123-45-67', // $phoneNumber
                'bad', // $countryCode
                'en', // $languageCode
            ], // Params for step 7
        ];
        $expected = [
            '', // Result of step 1
            'Velcom', // Result of step 2
            'МТС', // Result of step 3
            '', // Result of step 4
            'Velcom', // Result of step 5
            '', // Result of step 6
            'Velcom', // Result of step 7
        ];
        $this->runClassMethodGroup('getNameForNumber', $params, $expected);
    }

    /**
     * testGetDescriptionForNumber method
     *
     * @return void
     */
    public function testGetDescriptionForNumber()
    {
                $params = [
            [
                '', // $phoneNumber
                'BY', // $countryCode
                'BY', // $languageCode
                ], // Params for step 1
                [
                '+375296', // $phoneNumber
                'BY', // $countryCode
                'BY', // $languageCode
                ], // Params for step 2
                [
                '+375 29 123-45-67', // $phoneNumber
                'BY', // $countryCode
                'EN', // $languageCode
                ], // Params for step 3
                [
                '+375 17 234-56-78', // $phoneNumber
                'BY', // $countryCode
                'RU', // $languageCode
                ], // Params for step 4
                [
                '8 029 123-45-67', // $phoneNumber
                'BY', // $countryCode
                'bad', // $languageCode
                ], // Params for step 5
                [
                '+375 17 234-56-78', // $phoneNumber
                'bad', // $countryCode
                'RU', // $languageCode
                ], // Params for step 6
                ];
                $expected = [
                '', // Result of step 1
                '', // Result of step 2
                'Belarus', // Result of step 3
                'Минск', // Result of step 4
                '', // Result of step 5
                'Беларусь', // Result of step 6
                ];
                $this->runClassMethodGroup('getDescriptionForNumber', $params, $expected);
    }
}
