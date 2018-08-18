<?php
App::uses('MyModel', 'Tools.Model');
App::uses('MyCakeTestCase', 'Tools.TestSuite');

class MyModelTest extends MyCakeTestCase {

	public $Post;

	public $User;

	public $modelName = 'User';

	public $fixtures = ['core.user', 'core.post', 'core.author'];

	public function setUp() {
		parent::setUp();

		$this->Post = ClassRegistry::init('MyAppModelPost');
		$this->User = ClassRegistry::init('MyAppModelUser');
	}

	public function testObject() {
		$this->Post = ClassRegistry::init('MyModel');
		$this->assertTrue(is_object($this->Post));
		$this->assertInstanceOf('MyModel', $this->Post);
	}

	/**
	 * MyModelTest::testGet()
	 *
	 * @return void
	 */
	public function testGet() {
		$record = $this->Post->get(2);
		$this->assertEquals(2, $record['Post']['id']);

		$record = $this->Post->get(2, ['fields' => ['id', 'created']]);
		$this->assertEquals(2, count($record['Post']));

		$record = $this->Post->get(2, ['fields' => ['id', 'title', 'body'], 'contain' => ['Author']]);
		$this->assertEquals(3, count($record['Post']));
		$this->assertEquals(3, $record['Author']['id']);
	}

	/**
	 * MyModelTest::testGetRelatedInUse()
	 *
	 * @return void
	 */
	public function testGetRelatedInUse() {
		$this->Post->Author->displayField = 'user';
		$results = $this->Post->getRelatedInUse('Author', 'author_id', 'list');
		$expected = [1 => 'mariano', 3 => 'larry'];
		$this->assertEquals($expected, $results);
	}

	/**
	 * MyModelTest::testGetFieldInUse()
	 *
	 * @return void
	 */
	public function testGetFieldInUse() {
		$this->db = ConnectionManager::getDataSource('test');
		$this->skipIf(!($this->db instanceof Mysql), 'The test is only compatible with Mysql.');

		$results = $this->Post->getFieldInUse('author_id', 'list');
		$expected = [1 => 'First Post', 2 => 'Second Post'];
		$this->assertEquals($expected, $results);
	}

	/**
	 * MyModelTest::testEnum()
	 *
	 * @return void
	 */
	public function testEnum() {
		$array = [
			1 => 'foo',
			2 => 'bar',
		];

		$res = AppTestModel::enum(null, $array, false);
		$this->assertEquals($array, $res);

		$res = AppTestModel::enum(2, $array, false);
		$this->assertEquals('bar', $res);

		$res = AppTestModel::enum('2', $array, false);
		$this->assertEquals('bar', $res);

		$res = AppTestModel::enum(3, $array, false);
		$this->assertFalse($res);
	}

	/**
	 * Test 3.x shim get()
	 *
	 * @expectedException RecordNotFoundException
	 * @return void
	 */
	public function testGetInvalid() {
		$this->User->order = [];
		$this->User->get('xyz');
	}

	/**
	 * MyModelTest::testRecord()
	 *
	 * @return void
	 */
	public function testRecord() {
		$record = $this->Post->record(2);
		$this->assertEquals(2, $record['Post']['id']);

		$record = $this->Post->record(2, ['fields' => ['id', 'created']]);
		$this->assertEquals(2, count($record['Post']));

		$record = $this->Post->record(2, ['fields' => ['id', 'title', 'body'], 'contain' => ['Author']]);
		$this->assertEquals(3, count($record['Post']));
		$this->assertEquals(3, $record['Author']['id']);
	}

	/**
	 * Test record()
	 *
	 * @return void
	 */
	public function testRecordInvalid() {
		$this->User->order = [];
		$is = $this->User->record('xyz');
		$this->assertSame([], $is);
	}

	/**
	 * Test auto inc value of the current table
	 *
	 * @return void
	 */
	public function testGetNextAutoIncrement() {
		$this->db = ConnectionManager::getDataSource('test');
		$this->skipIf(!($this->db instanceof Mysql), 'The test is only compatible with Mysql.');

		$is = $this->User->getNextAutoIncrement();
		$this->out(returns($is));

		$schema = $this->User->schema('id');
		if ($schema['length'] == 36) {
			$this->assertFalse($is);
		} else {
			$this->assertTrue(is_int($is));
		}
	}

