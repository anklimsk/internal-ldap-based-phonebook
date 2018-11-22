<?php
App::uses('AppControllerTestCase', 'Test');
App::uses('LogsController', 'Controller');

/**
 * LogsView Test Case
 */
class LogsViewTest extends AppControllerTestCase {

/**
 * Target Controller name
 *
 * @var string
 */
	public $targetController = 'Logs';

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		'core.cake_session',
		'app.deferred',
		'app.log',
		'plugin.cake_ldap.department',
		'plugin.cake_ldap.employee',
		'plugin.cake_ldap.employee_ldap',
	];

/**
 * testIndex method
 *  User role: admin
 *
 * @return void
 */
	public function testIndexForAdmin() {
		$userInfo = [
			'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
			'prefix' => 'admin',
		];
		$this->applyUserInfo($userInfo);
		$this->generateMockedController();
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$view = $this->testAction('/admin/logs/index', $opt);
		$expected = 4;
		$numTableRows = $this->getNumberItemsByCssSelector($view, 'div#content div.container table > tbody > tr');
		$this->assertData($expected, $numTableRows);
	}

/**
 * testViewSuccessForAdmin method
 *  User role: admin
 *
 * @return void
 */
	public function testViewSuccessForAdmin() {
		$userInfo = [
			'role' => USER_ROLE_USER | USER_ROLE_ADMIN,
			'prefix' => 'admin',
		];
		$this->applyUserInfo($userInfo);
		$this->generateMockedController();
		$opt = [
			'method' => 'GET',
			'return' => 'contents',
		];
		$view = $this->testAction('/admin/logs/view/1', $opt);
		$numDl = $this->getNumberItemsByCssSelector($view, 'div#content div.container dl.dl-horizontal');
		$expected = 2;
		$this->assertData($expected, $numDl);
	}
}
