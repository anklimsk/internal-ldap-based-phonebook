<?php
/**
 * This file is the console shell task file of the application.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @author Mark Scherer
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package app.Console.Command.Task
 */

App::uses('AppShell', 'Console/Command');

/**
 * This task is used for recovery tree of departments in the queue.
 *
 * @package app.Console.Command.Task
 */
class QueueRecoveryDepartmentTask extends AppShell
{

    /**
     * Adding the QueueTask Model
     *
     * @var array
     */
    public $uses = [
        'Queue.QueuedTask',
        'CakeTheme.ExtendQueuedTask',
        'DepartmentExtension'
    ];

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
    public $timeout = RECOVER_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT;

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
     * Flag auto unserialize data. If true, unserialize data before run task.
     *
     * @var bool
     */
    public $autoUnserialize = true;

    /**
     * Main function.
     *  Used for recovery tree of departments.
     *
     * @param array $data The array passed to QueuedTask->createJob()
     * @param int $id The id of the QueuedTask
     * @triggers Model.afterUpdateTree $this->SubordinateDb
     * @return bool Success
     * @throws RuntimeException when seconds are 0;
     */
    public function run($data, $id = null)
    {
        $this->hr();
        $this->out(__('CakePHP Queue task for recovering tree.'));
        $queueLength = $this->ExtendQueuedTask->getLengthQueue('RecoveryDepartment');
        if ($queueLength > 0) {
            $this->out(__('Found order task in queue: %d. Skipped.', $queueLength));

            return true;
        }

        set_time_limit(RECOVER_TREE_DEPARTMENT_EXTENSION_TIME_LIMIT);
        $this->QueuedTask->updateProgress($id, 0);
        if ($this->DepartmentExtension->verify() === true) {
            $this->QueuedTask->markJobFailed($id, __('The recovery tree of departments is not required'));

            return true;
        }

        $this->QueuedTask->updateProgress($id, 0.5);
        $result = $this->DepartmentExtension->recoverDepartmentList(false);
        if (!$result) {
            $this->QueuedTask->markJobFailed($id, __('Error on recovery tree of departments.'));
        }

        $this->QueuedTask->updateProgress($id, 1);

        return true;
    }
}
