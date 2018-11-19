<?php
/**
 * This file contain configure for testing
 *
 * To modify parameters, copy this file into your own CakePHP APP/Test directory.
 * CakeExtendTest: Extended test tools for CakePHP.
 * @copyright Copyright 2016-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Test
 */
$config['Config'] = [
    'language' => 'eng',
    'adminEmail' => '',
];
$config[PROJECT_CONFIG_NAME] = [
    'EmailContact' => 'test@localhost.local',
    'EmailSubject' => 'Phonebook',
    'Company' => 'ТестОрг',
    'AutocompleteLimit' => 30,
    'ExternalAuth' => false,
    'SecretaryGroupMember' => 'CN=Web.Secretary,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com',
    'HumanResourcesGroupMember' => 'CN=Web.HumanResources,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com',
    'AdminGroupMember' => 'CN=Web.Admin,OU=Web,OU=Soft,OU=Группы,DC=fabrikam,DC=com',
    'EmailSmtphost' => 'localhost',
    'EmailSmtpport' => 25,
    'EmailSmtpuser' => '',
    'EmailNotifyUser' => false,
    'EmailSmtppassword' => '',
    'ShowDefaultPhoto' => true,
    'DefaultSearchAnyPart' => true,
    'CountryCode' => 'BY',
    'NumberFormat' => 'NATIONAL',
    'ManagerGroupDeferredSave' => USER_ROLE_ADMIN,
    'ExtendedFields' => serialize([CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY, CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER, CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID, CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER, CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER, CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION]),
    'ReadOnlyFields' => serialize([CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME]),
    'UseLdapOnEdit' => false,
    'MultipleValueLimit' => 4,
    'BirthdayNotifyPeriod' => 8,
];
$config['CakeSearchInfo'] = [
    'AutocompleteLimit' => 30,
    'DefaultSearchAnyPart' => true,
];
$config['CakeTheme'] = [
    'ViewExtension' => [
        'AutocompleteLimit' => 30,
    ]
];
$config['Email'] = [
    'live' => false,
];
$config['CakeLdap'] = [
    'LdapSync' => [
        'LdapFields' => [
            CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID => [
                'label' => __d('app_ldap_field_name', 'GUID'),
                'altLabel' => __d('app_ldap_field_name', 'GUID'),
                'priority' => 20,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect GUID of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                    'isUnique' => [
                        'rule' => ['isUnique'],
                        'message' => __d('cake_ldap_validation_errors', 'GUID of employee is not unique'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_DISTINGUISHED_NAME => [
                'label' => __d('app_ldap_field_name', 'Distinguished name'),
                'altLabel' => __d('app_ldap_field_name', 'Disting. name'),
                'priority' => 21,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect distinguished name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                    'isUnique' => [
                        'rule' => ['isUnique'],
                        'message' => __d('cake_ldap_validation_errors', 'Distinguished name of employee is not unique'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
                'label' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'altLabel' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'priority' => 1,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect full name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME => [
                'label' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'altLabel' => __dx('app_ldap_field_name', 'employee', 'Name'),
                'priority' => 2,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => '(a{2,} a.[ ]a.|a.[ ]a. a{2,})'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Display name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_INITIALS => [
                'label' => __d('app_ldap_field_name', 'Initials'),
                'altLabel' => __d('app_ldap_field_name', 'Init.'),
                'priority' => 24,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a.[ ]a.'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Initials name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_SURNAME => [
                'label' => __d('app_ldap_field_name', 'Surname'),
                'altLabel' => __d('app_ldap_field_name', 'Surn.'),
                'priority' => 3,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect last name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Surname of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_GIVEN_NAME => [
                'label' => __d('app_ldap_field_name', 'Given name'),
                'altLabel' => __d('app_ldap_field_name', 'Giv. name'),
                'priority' => 4,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect first name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Given name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MIDDLE_NAME => [
                'label' => __d('app_ldap_field_name', 'Middle name'),
                'altLabel' => __d('app_ldap_field_name', 'Mid. name'),
                'priority' => 5,
                'truncate' => false,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect middle name of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => 'a{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Middle name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_TITLE => [
                'label' => __d('app_ldap_field_name', 'Position'),
                'altLabel' => __d('app_ldap_field_name', 'Pos.'),
                'priority' => 15,
                'truncate' => true,
                'rules' => [
                    'notBlank' => [
                        'rule' => ['notBlank'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect position of employee'),
                        'allowEmpty' => false,
                        'required' => true,
                        'last' => true
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Position of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION => [
                'label' => __d('app_ldap_field_name', 'Subdivision'),
                'altLabel' => __d('app_ldap_field_name', 'Subdiv.'),
                'priority' => 14,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)\#\№]{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Subdivision of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_DEPARTMENT => [
                'label' => __d('app_ldap_field_name', 'Department'),
                'altLabel' => __d('app_ldap_field_name', 'Depart.'),
                'priority' => 13,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-regex' => '[0-9A-Za-z\u0410-\u044f\u0401\u0451\xc0-\xff\xb5\-\.\s\(\)]{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Department of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_TELEPHONE_NUMBER => [
                'label' => __d('app_ldap_field_name', 'Internal telephone'),
                'altLabel' => __d('app_ldap_field_name', 'Int. tel.'),
                'priority' => 8,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => '9{4}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Interoffice telephone of employee. Format: %s, where X - number from 0 to 9', 'XXXX')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_TELEPHONE_NUMBER => [
                'label' => __d('app_ldap_field_name', 'Landline telephone'),
                'altLabel' => __d('app_ldap_field_name', 'Land. tel.'),
                'priority' => 9,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'phone'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Local telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MOBILE_TELEPHONE_NUMBER => [
                'label' => __d('app_ldap_field_name', 'Mobile telephone'),
                'altLabel' => __d('app_ldap_field_name', 'Mob. tel.'),
                'priority' => 10,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'phone'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Mobile telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OTHER_MOBILE_TELEPHONE_NUMBER => [
                'label' => __d('app_ldap_field_name', 'Personal mobile telephone'),
                'altLabel' => __d('app_ldap_field_name', 'Person. mob. tel.'),
                'priority' => 11,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'phone'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Other mobile telephone of employee. Format: E.164, e.g. %s, where X - number from 0 to 9', '+XXXXXXXXXXXX')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_OFFICE_NAME => [
                'label' => __d('app_ldap_field_name', 'Office room'),
                'altLabel' => __d('app_ldap_field_name', 'Office'),
                'priority' => 12,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => [
                    'data-inputmask-mask' => '(9{1,4}[a{1}])|(9{1,4}-9{1})',
                    'data-inputmask-greedy' => 'false'
                ],
                'tooltip' => __d('app_ldap_field_tooltip', 'Office room of employee. Format: %s, where X - number from 0 to 9, L - letter', 'X(L)')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MAIL => [
                'label' => __d('app_ldap_field_name', 'E-mail'),
                'altLabel' => __d('app_ldap_field_name', 'E-mail'),
                'priority' => 6,
                'truncate' => false,
                'rules' => [
                    'email' => [
                        'rule' => ['email'],
                        'message' => __d('cake_ldap_validation_errors', 'Incorrect E-mail address'),
                        'allowEmpty' => true,
                        'required' => false,
                        'last' => false,
                    ],
                ],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'email'],
                'tooltip' => __d('app_ldap_field_tooltip', 'E-mail of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_MANAGER => [
                'label' => __d('app_ldap_field_name', 'Manager'),
                'altLabel' => __d('app_ldap_field_name', 'Manag.'),
                'priority' => 16,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'tooltip' => __d('app_ldap_field_tooltip', 'Manager of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO => [
                'label' => __d('app_ldap_field_name', 'Photo'),
                'altLabel' => __d('app_ldap_field_name', 'Photo'),
                'priority' => 22,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'tooltip' => __d('app_ldap_field_tooltip', 'Photo of employee %dpx X %dpx in JPEG format', PHOTO_WIDTH, PHOTO_HEIGHT)
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_COMPUTER => [
                'label' => __d('app_ldap_field_name', 'Computer'),
                'altLabel' => __d('app_ldap_field_name', 'Comp.'),
                'priority' => 18,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'tooltip' => __d('app_ldap_field_tooltip', 'Computer of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_EMPLOYEE_ID => [
                'label' => __d('app_ldap_field_name', 'Employee ID'),
                'altLabel' => __d('app_ldap_field_name', 'Empl. ID'),
                'priority' => 19,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => '9{1,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Employee ID. Format: %s, where X - number from 0 to 9', 'X')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_COMPANY => [
                'label' => __d('app_ldap_field_name', 'Company name'),
                'altLabel' => __d('app_ldap_field_name', 'Comp. name'),
                'priority' => 23,
                'truncate' => true,
                'rules' => [],
                'default' => null,
                'tooltip' => __d('app_ldap_field_tooltip', 'Company name of employee')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_BIRTHDAY => [
                'label' => __d('app_ldap_field_name', 'Birthday'),
                'altLabel' => __d('app_ldap_field_name', 'Birthd.'),
                'priority' => 17,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-alias' => 'yyyy-mm-dd'],
                'tooltip' => __d('app_ldap_field_tooltip', 'Date of birthday. Format: %s, where YYYY - year, MM - month and DD - day', 'YYYY-MM-DD')
            ],
            CAKE_LDAP_LDAP_ATTRIBUTE_SIP_PHONE => [
                'label' => __d('app_ldap_field_name', 'SIP telephone'),
                'altLabel' => __d('app_ldap_field_name', 'SIP tel.'),
                'priority' => 7,
                'truncate' => false,
                'rules' => [],
                'default' => null,
                'inputmask' => ['data-inputmask-mask' => '9{2,}'],
                'tooltip' => __d('app_ldap_field_tooltip', 'SIP telephone. Format: %s, where X - number from 0 to 9', 'XX')
            ],
        ],
        'Limits' => [
            'Query' => 1000,
            'Sync' => 5000,
        ],
        'TreeSubordinate' => [
            'Enable' => true,
            'Draggable' => true
        ],
        'Company' => 'ТестОрг',
        'Delete' => [
            'Departments' => false,
            'Employees' => false,
        ],
        'Query' => [
            'UseFindByLdapMultipleFields' => true
        ],
    ]
];
