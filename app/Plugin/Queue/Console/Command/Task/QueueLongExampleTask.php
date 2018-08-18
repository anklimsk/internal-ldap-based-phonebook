<?php

App::uses('QueueTask', 'Queue.Console/Command/Task');

/**
 * A Simple QueueTask example that runs for a while.
 *
 */
class QueueLongExampleTask extends QueueTask {

/**
 * ZendStudio Codecomplete Hint
 *
 * @var QueuedTask
 */
	public $QueuedTask;

/**
 * Timeout for run, after which the Task is reassigned to a new worker.
 *
 * @var int
 */
	public $timeout = 120;

/**
 * Number of times a failed instance of this task should be restarted before giving up.
 *
 * @var int
 */
	public $retries = 1;

/**
 * Stores any failure messages triggered during run()
 *
 * @var string
 */
	public $failureMessage = '';

/**
 * Example add functionality.
 * Will create one example job in the queue, which later will be executed using run();
 *
 * @return void
 */
	public function add() {
		$this->out('CakePHP Queue LongExample task.');
		$this->hr();
		$this->out('This is a very simple but long running example of a QueueTask.');
		$this->out('I will now add the Job into the Queue.');
		$this->out('This job will need at least 2 minutes to complete.');
		$this->out(' ');
		$this->out('To run a Worker use:');
		$this->out('	cake Queue.Queue runworker');
		$this->out(' ');
		$this->out('You can find the sourcecode of this task in:');
		$this->out(__FILE__);
		$this->out(' ');
		/*
		 * Adding a task of type 'example' with no additionally passed data
		 */
		if ($this->QueuedTask->createJob('LongExample', 2 * MINUTE)) {
			$this->out('OK, job created, now run the worker');
		} else {
			$this->err('Could not create Job');
		}
	}

/**
 * Example run function.
 * This function is executed, when a worker is executing a task.
 * The return parameter will determine, if the task will be marked completed, or be requeued.
 *
 * @param array $data The array passed to QueuedTask->createJob()
 * @param int $id The id of the QueuedTask
 * @return bool Success
 * @throws RuntimeException when seconds are 0;
 */
	public function run($data, $id = null) {
		$this->hr();
		$this->out('CakePHP Queue LongExample task.');
		$seconds = (int)$data;
		if (!$seconds) {
			throw new RuntimeException('Seconds need to be > 0');
		}
		$this->out('A total of ' . $seconds . ' seconds need to pass...');
		for ($i = 0; $i < $seconds; $i++) {
			sleep(1);
			$this->QueuedTask->updateProgress($id, ($i + 1) / $seconds);
		}
		$this->hr();
		$this->out(' ->Success, the LongExample Job was run.<-');
		$this->out(' ');
		$this->out(' ');
		return true;
	}

}
