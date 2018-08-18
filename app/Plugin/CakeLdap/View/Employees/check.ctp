<?php
    /**
     * This file is the view file of the plugin. Used for rendering
     *  result of checking state of tree subordinate employees.
     *
     * CakeLdap: Authentication of users by member group of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package plugin.View.Employees
     */

    $this->assign('title', __d('cake_ldap', 'Checking state tree of employees'));

    $this->Html->addCrumb(__d('cake_ldap', 'Employees'), ['controller' => 'employees', 'action' => 'index']);
    $this->Html->addCrumb(__d('cake_ldap', 'Checking tree'));
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
