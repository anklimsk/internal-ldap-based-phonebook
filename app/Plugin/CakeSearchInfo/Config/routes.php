<?php
/**
 * Routes configuration
 *
 * CakeSearchInfo: Search information in project database
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.Config
 */

	Router::connect(
		'/search',
		['controller' => 'search', 'action' => 'index', 'plugin' => 'cake_search_info']
	);
	Router::connect(
		'/search/:action/*',
		['controller' => 'search', 'plugin' => 'cake_search_info']
	);

	Router::parseExtensions();
	Router::setExtensions('json');
