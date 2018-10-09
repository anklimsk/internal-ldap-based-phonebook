<?php
/**
 * Plugin model for CakePHP.
 *
 * CakeNotify: Sending email from CakePHP using task queues
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.Model
 */

App::uses('AppModel', 'Model');

/**
 * Plugin model for CakePHP.
 *
 * @package plugin.Model
 */
class CakeNotifyAppModel extends AppModel {

/**
 * Name of the validation string domain to use when translating validation errors.
 *
 * @var array
 */
	public $validationDomain = 'cake_notify_validation_errors';
}
