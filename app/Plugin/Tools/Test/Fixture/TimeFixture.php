<?php
/* Time Fixture generated on: 2011-11-20 21:59:39 : 1321822779 */

/**
 * TimeFixture
 *
 */
class TimeFixture extends CakeTestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary', 'collate' => null, 'comment' => ''],
		'name' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 30, 'collate' => 'utf8_unicode_ci', 'comment' => 'noon, evening, ...', 'charset' => 'utf8'],
		'slug' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 20, 'collate' => 'utf8_unicode_ci', 'comment' => '', 'charset' => 'utf8'],
		'min_hour' => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => 2, 'collate' => null, 'comment' => 'inclusivly'],
		'max_hour' => ['type' => 'integer', 'null' => false, 'default' => '0', 'length' => 2, 'collate' => null, 'comment' => 'inclusivly'],
		'description' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_unicode_ci', 'comment' => '', 'charset' => 'utf8'],
		'price' => ['type' => 'float', 'null' => false, 'default' => null, 'length' => '8,2', 'collate' => null, 'comment' => ''],
		'price_premium' => ['type' => 'float', 'null' => false, 'default' => null, 'length' => '8,2', 'collate' => null, 'comment' => ''],
		'created' => ['type' => 'datetime', 'null' => true, 'default' => null, 'collate' => null, 'comment' => ''],
		'modified' => ['type' => 'datetime', 'null' => true, 'default' => null, 'collate' => null, 'comment' => ''],
		'indexes' => ['PRIMARY' => ['column' => 'id', 'unique' => 1]],
		'tableParameters' => []
	];

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = [
		[
			'id' => '2',
			'name' => 'Abend',
			'slug' => 'evening',
			'min_hour' => '16',
			'max_hour' => '24',
			'description' => '',
			'price' => '9.90',
			'price_premium' => '12.00',
			'created' => '2011-03-09 14:47:54',
			'modified' => '2011-10-07 15:13:29'
		],
		[
			'id' => '1',
			'name' => 'Mittag',
			'slug' => 'noon',
			'min_hour' => '10',
			'max_hour' => '16',
			'description' => '',
			'price' => '6.90',
			'price_premium' => '9.90',
			'created' => '2011-03-09 14:47:54',
			'modified' => '2011-10-07 15:13:06'
		],
	];
}
