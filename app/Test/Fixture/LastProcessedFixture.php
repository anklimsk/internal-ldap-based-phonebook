<?php
/**
 * LastProcessed Fixture
 */
class LastProcessedFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'last_processed';

/**
 * Fields
 *
 * @var array
 */
	public $fields = [
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
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'id' => LAST_PROCESSED_DEFERRED_SAVE,
			'last_proc_id' => '2',
			'modified' => '2017-11-16 12:00:01'
		],
		[
			'id' => LAST_PROCESSED_EMPLOYEE,
			'last_proc_id' => '5',
			'modified' => '2017-11-16 17:03:00'
		],
	];
}
