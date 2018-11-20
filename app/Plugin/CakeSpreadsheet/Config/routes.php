<?php
/**
 * Routes configuration
 *
 * CakeSpreadsheet: Generate MS Excel files with CakePHP.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package plugin.Config
 */

	Router::parseExtensions();
	Router::setExtensions([CAKE_SPREADSHEET_FILE_EXTENSION]);
