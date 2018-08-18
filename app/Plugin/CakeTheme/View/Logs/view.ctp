<?php
    /**
     * This file is the view file of the plugin. Used for rendering
     *  informations of log.
     *
     * CakeTheme: Set theme for application.
     * @copyright Copyright 2016, Andrey Klimov.
     * @package plugin.View.Logs
     */

    $this->assign('title', __d('view_extension', 'Detail information'));

    $this->Html->addCrumb(__d('view_extension', 'Logs'), ['controller' => 'logs', 'action' => 'index', 'plugin' => 'cake_theme']));
    $this->Html->addCrumb(__d('view_extension', 'Viewing'));
?>  
    <div class="container"> 
<?php
        echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
?>  
        <div class="row">
            <div class="col-xs-8 col-xs-offset-2 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">            
<?php
    echo $this->element('CakeTheme.infoLog', compact('log'));
?>              
            </div>
        </div>
    </div>