	/**
	 * MyModelTest::testDeconstruct()
	 *
	 * @return void
	 */
	public function testDeconstruct() {
		$data = ['year' => '2010', 'month' => '10', 'day' => 11];
		$res = $this->User->deconstruct('User.dob', $data);
		$this->assertEquals('2010-10-11', $res);

		$res = $this->User->deconstruct('User.dob', $data, 'datetime');
		$this->assertEquals('2010-10-11 00:00:00', $res);
	}

	/**
	 * Test that strings are correctly escaped using '
	 *
	 * @return void
	 */
	public function testEscapeValue() {
		$this->db = ConnectionManager::getDataSource('test');
		$this->skipIf(!($this->db instanceof Mysql), 'The test is only compatible with Mysql.');

		$res = $this->User->escapeValue(4);
		$this->assertSame(4, $res);

		$res = $this->User->escapeValue('4');
		$this->assertSame('4', $res);

		$res = $this->User->escapeValue('a');
		$this->assertSame('\'a\'', $res);

		$res = $this->User->escapeValue(true);
		$this->assertSame(1, $res);

		$res = $this->User->escapeValue(false);
		$this->assertSame(0, $res);

		$res = $this->User->escapeValue(null);
		$this->assertSame(null, $res);

		// comparison to cakes escapeField here (which use ` to escape)
		$res = $this->User->escapeField('dob');
		$this->assertSame('`User`.`dob`', $res);
	}

	/**
	 * @return void
	 */
	public function testUpdate() {
		$record = ['title' => 'x', 'body' => 'bx'];
		$result = $this->Post->save($record);
		$this->assertTrue((bool)$result);

		$record['body'] = 'bxx';
		$result = $this->Post->update($result['Post']['id'], ['body' => $record['body']]);
		$this->assertTrue((bool)$result);

		$this->assertSame($record['body'], $result['Post']['body']);

		$result = $this->Post->get($result['Post']['id']);
		$this->assertSame($record['body'], $result['Post']['body']);
	}

	/**
	 * @return void
	 */
	public function testToggleField() {
		$record = ['title' => 'x', 'body' => 0];
		$result = $this->Post->save($record);
		$this->assertTrue((bool)$result);

		$result = $this->Post->toggleField('body', $result['Post']['id']);
		$this->assertTrue((bool)$result);

		$this->assertSame(1, $result['Post']['body']);

		$result = $this->Post->get($result['Post']['id']);
		$this->assertSame('1', $result['Post']['body']);

		$result = $this->Post->toggleField('body', $result['Post']['id']);
		$this->assertTrue((bool)$result);

		$this->assertSame(0, $result['Post']['body']);

		$result = $this->Post->get($result['Post']['id']);
		$this->assertSame('0', $result['Post']['body']);
	}

	/**
	 * MyModelTest::testSaveAll()
	 *
	 * @return void
	 */
	public function testSaveAll() {
		$records = [
			['title' => 'x', 'body' => 'bx'],
			['title' => 'y', 'body' => 'by'],
		];
		$result = $this->User->saveAll($records);
		$this->assertTrue($result);

		$result = $this->User->saveAll($records, ['atomic' => false]);
		$this->assertTrue($result);

		$result = $this->User->saveAll($records, ['atomic' => false, 'returnArray' => true]);
		$expected = [true, true];
		$this->assertSame($expected, $result);
	}

	/**
	 * MyModelTest::testUpdateAllJoinless()
	 *
	 * @return void
	 */
	public function testUpdateAllJoinless() {
		$db = ConnectionManager::getDataSource($this->Post->useDbConfig);
		$db->getLog();
		$postTable = $db->fullTableName($this->Post->table);
		$authorTable = $db->fullTableName($this->Post->Author->table);

		// Note that the $fields argument needs manual string escaping whereas the $conditions one doesn't!
		$result = $this->Post->updateAll(['title' => '"Foo"'], ['title !=' => 'Foo']);
		$this->assertTrue($result);

		$queries = $db->getLog();
		$expected = 'UPDATE ' . $postTable . ' AS `Post` LEFT JOIN ' . $authorTable . ' AS `Author` ON (`Post`.`author_id` = `Author`.`id`) SET `Post`.`title` = "Foo"  WHERE `title` != \'Foo\'';
		$this->assertSame($expected, $queries['log'][0]['query']);

		// Now joinless
		$result = $this->Post->updateAllJoinless(['title' => '"Foo"'], ['title !=' => 'Foo']);
		$this->assertTrue($result);

		$queries = $db->getLog();
		$expected = 'UPDATE ' . $postTable . ' AS `Post`  SET `Post`.`title` = "Foo"  WHERE `title` != \'Foo\'';
		$this->assertSame($expected, $queries['log'][0]['query']);
	}

