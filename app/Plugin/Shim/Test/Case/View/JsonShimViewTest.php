<?php

App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('JsonShimView', 'Shim.View');

/**
 * JsonShimViewTest
 */
class JsonShimViewTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		Configure::delete('Shim.jsonOptions');
		Configure::write('debug', 0);
	}

/**
 * Generates testRenderWithoutView data.
 *
 * Note: array($data, $serialize, expected)
 *
 * @return void
 */
	public static function renderWithoutViewProvider() {
		return [
			// Test render with a valid string in _serialize.
			[
				['data' => ['user' => 'fake', 'list' => ['item1', 'item2']]],
				'data',
				json_encode(['user' => 'fake', 'list' => ['item1', 'item2']])
			],

			// Test render with a string with an invalid key in _serialize.
			[
				['data' => ['user' => 'fake', 'list' => ['item1', 'item2']]],
				'no_key',
				json_encode(null)
			],

			// Test render with a valid array in _serialize.
			[
				['no' => 'nope', 'user' => 'fake', 'list' => ['item1', 'item2']],
				['no', 'user'],
				json_encode(['no' => 'nope', 'user' => 'fake'])
			],

			// Test render with an empty array in _serialize.
			[
				['no' => 'nope', 'user' => 'fake', 'list' => ['item1', 'item2']],
				[],
				json_encode(null)
			],

			// Test render with a valid array with an invalid key in _serialize.
			[
				['no' => 'nope', 'user' => 'fake', 'list' => ['item1', 'item2']],
				['no', 'user', 'no_key'],
				json_encode(['no' => 'nope', 'user' => 'fake'])
			],

			// Test render with a valid array with only an invalid key in _serialize.
			[
				['no' => 'nope', 'user' => 'fake', 'list' => ['item1', 'item2']],
				['no_key'],
				json_encode(null)
			],

			// Test render with Null in _serialize (unset).
			[
				['no' => 'nope', 'user' => 'fake', 'list' => ['item1', 'item2']],
				null,
				null
			],

			// Test render with False in _serialize.
			[
				['no' => 'nope', 'user' => 'fake', 'list' => ['item1', 'item2']],
				false,
				json_encode(null)
			],

			// Test render with True in _serialize.
			[
				['no' => 'nope', 'user' => 'fake', 'list' => ['item1', 'item2']],
				true,
				json_encode(null)
			],

			// Test render with empty string in _serialize.
			[
				['no' => 'nope', 'user' => 'fake', 'list' => ['item1', 'item2']],
				'',
				json_encode(null)
			],

			// Test render with a valid array in _serialize and alias.
			[
				['original_name' => 'my epic name', 'user' => 'fake', 'list' => ['item1', 'item2']],
				['new_name' => 'original_name', 'user'],
				json_encode(['new_name' => 'my epic name', 'user' => 'fake'])
			],

			// Test render with an a valid array in _serialize and alias of a null value.
			[
				['null' => null],
				['null'],
				json_encode(['null' => null])
			],

			// Test render with a False value to be serialized.
			[
				['false' => false],
				'false',
				json_encode(false)
			],

			// Test render with a True value to be serialized.
			[
				['true' => true],
				'true',
				json_encode(true)
			],

			// Test render with an empty string value to be serialized.
			[
				['empty' => ''],
				'empty',
				json_encode('')
			],

			// Test render with a zero value to be serialized.
			[
				['zero' => 0],
				'zero',
				json_encode(0)
			],
		];
	}

/**
 * Custom error handler for use while testing methods that use json_encode
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param int $errline
 * @param array $errcontext
 * @return void
 * @throws CakeException
 */
	public function jsonEncodeErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
		throw new CakeException($errstr, 0, $errno, $errfile, $errline);
	}

/**
 * Test render with a valid string in _serialize.
 *
 * @dataProvider renderWithoutViewProvider
 * @return void
 */
	public function testRenderWithoutView($data, $serialize, $expected) {
		$Request = new CakeRequest();
		$Response = new CakeResponse();
		$Controller = new Controller($Request, $Response);

		$Controller->set($data);
		$Controller->set('_serialize', $serialize);
		$View = new JsonShimView($Controller);
		$output = $View->render(false);

		$this->assertSame($expected, $output);
	}

