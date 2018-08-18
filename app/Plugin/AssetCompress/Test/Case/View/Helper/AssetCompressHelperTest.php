<?php
App::uses('AssetConfig', 'AssetCompress.Lib');
App::uses('AssetCompiler', 'AssetCompress.Lib');
App::uses('AssetCompressHelper', 'AssetCompress.View/Helper');

App::uses('HtmlHelper', 'View/Helper');
App::uses('View', 'View');

class AssetCompressHelperTest extends CakeTestCase {

/**
 * start a test
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->_pluginPath = App::pluginPath('AssetCompress');
		$this->_testFiles = $this->_pluginPath . 'Test' . DS . 'test_files' . DS;
		$testFile = $this->_testFiles . 'Config' . DS . 'config.ini';

		AssetConfig::clearAllCachedKeys();

		Cache::drop(AssetConfig::CACHE_CONFIG);
		Cache::config(AssetConfig::CACHE_CONFIG, array(
			'path' => TMP,
			'prefix' => 'asset_compress_test_',
			'engine' => 'File'
		));

		$controller = null;
		$request = new CakeRequest(null, false);
		$request->webroot = '';
		$view = new View($controller);
		$view->request = $request;
		$this->Helper = new AssetCompressHelper($view, array('noconfig' => true));
		$Config = AssetConfig::buildFromIniFile($testFile);
		$this->Helper->config($Config);
		$this->Helper->Html = new HtmlHelper($view);

		Router::reload();
		Configure::write('debug', 2);
		Configure::write('App.fullBaseUrl', 'http://example.com');
	}

/**
 * end a test
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Helper);

		Cache::delete(AssetConfig::CACHE_BUILD_TIME_KEY, AssetConfig::CACHE_CONFIG);
		Cache::drop(AssetConfig::CACHE_CONFIG);
		// @codingStandardsIgnoreStart
		@unlink(TMP . AssetConfig::BUILD_TIME_FILE);
		// @codingStandardsIgnoreEnd
	}

/**
 * test that assets only have one base path attached
 *
 * @return void
 */
	public function testIncludeAssets() {
		Router::setRequestInfo(array(
			array('controller' => 'posts', 'action' => 'index', 'plugin' => null),
			array('base' => '/some/dir', 'webroot' => '/some/dir/', 'here' => '/some/dir/posts')
		));
		$this->Helper->Html->webroot = '/some/dir/';

		$this->Helper->addScript('one.js');
		$result = $this->Helper->includeAssets();
		$this->assertRegExp('#"/some/dir/cache_js/*#', $result, 'double dir set %s');
	}

/**
 * test that setting $compress = false echos original scripts
 *
 * @return void
 */
	public function testNoCompression() {
		$config = $this->Helper->config();
		$config->paths('css', null, array(
			$this->_testFiles . 'css' . DS
		));
		$config->paths('js', null, array(
			$this->_testFiles . 'js' . DS
		));

		$this->Helper->addCss('background.css', 'lib');
		$this->Helper->addCss('nav.css');
		$this->Helper->addScript('library_file.js');
		$this->Helper->addScript('classes/base_class.js');

		$result = $this->Helper->includeAssets(true);
		$expected = array(
			array(
				'link' => array(
					'type' => 'text/css',
					'rel' => 'stylesheet',
					'href' => 'preg:/.*css\/background\.css/'
				)
			),
			array(
				'link' => array(
					'type' => 'text/css',
					'rel' => 'stylesheet',
					'href' => 'preg:/.*css\/nav\.css/'
				)
			),
			array(
				'script' => array(
					'type' => 'text/javascript',
					'src' => 'preg:/.*js\/library_file\.js/'
				)
			),
			'/script',
			array(
				'script' => array(
					'type' => 'text/javascript',
					'src' => 'preg:/.*js\/classes\/base_class\.js/'
				)
			),
			'/script'
		);
		$this->assertTags($result, $expected, true);
	}

/**
 * test css addition
 *
 * @return void
 */
	public function testCssOrderPreserving() {
		$this->Helper->addCss('base');
		$this->Helper->addCss('reset');

		$hash = md5('base_reset');

		$result = $this->Helper->includeAssets();
		$expected = array(
			'link' => array(
				'type' => 'text/css',
				'rel' => 'stylesheet',
				'href' => '/cache_css/' . $hash . '.css?file%5B0%5D=base&amp;file%5B1%5D=reset'
			)
		);
		$this->assertTags($result, $expected);
	}

/**
 * test script addition
 *
 * @return void
 */
	public function testScriptOrderPreserving() {
		$this->Helper->addScript('libraries');
		$this->Helper->addScript('thing');

		$hash = md5('libraries_thing');

		$result = $this->Helper->includeAssets();
		$expected = array(
			'script' => array(
				'type' => 'text/javascript',
				'src' => '/cache_js/' . $hash . '.js?file%5B0%5D=libraries&amp;file%5B1%5D=thing'
			),
			'/script'
		);
		$this->assertTags($result, $expected);
	}

/**
 * test that magic slug builds work.
 *
 * @return void
 */
	public function testScriptMagicSlugs() {
		$this->Helper->addScript('libraries', ':hash-default');
		$this->Helper->addScript('thing', ':hash-default');
		$this->Helper->addScript('jquery.js', ':hash-jquery');
		$this->Helper->addScript('jquery-ui.js', ':hash-jquery');

		$hashOne = md5('libraries_thing');
		$hashTwo = md5('jquery.js_jquery-ui.js');

		$result = $this->Helper->includeAssets();
		$expected = array(
			array('script' => array(
				'type' => 'text/javascript',
				'src' => '/cache_js/' . $hashOne . '.js?file%5B0%5D=libraries&amp;file%5B1%5D=thing'
			)),
			'/script',
			array('script' => array(
				'type' => 'text/javascript',
				'src' => '/cache_js/' . $hashTwo . '.js?file%5B0%5D=jquery.js&amp;file%5B1%5D=jquery-ui.js'
			)),
			'/script'
		);
		$this->assertTags($result, $expected);
	}

/**
 * test generating two script files.
 *
 * @return void
 */
	public function testMultipleScriptFiles() {
		$this->Helper->addScript('libraries', 'default');
		$this->Helper->addScript('thing', 'second');

		$result = $this->Helper->includeAssets();
		$expected = array(
			array('script' => array(
				'type' => 'text/javascript',
				'src' => '/cache_js/default.js?file%5B0%5D=libraries'
			)),
			'/script',
			array('script' => array(
				'type' => 'text/javascript',
				'src' => '/cache_js/second.js?file%5B0%5D=thing'
			)),
			'/script'
		);
		$this->assertTags($result, $expected);
	}

/**
 * test includeJs() with multiple destination files.
 *
 * @return void
 */
	public function testIncludeJsMultipleDestination() {
		$this->Helper->addScript('libraries', 'default');
		$this->Helper->addScript('thing', 'second');
		$this->Helper->addScript('other', 'third');

		$result = $this->Helper->includeJs('default');
		$expected = array(
			array('script' => array(
				'type' => 'text/javascript',
				'src' => '/cache_js/default.js?file%5B0%5D=libraries'
			)),
		);
		$this->assertTags($result, $expected);

		$result = $this->Helper->includeJs('second', 'third');
		$expected = array(
			array('script' => array(
				'type' => 'text/javascript',
				'src' => '/cache_js/second.js?file%5B0%5D=thing'
			)),
			'/script',
			array('script' => array(
				'type' => 'text/javascript',
				'src' => '/cache_js/third.js?file%5B0%5D=other'
			)),
			'/script'
		);
		$this->assertTags($result, $expected);
	}

/**
 * test includeCss() with multiple destination files.
 *
 * @return void
 */
	public function testIncludeCssMultipleDestination() {
		$this->Helper->addCss('libraries', 'default');
		$this->Helper->addCss('thing', 'second');
		$this->Helper->addCss('other', 'third');

		$result = $this->Helper->includeCss('second', 'default');
		$expected = array(
			array('link' => array(
				'type' => 'text/css',
				'rel' => 'stylesheet',
				'href' => '/cache_css/second.css?file%5B0%5D=thing'
			)),
			array('link' => array(
				'type' => 'text/css',
				'rel' => 'stylesheet',
				'href' => '/cache_css/default.css?file%5B0%5D=libraries'
			)),
		);
		$this->assertTags($result, $expected);
	}

/**
 * test that including assets removes them from the list of files to be included.
 *
 * @return void
 */
	public function testIncludingFilesRemovesFromQueue() {
		$this->Helper->addCss('libraries', 'default');
		$result = $this->Helper->includeCss('default');
		$expected = array(
			'link' => array(
				'type' => 'text/css',
				'rel' => 'stylesheet',
				'href' => '/cache_css/default.css?file%5B0%5D=libraries'
			)
		);
		$this->assertTags($result, $expected);

		$result = $this->Helper->includeCss('default');
		$this->assertEquals('', $result);
	}

/**
 * Test that generated elements can have attributes added.
 *
 */
	public function testAttributesOnElements() {
		$result = $this->Helper->script('libs.js', array('defer' => true));

		$expected = array(
			array('script' => array(
				'defer' => 'defer',
				'type' => 'text/javascript',
				'src' => '/cache_js/libs.js'
			))
		);
		$this->assertTags($result, $expected);

		$result = $this->Helper->css('all.css', array('test' => 'value'));
		$expected = array(
			'link' => array(
				'type' => 'text/css',
				'test' => 'value',
				'rel' => 'stylesheet',
				'href' => '/cache_css/all.css'
			)
		);
		$this->assertTags($result, $expected);
	}

/**
 * test that a baseurl configuration works well.
 *
 * @return void
 */
	public function testBaseUrl() {
		Configure::write('debug', 0);
		$config = $this->Helper->config();
		$config->set('js.baseUrl', 'http://cdn.example.com/js/');
		$config->set('js.timestamp', false);

		$result = $this->Helper->script('libs.js');
		$expected = array(
			array('script' => array(
				'type' => 'text/javascript',
				'src' => 'http://cdn.example.com/js/libs.js'
			))
		);
		$this->assertTags($result, $expected);

		Configure::write('debug', 1);
		$result = $this->Helper->script('libs.js');
		$expected = array(
			array('script' => array(
				'type' => 'text/javascript',
				'src' => '/cache_js/libs.js'
			))
		);
		$this->assertTags($result, $expected);
	}

/**
 * Test that builds using themes defined in the ini file work
 * with themes.
 *
 * @return void
 */
	public function testDefinedBuildWithThemeNoBuiltAsset() {
		$this->Helper->theme = 'blue';
		$config = $this->Helper->config();
		$config->addTarget('themed.js', array(
			'theme' => true,
			'files' => array('libraries.js')
		));
		$result = $this->Helper->script('themed.js');
		$expected = array(
			array('script' => array(
				'type' => 'text/javascript',
				'src' => '/cache_js/themed.js?theme=blue'
			))
		);
		$this->assertTags($result, $expected);
	}

