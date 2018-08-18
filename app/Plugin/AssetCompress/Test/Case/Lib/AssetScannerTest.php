<?php
App::uses('AssetScanner', 'AssetCompress.Lib');

class AssetScannerTest extends CakeTestCase {

	public function setUp() {
		parent::setUp();
		$this->_pluginPath = App::pluginPath('AssetCompress');
		$this->_testFiles = $this->_pluginPath . 'Test' . DS . 'test_files' . DS;
		$paths = array(
			$this->_testFiles . 'js' . DS,
			$this->_testFiles . 'js' . DS . 'classes' . DS
		);
		$this->Scanner = $this->getMock('AssetScanner', array('_getWebroot'), array($paths));
		$this->Scanner
			->expects($this->any())
			->method('_getWebroot')
			->will($this->returnValue($this->_testFiles));
	}

	public function testFind() {
		$result = $this->Scanner->find('base_class.js');
		$expected = $this->_testFiles . 'js' . DS . 'classes' . DS . 'base_class.js';
		$this->assertEquals($expected, $result);

		$result = $this->Scanner->find('base_class.js', false);
		$this->assertEquals('/js/classes/base_class.js', $result, 'No WWW_ROOT replacement as it is a test file.');

		$this->assertFalse($this->Scanner->find('does not exist'));
	}

	public function testFindRelative() {
		$paths = array(
			$this->_testFiles . 'css' . DS,
			$this->_testFiles . 'vendor' . DS
		);
		$scanner = $this->getMock('AssetScanner', array('_getWebroot'), array($paths));
		$scanner
			->expects($this->any())
			->method('_getWebroot')
			->will($this->returnValue($this->_testFiles));

		$result = $scanner->find('vendor.css', false);
		$expected = '/vendor/vendor.css';
		$this->assertEquals($expected, $result);

		$result = $scanner->find('nav.css', false);
		$expected = '/css/nav.css';
		$this->assertEquals($expected, $result);
	}

	public function testFindOtherExtension() {
		$paths = array(
			$this->_testFiles . 'css' . DS
		);
		$scanner = new AssetScanner($paths);
		$result = $scanner->find('other.less');
		$expected = $this->_testFiles . 'css' . DS . 'other.less';
		$this->assertEquals($expected, $result);
	}

	public function testFindResolveThemePaths() {
		App::build(array(
			'View' => array($this->_testFiles . 'View' . DS)
		));
		$paths = array(
			$this->_testFiles . 'css' . DS
		);
		$scanner = new AssetScanner($paths, 'Blue');
		$result = $scanner->find('t:theme.css');
		$expected = $this->_testFiles . 'View' . DS . 'Themed' . DS . 'Blue' . DS . 'webroot' . DS . 'theme.css';
		$this->assertEquals($expected, $result);

		$result = $scanner->find('theme:theme.css');
		$this->assertEquals($expected, $result);

		$result = $scanner->find('t:theme.css', false);
		$expected = DS . 'theme' . DS . 'blue' . DS . 'theme.css';
		$this->assertEquals($expected, $result);

		$result = $scanner->find('theme:theme.css', false);
		$this->assertEquals($expected, $result);
	}

	public function testFindResolvePluginPaths() {
		App::build(array(
			'Plugin' => array($this->_testFiles . 'Plugin' . DS)
		));
		CakePlugin::load('TestAsset');

		$paths = array(
			$this->_testFiles . 'css' . DS
		);
		$scanner = new AssetScanner($paths);
		$result = $scanner->find('p:TestAsset:plugin.css');
		$expected = $this->_testFiles . 'Plugin' . DS . 'TestAsset' . DS . 'webroot' . DS . 'plugin.css';
		$this->assertEquals($expected, $result);

		$result = $scanner->find('plugin:TestAsset:plugin.css');
		$this->assertEquals($expected, $result);

		$expected = DS . 'test_asset' . DS . 'plugin.css';
		$result = $scanner->find('plugin:TestAsset:plugin.css', false);
		$this->assertEquals($expected, $result);

		$result = $scanner->find('p:TestAsset:plugin.css', false);
		$this->assertEquals($expected, $result);
	}

	public function testNormalizePaths() {
		$paths = array(
			$this->_testFiles . 'js',
			$this->_testFiles . 'js' . DS . 'classes'
		);
		$scanner = new AssetScanner($paths);

		$result = $scanner->find('base_class.js');
		$expected = $this->_testFiles . 'js' . DS . 'classes' . DS . 'base_class.js';
		$this->assertEquals($expected, $result);
	}

	public function testExpandStarStar() {
		$paths = array(
			$this->_testFiles . 'js' . DS . '**',
		);
		$scanner = new AssetScanner($paths);

		$result = $scanner->paths();
		$expected = array(
			$this->_testFiles . 'js' . DS,
			$this->_testFiles . 'js' . DS . 'classes' . DS,
			$this->_testFiles . 'js' . DS . 'secondary' . DS
		);
		$this->assertEquals($expected, $result);

		$result = $scanner->find('base_class.js');
		$expected = $this->_testFiles . 'js' . DS . 'classes' . DS . 'base_class.js';
		$this->assertEquals($expected, $result);

		$result = $scanner->find('another_class.js');
		$expected = $this->_testFiles . 'js' . DS . 'secondary' . DS . 'another_class.js';
		$this->assertEquals($expected, $result);
	}

	public function testExpandGlob() {
		$paths = array(
			$this->_testFiles . 'js' . DS,
			$this->_testFiles . 'js' . DS . '*'
		);
		$scanner = new AssetScanner($paths);

		$result = $scanner->find('base_class.js');
		$expected = $this->_testFiles . 'js' . DS . 'classes' . DS . 'base_class.js';
		$this->assertEquals($expected, $result);

		$result = $scanner->find('classes' . DS . 'base_class.js');
		$expected = $this->_testFiles . 'js' . DS . 'classes' . DS . 'base_class.js';
		$this->assertEquals($expected, $result);
	}

	public function testIsRemote() {
		$paths = array(
			$this->_testFiles . 'css' . DS
		);
		$scanner = new AssetScanner($paths);
		$this->assertFalse($scanner->isRemote('/Users/markstory/cakephp'));
		$this->assertFalse($scanner->isRemote('C:\\Project\\cakephp'));
		$this->assertTrue($scanner->isRemote('http://example.com'));
	}
}
