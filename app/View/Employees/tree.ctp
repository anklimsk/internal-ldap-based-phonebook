<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  tree of employees.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Employees
     */

    echo $this->AssetCompress->css('CakeTheme.tree', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.tree', ['block' => 'script']);
    echo $this->AssetCompress->css('CakeTheme.fileupload', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-image-min', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-image', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-i18n-' . $uiLcid2, ['block' => 'script']);

    $this->assign('title', __('Tree view information of employees'));

    $this->Html->addCrumb(__('Employees'), ['controller' => 'employees', 'action' => 'search']);
    $this->Html->addCrumb(__('Tree viewing'));
?>  
    <div class="container">
<?php
        echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
?>
        <div class="row bottom-buffer">
            <div class="col-xs-8 col-xs-offset-2 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">
<?php
        $dropUrl = $this->Html->url([
            'controller' => 'employees',
            'action' => 'drop',
            'ext' => 'json'
        ]);
        echo $this->element('CakeLdap.infoSubordinate', [
            'subordinate' => $employees,
            'draggable' => $isTreeDraggable,
            'expandAll' => $expandAll,
            'dropUrl' => $dropUrl
        ]);
?>          
            </div>
        </div>
    </div>
