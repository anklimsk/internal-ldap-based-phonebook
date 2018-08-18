<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  full list gallery of employees.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Employees
     */

    echo $this->AssetCompress->css('CakeTheme.tree', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.tree', ['block' => 'script']);

    $this->assign('title', __('Gallery of employees'));

    $this->Html->addCrumb(__('Employees'), ['controller' => 'employees', 'action' => 'search']);
    $this->Html->addCrumb(__('Gallery'));
?>  
    <div class="container">
<?php
        echo $this->ViewExtension->headerPage($pageHeader);
        echo $this->element('tableEmployeeGallery', compact('employeesGallery', 'emptyDepartmentName'));
?>  
    </div>
