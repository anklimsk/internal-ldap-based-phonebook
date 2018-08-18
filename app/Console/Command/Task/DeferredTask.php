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
 * This task is used to checking new deferred saves.
 *
 * @package app.Console.Command.Task
 */
class DeferredTask extends AppShell
{

    /**
     * Contains models to load and instantiate
     *
     * @var array
     * @link http://book.cakephp.org/2.0/en/console-and-shells.html#Shell::$uses
     */
    public $uses = ['Deferred'];

    /**
     * Gets the option parser instance and configures it.
     *
     * @return ConsoleOptionParser
     * @link http://book.cakephp.org/2.0/en/console-and-shells.html#Shell::getOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description(__('This task is used to checking new deferred saves scheduled.'));

        return $parser;
    }

    /**
     * Main method for this task (call default).
     *
     * @return void
     */
    public function execute()
    {
        $this->out(__('Checking new deferred saves in progress...'), 1, Shell::NORMAL);

        if ($this->Deferred->checkNewDeferredSave()) {
            $this->out(__('Checking new deferred saves complete successfully.'), 1, Shell::NORMAL);
        } else {
            $this->out('<error>' . __('Checking new deferred saves complete unsuccessfully.') . '</error>');
        }
    }
}
