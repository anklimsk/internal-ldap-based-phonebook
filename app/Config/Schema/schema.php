<?php
class AppSchema extends CakeSchema
{

    public function before($event = [])
    {
        $ds = ConnectionManager::getDataSource($this->connection);
        $ds->cacheSources = false;

        return true;
    }

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
