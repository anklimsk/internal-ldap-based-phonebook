<?php
App::uses('AppCakeTestCase', 'Test');
App::uses('PhoneNumber', 'Utility');

/**
 * PhoneNumberTest file
 *
 */
class PhoneNumberTest extends AppCakeTestCase {

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
	public function setUp() {
		parent::setUp();

		$this->_targetObject = new PhoneNumber();
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
 * testGetListFormats method
 *
 * @return void
 */
	public function testGetListFormats() {
		$result = $this->_targetObject->getListFormats();
		$expected = [
			'E164' => 0,
			'INTERNATIONAL' => 1,
			'NATIONAL' => 2,
			'RFC3966' => 3,
		];
		$this->assertData($expected, $result);
	}

/**
 * testFormat method
 *
 * @return void
 */
	public function testFormat() {
		$params = [
			[
				null, // $phoneNumber
				null, // $countryCode
				null, // $format
			],
			[
				'1234567', // $phoneNumber
				null, // $countryCode
				null, // $format
			],
			[
				'1234567', // $phoneNumber
				'BY', // $countryCode
				'INTERNATIONAL', // $format
			],
			[
				'9b34567', // $phoneNumber
				'BY', // $countryCode
				'INTERNATIONAL', // $format
			],
			[
				'+375173', // $phoneNumber
				'BY', // $countryCode
				'INTERNATIONAL', // $format
			],
			[
				'+375171234567', // $phoneNumber
				'BY', // $countryCode
				'INTERNATIONAL', // $format
			],
			[
				'+375171234567', // $phoneNumber
				'BY', // $countryCode
				'E164', // $format
			],
			[
				'+375171234567', // $phoneNumber
				'By', // $countryCode
				'Rfc3966', // $format
			],
			[
				'+375171234567', // $phoneNumber
				'by', // $countryCode
				'national', // $format
			],
			[
				'+375171234567', // $phoneNumber
				'BY', // $countryCode
				'bad', // $format
			],
			[
				'+375171234567', // $phoneNumber
				'Bad', // $countryCode
				'national', // $format
			],
		];
		$expected = [
			null,
			'1234567',
			'+375 1234567',
			'9b34567',
			'+375 173',
			'+375 17 123-45-67',
			'+375171234567',
			'tel:+375-17-123-45-67',
			'8 017 123-45-67',
			'+375171234567',
			'8 017 123-45-67',
		];

		$this->runClassMethodGroup('format', $params, $expected);
	}

/**
 * testIsValidNumber method
 *
 * @return void
 */
	public function testIsValidNumber() {
		$params = [
			[
				null, // $phoneNumber
				null, // $countryCode
			],
			[
				null, // $phoneNumber
				'BY', // $countryCode
			],
			[
				'1234567', // $phoneNumber
				null, // $countryCode
			],
			[
				'1234567', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'9b34567', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'+3751791', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'+375 17 123-45-67', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'80171234567', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'80171234567', // $phoneNumber
				'RU', // $countryCode
			],
		];
		$expected = [
			false,
			false,
			false,
			false,
			false,
			false,
			true,
			true,
			false,
		];

		$this->runClassMethodGroup('isValidNumber', $params, $expected);
	}

/**
 * testIsMobileNumber method
 *
 * @return void
 */
	public function testIsMobileNumber() {
		$params = [
			[
				null, // $phoneNumber
				null, // $countryCode
			],
			[
				null, // $phoneNumber
				'BY', // $countryCode
			],
			[
				'1234567', // $phoneNumber
				null, // $countryCode
			],
			[
				'1234567', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'9b34567', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'+375297', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'+375 29 123-45-67', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'+375 17 123-45-67', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'80291234567', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'80291234567', // $phoneNumber
				'RU', // $countryCode
			],
		];
		$expected = [
			false,
			false,
			false,
			false,
			false,
			false,
			true,
			false,
			true,
			false,
		];

		$this->runClassMethodGroup('isMobileNumber', $params, $expected);
	}

/**
 * testIsFixedLineNumber method
 *
 * @return void
 */
	public function testIsFixedLineNumber() {
		$params = [
			[
				null, // $phoneNumber
				null, // $countryCode
			],
			[
				null, // $phoneNumber
				'BY', // $countryCode
			],
			[
				'1234567', // $phoneNumber
				null, // $countryCode
			],
			[
				'1234567', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'9b34567', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'+3751736', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'+375 29 123-45-67', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'+375 17 123-45-67', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'80171234567', // $phoneNumber
				'BY', // $countryCode
			],
			[
				'80171234567', // $phoneNumber
				'RU', // $countryCode
			],
		];
		$expected = [
			false,
			false,
			false,
			false,
			false,
			false,
			false,
			true,
			true,
			false,
		];

		$this->runClassMethodGroup('isFixedLineNumber', $params, $expected);
	}

/**
 * testGetNameForNumber method
 *
 * @return void
 */
	public function testGetNameForNumber() {
		$params = [
			[
				null, // $phoneNumber
				null, // $countryCode
				null, // $languageCode
			],
			[
				null, // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'1234567', // $phoneNumber
				null, // $countryCode
				'RU', // $languageCode
			],
			[
				'1234567', // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'9b34567', // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'+375297', // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'+375171234567', // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'+375291234567', // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'+375 33 123-45-67', // $phoneNumber
				'By', // $countryCode
				'Ru', // $languageCode
			],
			[
				'+375 44 123-45-67', // $phoneNumber
				'by', // $countryCode
				'en', // $languageCode
			],
			[
				'+375291234567', // $phoneNumber
				'bad', // $countryCode
				'RU', // $languageCode
			],
			[
				'+375291234567', // $phoneNumber
				'by', // $countryCode
				'bad', // $languageCode
			],
		];
		$expected = [
			'',
			'',
			'',
			'',
			'',
			'МТС',
			'',
			'Velcom',
			'МТС',
			'Velcom',
			'Velcom',
			'Velcom',
		];

		$this->runClassMethodGroup('getNameForNumber', $params, $expected);
	}

/**
 * testGetDescriptionForNumber method
 *
 * @return void
 */
	public function testGetDescriptionForNumber() {
		$params = [
			[
				null, // $phoneNumber
				null, // $countryCode
				null, // $languageCode
			],
			[
				null, // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'1234567', // $phoneNumber
				null, // $countryCode
				'RU', // $languageCode
			],
			[
				'1234567', // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'9b34567', // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'+375172', // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'+375171234567', // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'+375291234567', // $phoneNumber
				'BY', // $countryCode
				'RU', // $languageCode
			],
			[
				'+375 33 123-45-67', // $phoneNumber
				'By', // $countryCode
				'Ru', // $languageCode
			],
			[
				'+375 44 123-45-67', // $phoneNumber
				'by', // $countryCode
				'en', // $languageCode
			],
			[
				'+375291234567', // $phoneNumber
				'bad', // $countryCode
				'RU', // $languageCode
			],
			[
				'+375291234567', // $phoneNumber
				'by', // $countryCode
				'bad', // $languageCode
			],
		];
		$expected = [
			'',
			'',
			'',
			'Беларусь',
			'',
			'Минск',
			'Минск',
			'Беларусь',
			'Беларусь',
			'Belarus',
			'Беларусь',
			'',
		];

		$this->runClassMethodGroup('getDescriptionForNumber', $params, $expected);
	}
}
