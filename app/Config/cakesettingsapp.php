<?php
/**
 * This file configures settings application
 *
 * To modify these parameters, copy this file into your own CakePHP APP/Config directory.
 * CakeSettingsApp: Manage settings of application.
 * @copyright Copyright 2016-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Config
 */

$config['CakeSettingsApp'] = [
	// The application settings key. Used in `Configure::read('Key')`
	// See https://book.cakephp.org/2.0/en/development/configuration.html#Configure::read
	'configKey' => PROJECT_CONFIG_NAME,
	// Use configuration UI of SMTP
	'configSMTP' => true,
	// Use configuration UI of Autocomplete limit
	'configAcLimit' => true,
	// Use configuration UI of Search base for LDAP
	'configADsearch' => true,
	// Use configuration UI of External authentication
	'configExtAuth' => true,
	// Setting users with role and prefix
	'authGroups' => [
		USER_ROLE_SECRETARY => [
			'field' => 'SecretaryGroupMember',
			'name' => __('secretary'),
			'prefix' => 'secret'
		],
		USER_ROLE_HUMAN_RESOURCES => [
			'field' => 'HumanResourcesGroupMember',
			'name' => __('human resources'),
			'prefix' => 'hr'
		],
		USER_ROLE_ADMIN => [
			'field' => 'AdminGroupMember',
			'name' => __('administrator'),
			'prefix' => 'admin'
		]
	],
	// List of languages for UI in format: key - ISO 639-1, value - ISO 639-2
	'UIlangs' => [
		'US' => 'eng',
		'RU' => 'rus',
	],
	// Custom settings scheme
	'schema' => [
		'ShowDefaultPhoto' => ['type' => 'boolean', 'default' => false],
		'DefaultSearchAnyPart' => ['type' => 'boolean', 'default' => false],
		'CountryCode' => ['type' => 'string', 'default' => 'US'],
		'NumberFormat' => ['type' => 'string', 'default' => 'INTERNATIONAL'],
		'ManagerGroupDeferredSave' => ['type' => 'integer', 'default' => 0],
		'ExtendedFields' => ['type' => 'string', 'default' => ''],
		'ReadOnlyFields' => ['type' => 'string', 'default' => ''],
		'UseLdapOnEdit' => ['type' => 'boolean', 'default' => false],
		'MultipleValueLimit' => ['type' => 'integer', 'default' => 0],
	],
	// List of fields with multiple value
	'serialize' => [
		'ExtendedFields',
		'ReadOnlyFields',
	],
	// List of alias for value of setting
	'alias' => [
		'AutocompleteLimit' => [
			'CakeSearchInfo.AutocompleteLimit',
			'CakeTheme.ViewExtension.AutocompleteLimit',
		],
		'Company' => [
			'CakeLdap.LdapSync.Company'
		],
		'SearchBase' => [
			'CakeLdap.LdapSync.SearchBase'
		],
		'EmailContact' => [
			'Config.adminEmail'
		],
		'EmailNotifyUser' => [
			'Email.live'
		],
		'DefaultSearchAnyPart' => [
			'CakeSearchInfo.DefaultSearchAnyPart'
		]
	]
];
