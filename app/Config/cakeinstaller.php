<?php
/**
 * This file configures installer
 *
 * To modify these parameters, copy this file into your own CakePHP APP/Config directory.
 * CakeInstaller: Installer of CakePHP web application.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.Config
 */

$config['CakeInstaller'] = [
    // Version of PHP for check
    'PHPversion' => [
        [
            // Version
            '5.4.0',
            // Operator
            '>='
        ],
    ],
    // PHP Extension for check
    'PHPextensions' => [
        [
            // Extension name
            'pdo',
            // Critical need
            true
        ],
        [
            // Extension name
            'ldap',
            // Critical need
            true
        ],
        [
            // Extension name
            'gd',
            // Critical need
            true
        ],
        [
            // Extension name
            'exif',
            // Critical need
            true
        ],
        [
            // Extension name
            'xml',
            // Critical need
            true
        ],
        [
            // Extension name
            'zip',
            // Critical need
            true
        ],
/*
        array(
            // Extension name
            'runkit',
            // Critical need
            false
        ),
*/
    ],
    // Commands for installer
    'installerCommands' => [
        // Set Installer UI language
        'setuilang',
        // Checking PHP environment
        'check',
        // Set file system permissions on the temporary directory
        'setdirpermiss',
        // Set security key
        'setsecurkey',
        // Set timezone
        'settimezone',
        // Set base URL
        'setbaseurl',
        // Configure database
        'configdb',
        // Check connect to database
        'connectdb',
        // Create database and initialize data
        'createdb',
        // Create symlinks to files
        'createsymlinks',
        // Create cron jobs
        'createcronjobs',
        // Install this application
        'install',
    ],
    // Tasks for action - install
    'installTasks' => [
        // Set Installer UI language
        'setuilang',
        // Checking PHP environment
        'check',
        // Set file system permissions on the temporary directory
        'setdirpermiss',
        // Set security key
        'setsecurkey',
        // Set timezone
        'settimezone',
        // Set base URL
        'setbaseurl',
        // Configure database connections
        'configdb',
        // Check connect to database
        'connectdb',
        // Create database and initialize data
        'createdb',
        // Create symlinks to files
        'createsymlinks',
        // Create cron jobs
        'createcronjobs',
    ],
    // List of database connection for configure
    'configDBconn' => [
        // Main connection for application
        'default',
        // LDAP connection for application
        'ldap',
        // Test connection for application
        //'test',
    ],
    'customConnections' => [
/*
        // Name of connection (property of class DATABASE_CONFIG)
        'connectionName' => array(
            // Name of connection parameter
            'paramName' => array(
                // Label of parameter. If empty, use default label or parameter name
                'label' => 'label of param',
                // Value of parameter. If exists, skip next options
                'value' => 'value of param',
                // Default value of parameter (empty console input in interactive mode)
                'defaultValue' => 'default value of param',
                // Allow empty value of parameter
                'alowEmpty' => false,
                // List of variants for console input parameter value
                // Format I:
                'options' => array('value 1', 'value 2'),
                // Format II:
                'options' => array('label of variant 1' => 'value of variant 1', 'y' => true),
                // PCRE pattern for validation console input parameter value
                'validationPattern' => '/\w{2,}\@\w{2,}\.\w{2,}/',
            )
        ),
*/
        'ldap' => [
            'datasource' => [
                'value' => 'CakeLdap.LdapExtSource',
            ],
            'persistent' => [],
            'host' => [
                'defaultValue' => '',
                'label' => __d('cake_installer_label', 'LDAP host'),
            ],
            'port' => [
                'defaultValue' => 389,
            ],
            'login' => [
                'defaultValue' => '',
                'label' => __d('cake_installer_label', 'User principal name (user@fabrikam.com)'),
                'validationPattern' => '/\w{2,}\@\w{2,}\.\w{2,}/',
            ],
            'password' => [
                'alowEmpty' => false,
            ],
            'database' => [
                'value' => '',
            ],
            'basedn' => [
                'label' => __d('cake_installer_label', 'The DN of the search base (DC=fabrikam,DC=com)'),
                'validationPattern' => '/^([a-z][a-z0-9-]*)=(?![ #])(((?![\\="+,;<>]).)|(\\[ \\#="+,;<>])|(\\[a-f0-9][a-f0-9]))*(,([a-z][a-z0-9-]*)=(?![ #])(((?![\\="+,;<>]).)|(\\[ \\#="+,;<>])|(\\[a-f0-9][a-f0-9]))*)*$/i',
            ],
            'type' => [
                'defaultValue' => 'ActiveDirectory',
                'label' => __d('cake_installer_label', 'LDAP server type'),
                'options' => ['ActiveDirectory', 'OpenLDAP', 'Netscape'],
            ],
            'tls' => [
                'defaultValue' => 'n',
                'label' => __d('cake_installer_label', 'Use TLS?'),
                'options' => ['n' => false, 'y' => true],
            ],
            'version' => [
                'defaultValue' => 3,
                'label' => __d('cake_installer_label', 'Version of LDAP protocol'),
                'options' => [2, 3],
            ],
        ],
    ],
    // List of symlinks for creation in format:
    // key - link; value - target.
    'schemaCreationList' => [
        'sessions',
        '-p Queue',
        '-p CakeLdap'
    ],
    // List of additional schemes for checking exists in
    // database
    'schemaCheckingList' => [
        'sessions',
        '-p Queue',
    ],
    // List of symlinks for creation in format:
    // key - link; value - target.
    'symlinksCreationList' => [
        APP . 'webroot' . DS . 'cake_theme' => APP . 'Plugin' . DS . 'CakeTheme' . DS . 'webroot',
        APP . 'webroot' . DS . 'cake_installer' => APP . 'Plugin' . DS . 'CakeInstaller' . DS . 'webroot',
    ],
    // List of cron job for creation in format:
    // key - command; value - start time.
    'cronJobs' => [
        'cd ' . APP . ' && Console/cake Queue.Queue runworker -q' => '*/10 * * * *',
        'cd ' . APP . ' && Console/cake CakeLdap.cron sync -q' => '10 7 * * *',
        'cd ' . APP . ' && Console/cake generate all all -q' => '0 7 * * mon',
        'cd ' . APP . ' && Console/cake cron deferred -q' => '*/15 * * * *',
    ],
    // List of languages for installer UI in format: ISO639-2
    'UIlangList' => [
        'eng',
        'rus',
    ]
];
