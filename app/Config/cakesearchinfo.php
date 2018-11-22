<?php
/**
 * This file configures search info
 *
 * To modify these parameters, copy this file into your own CakePHP APP/Config directory.
 * CakeSearchInfo: Search information in project database
 * @copyright Copyright 2016-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Config
 */

$config['CakeSearchInfo'] = [
	'QuerySearchMinLength' => 2,
	'AutocompleteLimit' => 10,
	'TargetDeep' => 1,
	'DefaultSearchAnyPart' => false,
	'TargetModels' => [],
	'IncludeFields' => [],
];
