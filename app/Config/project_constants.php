<?php
/**
 * This file is constants definition file of the application.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2019, Andrey Klimov.
 * @package app.Config
 */

/**
 * Name of project
 *
 * Used for set project name in main menu and in E-mail template.
 *  Translate domain - `project`.
 */
if (!defined('PROJECT_NAME')) {
	define('PROJECT_NAME', 'Internal phone book');
}

/**
 * Title of page
 *
 * Used for set title of page. Translate domain - `project`.
 *  Default value `Project title`
 */
if (!defined('PROJECT_PAGE_TITLE')) {
	define('PROJECT_PAGE_TITLE', PROJECT_NAME);
}

/**
 * Project author
 *
 * Used for set project author in footer of page.
 *  Default value ``
 */
if (!defined('PROJECT_AUTHOR')) {
	define('PROJECT_AUTHOR', '&copy; 2017-2019, <a href="https://anklimsk.github.io/internal-ldap-based-phonebook">Andrey Klimov</a>.');
}

/**
 * Version of project
 *
 * Used for set project version in footer of page.
 *  Default value ``
 */
if (!defined('PROJECT_VERSION')) {
	define('PROJECT_VERSION', '1.0.9');
}

/**
 * Name of project without space char
 *
 * Used for set configure key. Default value `Project`
 */
if (!defined('PROJECT_CONFIG_NAME')) {
	define('PROJECT_CONFIG_NAME', 'Phonebook');
}

/**
 * Name of image file organization logo
 *
 * Used on title page in exported PDF file. Size `200` X `200` px.
 *  Default value `org_logo.png`
 */
if (!defined('ORG_LOGO_IMAGE')) {
	define('ORG_LOGO_IMAGE', 'org_logo.png');
}

/**
 * Name of image file organization logo
 *
 * Used in the main menu of application. Size `32` X `32` px.
 *  Default value `project-logo.png`
 */
if (!defined('PROJECT_LOGO_IMAGE_SMALL')) {
	define('PROJECT_LOGO_IMAGE_SMALL', 'project-logo.png');
}

/**
 * Global Query limit
 *
 * Used for set global find limit, if needed. Default value `1000`
 */
if (!defined('GLOBAL_QUERY_LIMIT')) {
	define('GLOBAL_QUERY_LIMIT', 5000);
}

/**
 * Multiple value field rows limit
 *
 * Used for set multiple value field input rows limit. Default value `15`
 */
if (!defined('MULTIPLE_VALUE_FIELD_ROWS_LIMIT')) {
	define('MULTIPLE_VALUE_FIELD_ROWS_LIMIT', 15);
}

/**
 * Birthday list limit
 *
 * Is used to set the limit on the list of birthdays for notifications.
 *  Default value `20`
 */
if (!defined('BIRTHDAY_LIST_LIMIT')) {
	define('BIRTHDAY_LIST_LIMIT', 20);
}

/**
 * Birthday list show lines limit
 *
 * Is used to set the limit lines on show list of
 *  birthdays for notifications. Default value `5`
 */
if (!defined('BIRTHDAY_LIST_SHOW_LINES_LIMIT')) {
	define('BIRTHDAY_LIST_SHOW_LINES_LIMIT', 5);
}

/**
 * Group process deferred save limit
 *
 * Is used to set the limit for process group deferred save.
 *  Default value `60`
 */
if (!defined('DEFERRED_SAVE_GROUP_PROCESS_LIMIT')) {
	define('DEFERRED_SAVE_GROUP_PROCESS_LIMIT', 60);
}

/**
 * Limit of new deferred saves in email
 *
 * Is used to set the limit for list of new deferred save in email.
 *  Default value `10`
 */
if (!defined('DEFERRED_SAVE_CHECK_NEW_EMAIL_LIST_LIMIT')) {
	define('DEFERRED_SAVE_CHECK_NEW_EMAIL_LIST_LIMIT', 3);
}

