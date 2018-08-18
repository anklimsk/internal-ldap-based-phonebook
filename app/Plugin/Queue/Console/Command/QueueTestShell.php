<?php
App::uses('AppShell', 'Console/Command');
App::uses('EmailLib', 'Tools.Lib');

if (!defined('FORMAT_DB_DATE')) {
	define('FORMAT_DB_DATETIME', 'Y-m-d H:i:s');
}

/**
 * Testing email etc
 *
 * @author Mark Scherer
 */
class QueueTestShell extends AppShell {

	public $uses = [
		'Queue.QueuedTask'
	];

/**
 * Test queue of email job
 *
 * @return void
 */
	public function email() {
		$data = [
			'settings' => [
				'subject' => 'Some test - ' . date(FORMAT_DB_DATETIME),
				'to' => Configure::read('Config.admin_email'),
				'domain' => 'example.org',
			],
			'vars' => [
				'content' => 'I am a test',
			]
		];

		if ($this->QueuedTask->createJob('Email', $data)) {
			$this->out('OK, test email created');
		} else {
			$this->err('Could not create test email');
		}
	}

/**
 * Test sending emails via CLI and Queue transport.
 *
 * @return void
 */
	public function complete_email() {
		Configure::write('debug', 0);
		$Email = new EmailLib();
		$Email->to('markscherer@gmx.de', 'Mark Test');
		$Email->subject('Testing Message');
		$host = Configure::read('App.host');
		if ($host) {
			$Email->domain($host);
		}

		$config = $Email->config();
		if (!isset($config['queue'])) {
			$this->error('queue key in config missing');
		}

		$res = $Email->send('Foo');
		if (!$res) {
			$this->error('Could not send email: ' . $Email->getError());
		}
		$this->out('YEAH!');
	}

}