	/**
	 * MyModelTest::testDeleteAll()
	 *
	 * @return void
	 */
	public function testDeleteAll() {
		$db = ConnectionManager::getDataSource($this->Post->useDbConfig);
		$db->getLog();
		$postTable = $db->fullTableName($this->Post->table);
		$authorTable = $db->fullTableName($this->Post->Author->table);

		$result = $this->Post->deleteAll(['title !=' => 'Foo']);
		$this->assertTrue($result);

		$queries = $db->getLog();
		$expected = 'SELECT `Post`.`id` FROM ' . $postTable . ' AS `Post` LEFT JOIN ' . $authorTable . ' AS `Author` ON (`Post`.`author_id` = `Author`.`id`)  WHERE `title` != \'Foo\'  GROUP BY `Post`.`id`';
		$this->assertSame($expected, $queries['log'][0]['query']);

		$expected = 'DELETE `Post` FROM ' . $postTable . ' AS `Post`   WHERE `Post`.`id` IN';
		$this->assertContains($expected, $queries['log'][1]['query']);
	}

	/**
	 * MyModelTest::testDeleteAllJoinless()
	 *
	 * @return void
	 */
	public function testDeleteAllJoinless() {
		// Now joinless
		$db = ConnectionManager::getDataSource($this->Post->useDbConfig);
		$db->getLog();
		$postTable = $db->fullTableName($this->Post->table);
		$authorTable = $db->fullTableName($this->Post->Author->table);

		$result = $this->Post->deleteAllJoinless(['title !=' => 'Foo']);
		$this->assertTrue($result);

		$queries = $db->getLog();
		$expected = 'SELECT `Post`.`id` FROM ' . $postTable . ' AS `Post`   WHERE `title` != \'Foo\'  GROUP BY `Post`.`id`';
		$this->assertSame($expected, $queries['log'][0]['query']);

		$expected = 'DELETE `Post` FROM ' . $postTable . ' AS `Post`   WHERE `Post`.`id` IN';
		$this->assertContains($expected, $queries['log'][1]['query']);
	}

	/**
	 * Test deleteAllRaw()
	 *
	 * @return void
	 */
	public function testDeleteAllRaw() {
		$result = $this->User->deleteAllRaw(['user !=' => 'foo', 'created <' => date(FORMAT_DB_DATE), 'id >' => 1]);
		$this->assertTrue($result);
		$result = $this->User->getAffectedRows();
		$this->assertSame(3, $result);

		$result = $this->User->deleteAllRaw();
		$this->assertTrue($result);
		$result = $this->User->getAffectedRows();
		$this->assertSame(1, $result);
	}

	/**
	 * Test truncate
	 *
	 * @return void
	 */
	public function testTruncate() {
		$is = $this->User->find('count');
		$this->assertEquals(4, $is);

		$is = $this->User->getNextAutoIncrement();
		$this->assertEquals(5, $is);

		$is = $this->User->truncate();
		$is = $this->User->find('count');
		$this->assertEquals(0, $is);

		$is = $this->User->getNextAutoIncrement();
		$this->assertEquals(1, $is);
	}

	/**
	 * Test that 2.x invalidates() can behave like 1.x invalidates()
	 * and that you are able to abort on single errors (similar to using last=>true)
	 *
	 * @return void
	 */
	public function testInvalidates() {
		$TestModel = new AppTestModel();

		$TestModel->validate = [
			'title' => [
				'tooShort' => [
					'rule' => ['minLength', 50],
					'last' => false
				],
				'onlyLetters' => ['rule' => '/^[a-z]+$/i']
			],
		];
		$data = [
			'title' => 'I am a short string'
		];
		$TestModel->create($data);
		$TestModel->invalidate('title', 'someCustomMessage');

		$result = $TestModel->validates();
		$this->assertFalse($result);

		$result = $TestModel->validationErrors;
		$expected = [
			'title' => ['someCustomMessage', 'tooShort', 'onlyLetters']
		];
		$this->assertEquals($expected, $result);
		$result = $TestModel->validationErrors;
		$this->assertEquals($expected, $result);

		// invalidate a field with 'last' => true and stop further validation for this field
		$TestModel->create($data);

		$TestModel->invalidate('title', 'someCustomMessage', true);

		$result = $TestModel->validates();
		$this->assertFalse($result);
		$result = $TestModel->validationErrors;
		$expected = [
			'title' => ['someCustomMessage']
		];
		$this->assertEquals($expected, $result);
		$result = $TestModel->validationErrors;
		$this->assertEquals($expected, $result);
	}

