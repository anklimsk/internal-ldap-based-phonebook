<?php
/**
 * This file is the model file of the application. Used for
 *  management alternate mobile phone numbers.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Model
 */

App::uses('OthermobileDb', 'CakeLdap.Model');

/**
 * The model is used to obtain information about alternate
 *  mobile phone numbers.
 *
 * @package app.Model
 */
class Othermobile extends OthermobileDb {

/**
 * Name of the model.
 *
 * @var string
 * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#name
 */
	public $name = 'Othermobile';

/**
 * Custom database table name, or null/false if no table association is desired.
 *
 * @var string
 */
	public $useTable = 'othermobiles';

}
