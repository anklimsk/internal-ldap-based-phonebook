<?php
    /**
     * This file is the view file of the plugin. Used for render
     *  interface of settings application.
     *
     * CakeSettingsApp: Manage settings of application.
     * @copyright Copyright 2016, Andrey Klimov.
     * @package plugin.View.Settings
     */

    echo $this->AssetCompress->css('CakeTheme.flagstrap', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.flagstrap', ['block' => 'script']);

    $this->assign('title', __d('cake_settings_app', 'Application settings'));

    $this->Html->addCrumb(__d('cake_settings_app', 'Application settings'), ['controller' => 'settings', 'action' => 'index', 'plugin' => 'cake_settings_app']);
    $this->Html->addCrumb(__d('cake_settings_app', 'Settings'));
?>
    <div class="container">
<?php
    echo $this->ViewExtension->headerPage($pageHeader);
    echo $this->element('CakeSettingsApp.formSettings', compact(
        'errors',
        'countryCode',
        'groupList',
        'configUIlangs',
        'configSMTP',
        'configAcLimit',
        'configADsearch',
        'configExtAuth',
        'authGroups',
        'UIlang',
        'UIlangs',
        'varsExt'
    ));
?>  
    </div>