/**
 * Limit for exported to PDF or Excel files data
 *
 * Used for set limit data for export files. Default value `1000`
 */
if (!defined('EXPORT_DATA_LIMIT')) {
	define('EXPORT_DATA_LIMIT', 1000);
}

/**
 * Limit of length for label item header in exported files
 *
 * Used for set limit of length for label item header
 *  in exported files. Default value `10`
 */
if (!defined('EXPORT_LABEL_ITEM_LENGTH_LIMIT')) {
	define('EXPORT_LABEL_ITEM_LENGTH_LIMIT', 10);
}

/**
 * Bit mask for user role `User`
 *
 * Default role for authorized user.
 * Used for set user role. Default value `1`
 */
if (!defined('USER_ROLE_USER')) {
	define('USER_ROLE_USER', 1);
}

/**
 * Bit mask for user role `Secretary`
 *
 * Role for Secretaries
 * Used for set user role. Default value `2`
 */
if (!defined('USER_ROLE_SECRETARY')) {
	define('USER_ROLE_SECRETARY', 2);
}

/**
 * Bit mask for user role `Human resources`
 *
 * Role for Human resources
 * Used for set user role. Default value `4`
 */
if (!defined('USER_ROLE_HUMAN_RESOURCES')) {
	define('USER_ROLE_HUMAN_RESOURCES', 4);
}

/**
 * Bit mask for user role `Admin`
 *
 * Role for Administrators
 * Used for set user role. Default value `8`
 */
if (!defined('USER_ROLE_ADMIN')) {
	define('USER_ROLE_ADMIN', 8);
}

/**
 * Type to generate the export file - `all`
 *
 * Used for set generate type param. Default value `all`
 */
if (!defined('GENERATE_FILE_DATA_TYPE_ALL')) {
	define('GENERATE_FILE_DATA_TYPE_ALL', 'all');
}

/**
 * Type to generate the export file - `by alphabet`
 *
 * Used for set generate type param. Default value `alph`
 */
if (!defined('GENERATE_FILE_DATA_TYPE_ALPH')) {
	define('GENERATE_FILE_DATA_TYPE_ALPH', 'alph');
}

/**
 * Type to generate the export file - `by departments`
 *
 * Used for set generate type param. Default value `depart`
 */
if (!defined('GENERATE_FILE_DATA_TYPE_DEPART')) {
	define('GENERATE_FILE_DATA_TYPE_DEPART', 'depart');
}

/**
 * View for generate the exported files - `all`
 *
 * Used for set generate view param. Default value `all`
 */
if (!defined('GENERATE_FILE_VIEW_TYPE_ALL')) {
	define('GENERATE_FILE_VIEW_TYPE_ALL', 'all');
}

/**
 * View for generate the exported files - `PDF`
 *
 * Used for set generate view param. Default value `pdf`
 */
if (!defined('GENERATE_FILE_VIEW_TYPE_PDF')) {
	define('GENERATE_FILE_VIEW_TYPE_PDF', 'pdf');
}

/**
 * View for generate the exported files - `MS Excel`
 *
 * Used for set generate view param. Default value `excel`
 */
if (!defined('GENERATE_FILE_VIEW_TYPE_EXCEL')) {
	define('GENERATE_FILE_VIEW_TYPE_EXCEL', 'excel');
}

/**
 * Cache configuration for store statistics information
 *
 * Used for access to cached data of for store statistics information.
 *  Default value `statistics_info`
 */
if (!defined('CACHE_KEY_STATISTICS_INFO')) {
	define('CACHE_KEY_STATISTICS_INFO', 'statistics_info');
}

/**
 * Cache configuration for store application configuration information
 *
 * Used for access to cached data of for store application configuration
 *  information. Default value `appcfg_info`
 */
if (!defined('CACHE_KEY_EXT_CFG_INFO')) {
	define('CACHE_KEY_EXT_CFG_INFO', 'extcfg_info');
}

