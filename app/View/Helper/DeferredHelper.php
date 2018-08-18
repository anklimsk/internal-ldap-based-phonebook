<?php
/**
 * This file is the helper file of the application.
 * Deferred Helper.
 * Methods to make deferred save data more readable.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Helper
 */
App::uses('AppHelper', 'View/Helper');
App::uses('Hash', 'Utility');

/**
 * Deferred save helper used to make deferred save
 *  data more readable.
 *
 * @package app.View.Helper
 */
class DeferredHelper extends AppHelper
{

    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    public $helpers = [
        'Html',
        'EmployeeInfo',
    ];

    /**
     * Preparing information of deferred save for displaying.
     *
     * @param array $data Data for preparing
     * @return array Return prepared data
     */
    protected function _prepareDeferredData($data = null)
    {
        $result = [];
        if (empty($data) || !is_array($data)) {
            return $result;
        }

        foreach ($data as $field => $value) {
            $bindModelName = null;
            switch ($field) {
                case CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO:
                    if (!empty($value) && !isBinary($value)) {
                        $value = base64_decode($value);
                    }
                    $result['Employee'][$field] = $value;
                    break;
                case CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER:
                    if (empty($value)) {
                        $value = [
                            'id' => '',
                            CAKE_LDAP_LDAP_DISTINGUISHED_NAME => '',
                            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => '',
                            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => '',
                        ];
                    }
                    $result['Manager'] = $value;
                    break;
                case CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT:
                    $result['Department']['value'] = $value;
                    break;
                case CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER:
                    $bindModelName = 'Othertelephone';
                    // no break
                case CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER:
                    if (empty($bindModelName)) {
                        $bindModelName = 'Othermobile';
                    }
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    foreach ($value as $valueItem) {
                        $result[$bindModelName][]['value'] = $valueItem;
                    }
                    break;
                default:
                    $result['Employee'][$field] = $value;
            }
        }

        return $result;
    }

    /**
     * Preparing label for deferred save fields.
     *
     * @param array $fieldsLabel List of labels for
     *  deferred save fields.
     * @param array $data Data of deferred save
     * @return array Return list of uses labels.
     */
    protected function _prepareFieldsLabel($fieldsLabel = null, $data = null)
    {
        $result = [];
        if (empty($fieldsLabel) || !is_array($fieldsLabel)) {
            return $result;
        }

        if (empty($data) || !is_array($data)) {
            return $fieldsLabel;
        }

        foreach ($fieldsLabel as $fieldPath => $fieldLabel) {
            if (Hash::check($data, $fieldPath)) {
                $result[$fieldPath] = $fieldLabel;
            }
        }

        return $result;
    }

    /**
     * Return list of rendered items information of deferred save
     *
     * @param array $data Changed data of deferred save.
     * @param array $localData Current data of deferred save.
     * @param array $fieldsLabel Labels for fields.
     * @param array $fieldsConfig Configuration of fields.
     * @return string Return list of rendered items information of
     *  deferred save.
     */
    public function getDeferredInfo($data = null, $localData = null, $fieldsLabel = null, $fieldsConfig = null)
    {
        if (empty($localData)) {
            $localData = [];
        }

        $emptyText = $this->EmployeeInfo->getEmptyText();
        if (empty($data)) {
            return $emptyText;
        }

        $descriptionList = '';
        $employeeData = $this->_prepareDeferredData($data);
        $fieldsLabel = $this->_prepareFieldsLabel($fieldsLabel, $employeeData);
        $employeeInfo = $this->EmployeeInfo->getInfo($employeeData, $fieldsLabel, $fieldsConfig, [], false);
        $employeeInfoLocal = [];
        if (!empty($localData)) {
            $employeeLocalData = $this->_prepareDeferredData($localData);
            $employeeInfoLocal = $this->EmployeeInfo->getInfo($employeeLocalData, $fieldsLabel, $fieldsConfig, [], false);
        }
        foreach ($employeeInfo as $label => $info) {
            if (isset($employeeInfoLocal[$label])) {
                $info = $employeeInfoLocal[$label] . ' &rarr; ' . $info;
            }
            $descriptionList .= $this->Html->tag('dt', $label . ':');
            $descriptionList .= $this->Html->tag('dd', $info);
        }
        if (!empty($descriptionList)) {
            $result = $this->Html->tag('dl', $descriptionList, ['class' => 'dl-horizontal list-compact']);
        } else {
            $result = $emptyText;
        }

        return $result;
    }
}
