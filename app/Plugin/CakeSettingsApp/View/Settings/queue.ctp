<?php
    /**
     * This file is the view file of the application. Used for settings application.
     *
     * @copyright Copyright 2014-2015, Andrey Klimov.
     * @package app.View.Settings
     */

    $this->assign('title', __d('cake_settings_app', 'Queue of tasks'));

    $this->Html->addCrumb(__d('cake_settings_app', 'Application settings'), ['controller' => 'settings', 'action' => 'index', 'plugin' => 'cake_settings_app']);
    $this->Html->addCrumb(__d('cake_settings_app', 'Queue of tasks'));
?>
<div class="container-fluid" data-toggle="repeat" data-repeat-time="300">
<?php
    echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
if (!empty($stateData)) {
    echo $this->Html->tag('h3', __d('cake_settings_app', 'State queue of tasks'), ['class' => 'text-center']);
    echo $this->ViewExtension->barState($stateData);
}
    echo $this->element('tableQueue', compact('queue', 'groupActions', 'taskStateList', 'usePost'));
?>
</div>
