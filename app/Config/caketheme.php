<?php
/**
 * This file configures main theme
 *
 * To modify these parameters, copy this file into your own CakePHP APP/Config directory.
 * CakeTheme: Set theme for application.
 * @copyright Copyright 2016-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package plugin.Config
 */

$config['CakeTheme'] = [
	'AdditionalFiles' => [
		// List of additional CSS files
		'css' => [],
		// List of additional JS files
		'js' => [],
	],
	'AjaxFlash' => [
		// List of keys for flash messages
		'flashKeys' => [
			'flash',
			'auth',
		],
		// Time out for message types: flash_information, flash_success, flash_notification
		'timeOut' => 30,
		// Delay to delete flash messages
		'delayDeleteFlash' => 2,
		// Register global ajax callback complete() for checking update part of page
		'globalAjaxComplete' => false,
		// Options for 'jQuery.noty' plugin (see http://ned.im/noty/#/about or https://github.com/needim/noty)
		'theme' => 'bootstrap-v3',
		'layout' => 'topRight',
		'open' => 'animated bounceInRight',
		'close' => 'animated bounceOutRight',
	],
	'TourApp' => [
		//  Steps of tour.
		//  See 'Step Options' http://bootstraptour.com/api/
		'Steps' => []
	],
	'ViewExtension' => [
		// Autocomplete limit for filter of table
		'AutocompleteLimit' => 10,
		// Server-Sent Events
		'SSE' => [
			// Default text for Noty message
			'text' => __d('view_extension', 'Waiting to run task'),
			// Labels for data
			'label' => [
				// Task name
				'task' => __d('view_extension', 'Task'),
				// Completed percentage
				'completed' => __d('view_extension', 'Completed'),
				// Message from task
				'message' => __d('view_extension', 'Message')
			],
			// The number of repeated attempts to start pending tasks
			'retries' => 100,
			// Delay to delete task messages
			'delayDeleteTask' => 2,
		],
		// ViewExtension Helper
		'Helper' => [
			// Default FontAwesome icon prefix
			'defaultIconPrefix' => 'fas',
			// Default FontAwesome icon size
			'defaultIconSize' => '',
			// Default Bootstrap button prefix
			'defaultBtnPrefix' => 'btn',
			// Default Bootstrap button size
			'defaultBtnSize' => 'btn-xs',
		],
	]
];
