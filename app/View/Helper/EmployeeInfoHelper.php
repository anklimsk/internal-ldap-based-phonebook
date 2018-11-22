<?php
/**
 * This file is the helper file of the application.
 * Employee information helper.
 * Methods to make employee data more readable.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Helper
 */
App::uses('EmployeeInfoBaseHelper', 'CakeLdap.View/Helper');
App::uses('Language', 'CakeBasicFunctions.Utility');
App::uses('Hash', 'Utility');

/**
 * Employee information helper used to make employee
 *  data more readable.
 *
 * @package app.View.Helper
 */
class EmployeeInfoHelper extends EmployeeInfoBaseHelper {

/**
 * Stores country code for parsing telephone
 *  number in format ISO 3166-1.
 *
 * @var string
 */
	protected $_countryCode = 'US';

/**
 * Stores language code of current UI language
 *  in format ISO 639-1.
 *
 * @var string
 */
	protected $_languageCode = 'en';

/**
 * Stores telephone number format for parsing telephone,
 *  should be one of: `E164`, `RFC3966`, `INTERNATIONAL` or `NATIONAL`.
 *
 * @var string
 * @see https://github.com/giggsey/libphonenumber-for-php
 */
	protected $_libPhoneNumberFormat = 'INTERNATIONAL';

/**
 * Constructor
 *
 * @param View $View The View this helper is being attached to.
 * @param array $settings Configuration settings for the helper.
 */
	public function __construct(View $View, $settings = []) {
		$this->helpers[] = 'Phonenumber';
		$language = new Language();
		$this->_languageCode = $language->getCurrentUiLang(true);

		$countryCode = Configure::read(PROJECT_CONFIG_NAME . '.CountryCode');
		if (!empty($countryCode)) {
			$this->_countryCode = $countryCode;
		}

		$libPhoneNumberFormat = Configure::read(PROJECT_CONFIG_NAME . '.NumberFormat');
		if (!empty($libPhoneNumberFormat)) {
			$this->_libPhoneNumberFormat = $libPhoneNumberFormat;
		}
		parent::__construct($View, $settings);
	}

/**
 * Return country code.
 *
 * @return string Return country code.
 */
	protected function _getCountryCode() {
		return $this->_countryCode;
	}

/**
 * Return language code.
 *
 * @return string Return language code.
 */
	protected function _getLanguageCode() {
		return $this->_languageCode;
	}

/**
 * Return telephone number format.
 *
 * @return string Return telephone number format.
 */
	protected function _getLibPhoneNumberFormat() {
		return $this->_libPhoneNumberFormat;
	}

/**
 * Return formatted telephone number with additional
 *  information.
 *
 * @param string $data Data for processing
 * @param bool $isDescription If True, return description
 *  for telephone number. Otherwise return name of telephone number.
 * @return string Return formatted telephone number with
 *  additional information.
 */
	protected function _getInfoForTelephone($data = null, $isDescription = true) {
		if (empty($data)) {
			$result = $this->ViewExtension->showEmpty($data);

			return $result;
		} else {
			$data = strip_tags($data);
		}

		$countryCode = $this->_getCountryCode();
		$languageCode = $this->_getLanguageCode();
		$libPhoneNumberFormat = $this->_getLibPhoneNumberFormat();

		$telephoneText = $this->Phonenumber->format($data, $countryCode, $libPhoneNumberFormat);
		if ($isDescription) {
			$tooltipText = $this->Phonenumber->getDescriptionForNumber($data, $countryCode, $languageCode);
		} else {
			$tooltipText = $this->Phonenumber->getNameForNumber($data, $countryCode, $languageCode);
		}

		if (empty($tooltipText)) {
			return $telephoneText;
		}

		$result = $this->Html->tag(
			'abbr',
			$telephoneText,
			[
				'title' => $tooltipText,
				'data-toggle' => 'tooltip'
			]
		);

		return $result;
	}

/**
 * Return formatted telephone number with description.
 *
 * @param string $data Data for processing
 * @param bool $returnTableRow If True, return result for row of table.
 *  Otherwise, return as list item.
 * @return string Return formatted telephone number with
 *  description.
 */
	protected function _getValueForTelephoneDescription($data = null, $returnTableRow = true) {
		return $this->_getInfoForTelephone($data, true);
	}

/**
 * Return formatted telephone number with name.
 *
 * @param string $data Data for processing
 * @param bool $returnTableRow If True, return result for row of table.
 *  Otherwise, return as list item.
 * @return string Return formatted telephone number with name.
 */
	protected function _getValueForTelephoneName($data = null, $returnTableRow = true) {
		return $this->_getInfoForTelephone($data, false);
	}

/**
 * Return department name with full name.
 *
 * @param string $data Data for processing
 * @param bool $returnTableRow If True, return result for row of table.
 *  Otherwise, return as list item.
 * @param array $fullData Full data of employee.
 * @return string Return department name with full name.
 */
	protected function _getValueForDepartmentName($data = null, $returnTableRow = true, $fullData = null) {
		$departmentName = $this->ViewExtension->showEmpty($data);
		if (empty($fullData) || !is_array($fullData)) {
			$fullData = [];
		}
		$departmentExtension = Hash::get($fullData, 'DepartmentExtension.name');
		if (empty($departmentExtension)) {
			return $departmentName;
		} elseif (empty($data)) {
			return $departmentExtension;
		}

		if ($returnTableRow) {
			$result = $this->Html->tag(
				'abbr',
				$departmentName,
				[
					'title' => $departmentExtension,
					'data-toggle' => 'tooltip'
				]
			);
		} else {
			$result = $departmentExtension;
			if ($departmentExtension !== $departmentName) {
				$result .= ' (' . $departmentName . ')';
			}
		}

		return $result;
	}
}
