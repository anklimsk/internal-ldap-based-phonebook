<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  full list of departments.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Departments
     */

    echo $this->AssetCompress->css('CakeTheme.tree', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.tree', ['block' => 'script']);

    $this->assign('title', $pageHeader);
    $this->ViewExtension->addBreadCrumbs($breadCrumbs);
?>
<div class="container">
<?php
    echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
    echo $this->element('tableDepartment', compact('departments'));
?>
</div>