	public function testRawAssets() {
		$Config = AssetConfig::buildFromIniFile($this->_testFiles . 'Config' . DS . 'config.ini');

		$paths = array(
			$this->_testFiles . 'js' . DS
		);

		$helper = $this->getMock('AssetCompressHelper', array('_getScanner'), array(new View(), array('noconfig' => true)));
		$helper->config($Config);

		$scanner = $this->getMock('AssetScanner', array('_getWebroot'), array($paths, null));
		$scanner
			->expects($this->any())
			->method('_getWebroot')
			->will($this->returnValue($this->_testFiles));
		$helper
			->expects($this->any())
			->method('_getScanner')
			->will($this->returnValue($scanner));

		$config = $helper->config();
		$config->addTarget('raw.js', array(
			'files' => array('classes/base_class.js', 'classes/base_class_two.js')
		));
		$config->paths('js', null, $paths);

		$result = $helper->script('raw.js', array('raw' => true));
		$expected = array(
			array(
				'script' => array(
					'type' => 'text/javascript',
					'src' => '/js/classes/base_class.js'
				),
			),
			'/script',
			array(
				'script' => array(
					'type' => 'text/javascript',
					'src' => '/js/classes/base_class_two.js'
				),
			),
			'/script',
		);
		$this->assertTags($result, $expected);
	}

