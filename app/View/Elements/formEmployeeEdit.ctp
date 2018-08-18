<?php
/**
 * This file is the view file of the application. Used for render
 *  form for editing employee
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Elements
 */

if (!isset($fieldsLabel)) {
    $fieldsLabel = [];
}

if (!isset($fieldsLabelAlt)) {
    $fieldsLabelAlt = [];
}

if (!isset($fieldsInputMask)) {
    $fieldsInputMask = [];
}

if (!isset($fieldsInputTooltip)) {
    $fieldsInputTooltip = [];
}

if (!isset($readOnlyFields)) {
    $readOnlyFields = [];
}

if (!isset($managers)) {
    $managers = [];
}

if (!isset($departments)) {
    $departments = [];
}

if (!isset($changedFields)) {
    $changedFields = [];
}

if (!isset($maxLinesMultipleValue)) {
    $maxLinesMultipleValue = (int)MULTIPLE_VALUE_FIELD_ROWS_LIMIT;
}

if (!isset($dn)) {
    $dn = '';
}

    $inputDefaultOptions = [
        'data-toggle' => 'tooltip',
        'type' => 'text',
        'autocomplete' => 'off',
    ];

    $inputAutocompleteOptions = [
        'type' => 'autocomplete',
        'url' => '/cake_theme/filter/autocomplete.json',
        'min-length' => CAKE_SEARCH_INFO_QUERY_SEARCH_MIN_LENGTH,
    ];

    $modelName = 'EmployeeEdit';

    $labelDefault = [$modelName . '.' . CAKE_LDAP_LDAP_DISTINGUISHED_NAME => ''];
    $fieldsLabel += $labelDefault;
    $fieldsLabelAlt += $labelDefault;

    $legend = null;
    $inputStatic = [
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => []
    ];
    $inputList = [
        $modelName . '.' . CAKE_LDAP_LDAP_DISTINGUISHED_NAME => ['type' => 'hidden'],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => ['type' => 'hidden'],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => ['type' => 'hidden'],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => ['type' => 'hidden'],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => ['data-autocomplete-type' => 'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME] +
            $inputAutocompleteOptions,
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => ['data-autocomplete-type' => 'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME] +
            $inputAutocompleteOptions,
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => ['data-autocomplete-type' => 'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE] +
            $inputAutocompleteOptions,
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => ['data-autocomplete-type' => 'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION] +
            $inputAutocompleteOptions,
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => ['label' => [null, __('Department of employee')],
            'options' => $departments, 'type' => 'select',
            'empty' => __('Select department')],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => ['type' => 'dateSelect'],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [],
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => [
            'label' => [null, __('Manager of employee')],
            'options' => $managers,
            'type' => 'select',
            'empty' => __('Select manager'),
            'data-abs-ajax-url' => $this->Html->url(['controller' => 'employees', 'action' => 'managers', 'ext' => 'json']),
            'data-abs-ajax-data' => json_encode(['q' => '{{{q}}}', 'dn' => $dn]),
            'data-abs-min-length' => 2,
        ],
    ];

    $tabsListFull = [
        'Name' => [
            $modelName . '.' . CAKE_LDAP_LDAP_DISTINGUISHED_NAME,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME,
        ],
        'E-mail' => [
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MAIL,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER,
        ],
        'SIP' => [
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER,
        ],
        'Office' => [
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_TITLE,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER,
        ],
        'Birthday' => [
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER,
            $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY,
        ],
    ];

    $fieldsProcessList = [
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => 'E-mail',
        $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => 'SIP',
    ];

    $inputList = array_intersect_key($inputList, $fieldsLabel);
    foreach ($inputList as $inputFieldName => &$inputOptions) {
        if (isset($inputOptions['type']) && ($inputOptions['type'] === 'hidden')) {
            continue;
        }
        if (isset($fieldsLabel[$inputFieldName])) {
            if (isset($inputOptions['label'])) {
                if (is_array($inputOptions['label'])) {
                    $inputOptions['label'][0] = $fieldsLabel[$inputFieldName];
                } else {
                    $inputOptions['label'] = $fieldsLabel[$inputFieldName] . '&nbsp;' . $inputOptions['label'];
                }
            } else {
                $inputOptions['label'] = $fieldsLabel[$inputFieldName];
            }
            if (is_array($inputOptions['label'])) {
                $inputOptions['label'][2] = ':';
            } else {
                $inputOptions['label'] .= ':';
            }
        }
        if (isset($fieldsInputMask[$inputFieldName]) && is_array($fieldsInputMask[$inputFieldName]) &&
            (!isset($inputOptions['type']) || (isset($inputOptions['type']) && ($inputOptions['type'] === 'text')))) {
            foreach ($fieldsInputMask[$inputFieldName] as $dataAttr => $mask) {
                if (ctype_digit((string)$dataAttr)) {
                    continue;
                }

                $inputOptions[$dataAttr] = $mask;
            }
        }
        if (isset($fieldsInputTooltip[$inputFieldName])) {
            if (isset($inputOptions['type']) && ($inputOptions['type'] === 'select')) {
                if (isset($inputOptions['label']) && is_array($inputOptions['label'])) {
                    $inputOptions['label'][1] = $fieldsInputTooltip[$inputFieldName];
                }
            } else {
                $inputOptions['title'] = $fieldsInputTooltip[$inputFieldName];
            }
        }
        if (in_array($inputFieldName, $readOnlyFields)) {
            $inputOptions['readonly'] = true;
        }
        if (in_array($inputFieldName, $changedFields)) {
            $inputOptions['div'] = 'form-group has-warning';
        }
        $inputOptions += $inputDefaultOptions;

        if (!isset($fieldsProcessList[$inputFieldName])) {
            continue;
        }

        $tabName = $fieldsProcessList[$inputFieldName];
        $this->Html->setEntity($inputFieldName);
        list($inputModel, $inputField) = $this->Html->entity();
        $inputOptionsSpecific = [
            'required' => false,
            'errorMessage' => false,
            'name' => 'data[' . $inputModel . '][' . $inputField . '][]',
        ];
        if ($this->Form->isFieldError($inputFieldName)) {
            $inputOptionsSpecific['div'] = 'form-group has-error error';
        }
        for ($i = 0; $i < $maxLinesMultipleValue; $i++) {
            if ($i > 0) {
                $inputOptions['label'] = false;
            }
            $inputFieldNameMultiple = $inputFieldName . '.' . $i;
            $inputList[$inputFieldNameMultiple] = $inputOptions + $inputOptionsSpecific;
            $tabsListFull[$tabName][] = $inputFieldNameMultiple;
        }
        unset($inputList[$inputFieldName]);
    }
    unset($inputOptions);

    $inputStatic = array_intersect_key($inputStatic, $fieldsLabel);
    foreach ($inputStatic as $inputFieldName => &$inputOptions) {
        if (isset($fieldsLabel[$inputFieldName])) {
            if (isset($inputOptions['label'])) {
                if (is_array($inputOptions['label'])) {
                    $inputOptions['label'][0] = $fieldsLabel[$inputFieldName];
                } else {
                    $inputOptions['label'] = $fieldsLabel[$inputFieldName] . '&nbsp;' . $inputOptions['label'];
                }
            } else {
                $inputOptions['label'] = $fieldsLabel[$inputFieldName];
            }
        }
        if (!isset($inputOptions['label'])) {
            $inputOptions['label'] = $this->Form->getLabelTextFromField($inputFieldName);
        }
        if (is_array($inputOptions['label'])) {
            $inputOptions['label'][2] = ':';
        } else {
            $inputOptions['label'] .= ':';
        }
    }

    $tabsList = [];
    foreach ($tabsListFull as $tabName => $inputListTab) {
        $tabNameList = [];
        if ($tabName === 'Name') {
            $fieldName = $modelName . '.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME;
            if (isset($fieldsLabel[$fieldName]) && !empty($fieldsLabel[$fieldName])) {
                $tabNameList[] = $fieldsLabel[$fieldName];
            }
        } else {
            foreach ($inputListTab as $fieldName) {
                $sizeTabNameList = count($tabNameList);
                if ($sizeTabNameList == 3) {
                    $tabNameList[] = '...';
                    break;
                }

                if (strrpos($fieldName, '.0', -2) !== false) {
                }
                    $fieldName = rtrim($fieldName, ".0");
                if (isset($fieldsLabelAlt[$fieldName]) && !empty($fieldsLabelAlt[$fieldName])) {
                    $tabNameList[] = $fieldsLabelAlt[$fieldName];
                } elseif (isset($fieldsLabel[$fieldName]) && !empty($fieldsLabel[$fieldName])) {
                    $tabNameList[] = $fieldsLabel[$fieldName];
                }
            }
        }
        if (empty($tabNameList)) {
            $tabNameList[] = $tabName;
        }
        $tabNameFull = implode(', ', $tabNameList);
        if (count(array_intersect($inputListTab, $changedFields)) > 0) {
            $tabNameFull .= '&nbsp;' . $this->ViewExtension->iconTag('fas fa-info-circle fa-lg');
        }
        $tabsList[$tabNameFull] = $inputListTab;
    }

    echo $this->Form->createFormTabs($inputList, $inputStatic, $tabsList, $legend, $modelName);
