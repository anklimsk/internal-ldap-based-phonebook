<?php
/**
 * This file is the model file of the plugin.
 * Methods for management settings of application.
 *
 * CakeSettingsApp: Manage settings of application.
 * @copyright Copyright 2016-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package plugin.Model
 */

App::uses('SettingBase', 'CakeSettingsApp.Model');

/**
 * Setting for CakeSettingsApp.
 *
 * @package plugin.Model
 */
class Setting extends SettingBase {

/**
 * Name of the model.
 *
 * @var string
 * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#name
 */
	public $name = 'Setting';

/**
 * List of validation rules. It must be an array with the field name as key and using
 * as value one of the following possibilities
 *
 * @var array
 * @link http://book.cakephp.org/2.0/en/models/model-attributes.html#validate
 * @link http://book.cakephp.org/2.0/en/models/data-validation.html
 */
	public $validate = [];

/**
 * Return extended variables for form of application settings
 *
 * @return array Extended variables
 */
	public function getVars() {
		$variables = [];

		return $variables;
	}
}