/**
 * Test render with _jsonOptions setting.
 *
 * @return void
 */
	public function testRenderWithoutViewJsonOptions() {
		$this->skipIf(!version_compare(PHP_VERSION, '5.3.0', '>='), 'Needs PHP5.3+ for these constants to be tested');

		$Request = new CakeRequest();
		$Response = new CakeResponse();
		$Controller = new Controller($Request, $Response);

		// Test render with encode <, >, ', &, and " for RFC4627-compliant to be serialized.
		$data = ['rfc4627_escape' => '<tag> \'quote\' "double-quote" &'];
		$serialize = 'rfc4627_escape';
		$expected = json_encode('<tag> \'quote\' "double-quote" &', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

		$Controller->set($data);
		$Controller->set('_serialize', $serialize);
		$Controller->set('_jsonOptions', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
		$View = new JsonShimView($Controller);
		$output = $View->render(false);

		$this->assertSame($expected, $output);
	}
/**
 * Test that rendering with _serialize does not load helpers.
 *
 * @return void
 */
	public function testRenderSerializeNoHelpers() {
		$Request = new CakeRequest();
		$Response = new CakeResponse();
		$Controller = new Controller($Request, $Response);

		$Controller->helpers = ['Html'];
		$Controller->set([
			'tags' => ['cakephp', 'framework'],
			'_serialize' => 'tags'
		]);
		$View = new JsonShimView($Controller);
		$View->render();

		$this->assertFalse(isset($View->Html), 'No helper loaded.');
	}

/**
 * testJsonpResponse method
 *
 * @return void
 */
	public function testJsonpResponse() {
		$Request = new CakeRequest();
		$Response = new CakeResponse();
		$Controller = new Controller($Request, $Response);

		$data = ['user' => 'fake', 'list' => ['item1', 'item2']];
		$Controller->set([
			'data' => $data,
			'_serialize' => 'data',
			'_jsonp' => true
		]);
		$View = new JsonShimView($Controller);
		$output = $View->render(false);

		$this->assertSame(json_encode($data), $output);
		$this->assertSame('application/json', $Response->type());

		$View->request->query = ['callback' => 'jfunc'];
		$output = $View->render(false);
		$expected = 'jfunc(' . json_encode($data) . ')';
		$this->assertSame($expected, $output);
		$this->assertSame('application/javascript', $Response->type());

		$View->request->query = ['jsonCallback' => 'jfunc'];
		$View->viewVars['_jsonp'] = 'jsonCallback';
		$output = $View->render(false);
		$expected = 'jfunc(' . json_encode($data) . ')';
		$this->assertSame($expected, $output);
	}

/**
 * Test render with a View file specified.
 *
 * @return void
 */
	public function testRenderWithView() {
		App::build([
			'View' => [CAKE . 'Test' . DS . 'test_app' . DS . 'View' . DS]
		]);
		$Request = new CakeRequest();
		$Response = new CakeResponse();
		$Controller = new Controller($Request, $Response);
		$Controller->name = $Controller->viewPath = 'Posts';

		$data = [
			'User' => [
				'username' => 'fake'
			],
			'Item' => [
				['name' => 'item1'],
				['name' => 'item2']
			]
		];
		$Controller->set('user', $data);
		$View = new JsonShimView($Controller);
		$output = $View->render('index');

		$expected = json_encode(['user' => 'fake', 'list' => ['item1', 'item2'], 'paging' => null]);
		$this->assertSame($expected, $output);
		$this->assertSame('application/json', $Response->type());
	}

/**
 * Test render with a View file specified and named parameters.
 *
 * @return void
 */
	public function testRenderWithViewAndNamed() {
		App::build([
			'View' => [CAKE . 'Test' . DS . 'test_app' . DS . 'View' . DS]
		]);
		$Request = new CakeRequest(null, false);
		$Request->params['named'] = ['page' => 2];
		$Response = new CakeResponse();
		$Controller = new Controller($Request, $Response);
		$Controller->name = $Controller->viewPath = 'Posts';

		$data = [
			'User' => [
				'username' => 'fake'
			],
			'Item' => [
				['name' => 'item1'],
				['name' => 'item2']
			]
		];
		$Controller->set('user', $data);
		$Controller->helpers = ['Paginator'];
		$View = new JsonShimView($Controller);
		$output = $View->render('index');

		$expected = ['user' => 'fake', 'list' => ['item1', 'item2'], 'paging' => ['page' => 2]];
		$this->assertSame(json_encode($expected), $output);
		$this->assertSame('application/json', $Response->type());

		$View->request->query = ['jsonCallback' => 'jfunc'];
		$Controller->set('_jsonp', 'jsonCallback');
		$View = new JsonShimView($Controller);
		$View->helpers = ['Paginator'];
		$output = $View->render('index');
		$expected['paging']['?']['jsonCallback'] = 'jfunc';
		$expected = 'jfunc(' . json_encode($expected) . ')';
		$this->assertSame($expected, $output);
		$this->assertSame('application/javascript', $Response->type());
	}

/**
 * JsonViewTest::testRenderInvalidJSON()
 *
 * @return void
 */
	public function testRenderInvalidJSON() {
		$Request = new CakeRequest();
		$Response = new CakeResponse();
		$Controller = new Controller($Request, $Response);

		// non utf-8 stuff
		$data = ['data' => ['foo' => 'bar' . chr('0x97')]];

		$Controller->set($data);
		$Controller->set('_serialize', 'data');
		$View = new JsonShimView($Controller);

		// Use a custom error handler
		set_error_handler([$this, 'jsonEncodeErrorHandler']);

		try {
			$View->render();
			restore_error_handler();
			$this->fail('Failed asserting that exception of type "CakeException" is thrown.');
		} catch (CakeException $e) {
			restore_error_handler();
			$this->assertRegExp('/UTF-8/', $e->getMessage());
			return;
		}
	}

/**
 * JsonViewTest::testRenderJSONBoolFalse()
 *
 * @return void
 */
	public function testRenderJSONBoolFalse() {
		$Request = new CakeRequest();
		$Response = new CakeResponse();
		$Controller = new Controller($Request, $Response);

		// encoding a false, ensure this doesn't trigger exception
		$data = ['rfc4627_escape' => '<tag> \'quote\' "double-quote" &'];

		$Controller->set($data);
		$Controller->set('_serialize', 'rfc4627_escape');
		$View = new JsonShimView($Controller);
		$output = $View->render();
		$expected = json_encode('<tag> \'quote\' "double-quote" &', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
		$this->assertSame($expected, $output);
	}

	public function testShimJsonOptions() {
		$Request = new CakeRequest();
		$Response = new CakeResponse();
		$Controller = new Controller($Request, $Response);

		// Test render with encode <, >, ', &, and " for RFC4627-compliant to be serialized.
		$data = ['rfc4627_escape' => '<tag> \'quote\' "double-quote" &'];

		$Controller->set($data);
		$Controller->set('_serialize', 'rfc4627_escape');
		$View = new JsonShimView($Controller);
		$output = $View->render();
		$expected = json_encode('<tag> \'quote\' "double-quote" &', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
		$this->assertSame($expected, $output);
	}

	public function testShimJsonOptionsDisableAll() {
		$Request = new CakeRequest();
		$Response = new CakeResponse();
		$Controller = new Controller($Request, $Response);

		// Test render with encode <, >, ', &, and " for RFC4627-compliant to be serialized.
		$data = ['rfc4627_escape' => '<tag> \'quote\' "double-quote" &'];

		$Controller->set($data);
		$Controller->set('_serialize', 'rfc4627_escape');
		$Controller->set('_jsonOptions', false);
		$View = new JsonShimView($Controller);
		$output = $View->render();
		$expected = json_encode('<tag> \'quote\' "double-quote" &');
		$this->assertSame($expected, $output);
	}

	public function testShimJsonOptionsUsingConfigure() {
		$Request = new CakeRequest();
		$Response = new CakeResponse();
		$Controller = new Controller($Request, $Response);

		// Test render with encode <, >, ', &, and " for RFC4627-compliant to be serialized.
		$data = ['rfc4627_escape' => '<tag> \'quote\' "double-quote" &'];

		Configure::write('Shim.jsonOptions', JSON_HEX_TAG | JSON_HEX_APOS);

		$Controller->set($data);
		$Controller->set('_serialize', 'rfc4627_escape');
		$View = new JsonShimView($Controller);
		$output = $View->render();
		$expected = json_encode('<tag> \'quote\' "double-quote" &', JSON_HEX_TAG | JSON_HEX_APOS);
		$this->assertSame($expected, $output);
	}

}
