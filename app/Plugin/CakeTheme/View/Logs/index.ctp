<?php
    /**
     * This file is the view file of the plugin. Used for rendering
     *  table of logs.
     *
     * CakeTheme: Set theme for application.
     * @copyright Copyright 2016, Andrey Klimov.
     * @package plugin.View.Logs
     */

    $this->assign('title', __d('view_extension', 'Logs'));

    $this->Html->addCrumb(__d('view_extension', 'Logs'), ['controller' => 'logs', 'action' => 'index', 'plugin' => 'cake_theme']);
    $this->Html->addCrumb(__d('view_extension', 'Index'));
?>
<div class="container-fluid">
<?php
    echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
    echo $this->element('CakeTheme.tableLog', compact('logs'));
?>
</div>
