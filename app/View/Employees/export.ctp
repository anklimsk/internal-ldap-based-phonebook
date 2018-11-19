<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  full list of exported files.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
     * @package app.View.Employees
     */

    $this->assign('title', $pageHeader);
    $this->ViewExtension->addBreadCrumbs($breadCrumbs);
?>
<div class="container">
<?php
    echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
?>
    <div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
<?php
    echo $this->element('tableExport', compact('exportInfo'));
?>
        </div>
    </div>
</div>
