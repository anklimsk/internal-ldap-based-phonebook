<?php
    /**
     * This file is the view file of the application. Used for checking
     *  state tree of employees.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Employees
     */

    $this->assign('title', __('Checking state tree of employees'));

    $this->Html->addCrumb(__('Employees'), ['controller' => 'employees', 'action' => 'search']);
    $this->Html->addCrumb(__('Checking tree'));
?>  
    <div class="container">
<?php
        echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
        $urlNode = [
            'controller' => 'employees',
            'action' => 'view',
        ];
        echo $this->element('CakeTheme.tableTreeState', compact('treeState', 'urlNode'));
?>
    </div>
