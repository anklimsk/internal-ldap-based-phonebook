<?php
/**
 * Routes configuration
 *
 * CakeSpreadsheet: Generate MS Excel files with CakePHP.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package plugin.Config
 */

	Router::parseExtensions();
	Router::setExtensions([CAKE_SPREADSHEET_FILE_EXTENSION]);
