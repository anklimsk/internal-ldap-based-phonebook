<?php
/**
 * This file is the console shell task file of the application.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.Console.Command.Task
 */

App::uses('AppShell', 'Console/Command');

/**
 * This task is used to generate export files.
 *
 * @package app.Console.Command.Task
 */
class GenerateTask extends AppShell
{

    /**
     * Contains models to load and instantiate
     *
     * @var array
     * @link http://book.cakephp.org/2.0/en/console-and-shells.html#Shell::$uses
     */
    public $uses = [
        'Queue.QueuedTask',
        'Employee'
    ];

    /**
     * Gets the option parser instance and configures it.
     *
     * @return ConsoleOptionParser
     * @link http://book.cakephp.org/2.0/en/console-and-shells.html#Shell::getOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->addArguments([
            'view' => [
                'help' => __('The view of generated files.'),
                'required' => true,
                'choices' => [GENERATE_FILE_VIEW_TYPE_ALL, GENERATE_FILE_VIEW_TYPE_PDF, GENERATE_FILE_VIEW_TYPE_EXCEL]
            ],
            'type' => [
                'help' => __('The type of generated files.'),
                'required' => true,
                'choices' => [GENERATE_FILE_DATA_TYPE_ALL, GENERATE_FILE_DATA_TYPE_ALPH, GENERATE_FILE_DATA_TYPE_DEPART]
            ]
        ])->description(__('This task is used to generate PDF or Excel files scheduled.'));

        return $parser;
    }

    /**
     * Main method for this task (call default).
     *
     * @uses Shell::$args to retrieve `View` and `Type`
     * @return void
     */
    public function execute()
    {
        if (count($this->args) < 2) {
            $this->out('<error>' . __("Empty parameters. Run this task with parameter '-h' or '--help'") . '</error>');

            return;
        }

        $view = $this->args[0];
        $type = $this->args[1];
        if (!in_array($view, constsVals('GENERATE_FILE_VIEW_TYPE_')) ||
            !in_array($type, constsVals('GENERATE_FILE_DATA_TYPE_'))) {
            $this->out('<error>' . __("Invalid parameters. Run this task with parameter '-h' or '--help'") . '</error>');

            return;
        }

        $viewName = mb_ucfirst(constValToLcSingle('GENERATE_FILE_VIEW_TYPE_', $view, ' '));
        $typeName = mb_ucfirst(constValToLcSingle('GENERATE_FILE_DATA_TYPE_', $type, ' '));
        $this->out(__('Generate %s files (%s) in progress...', $viewName, $typeName), 1, Shell::NORMAL);
        if ($this->QueuedTask->createJob('Generate', [$view, $type], null, 'export')) {
            $this->out(__('Generate %s files set in queue successfully.', $viewName), 1, Shell::NORMAL);
        } else {
            $this->out('<error>' . __('Generate %s set in queue unsuccessfully.', $viewName) . '</error>');
        }
    }
}