	public function testRawAssetsPlugin() {
		App::build(array(
			'Plugin' => array($this->_testFiles . 'Plugin' . DS)
		));
		CakePlugin::load('TestAsset');

		$config = AssetConfig::buildFromIniFile($this->_testFiles . 'Config/plugins.ini');
		$config->paths('css', null, array(
			$this->_testFiles . 'css' . DS
		));
		$config->paths('js', null, array(
			$this->_testFiles . 'js' . DS
		));
		$this->Helper->config($config);

		$result = $this->Helper->css('plugins.css', array('raw' => true));
		$expected = array(
			array(
				'link' => array(
					'type' => 'text/css',
					'rel' => 'stylesheet',
					'href' => 'preg:/.*css\/nav.css/'
				)
			),
			array(
				'link' => array(
					'type' => 'text/css',
					'rel' => 'stylesheet',
					'href' => '/test_asset/plugin.css'
				)
			),
		);
		$this->assertTags($result, $expected);

		$result = $this->Helper->script('plugins.js', array('raw' => true));
		$expected = array(
			array(
				'script' => array(
					'type' => 'text/javascript',
					'src' => '/test_asset/plugin.js'
				)
			)
		);
		$this->assertTags($result, $expected);
	}

	public function testCompiledBuildWithThemes() {
		Configure::write('debug', 0);
		$config = $this->Helper->config();
		$config->general('writeCache', true);
		$config->set('js.timestamp', false);
		$config->cachePath('js', TMP);
		$config->addTarget('asset_test.js', array(
			'files' => array('one.js'),
			'theme' => true
		));

		$this->Helper->theme = 'blue';
		$result = $this->Helper->script('asset_test.js');
		$result = str_replace('/', DS, $result);
		$this->assertContains('blue-asset_test.js', $result);
	}

