<?php
/**
 * This file is the view file of the application. Used for render
 *  left form for edit settings of application
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

if (!isset($varsExt) || !is_array($varsExt)) {
    $varsExt = [];
}

if (!isset($countryCodePhoneLib)) {
    $countryCodePhoneLib = [];
}

if (!isset($numberFormatList)) {
    $numberFormatList = [];
}

if (!empty($varsExt)) {
    extract($varsExt);
}

    echo $this->Form->inputs([
        'legend' => __('Photo'),
        'Setting.ShowDefaultPhoto' => ['label' => [__('Show default photo'),
            __('Show default photo, if the photo is not specified'), ':'],
            'type' => 'checkbox'],
    ]);
    echo $this->Form->inputs([
        'legend' => __('Phone library'),
        'Setting.CountryCode' => ['label' => [__('Country'),
            __('Country code for parse telephone number'), ':'],
            'type' => 'flag'],
        'Setting.NumberFormat' => ['label' => [__('Format of telephone number'),
            __('Display format for telephone or mobile phone number'), ':'],
            'type' => 'select', 'options' => $numberFormatList]
    ]);
    echo $this->Form->inputs([
        'legend' => __('Deferred saves'),
        'Setting.ManagerGroupDeferredSave' => ['label' => [__('Group of users for management deferred saves'),
            __('A group of users serving deferred saves: approving, rejecting, editing and deleting.'), ':'],
            'type' => 'select', 'options' => $groupDeferredSaveList],
    ]);