/**
 * Cache configuration for store information about employees
 *
 * Used for access to cached data of for store information about employees.
 *  Default value `employees_local_info`
 */
if (!defined('CACHE_KEY_EMPLOYEES_LOCAL_INFO')) {
	define('CACHE_KEY_EMPLOYEES_LOCAL_INFO', 'employees_local_info');
}

/**
 * Cache configuration for store information about departments
 *
 * Used for access to cached data of for store information about departments.
 *  Default value `departments_local_info`
 */
if (!defined('CACHE_KEY_DEPARTMENTS_LOCAL_INFO')) {
	define('CACHE_KEY_DEPARTMENTS_LOCAL_INFO', 'departments_local_info');
}

/**
 * Time limit for renaming department for console task
 *
 * Used for set time limit of rename department. Default value `30`
 */
if (!defined('TASK_RENAME_DEPARTMENT_TIME_LIMIT')) {
	define('TASK_RENAME_DEPARTMENT_TIME_LIMIT', 30);
}

/**
 * Time limit for generate export files for console task
 *
 * Used for set time limit of generate export files. Default value `120`
 */
if (!defined('TASK_EXPORT_GENERATE_TIME_LIMIT')) {
	define('TASK_EXPORT_GENERATE_TIME_LIMIT', 240);
}

/**
 * Time limit for generate PDF files
 *
 * Used for set time limit of generate export files. Default value `120`
 */
if (!defined('PDF_GENERATE_TIME_LIMIT')) {
	define('PDF_GENERATE_TIME_LIMIT', 120);
}

/**
 * Time limit for generate Excel files
 *
 * Used for set time limit of generate export files. Default value `120`
 */
if (!defined('EXCEL_GENERATE_TIME_LIMIT')) {
	define('EXCEL_GENERATE_TIME_LIMIT', 120);
}

/**
 * Time limit for check state tree of departments
 *
 * Used for set time limit of check state tree of departments. Default value `60`
 */
if (!defined('CHECK_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT')) {
	define('CHECK_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT', 60);
}

/**
 * Time limit for recover tree of departments
 *
 * Used for set time limit of recover tree of departments. Default value `120`
 */
if (!defined('RECOVER_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT')) {
	define('RECOVER_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT', 120);
}

/**
 * Time limit for reorder tree of departments
 *
 * Used for set time limit of reorder tree of departments. Default value `120`
 */
if (!defined('REORDER_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT')) {
	define('REORDER_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT', 120);
}

/**
 * Time limit for processing group of deferred saves
 *
 * Used for set time limit of processing group deferred saves. Default value `120`
 */
if (!defined('DEFERRED_SAVE_GROUP_PROCESS_TIME_LIMIT')) {
	define('DEFERRED_SAVE_GROUP_PROCESS_TIME_LIMIT', 120);
}

/**
 * Line width in PDF file
 *
 * Used for scale cell in PDF files. Default value `180`
 */
if (!defined('PDF_LINE_WIDTH')) {
	define('PDF_LINE_WIDTH', 180);
}

/**
 * Width of employee photo
 *
 * Used for set width of uploaded photo, and width of rendered photo.
 *  Default value `200` px
 */
if (!defined('PHOTO_WIDTH')) {
	define('PHOTO_WIDTH', 200);
}

/**
 * Height of employee photo
 *
 * Used for set height of uploaded photo, and height of rendered photo.
 *  Default value `200` px
 */
if (!defined('PHOTO_HEIGHT')) {
	define('PHOTO_HEIGHT', 200);
}

/**
 * Full path to directory for import
 *
 * Used for store imported files. Default value `/tmp/import`
 */
if (!defined('UPLOAD_DIR')) {
	define('UPLOAD_DIR', TMP . 'import' . DS);
}

/**
 * Allowed extensions of files for upload (PCRE)
 *
 * Used for checking imported files on server. Default value `/\.(jpe?g)$/i`
 */
