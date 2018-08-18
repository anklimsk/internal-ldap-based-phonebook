<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  full list of logs.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Logs
     */

    echo $this->AssetCompress->css('CakeTheme.tree', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.tree', ['block' => 'script']);
    echo $this->AssetCompress->css('CakeTheme.fileupload', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-image-min', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-image', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-i18n-' . $uiLcid2, ['block' => 'script']);

    $this->assign('title', __('Logs'));

    $this->Html->addCrumb(__('Logs'), ['controller' => 'logs', 'action' => 'index']);
    $this->Html->addCrumb(__('Index'));
?>
<div class="container">
<?php
    echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
    echo $this->element('tableLog', compact('logs', 'fieldsLabel', 'fieldsConfig'));
?>
</div>