	public function testUrlBasic() {
		$url = $this->Helper->url('all.css');
		$this->assertEquals('/cache_css/all.css', $url);

		$url = $this->Helper->url('libs.js');
		$this->assertEquals('/cache_js/libs.js', $url);
	}

	public function testUrlProductionMode() {
		Configure::write('debug', 0);
		$this->Helper->config()->set('js.timestamp', false);

		$result = $this->Helper->url('libs.js');
		$this->assertEquals('/cache_js/libs.js', $result);
	}

	public function testUrlFullOption() {
		$version = Configure::read('Cake.version');
		$this->skipIf(version_compare($version, '2.4.0', '<'));

		$result = $this->Helper->url('libs.js', array('full' => true));
		$this->assertEquals(
			'http://example.com/cache_js/libs.js',
			$result
		);

		$result = $this->Helper->url('libs.js', true);
		$this->assertEquals(
			'http://example.com/cache_js/libs.js',
			$result
		);
	}

/**
 * test that baseurl and timestamps play nice.
 *
 * @return void
 */
	public function testUrlWithBaseUrlAndTimestamp() {
		Configure::write('debug', 0);
		$config = $this->Helper->config();
		$config->set('js.baseUrl', 'http://cdn.example.com/js/');
		$config->set('js.timestamp', true);
		$config->general('cacheConfig', true);

		// populate the cache.
		Cache::write(AssetConfig::CACHE_BUILD_TIME_KEY, array('libs.js' => 1234), AssetConfig::CACHE_CONFIG);

		$result = $this->Helper->url('libs.js');
		$expected = 'http://cdn.example.com/js/libs.v1234.js';
		$this->assertEquals($expected, $result);
	}

/**
 * Test exceptions when getting URLs
 *
 * @expectedException Exception
 * @expectedExceptionMessage Cannot get URL for build file that does not exist.
 */
	public function testUrlError() {
		$this->Helper->url('nope.js');
	}

/**
 * test in development script links are created
 *
 * @return void
 */
	public function testInlineCssDevelopment() {
		$config = $this->Helper->config();
		$config->paths('css', null, array(
			$this->_testFiles . 'css' . DS
		));

		$config->addTarget('nav.css', array(
			'files' => array('nav.css')
		));

		Configure::write('debug', 1);
		$results = $this->Helper->inlineCss('nav.css');

		$expected = <<<EOF
<style type="text/css">@import url("reset/reset.css");
#nav {
	width:100%;
}</style>
EOF;
		$this->assertEquals($expected, $results);
	}

