<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  information of employee in modal window.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Employees.mod
     */

    $linkOpt = [];
    $viewurl = $this->Html->url(
        [
            'controller' => 'employees',
            'action' => 'view'
        ]
    );
    echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
    echo $this->element('CakeLdap.infoEmployeeFull', compact(
        'employee',
        'fieldsLabel',
        'fieldsLabelExtend',
        'fieldsConfig',
        'id',
        'linkOpt',
        'viewurl'
    ));