	/**
	 * MyModelTest::testValidateRange()
	 *
	 * @return void
	 */
	public function testValidateRange() {
		$is = $this->User->validateRange(['range' => 2], 1, 3);
		$this->assertTrue($is);

		$is = $this->User->validateRange(['range' => 2.4], 1.5, 2.3);
		$this->assertFalse($is);

		$is = $this->User->validateRange(['range' => -5], -10, 1);
		$this->assertTrue($is);

		$is = $this->User->validateRange(['range' => 'word'], 1.5, 2.3);
		$this->assertFalse($is);

		$is = $this->User->validateRange(['range' => 5.1]);
		$this->assertTrue($is);

		$is = $this->User->validateRange(['range' => 2.1], 2.1, 3.2);
		$this->assertTrue($is);

		$is = $this->User->validateRange(['range' => 3.2], 2.1, 3.2);
		$this->assertTrue($is);
	}

	/**
	 * MyModelTest::testValidateIdentical()
	 *
	 * @return void
	 */
	public function testValidateIdentical() {
		$this->User->data = [$this->User->alias => ['y' => 'efg']];
		$is = $this->User->validateIdentical(['x' => 'efg'], 'y');
		$this->assertTrue($is);

		$this->User->data = [$this->User->alias => ['y' => '2']];
		$is = $this->User->validateIdentical(['x' => 2], 'y');
		$this->assertFalse($is);

		$this->User->data = [$this->User->alias => ['y' => '3']];
		$is = $this->User->validateIdentical(['x' => 3], 'y', ['cast' => 'int']);
		$this->assertTrue($is);

		$this->User->data = [$this->User->alias => ['y' => '3']];
		$is = $this->User->validateIdentical(['x' => 3], 'y', ['cast' => 'string']);
		$this->assertTrue($is);
	}

	/**
	 * MyModelTest::testValidateKey()
	 *
	 * @return void
	 */
	public function testValidateKey() {
		//$this->User->data = array($this->User->alias=>array('y'=>'efg'));
		$testModel = new AppTestModel();

		$is = $testModel->validateKey(['id' => '2']);
		$this->assertFalse($is);

		$is = $testModel->validateKey(['id' => 2]);
		$this->assertFalse($is);

		$is = $testModel->validateKey(['id' => '4e6f-a2f2-19a4ab957338']);
		$this->assertFalse($is);

		$is = $testModel->validateKey(['id' => '4dff6725-f0e8-4e6f-a2f2-19a4ab957338']);
		$this->assertTrue($is);

		$is = $testModel->validateKey(['id' => '']);
		$this->assertFalse($is);

		$is = $testModel->validateKey(['id' => ''], ['allowEmpty' => true]);
		$this->assertTrue($is);

		$is = $testModel->validateKey(['foreign_id' => '2']);
		$this->assertTrue($is);

		$is = $testModel->validateKey(['foreign_id' => 2]);
		$this->assertTrue($is);

		$is = $testModel->validateKey(['foreign_id' => 2.3]);
		$this->assertFalse($is);

		$is = $testModel->validateKey(['foreign_id' => -2]);
		$this->assertFalse($is);

		$is = $testModel->validateKey(['foreign_id' => '4dff6725-f0e8-4e6f-a2f2-19a4ab957338']);
		$this->assertFalse($is);

		$is = $testModel->validateKey(['foreign_id' => 0]);
		$this->assertFalse($is);

		$is = $testModel->validateKey(['foreign_id' => 0], ['allowEmpty' => true]);
		$this->assertTrue($is);
	}

	/**
	 * MyModelTest::testValidateEnum()
	 *
	 * @return void
	 */
	public function testValidateEnum() {
		//$this->User->data = array($this->User->alias=>array('y'=>'efg'));
		$testModel = new AppTestModel();
		$is = $testModel->validateEnum(['x' => '1'], true);
		$this->assertTrue($is);

		$is = $testModel->validateEnum(['x' => '4'], true);
		$this->assertFalse($is);

		$is = $testModel->validateEnum(['x' => '5'], true, ['4', '5']);
		$this->assertTrue($is);

		$is = $testModel->validateEnum(['some_key' => '3'], 'x', ['4', '5']);
		$this->assertTrue($is);
	}

