<?php
/**
 * This file is the bootstrap file of the plugin.
 * Definition constants for plugin.
 *
 * CakeSpreadsheet: Generate MS Excel files with CakePHP.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package plugin.Config
 */

/**
 * File extension
 *
 * Used for set file extension. Default value `xlsx`
 */
if (!defined('CAKE_SPREADSHEET_FILE_EXTENSION')) {
	define('CAKE_SPREADSHEET_FILE_EXTENSION', 'xlsx');
}

/**
 * PhpSpreadsheet writer
 *
 * Used for set PhpSpreadsheet writer. Default value `Xlsx`
 */
if (!defined('CAKE_SPREADSHEET_PHPSPREADSHEET_WRITER')) {
	define('CAKE_SPREADSHEET_PHPSPREADSHEET_WRITER', 'Xlsx');
}
