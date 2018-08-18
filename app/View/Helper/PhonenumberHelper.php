<?php
/**
 * This file is the helper file of the application.
 * PhoneNumber Helper.
 * Methods to make Phone number more readable.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Helper
 */
App::uses('AppHelper', 'View/Helper');
App::uses('PhoneNumber', 'Utility');

/**
 * Phone number helper used to make Phone number more readable.
 *
 * @package app.View.Helper
 */
class PhonenumberHelper extends AppHelper
{

    /**
     * Stores the PhoneNumber() utility object.
     *
     * @var object
     */
    public $PhoneNumber = null;

    /**
     * Constructor
     *
     * @param View $View The View this helper is being attached to.
     * @param array $settings Configuration settings for the helper.
     */
    public function __construct(View $View, $settings = [])
    {
        parent::__construct($View, $settings);
        $this->PhoneNumber = new PhoneNumber();
    }

    /**
     * Return formatted phone number
     *
     * @param string $phoneNumber Phone number for formatting
     * @param string $countryCode Region that we are expecting the number to be from.
     * @param string $format The PhoneNumberFormat the phone number should be formatted into.
     * @return string Return formatted phone number, or input phone number on failure.
     */
    public function format($phoneNumber = '', $countryCode = '', $format = 'INTERNATIONAL')
    {
        return $this->PhoneNumber->format($phoneNumber, $countryCode, $format);
    }

    /**
     * Return carrier name for the given phone number
     *
     * @param string $phoneNumber Phone number for which we want to get a carrier name.
     * @param string $countryCode Region that we are expecting the number to be from.
     * @param string $languageCode The language code in which the name should be written.
     * @return string String a carrier name for the given phone number, or input phone number
     *  on failure.
     */
    public function getNameForNumber($phoneNumber = '', $countryCode = '', $languageCode = '')
    {
        return $this->PhoneNumber->getNameForNumber($phoneNumber, $countryCode, $languageCode);
    }

    /**
     * Return text description for the given language code for the given phone number
     *
     * @param string $phoneNumber Phone number for which we want to get a text description .
     * @param string $countryCode Region that we are expecting the number to be from.
     * @param string $languageCode The language code for which the description should be written.
     * @return string String a text description for the given language code for the given
     *  phone number, or input phone number on failure.
     */
    public function getDescriptionForNumber($phoneNumber = '', $countryCode = '', $languageCode = '')
    {
        return $this->PhoneNumber->getDescriptionForNumber($phoneNumber, $countryCode, $languageCode);
    }
}
