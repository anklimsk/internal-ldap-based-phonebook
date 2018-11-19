<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  information of employee.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
     * @package app.View.Employees
     */

    echo $this->AssetCompress->css('CakeTheme.tree', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.tree', ['block' => 'script']);

    $this->assign('title', $pageHeader);
    $this->ViewExtension->addBreadCrumbs($breadCrumbs);

    $viewurl = $this->Html->url(
        [
            'controller' => 'employees',
            'action' => 'view'
        ]
    );
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