	/**
	 * MyModelTest::testGuaranteeFields()
	 *
	 * @return void
	 */
	public function testGuaranteeFields() {
		$res = $this->User->guaranteeFields([]);
		//debug($res);
		$this->assertTrue(empty($res));

		$res = $this->User->guaranteeFields(['x', 'y']);
		//debug($res);
		$this->assertTrue(!empty($res));
		$this->assertEquals($res, [$this->modelName => ['x' => '', 'y' => '']]);

		$res = $this->User->guaranteeFields(['x', 'OtherModel.y']);
		//debug($res);
		$this->assertTrue(!empty($res));
		$this->assertEquals($res, [$this->modelName => ['x' => ''], 'OtherModel' => ['y' => '']]);
	}

	/**
	 * MyModelTest::testRequireFields()
	 *
	 * @return void
	 */
	public function testRequireFields() {
		$this->User->requireFields(['foo', 'bar']);
		$data = [
			'foo' => 'foo',
		];
		$this->User->set($data);
		$result = $this->User->validates();
		$this->assertFalse($result);

		$data = [
			'foo' => 'foo',
			'bar' => '',
		];
		$this->User->set($data);
		$result = $this->User->validates();
		$this->assertTrue($result);

		// Allow field to be empty as long as it is present
		$this->User->requireFields(['foo', 'test'], true);
		$data = [
			'foo' => 'foo',
			'test' => ''
		];
		$this->User->set($data);
		$result = $this->User->validates();
		$this->assertTrue($result);
	}

	/**
	 * MyModelTest::testSet()
	 *
	 * @return void
	 */
	public function testSet() {
		$data = [$this->modelName => ['x' => 'hey'], 'OtherModel' => ['y' => '']];
		$this->User->data = [];

		$res = $this->User->set($data, null, ['x', 'z']);
		$this->out($res);
		$this->assertTrue(!empty($res));
		$this->assertEquals($res, [$this->modelName => ['x' => 'hey', 'z' => ''], 'OtherModel' => ['y' => '']]);

		$res = $this->User->data;
		$this->out($res);
		$this->assertTrue(!empty($res));
		$this->assertEquals($res, [$this->modelName => ['x' => 'hey', 'z' => ''], 'OtherModel' => ['y' => '']]);
	}

	/**
	 * MyModelTest::testValidateWithGuaranteeFields()
	 *
	 * @return void
	 */
	public function testValidateWithGuaranteeFields() {
		$data = [$this->modelName => ['x' => 'hey'], 'OtherModel' => ['y' => '']];

		$data = $this->User->guaranteeFields(['x', 'z'], $data);
		$this->out($data);
		$this->assertTrue(!empty($data));
		$this->assertEquals([$this->modelName => ['x' => 'hey', 'z' => ''], 'OtherModel' => ['y' => '']], $data);

		$res = $this->User->set($data);
		$this->out($res);
		$this->assertTrue(!empty($res));
		$this->assertEquals($res, [$this->modelName => ['x' => 'hey', 'z' => ''], 'OtherModel' => ['y' => '']]);
	}

	public function testWhitelist() {
		$data = [
			'name' => 'foo',
			'x' => 'y',
			'z' => 'yes'
		];
		$this->User->set($data);
		$result = $this->User->whitelist(['name', 'x']);
		$this->assertEquals(['name', 'x'], array_keys($this->User->data['User']));
	}

	/**
	 * MyModelTest::testBlacklist()
	 * Note that one should always prefer a whitelist over a blacklist.
	 *
	 * @return void
	 */
	public function testBlacklist() {
		$data = [
			'name' => 'foo',
			'x' => 'y',
			'z' => 'yes'
		];
		$this->User->set($data);
		$this->User->blacklist(['x']);
		$this->assertEquals(['name', 'z'], array_keys($this->User->data['User']));
	}

	/**
	 * MyModelTest::testGenerateWhitelistFromBlacklist()
	 *
	 * @return void
	 */
	public function testGenerateWhitelistFromBlacklist() {
		$result = $this->User->generateWhitelistFromBlacklist(['password']);
		$expected = ['id', 'user', 'created', 'updated'];
		$this->assertEquals($expected, array_values($expected));
	}

