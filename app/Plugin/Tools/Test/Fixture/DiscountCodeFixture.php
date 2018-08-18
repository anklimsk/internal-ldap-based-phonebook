<?php
/* DiscountCode Fixture generated on: 2011-11-20 21:59:00 : 1321822740 */

/**
 * DiscountCodeFixture
 *
 */
class DiscountCodeFixture extends CakeTestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary', 'collate' => null, 'comment' => ''],
		'discount_id' => ['type' => 'integer', 'null' => true, 'default' => null, 'length' => 10, 'collate' => null, 'comment' => ''],
		'code' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 20, 'collate' => 'utf8_unicode_ci', 'comment' => '', 'charset' => 'utf8'],
		'used' => ['type' => 'boolean', 'null' => false, 'default' => '0', 'collate' => null, 'comment' => ''],
		'model' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 30, 'collate' => 'utf8_unicode_ci', 'comment' => '', 'charset' => 'utf8'],
		'foreign_id' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'utf8_unicode_ci', 'comment' => '', 'charset' => 'utf8'],
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
			'id' => '1',
			'discount_id' => '1',
			'code' => 'f7f3ac78268226',
			'used' => 1,
			'model' => 'Order',
			'foreign_id' => '4ec034e9-f0e0-48ff-aaa2-201c7cb063f2',
			'created' => '2011-11-10 18:07:46',
			'modified' => '2011-11-14 01:09:06'
		],
		[
			'id' => '2',
			'discount_id' => '1',
			'code' => '5b62f7111sss',
			'used' => 1,
			'model' => 'Order',
			'foreign_id' => '4ec51a1c-2cc0-4674-8587-18907cb063f2',
			'created' => '2011-11-17 11:58:52',
			'modified' => '2011-11-17 15:48:24'
		],
		[
			'id' => '3',
			'discount_id' => '1',
			'code' => 'xxxxxxxxxx',
			'used' => 0,
			'model' => '',
			'foreign_id' => '',
			'created' => '2011-11-17 17:30:17',
			'modified' => '2011-11-17 17:30:17'
		],
	];
}