if (!defined('UPLOAD_FILE_TYPES_SERVER')) {
	define('UPLOAD_FILE_TYPES_SERVER', '/\.(jpe?g)$/i');
}

/**
 * Allowed extensions of files for upload (PCRE)
 *
 * Used for checking imported files on client. Default value `(\.|\/)(jpe?g)$`
 */
if (!defined('UPLOAD_FILE_TYPES_CLIENT')) {
	define('UPLOAD_FILE_TYPES_CLIENT', '(\.|\/)(jpe?g)$');
}

/**
 * Limit size of uploaded files
 *
 * Used for set limit size for uploaded files, bytes. Default value `1Mb`
 */
if (!defined('UPLOAD_FILE_SIZE_LIMIT')) {
	define('UPLOAD_FILE_SIZE_LIMIT', 1024 * 1024);
}

/**
 * Full path to directory for export
 *
 * Used for store exported files. Default value `/tmp/export`
 */
if (!defined('EXPORT_DIR')) {
	define('EXPORT_DIR', TMP . 'export' . DS);
}

/**
 * Group action `Delete` for deferred save
 *
 * Used for deleting group of deferred saves. Default value `1`
 */
if (!defined('GROUP_ACTION_DEFERRED_SAVE_DELETE')) {
	define('GROUP_ACTION_DEFERRED_SAVE_DELETE', 1);
}

/**
 * Group action `Approve` for deferred save
 *
 * Used for approving group of deferred saves. Default value `2`
 */
if (!defined('GROUP_ACTION_DEFERRED_SAVE_APPROVE')) {
	define('GROUP_ACTION_DEFERRED_SAVE_APPROVE', 2);
}

/**
 * Group action `Reject` for deferred save
 *
 * Used for rejecting group of deferred saves. Default value `3`
 */
if (!defined('GROUP_ACTION_DEFERRED_SAVE_REJECT')) {
	define('GROUP_ACTION_DEFERRED_SAVE_REJECT', 3);
}

/**
 * Group action `Approve` for internal deferred save
 *
 * Used for approving group of internal deferred saves. Default value `4`
 */
if (!defined('GROUP_ACTION_DEFERRED_SAVE_INTERNAL_APPROVE')) {
	define('GROUP_ACTION_DEFERRED_SAVE_INTERNAL_APPROVE', 4);
}

/**
 * Group action `Delete` for log
 *
 * Used for deleting group of logs. Default value `1`
 */
if (!defined('GROUP_ACTION_LOG_DELETE')) {
	define('GROUP_ACTION_LOG_DELETE', 1);
}

/**
 * ID record of last processed deferred save
 *
 * Used for checking new not processed deferred saves.
 *  Default value `1`
 */
if (!defined('LAST_PROCESSED_DEFERRED_SAVE')) {
	define('LAST_PROCESSED_DEFERRED_SAVE', 1);
}

/**
 * ID record of last processed employee
 *
 * Used for checking new not processed employee.
 *  Default value `2`
 */
if (!defined('LAST_PROCESSED_EMPLOYEE')) {
	define('LAST_PROCESSED_EMPLOYEE', 2);
}

/**
 * The task of the shell `cron` used for checking new deferred save
 *
 * Used for set name of command. Default value `deferred`
 */
if (!defined('SHELL_CRON_TASK_DEFFERED')) {
	define('SHELL_CRON_TASK_DEFFERED', 'deferred');
}

/**
 * The task of the shell `cron` used for generate files export
 *
 * Used for set name of command. Default value `generate`
 */
if (!defined('SHELL_CRON_TASK_GENERATE')) {
	define('SHELL_CRON_TASK_GENERATE', 'generate');
}

/**
 * Time delay for start synchronize information after deferred saves
 *
 * Used for set time delay for start synchronize information after deferred
 *  saves. Default value `30`
 */
if (!defined('DEFERRED_SAVE_SYNC_DELAY')) {
	define('DEFERRED_SAVE_SYNC_DELAY', 30);
}
