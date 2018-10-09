<?php
/**
 * This file is the view file of the application. Used for editing
 *  information of department.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Departments
 */

$this->assign('title', $pageHeader);
$this->ViewExtension->addBreadCrumbs($breadCrumbs);
?>
    <div class="container">
<?php
        echo $this->ViewExtension->headerPage($pageHeader);
?>
        <div class="row">
            <div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
<?php
        echo $this->element('formDepartmentBase', compact('fieldInputMask'));
?>
            </div>
        </div>
    </div>
