<?php
/**
 * Routes configuration
 *
 * CakeTheme: Set theme for application.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.Config
 */

	Router::parseExtensions();
	Router::setExtensions(['json', 'sse', 'pop', 'mod', 'prt']);