	/**
	 * MyModelTest::testInvalidate()
	 *
	 * @return void
	 */
	public function testInvalidate() {
		$this->User->create();
		$this->User->invalidate('fieldx', __d('tools', 'e %s f', 33));
		$res = $this->User->validationErrors;
		$this->out($res);
		$this->assertTrue(!empty($res));

		$this->User->create();
		$this->User->invalidate('Model.fieldy', __d('tools', 'e %s f %s g', 33, 'xyz'));
		$res = $this->User->validationErrors;
		$this->out($res);
		$this->assertTrue(!empty($res) && $res['Model.fieldy'][0] === 'e 33 f xyz g');

		$this->User->create();
		$this->User->invalidate('fieldy', __d('tools', 'e %s f %s g %s', true, 'xyz', 55));
		$res = $this->User->validationErrors;
		$this->out($res);
		$this->assertTrue(!empty($res) && $res['fieldy'][0] === 'e 1 f xyz g 55');

		$this->User->create();
		$this->User->invalidate('fieldy', ['valErrMandatoryField']);
		$res = $this->User->validationErrors;
		$this->out($res);
		$this->assertTrue(!empty($res));

		$this->User->create();
		$this->User->invalidate('fieldy', 'valErrMandatoryField');
		$res = $this->User->validationErrors;
		$this->out($res);
		$this->assertTrue(!empty($res));

		$this->User->create();
		$this->User->invalidate('fieldy', __d('tools', 'a %s b %s c %s %s %s %s %s h %s', 1, 2, 3, 4, 5, 6, 7, 8));
		$res = $this->User->validationErrors;
		$this->out($res);
		$this->assertTrue(!empty($res) && $res['fieldy'][0] === 'a 1 b 2 c 3 4 5 6 7 h 8');
	}

