<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  information of deferred save.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Deferred
     */

    $this->assign('title', $pageHeader);
    $this->ViewExtension->addBreadCrumbs($breadCrumbs);
?>
    <div class="container">
<?php
        echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
        echo $this->element('infoDeferred', compact('deferredSave', 'fieldsLabel', 'fieldsConfig'));
?>
    </div>
