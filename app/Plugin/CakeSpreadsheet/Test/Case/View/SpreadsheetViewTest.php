<?php
App::uses('AppCakeTestCase', 'CakeSpreadsheet.Test');
App::uses('SpreadsheetView', 'CakeSpreadsheet.View');
App::uses('Controller', 'Controller');

class SpreadsheetTestController extends Controller {
}

/**
 * SpreadsheetViewTest Test Case
 */
class SpreadsheetViewTest extends AppCakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		$pathView = CakePlugin::path('CakeSpreadsheet') . 'Test' . DS . 'test_app' . DS . 'View' . DS;
		App::build(['View' => $pathView]);
		parent::setUp();

		$Controller = new SpreadsheetTestController();
		$this->_targetObject = new SpreadsheetView($Controller);
	}

/**
 * testConstruct method
 *
 * @return void
 */
	public function testConstruct() {
		$result = $this->_targetObject->response->type();
		$expected = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
		$this->assertData($expected, $result);
	}

/**
 * testSetFileNameEmpty method
 *
 * @return void
 */
	public function testSetFileNameEmpty() {
		$result = $this->_targetObject->setFileName();
		$this->assertFalse($result);
	}

/**
 * testSetFileNameSuccess method
 *
 * @return void
 */
	public function testSetFileNameSuccess() {
		$result = $this->_targetObject->setFileName('export');
		$this->assertTrue($result);
	}

/**
 * testGetFileNameDefault method
 *
 * @return void
 */
	public function testGetFileNameDefault() {
		$result = $this->_targetObject->getFileName();
		$expected = 'Report';
		$this->assertData($expected, $result);
	}

/**
 * testRender method
 *
 * @return void
 */
	public function testRender() {
		$output = $this->_targetObject->render('index');
		$result = bin2hex(substr($output, 0, 4));
		$expected = '504b0304';
		$this->assertData($expected, $result);

		$result = $this->_targetObject->response->header();
		$expected = [
			'Content-Disposition' => 'attachment; filename="TestFile.' . CAKE_SPREADSHEET_FILE_EXTENSION . '"'
		];
		$this->assertData($expected, $result);

		$result = $this->_targetObject->Spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, 1)->getValue();
		$expected = 'Some text...';
		$this->assertData($expected, $result);
	}
}
