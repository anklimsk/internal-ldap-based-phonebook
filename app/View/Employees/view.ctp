<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  information of employee.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Employees
     */

    echo $this->AssetCompress->css('CakeTheme.tree', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.tree', ['block' => 'script']);

    $this->assign('title', __('Detail information'));

    $viewurl = $this->Html->url(
        [
            'controller' => 'employees',
            'action' => 'view'
        ]
    );
    $this->Html->addCrumb(__('Employees'), ['controller' => 'employees', 'action' => 'search']);
    if (isset($employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME]) && !empty($employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME])) {
        $this->Html->addCrumb(
            $this->Text->truncate(h($employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME]), 20),
            ['controller' => 'employees', 'action' => 'search', '?' => [
                'query' => $employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME], 'target' => ['Employee.Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DISPLAY_NAME]
            ]]
        );
    }
    $this->Html->addCrumb(__('Viewing'));
?>  
    <div class="container">
<?php
        echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
        echo $this->element('CakeLdap.infoEmployeeFull', compact(
            'employee',
            'fieldsLabel',
            'fieldsLabelExtend',
            'fieldsConfig',
            'id',
            'viewurl'
        ));
?>      
    </div>
