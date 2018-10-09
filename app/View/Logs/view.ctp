<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  information of log.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Logs
     */

    $this->assign('title', $pageHeader);
    $this->ViewExtension->addBreadCrumbs($breadCrumbs);
?>
    <div class="container">
<?php
        echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
        echo $this->element('infoLog', compact('log', 'fieldsLabel', 'fieldsConfig'));
?>
    </div>
