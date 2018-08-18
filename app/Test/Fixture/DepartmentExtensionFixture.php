<?php
/**
 * DepartmentExtension Fixture
 */
class DepartmentExtensionFixture extends CakeTestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'],
        'department_id' => ['type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false],
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
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 1,
            'department_id' => 1,
            'parent_id' => null,
            'lft' => 1,
            'rght' => 2,
            'name' => 'Управление инженерных изысканий'
        ],
        [
            'id' => 2,
            'department_id' => 2,
            'parent_id' => null,
            'lft' => 3,
            'rght' => 4,
            'name' => 'Отдел связи'
        ],
        [
            'id' => 3,
            'department_id' => 3,
            'parent_id' => null,
            'lft' => 5,
            'rght' => 6,
            'name' => 'Отдел информационных технологий'
        ],
        [
            'id' => 4,
            'department_id' => 4,
            'parent_id' => null,
            'lft' => 7,
            'rght' => 8,
            'name' => 'Отдел распределительных сетей'
        ],
        [
            'id' => 5,
            'department_id' => 5,
            'parent_id' => null,
            'lft' => 9,
            'rght' => 10,
            'name' => 'Автотранспортный отдел'
        ],
        [
            'id' => 6,
            'department_id' => 6,
            'parent_id' => null,
            'lft' => 11,
            'rght' => 12,
            'name' => 'Охрана Труда'
        ],
        [
            'id' => 7,
            'department_id' => 7,
            'parent_id' => null,
            'lft' => 13,
            'rght' => 14,
            'name' => 'Строительный отдел'
        ],
    ];
}
