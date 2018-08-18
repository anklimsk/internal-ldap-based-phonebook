<?php
/**
 * Plugin model for CakePHP.
 *
 * CakeSettingsApp: Manage settings of application.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.Model
 */

App::uses('AppModel', 'Model');

/**
 * Plugin model for CakePHP.
 *
 * @package plugin.Model
 */
class CakeSettingsAppAppModel extends AppModel
{

    /**
     * Name of the validation string domain to use when translating validation errors.
     *
     * @var string
     */
    public $validationDomain = 'cake_settings_app_validation_errors';

    /**
     * List of behaviors to load when the model object is initialized. Settings can be
     * passed to behaviors by using the behavior name as index. Eg:
     *
     * public $actsAs = array('Translate', 'MyBehavior' => array('setting1' => 'value1'))
     *
     * @var array
     * @link http://book.cakephp.org/2.0/en/models/behaviors.html#using-behaviors
     */
    public $actsAs = [
        'CakeConfigPlugin.InitConfig' => [
            'pluginName' => 'CakeSettingsApp'
        ]
    ];
}
