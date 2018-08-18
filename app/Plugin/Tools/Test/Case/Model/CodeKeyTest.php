<?php

App::uses('CodeKey', 'Tools.Model');
App::uses('MyCakeTestCase', 'Tools.TestSuite');

class CodeKeyTest extends MyCakeTestCase {

	public $CodeKey = null;

	public $fixtures = ['plugin.tools.code_key'];

	public function setUp() {
		parent::setUp();

		$this->CodeKey = ClassRegistry::init('Tools.CodeKey');
	}

	public function testCodeKeyInstance() {
		$this->assertInstanceOf('CodeKey', $this->CodeKey);
	}

	public function testGenerateKey() {
		$key = $this->CodeKey->generateKey(4);
		//pr($key);
		$this->assertTrue(!empty($key) && strlen($key) === 4);
	}

	public function testNewKeySpendKey() {
		$key = $this->CodeKey->newKey('test', null, null, 'xyz');
		$this->assertTrue(!empty($key));

		$res = $this->CodeKey->useKey('test', $key);
		//pr($res);
		$this->assertTrue(!empty($res));

		$res = $this->CodeKey->useKey('test', $key);
		//pr($res);
		$this->assertTrue(!empty($res) && !empty($res['CodeKey']['used']));

		$res = $this->CodeKey->useKey('test', $key . 'x');
		$this->assertFalse($res);

		$res = $this->CodeKey->useKey('testx', $key);
		$this->assertFalse($res);
	}

	public function testGarbageCollector() {
		$data = [
			'created' => date(FORMAT_DB_DATETIME, time() - 3 * MONTH),
			'type' => 'y',
			'key' => 'x'
		];
		$this->CodeKey->create();
		$this->CodeKey->save($data, ['validate' => false]);
		$count = $this->CodeKey->find('count');
		$this->CodeKey->garbageCollector();
		$count2 = $this->CodeKey->find('count');
		$this->assertTrue($count > $count2);
	}

}
