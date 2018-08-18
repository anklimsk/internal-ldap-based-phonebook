<?php
/**
 * This file is the model file of the application.
 * Methods for management settings of application.
 *
 * CakeSettingsApp: Manage settings of application.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package app.Model
 */

App::uses('SettingBase', 'CakeSettingsApp.Model');
App::uses('ClassRegistry', 'Utility');
App::uses('PhoneNumber', 'Utility');

/**
 * Setting for CakeSettingsApp.
 *
 * @package app.Model
 */
class Setting extends SettingBase
{

    /**
     * Name of the model.
     *
     * @var string
     * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#name
     */
    public $name = 'Setting';

    /**
     * List of validation rules. It must be an array with the field name as key and using
     * as value one of the following possibilities
     *
     * @var array
     * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#validate
     * @link http://book.cakephp.org/2.0/en/models/data-validation.html
     */
    public $validate = [
        'ShowDefaultPhoto' => [
            'rule' => 'boolean',
            'message' => 'Incorrect value for checkbox',
            'required' => true,
            'allowEmpty' => true,
        ],
        'DefaultSearchAnyPart' => [
            'rule' => 'boolean',
            'message' => 'Incorrect value for checkbox',
            'required' => true,
            'allowEmpty' => true,
        ],
        'CountryCode' => [
            'rule' => ['lengthBetween', 2, 2],
            'message' => 'This field must contain a valid country code',
            'required' => true,
            'allowEmpty' => false,
        ],
        'NumberFormat' => [
            'rule' => ['inList', ['E164', 'INTERNATIONAL', 'NATIONAL', 'RFC3966']],
            'message' => 'This field must contain a valid format of telephone number',
            'required' => true,
            'allowEmpty' => false,
        ],
        'MultipleValueLimit' => [
            'rule' => ['range', 3, MULTIPLE_VALUE_FIELD_ROWS_LIMIT],
            'message' => 'This field must contain a valid limit for rows of multiple value fields between %d and %d',
            'required' => true,
            'allowEmpty' => false,
        ],
    ];

    /**
     * Return extended variables for form of application settings
     *
     * @return array Extended variables
     */
    public function getVars()
    {
        $phoneNumber = new PhoneNumber();
        $numberFormatList = [];
        $groupDeferredSaveList = [];
        $listFormats = $phoneNumber->getListFormats();
        if (!empty($listFormats)) {
            $listFormats = array_keys($listFormats);
            $numberFormatList = array_combine($listFormats, $listFormats);
            translArray($numberFormatList, 'number_format');
        }
        $modelConfigSettingsApp = ClassRegistry::init('CakeSettingsApp.ConfigSettingsApp');
        $authGroupsList = $modelConfigSettingsApp->getAuthGroups();
        $groupDeferredSaveAllow = ['hr', 'admin'];
        if (!empty($authGroupsList)) {
            foreach ($authGroupsList as $userRole => $userInfo) {
                if (!in_array($userInfo['prefix'], $groupDeferredSaveAllow)) {
                    continue;
                }

                $groupDeferredSaveList[$userRole] = mb_ucfirst($userInfo['name']);
            }
        }
        $variables = compact('numberFormatList', 'groupDeferredSaveList');

        $modelConfigSync = ClassRegistry::init('CakeLdap.ConfigSync');
        $ldapFieldsInfo = $modelConfigSync->getLdapFieldsInfo();
        $excludeLdapFields = [
            CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
            CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
            CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY,
            CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
        ];
        $ldapFieldsInfo = array_diff_key($ldapFieldsInfo, array_flip($excludeLdapFields));
        $listFieldsLdap = [];
        foreach ($ldapFieldsInfo as $fieldName => $fieldInfo) {
            $listFieldsLdap[$fieldName] = (isset($fieldInfo['label']) ? $fieldInfo['label'] : $fieldName);
        }
        asort($listFieldsLdap);

        $variables['countryCodePhoneLib'] = $this->getConfig('CountryCode');

        $variables['extendViewFieldsList'] = $listFieldsLdap;
        $variables['readOnlyFieldsList'] = $listFieldsLdap;

        return $variables;
    }
}
