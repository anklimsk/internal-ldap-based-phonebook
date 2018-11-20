<?php
App::uses('AppCakeTestCase', 'CakeTCPDF.Test');
App::uses('PdfView', 'CakeTCPDF.View');
App::uses('Controller', 'Controller');

class PdfTestController extends Controller {
}

/**
 * PdfViewTest Test Case
 */
class PdfViewTest extends AppCakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		$pathView = CakePlugin::path('CakeTCPDF') . 'Test' . DS . 'test_app' . DS . 'View' . DS;
		App::build(['View' => $pathView]);
		parent::setUp();

		$Controller = new PdfTestController();
		$this->_targetObject = new PdfView($Controller);
	}

/**
 * testConstruct method
 *
 * @return void
 */
	public function testConstruct() {
		$result = $this->_targetObject->response->type();
		$expected = 'application/pdf';
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
		$expected = '25504446';
		$this->assertData($expected, $result);

		$result = $this->_targetObject->response->header();
		$expected = [
			'Content-Disposition' => 'attachment; filename="TestFile.pdf"'
		];
		$this->assertData($expected, $result);
	}
}
