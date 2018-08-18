<?php
/**
 * This file is the console shell task file of the application.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @author Mark Scherer
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package app.Console.Command.Task
 */

App::uses('AppShell', 'Console/Command');

/**
 * This task is used for renaming department in the queue.
 *
 * @package app.Console.Command.Task
 */
class QueueRenameDepartmentTask extends AppShell
{

    /**
     * Adding the QueueTask Model
     *
     * @var array
     */
    public $uses = [
        'Queue.QueuedTask',
        'Department'
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
    public $timeout = TASK_RENAME_DEPARTMENT_TIME_LIMIT;

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
     * Used for renaming department.
     *
     * @param array $data The array passed to QueuedTask->createJob()
     * @param int $id The id of the QueuedTask
     * @return bool Success
     * @throws RuntimeException when seconds are 0;
     */
    public function run($data, $id = null)
    {
        $this->hr();
        $this->out(__('CakePHP Queue task for renaming department.'));
        if (empty($data) || !is_array($data)) {
            $data = [];
        }
        $dataDefault = [
            'oldName' => null,
            'newName' => null,
            'userRole' => null,
            'userId' => null,
            'useLdap' => false,
        ];
        $data += $dataDefault;
        extract($data);

        $result = $this->Department->renameDepartment($oldName, $newName, $userRole, $userId, $useLdap);
        if (!$result) {
            $this->QueuedTask->markJobFailed($id, __('Error on renaming department from "%s" to "%s"', $oldName, $newName));
        }
        $this->QueuedTask->updateProgress($id, 1);

        return true;
    }
}
