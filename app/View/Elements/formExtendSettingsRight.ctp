<?php
/**
 * This file is the view file of the application. Used for render
 *  right form for edit settings of application
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

if (!isset($varsExt) || !is_array($varsExt)) {
    $varsExt = [];
}

if (!isset($extendViewFieldsList)) {
    $extendViewFieldsList = [];
}

if (!isset($readOnlyFieldsList)) {
    $readOnlyFieldsList = [];
}

if (!empty($varsExt)) {
    extract($varsExt);
}

    echo $this->Form->inputs([
        'legend' => __('Search information'),
        'Setting.DefaultSearchAnyPart' => ['label' => [__('Search in any part string'),
            __('Default value for flag of search in any part string'), ':'],
            'type' => 'checkbox'],
    ]);
    echo $this->Form->inputs([
        'legend' => __('Editing information'),
        'Setting.UseLdapOnEdit' => ['label' => [__('Use information from LDAP'),
            __('Use information from LDAP server instead database on editing'), ':'],
            'type' => 'checkbox'],
        'Setting.MultipleValueLimit' => ['label' => __('Limit number of rows for multiple value') . ':',
            'title' => __('Limit number of rows for fields with multiple value'),
            'type' => 'spin', 'min' => 3,
            'max' => MULTIPLE_VALUE_FIELD_ROWS_LIMIT, 'step' => 1,
            'maxboostedstep' => 5, 'verticalbuttons' => true,
        ],
    ]);
    echo $this->Form->inputs([
        'legend' => __('Fields'),
        'Setting.ExtendedFields' => ['label' => [__('Extended fields'),
            __('Fields available for user with role: secretary, human resources or administrator'), ':'],
            'type' => 'select', 'options' => $extendViewFieldsList, 'multiple' => true],
        'Setting.ReadOnlyFields' => ['label' => [__('Read only fields'),
            __('Fields available only for read'), ':'], 'type' => 'select',
            'options' => $readOnlyFieldsList, 'multiple' => true],
    ]);
