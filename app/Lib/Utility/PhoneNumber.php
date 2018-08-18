<?php
/**
 * This file is the util file of the application.
 * PhoneNumber Utility.
 * Methods to make Phone number more readable.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.Lib.Utility
 */

App::import(
    'Vendor',
    'LibPhoneNumber',
    ['file' => 'LibPhoneNumber' . DS . 'autoload.php']
);

/**
 * Phone number helper library.
 * Methods to make Phone number more readable.
 *
 * @package app.Lib.Utility
 */
class PhoneNumber
{

    /**
     * Stores the PhoneNumberUtil() instance to carry out international phone number formatting,
     * parsing, or validation. The instance is loaded with phone number metadata for a number of most
     * commonly used regions.
     *
     * @var object
     */
    protected $_PhoneUtil = null;

    /**
     * Constructor.
     *
     * Return void
     */
    public function __construct()
    {
        $this->_PhoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    }

    /**
     * Return list of phone number formats
     *
     * @return array Return list of phone number formats.
     */
    public function getListFormats()
    {
        $classPhoneNumberFormat = new ReflectionClass('\libphonenumber\PhoneNumberFormat');

        return $classPhoneNumberFormat->getConstants();
    }

    /**
     * Return formatted phone number
     *
     * @param string $phoneNumber Phone number for formatting
     * @param string $countryCode Region that we are expecting the number to be from
     *  in format ISO 3166-2.
     * @param string $formatName The PhoneNumberFormat the phone number should be formatted into.
     * @return string Return formatted phone number, or input phone number on failure.
     */
    public function format($phoneNumber = '', $countryCode = '', $formatName = 'E164')
    {
        if (empty($phoneNumber) || empty($countryCode)) {
            return $phoneNumber;
        }

        $formatName = mb_strtoupper($formatName);
        $listFormats = $this->getListFormats();
        if (!empty($formatName) && isset($listFormats[$formatName])) {
            $formatVal = $listFormats[$formatName];
        } else {
            $formatVal = array_shift($listFormats);
        }

        $phoneNumber = (string)$phoneNumber;
        $countryCode = (string)$countryCode;
        try {
            $telNumberProto = $this->_PhoneUtil->parse($phoneNumber, $countryCode);
        } catch (libphonenumber\NumberParseException $e) {
            return $phoneNumber;
        }

        return $this->_PhoneUtil->format($telNumberProto, $formatVal);
    }

    /**
     * Check whether the phone number is of a valid pattern
     *
     * @param string $phoneNumber Phone number for check.
     * @param string $countryCode Region that we are expecting the number to be from
     *  in format ISO 3166-2.
     * @return bool Return True, indicates the number is of a valid pattern.
     */
    public function isValidNumber($phoneNumber = '', $countryCode = '')
    {
        $result = false;
        if (empty($phoneNumber)) {
            return $result;
        }

        $phoneNumber = (string)$phoneNumber;
        $countryCode = (string)$countryCode;
        try {
            $telNumberProto = $this->_PhoneUtil->parse($phoneNumber, $countryCode);
        } catch (libphonenumber\NumberParseException $e) {
            return $result;
        }

        return $this->_PhoneUtil->isValidNumber($telNumberProto);
    }

    /**
     * Check whether the phone number is valid type
     *
     * @param string $phoneNumber Phone number for check.
     * @param string $countryCode Region that we are expecting the number to be from
     *  in format ISO 3166-2.
     * @param int $numberType Type of number for check.
     * @return bool Return True, indicates whether the number is valid type.
     */
    protected function _isValidNumberType($phoneNumber = '', $countryCode = '', $numberType = null)
    {
        $result = false;
        if (empty($phoneNumber) || ($numberType === null)) {
            return $result;
        }

        $phoneNumber = (string)$phoneNumber;
        $countryCode = (string)$countryCode;
        try {
            $telNumberProto = $this->_PhoneUtil->parse($phoneNumber, $countryCode);
        } catch (libphonenumber\NumberParseException $e) {
            return $result;
        }
        if ($this->_PhoneUtil->isValidNumber($telNumberProto) &&
            ($this->_PhoneUtil->getNumberType($telNumberProto) === $numberType)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Check whether the phone number is Mobile number
     *
     * @param string $phoneNumber Phone number for check.
     * @param string $countryCode Region that we are expecting the number to be from
     *  in format ISO 3166-2.
     * @return bool Return True, indicates whether the number is Mobile number.
     */
    public function isMobileNumber($phoneNumber = '', $countryCode = '')
    {
        return $this->_isValidNumberType($phoneNumber, $countryCode, \libphonenumber\PhoneNumberType::MOBILE);
    }

    /**
     * Check whether the phone number is Fixed line number
     *
     * @param string $phoneNumber Phone number for check.
     * @param string $countryCode Region that we are expecting the number to be from
     *  in format ISO 3166-2.
     * @return bool Return True, indicates whether the number is Fixed line number.
     */
    public function isFixedLineNumber($phoneNumber = '', $countryCode = '')
    {
        return $this->_isValidNumberType($phoneNumber, $countryCode, \libphonenumber\PhoneNumberType::FIXED_LINE);
    }

    /**
     * Return carrier name for the given phone number
     *
     * @param string $phoneNumber Phone number for which we want to get a carrier name.
     * @param string $countryCode Region that we are expecting the number to be from
     *  in format ISO 3166-2.
     * @param string $languageCode The language code in which the name should be written.
     * @return string String a carrier name for the given phone number, or empty string
     *  on failure.
     */
    public function getNameForNumber($phoneNumber = '', $countryCode = '', $languageCode = '')
    {
        $result = '';
        if (empty($phoneNumber)) {
            return $result;
        }

        $phoneNumber = (string)$phoneNumber;
        $countryCode = (string)$countryCode;
        $languageCode = (string)$languageCode;
        try {
            $telNumberProto = $this->_PhoneUtil->parse($phoneNumber, $countryCode);
        } catch (libphonenumber\NumberParseException $e) {
            return $result;
        }
        $carrierMapper = \libphonenumber\PhoneNumberToCarrierMapper::getInstance();

        return $carrierMapper->getNameForValidNumber($telNumberProto, $languageCode);
    }

    /**
     * Return text description for the given language code for the given phone number
     *
     * @param string $phoneNumber Phone number for which we want to get a text description .
     * @param string $countryCode Region that we are expecting the number to be from
     *  in format ISO 3166-2.
     * @param string $languageCode The language code for which the description should be written
     *  in format ISO 639-1.
     * @return string String a text description for the given language code for the given
     *  phone number, or empty string on failure.
     */
    public function getDescriptionForNumber($phoneNumber = '', $countryCode = '', $languageCode = '')
    {
        $result = '';
        if (empty($phoneNumber)) {
            return $result;
        }

        $phoneNumber = (string)$phoneNumber;
        $countryCode = (string)$countryCode;
        $languageCode = (string)$languageCode;
        try {
            $telNumberProto = $this->_PhoneUtil->parse($phoneNumber, $countryCode);
        } catch (libphonenumber\NumberParseException $e) {
            return $result;
        }
        $offlineGeocoder = \libphonenumber\geocoding\PhoneNumberOfflineGeocoder::getInstance();

        return $offlineGeocoder->getDescriptionForValidNumber($telNumberProto, $languageCode, $countryCode);
    }
}
