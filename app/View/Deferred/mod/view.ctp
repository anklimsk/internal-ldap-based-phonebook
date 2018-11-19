<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  information of deferred save in modal window.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
     * @package app.View.Deferred.mod
     */

    echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
    echo $this->element('infoDeferred', compact('deferredSave', 'fieldsLabel', 'fieldsConfig'));
