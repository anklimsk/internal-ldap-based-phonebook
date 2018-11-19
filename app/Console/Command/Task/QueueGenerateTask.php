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
 * This task is used for generate export files in the queue.
 *
 * @package app.Console.Command.Task
 */
class QueueGenerateTask extends AppShell
{

    /**
     * Adding the QueueTask Model
     *
     * @var array
     */
    public $uses = [
        'Queue.QueuedTask',
        'CakeTheme.ExtendQueuedTask',
        'Employee'
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
    public $timeout = TASK_EXPORT_GENERATE_TIME_LIMIT;

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
     * Used for generating export files.
     *
     * @param array $data The array passed to QueuedTask->createJob()
     * @param int $id The id of the QueuedTask
     * @return bool Success
     * @throws RuntimeException when seconds are 0;
     */
    public function run($data, $id = null)
    {
        $this->hr();
        $this->out(__('CakePHP Queue task for generating files.'));
        if (empty($data) || !is_array($data)) {
            $data = [];
        }
        $dataDefault = [
            'view' => null,
            'type' => null,
        ];
        $data += $dataDefault;
        extract($data);
        if (empty($view) || empty($type)) {
            return false;
        }

        if (($view === GENERATE_FILE_VIEW_TYPE_ALL) &&
            ($type === GENERATE_FILE_DATA_TYPE_ALL)) {
            $queueLength = $this->ExtendQueuedTask->getLengthQueue('Generate');
            if ($queueLength > 0) {
                $this->out(__('Found generating task in queue: %d. Skipped.', $queueLength));

                return true;
            }
        }

        $this->Employee->generateExportTask($view, $type, $id);

        return true;
    }
}
