<?php
/**
 * This file is the schema file of the plugin.
 *  Use for database management.
 *
 * CakeTheme: Set theme for application.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.Model
 */

App::uses('ConnectionManager', 'Model');

/**
 * Schema for CakeTheme.
 *
 * @package plugin.Config.Schema
 */
class CakeThemeSchema extends CakeSchema
{

    /**
     * Before callback to be implemented in subclasses.
     *
     * Actions:
     *  - Disabling cached available tables and schema descriptions.
     * @param array $event Schema object properties.
     * @return bool Should process continue.
     */
    public function before($event = [])
    {
        $ds = ConnectionManager::getDataSource($this->connection);
        $ds->cacheSources = false;

        return true;
    }

    /**
     * Schema of database table `logs`.
     *
     * @var array
     */
    public $logs = [
        'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'unsigned' => true, 'key' => 'primary'],
        'title' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'description' => ['type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'change' => ['type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'model' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 40, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'foreign_id' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'action' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 40, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'user_id' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
        'created' => ['type' => 'datetime', 'null' => false, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => false, 'default' => null],
        'indexes' => [
            'PRIMARY' => ['column' => 'id', 'unique' => 1]
        ],
        'tableParameters' => ['charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB']
    ];
}
