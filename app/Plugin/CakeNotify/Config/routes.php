<?php
/**
 * Routes configuration
 *
 * CakeNotify: Sending email from CakePHP using task queues
 * @copyright Copyright 2017, Andrey Klimov.
 * @package plugin.Config
 */

	Router::parseExtensions();
	Router::setExtensions('json', 'sse');
