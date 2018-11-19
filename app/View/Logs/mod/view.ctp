<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  information of log in modal window.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
     * @package app.View.Logs.mod
     */

    echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
    echo $this->element('infoLog', compact('log', 'fieldsLabel', 'fieldsConfig'));
