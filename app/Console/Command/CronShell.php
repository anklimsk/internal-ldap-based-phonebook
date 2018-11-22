<?php
/**
 * This file is the console shell file of the application.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.Console.Command
 */

App::uses('AppShell', 'Console/Command');
App::uses('CakeText', 'Utility');

/**
 * This shell is used to execute tasks on a schedule.
 *
 * @package app.Console.Command
 */
class CronShell extends AppShell {

/**
 * Contains tasks to load and instantiate
 *
 * @var array
 * @link http://book.cakephp.org/2.0/en/console-and-shells.html#Shell::$tasks
 */
	public $tasks = [
		'Deferred',
		'Generate'
	];

/**
 * Gets the option parser instance and configures it.
 *
 * @return ConsoleOptionParser
 * @link http://book.cakephp.org/2.0/en/console-and-shells.html#Shell::getOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->addSubcommands([
			SHELL_CRON_TASK_DEFFERED => [
					'help' => __('Checking new deferred save'),
					'parser' => $this->Deferred->getOptionParser()
			],
			SHELL_CRON_TASK_GENERATE => [
					'help' => __('Generate PDF or Excel files'),
					'parser' => $this->Generate->getOptionParser()
			],
		]);

		return $parser;
	}

/**
 * Main method for this task (call default).
 *
 * @return void
 */
	public function main() {
		$this->out(__('Cron task of the shell'));
		$this->hr();
		$this->out(__('This shell is used to execute task scheduled.'));
		$this->out(__('Available tasks: %s.', CakeText::toList(constsVals('SHELL_CRON_TASK_'), __('and'))));
	}
}