	/**
	 * MyModelTest::testValidateDate()
	 *
	 * @return void
	 */
	public function testValidateDate() {
		$data = ['field' => '2010-01-22'];
		$res = $this->User->validateDate($data);
		//debug($res);
		$this->assertTrue($res);

		$data = ['field' => '2010-02-29'];
		$res = $this->User->validateDate($data);
		//debug($res);
		$this->assertFalse($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-22']];
		$data = ['field' => '2010-02-23 11:11:11'];
		$res = $this->User->validateDate($data, ['after' => 'after']);
		//debug($res);
		$this->assertTrue($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-24 11:11:11']];
		$data = ['field' => '2010-02-23'];
		$res = $this->User->validateDate($data, ['after' => 'after']);
		//debug($res);
		$this->assertFalse($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-25']];
		$data = ['field' => '2010-02-25'];
		$res = $this->User->validateDate($data, ['after' => 'after']);
		//debug($res);
		$this->assertTrue($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-25']];
		$data = ['field' => '2010-02-25'];
		$res = $this->User->validateDate($data, ['after' => 'after', 'min' => 1]);
		//debug($res);
		$this->assertFalse($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-24']];
		$data = ['field' => '2010-02-25'];
		$res = $this->User->validateDate($data, ['after' => 'after', 'min' => 2]);
		//debug($res);
		$this->assertFalse($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-24']];
		$data = ['field' => '2010-02-25'];
		$res = $this->User->validateDate($data, ['after' => 'after', 'min' => 1]);
		//debug($res);
		$this->assertTrue($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-24']];
		$data = ['field' => '2010-02-25'];
		$res = $this->User->validateDate($data, ['after' => 'after', 'min' => 2]);
		//debug($res);
		$this->assertFalse($res);

		$this->User->data = [$this->User->alias => ['before' => '2010-02-24']];
		$data = ['field' => '2010-02-24'];
		$res = $this->User->validateDate($data, ['before' => 'before', 'min' => 1]);
		//debug($res);
		$this->assertFalse($res);

		$this->User->data = [$this->User->alias => ['before' => '2010-02-25']];
		$data = ['field' => '2010-02-24'];
		$res = $this->User->validateDate($data, ['before' => 'before', 'min' => 1]);
		//debug($res);
		$this->assertTrue($res);

		$this->User->data = [$this->User->alias => ['before' => '2010-02-25']];
		$data = ['field' => '2010-02-24'];
		$res = $this->User->validateDate($data, ['before' => 'before', 'min' => 2]);
		//debug($res);
		$this->assertFalse($res);

		$this->User->data = [$this->User->alias => ['before' => '2010-02-26']];
		$data = ['field' => '2010-02-24'];
		$res = $this->User->validateDate($data, ['before' => 'before', 'min' => 2]);
		//debug($res);
		$this->assertTrue($res);
	}

	/**
	 * MyModelTest::testValidateDatetime()
	 *
	 * @return void
	 */
	public function testValidateDatetime() {
		$data = ['field' => '2010-01-22 11:11:11'];
		$res = $this->User->validateDatetime($data);
		//debug($res);
		$this->assertTrue($res);

		$data = ['field' => '2010-01-22 11:61:11'];
		$res = $this->User->validateDatetime($data);
		//debug($res);
		$this->assertFalse($res);

		$data = ['field' => '2010-02-29 11:11:11'];
		$res = $this->User->validateDatetime($data);
		//debug($res);
		$this->assertFalse($res);

		$data = ['field' => ''];
		$res = $this->User->validateDatetime($data, ['allowEmpty' => true]);
		//debug($res);
		$this->assertTrue($res);

		$data = ['field' => '0000-00-00 00:00:00'];
		$res = $this->User->validateDatetime($data, ['allowEmpty' => true]);
		//debug($res);
		$this->assertTrue($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-22 11:11:11']];
		$data = ['field' => '2010-02-23 11:11:11'];
		$res = $this->User->validateDatetime($data, ['after' => 'after']);
		//debug($res);
		$this->assertTrue($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-24 11:11:11']];
		$data = ['field' => '2010-02-23 11:11:11'];
		$res = $this->User->validateDatetime($data, ['after' => 'after']);
		//debug($res);
		$this->assertFalse($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-23 11:11:11']];
		$data = ['field' => '2010-02-23 11:11:11'];
		$res = $this->User->validateDatetime($data, ['after' => 'after']);
		//debug($res);
		$this->assertFalse($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-23 11:11:11']];
		$data = ['field' => '2010-02-23 11:11:11'];
		$res = $this->User->validateDatetime($data, ['after' => 'after', 'min' => 1]);
		//debug($res);
		$this->assertFalse($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-23 11:11:11']];
		$data = ['field' => '2010-02-23 11:11:11'];
		$res = $this->User->validateDatetime($data, ['after' => 'after', 'min' => 0]);
		//debug($res);
		$this->assertTrue($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-23 11:11:10']];
		$data = ['field' => '2010-02-23 11:11:11'];
		$res = $this->User->validateDatetime($data, ['after' => 'after']);
		//debug($res);
		$this->assertTrue($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-23 11:11:12']];
		$data = ['field' => '2010-02-23 11:11:11'];
		$res = $this->User->validateDatetime($data, ['after' => 'after']);
		//debug($res);
		$this->assertFalse($res);
	}

	/**
	 * MyModelTest::testValidateTime()
	 *
	 * @return void
	 */
	public function testValidateTime() {
		$data = ['field' => '11:21:11'];
		$res = $this->User->validateTime($data);
		//debug($res);
		$this->assertTrue($res);

		$data = ['field' => '11:71:11'];
		$res = $this->User->validateTime($data);
		//debug($res);
		$this->assertFalse($res);

		$this->User->data = [$this->User->alias => ['before' => '2010-02-23 11:11:12']];
		$data = ['field' => '2010-02-23 11:11:11'];
		$res = $this->User->validateTime($data, ['before' => 'before']);
		//debug($res);
		$this->assertTrue($res);

		$this->User->data = [$this->User->alias => ['after' => '2010-02-23 11:11:12']];
		$data = ['field' => '2010-02-23 11:11:11'];
		$res = $this->User->validateTime($data, ['after' => 'after']);
		//debug($res);
		$this->assertFalse($res);
	}

	/**
	 * MyModelTest::testValidateUrl()
	 *
	 * @return void
	 */
	public function testValidateUrl() {
		$data = ['field' => 'www.dereuromark.de'];
		$res = $this->User->validateUrl($data, ['allowEmpty' => true]);
		$this->assertTrue($res);

		$data = ['field' => 'www.xxxde'];
		$res = $this->User->validateUrl($data, ['allowEmpty' => true]);
		$this->assertFalse($res);

		$data = ['field' => 'www.dereuromark.de'];
		$res = $this->User->validateUrl($data, ['allowEmpty' => true, 'autoComplete' => false]);
		$this->assertFalse($res);

		$data = ['field' => 'http://www.dereuromark.de'];
		$res = $this->User->validateUrl($data, ['allowEmpty' => true, 'autoComplete' => false]);
		$this->assertTrue($res);

		$data = ['field' => 'www.dereuromark.de'];
		$res = $this->User->validateUrl($data, ['strict' => true]);
		$this->assertTrue($res); # aha

		$data = ['field' => 'http://www.dereuromark.de'];
		$res = $this->User->validateUrl($data, ['strict' => false]);
		$this->assertTrue($res);

		$this->skipIf(empty($_SERVER['HTTP_HOST']), 'No HTTP_HOST');

		$data = ['field' => 'http://xyz.de/some/link'];
		$res = $this->User->validateUrl($data, ['deep' => false, 'sameDomain' => true]);
		$this->assertFalse($res);

		$data = ['field' => '/some/link'];
		$res = $this->User->validateUrl($data, ['deep' => false, 'autoComplete' => true]);
		$this->assertTrue($_SERVER['HTTP_HOST'] === 'localhost' ? !$res : $res);

		$data = ['field' => 'http://' . $_SERVER['HTTP_HOST'] . '/some/link'];
		$res = $this->User->validateUrl($data, ['deep' => false]);
		$this->assertTrue($_SERVER['HTTP_HOST'] === 'localhost' ? !$res : $res);

		$data = ['field' => '/some/link'];
		$res = $this->User->validateUrl($data, ['deep' => false, 'autoComplete' => false]);
		$this->assertTrue((env('REMOTE_ADDR') !== '127.0.0.1') ? !$res : $res);

		//$this->skipIf(strpos($_SERVER['HTTP_HOST'], '.') === false, 'No online HTTP_HOST');

		$data = ['field' => '/some/link'];
		$res = $this->User->validateUrl($data, ['deep' => false, 'sameDomain' => true]);
		$this->assertTrue($_SERVER['HTTP_HOST'] === 'localhost' ? !$res : $res);

		$data = ['field' => 'https://github.com/'];
		$res = $this->User->validateUrl($data, ['deep' => false]);
		$this->assertTrue($res);

		$data = ['field' => 'https://github.com/'];
		$res = $this->User->validateUrl($data, ['deep' => true]);
		$this->assertTrue($res);
	}

	/**
	 * MyModelTest::testValidateUnique()
	 *
	 * @return void
	 */
	public function testValidateUnique() {
		$this->Post->validate['title'] = [
			'validateUnique' => [
				'rule' => 'validateUnique',
				'message' => 'valErrRecordTitleExists'
			],
		];
		$data = [
			'title' => 'abc',
			'published' => 'N'
		];
		$this->Post->create($data);
		$res = $this->Post->validates();
		$this->assertTrue($res);
		$res = $this->Post->save($res, ['validate' => false]);
		$this->assertTrue((bool)$res);

		$this->Post->create();
		$res = $this->Post->save($data);
		$this->assertFalse($res);

		// One dependent field
		$this->Post->validate['title'] = [
			'validateUnique' => [
				'rule' => ['validateUnique', ['published']],
				'message' => 'valErrRecordTitleExists'
			],
		];
		$data = [
			'title' => 'abc',
			'published' => 'Y'
		];
		$this->Post->create($data);
		$res = $this->Post->validates();
		$this->assertTrue($res);
		$res = $this->Post->save($res, ['validate' => false]);
		$this->assertTrue((bool)$res);

		$this->Post->create();
		$res = $this->Post->save($data);
		$this->assertFalse($res);

		// Too dependent fields
		$this->Post->validate['title'] = [
			'validateUnique' => [
				'rule' => ['validateUnique', ['published', 'author_id']],
				'message' => 'valErrRecordTitleExists',
			],
		];

		$this->User->create();
		$user = $this->User->save(['user' => 'Foo']);

		$data = [
			'title' => 'abc',
			'published' => 'Y',
			'author_id' => $user['User']['id']
		];
		$this->Post->create();
		$res = $this->Post->save($data);
		$this->assertTrue((bool)$res);

		$this->Post->create();
		$res = $this->Post->save($data);
		$this->assertFalse($res);
	}

}

class MyAppModelPost extends MyModel {

	public $name = 'Post';

	public $alias = 'Post';

	public $belongsTo = 'Author';

}

class MyAppModelUser extends MyModel {

	public $name = 'User';

	public $alias = 'User';

}

class AppTestModel extends MyModel {

	public $useTable = false;

	protected $_schema = [
		'id' => [
			'type' => 'string',
			'null' => false,
			'default' => '',
			'length' => 36,
			'key' => 'primary',
			'collate' => 'utf8_unicode_ci',
			'charset' => 'utf8',
		],
		'foreign_id' => [
			'type' => 'integer',
			'null' => false,
			'default' => '0',
			'length' => 10,
		],
	];

	public static function x() {
		return ['1' => 'x', '2' => 'y', '3' => 'z'];
	}

}
