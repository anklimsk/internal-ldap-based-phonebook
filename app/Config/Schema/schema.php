<?php
/**
 * Schema database management for CakePHP.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       Cake.Model
 * @since         CakePHP(tm) v 1.2.0.5550
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Class for Schema management.
 *
 * @package       app.Config.Schema
 */
class AppSchema extends CakeSchema {

/**
 * Before callback to be implemented in subclasses.
 *
 * Actions:
 *  - Disabling caching available tables and schema descriptions.
 *
 * @param array $event Schema object properties.
 * @return bool Should process continue.
 */
	public function before($event = []) {
		$ds = ConnectionManager::getDataSource($this->connection);
		$ds->cacheSources = false;

		return true;
	}

/**
 * Schema of table `deferred`.
 *
 * @var array
 */
	public $deferred = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'],
		'employee_id' => ['type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'],
		'internal' => ['type' => 'boolean', 'null' => false, 'default' => null],
		'data' => ['type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
		'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'indexes' => [
			'PRIMARY' => ['column' => 'id', 'unique' => 1],
			'id_UNIQUE' => ['column' => 'id', 'unique' => 1]
		],
		'tableParameters' => ['charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB']
	];

/**
 * Schema of table `department_extensions`.
 *
 * @var array
 */
	public $department_extensions = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'],
		'department_id' => ['type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'],
		'parent_id' => ['type' => 'integer', 'null' => true, 'default' => null, 'length' => 10, 'unsigned' => false],
		'lft' => ['type' => 'integer', 'null' => true, 'default' => null, 'length' => 10, 'unsigned' => false],
		'rght' => ['type' => 'integer', 'null' => true, 'default' => null, 'length' => 10, 'unsigned' => false],
		'name' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
		'indexes' => [
			'PRIMARY' => ['column' => 'id', 'unique' => 1],
			'id_UNIQUE' => ['column' => 'id', 'unique' => 1]
		],
		'tableParameters' => ['charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB']
	];

/**
 * Schema of table `last_processed`.
 *
 * @var array
 */
	public $last_processed = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'],
		'last_proc_id' => ['type' => 'integer', 'null' => true, 'default' => null, 'length' => 10, 'unsigned' => false],
		'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'indexes' => [
			'PRIMARY' => ['column' => 'id', 'unique' => 1],
			'id_UNIQUE' => ['column' => 'id', 'unique' => 1]
		],
		'tableParameters' => ['charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB']
	];

/**
 * Schema of table `logs`.
 *
 * @var array
 */
	public $logs = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'],
		'user_id' => ['type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'],
		'employee_id' => ['type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'],
		'data' => ['type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'],
		'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'indexes' => [
			'PRIMARY' => ['column' => 'id', 'unique' => 1],
			'id_UNIQUE' => ['column' => 'id', 'unique' => 1]
		],
		'tableParameters' => ['charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB']
	];
}
