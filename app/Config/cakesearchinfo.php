<?php
/**
 * This file configures installer
 *
 * To modify these parameters, copy this file into your own CakePHP APP/Config directory.
 * CakeSearchInfo: Search information in project database
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.Config
 */

$config['CakeSearchInfo'] = [
    'QuerySearchMinLength' => 2,
    'AutocompleteLimit' => 10,
    'TargetDeep' => 1,
    'DefaultSearchAnyPart' => false,
    'TargetModels' => [],
    'IncludeFields' => [],
];