/**
 * test inline css is generated
 *
 * @return void
 */
	public function testInlineCss() {
		$config = $this->Helper->config();
		$config->paths('css', null, array(
			$this->_testFiles . 'css' . DS
		));

		$config->addTarget('nav.css', array(
			'files' => array('nav.css')
		));

		Configure::write('debug', 0);

		$expected = <<<EOF
<style type="text/css">@import url("reset/reset.css");
#nav {
	width:100%;
}</style>
EOF;

		$result = $this->Helper->inlineCss('nav.css');
		$this->assertEquals($expected, $result);
	}

/**
 * test inlineCss() with multiple input files.
 *
 * @return void
 */
	public function testInlineCssMultiple() {
		$config = $this->Helper->config();
		$config->paths('css', null, array(
			$this->_testFiles . 'css' . DS
		));

		$config->addTarget('nav.css', array(
			'files' => array('nav.css', 'has_import.css')
		));

		Configure::write('debug', 0);

		$expected = <<<EOF
<style type="text/css">@import url("reset/reset.css");
#nav {
	width:100%;
}

@import "nav.css";
@import "theme:theme.css";
body {
	color:#f00;
	background:#000;
}</style>
EOF;

		$result = $this->Helper->inlineCss('nav.css');
		$this->assertEquals($expected, $result);
	}

/**
 * test in development script links are created
 *
 * @return void
 */
	public function testInlineScriptDevelopment() {
		$config = $this->Helper->config();
		$config->set('js.filters', array());

		$config->paths('js', null, array(
			$this->_testFiles . 'js' . DS . 'classes'
		));

		$config->addTarget('all.js', array(
			'files' => array('base_class.js')
		));

		Configure::write('debug', 1);
		$results = $this->Helper->inlineScript('all.js');

		$expected = <<<EOF
<script>var BaseClass = new Class({

});</script>
EOF;

		$this->assertEquals($expected, $results);
	}

/**
 * test inline javascript is generated
 *
 * @return void
 */
	public function testInlineScript() {
		$config = $this->Helper->config();
		$config->set('js.filters', array());
		$config->paths('js', null, array(
			$this->_testFiles . 'js' . DS . 'classes'
		));

		$config->addTarget('all.js', array(
			'files' => array('base_class.js')
		));

		Configure::write('debug', 0);

		$expected = <<<EOF
<script>var BaseClass = new Class({

});</script>
EOF;

		$result = $this->Helper->inlineScript('all.js');
		$this->assertEquals($expected, $result);
	}

/**
 * test inline javascript for multiple files is generated
 *
 * @return void
 */
	public function testInlineScriptMultiple() {
		$config = $this->Helper->config();
		$config->set('js.filters', array());
		$config->paths('js', null, array(
			$this->_testFiles . 'js' . DS . 'classes'
		));

		$config->addTarget('all.js', array(
			'files' => array('base_class.js', 'base_class_two.js')
		));

		Configure::write('debug', 0);

		$expected = <<<EOF
<script>var BaseClass = new Class({

});
//= require "base_class"
var BaseClassTwo = BaseClass.extend({

});</script>
EOF;

		$result = $this->Helper->inlineScript('all.js');
		$this->assertEquals($expected, $result);
	}
}
