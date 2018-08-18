<?php
App::uses('CustomFindsBehavior', 'Tools.Model/Behavior');
App::uses('AppModel', 'Model');
App::uses('MyCakeTestCase', 'Tools.TestSuite');

class CustomFindsBehaviorTest extends MyCakeTestCase {

	public function setUp() {
		parent::setUp();

		$this->CustomFinds = new CustomFindsBehavior();

		$this->Model = new CustomFindsTest();

		$this->Model->customFinds = [
			'topSellers' => [
				'fields' => ['Product.name', 'Product.price'],
				'contain' => ['ProductImage.source'],
				'conditions' => ['Product.countSeller >' => 20, 'Product.is_active' => 1],
				'recursive' => 1,
				//All other find options
			]
		];
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function testObject() {
		$this->assertInstanceOf('CustomFindsBehavior', $this->CustomFinds);
	}

	public function testModify() {
		$query = [
			'custom' => 'topSellers',
			'recursive' => 0,
			'conditions' => ['Product.count >' => 0],
		];

		$res = $this->Model->Behaviors->CustomFinds->beforeFind($this->Model, $query);
		//pr($res);
		$queryResult = $this->Model->customFinds['topSellers'];
		$queryResult['recursive'] = 0;
		$queryResult['conditions']['Product.count >'] = 0;

		$this->assertTrue(!empty($res));
		$this->assertSame($queryResult['recursive'], $res['recursive']);
		$this->assertSame($queryResult['conditions'], $res['conditions']);
	}

	public function testModifyWithRemove() {
		$query = [
			'custom' => 'topSellers',
			'conditions' => ['Product.count >' => 0],
			'remove' => ['conditions']
		];

		$res = $this->Model->Behaviors->CustomFinds->beforeFind($this->Model, $query);
		//pr($res);
		$queryResult = $this->Model->customFinds['topSellers'];
		$queryResult['conditions'] = ['Product.count >' => 0];

		$this->assertTrue(!empty($res));
		$this->assertSame($queryResult['recursive'], $res['recursive']);
		$this->assertSame($queryResult['conditions'], $res['conditions']);

		$query = [
			'custom' => 'topSellers',
			'conditions' => ['Product.count >' => 0],
			'remove' => ['conditions' => ['Product.countSeller >']]
		];

		$res = $this->Model->Behaviors->CustomFinds->beforeFind($this->Model, $query);
		//pr($res);
		$queryResult = $this->Model->customFinds['topSellers'];
		unset($queryResult['conditions']['Product.countSeller >']);
		$queryResult['conditions']['Product.count >'] = 0;

		$this->assertTrue(!empty($res));
		$this->assertSame($queryResult['conditions'], $res['conditions']);
	}

}

class CustomFindsTest extends AppModel {

	public $useTable = false;

	public $actsAs = ['Tools.CustomFinds'];

}
