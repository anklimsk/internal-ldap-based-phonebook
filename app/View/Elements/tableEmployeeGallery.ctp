<?php
/**
 * This file is the view file of the application. Used for render
 *  table of employees gallery
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Elements
 */
App::uses('Hash', 'Utility');

if (!isset($employeesGallery)) {
    $employeesGallery = [];
}

if (!isset($emptyDepartmentName)) {
    $emptyDepartmentName = '';
}

if (empty($employeesGallery)) {
    return;
}
?>      
    <div class="table-responsive table-filter">
<?php echo $this->Filter->openFilterForm(); ?>      
        <table class="table table-hover table-striped table-condensed">
            <thead>
<?php
    $formInputs = [
        'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
            'label' => __('Name of employee'),
        ],
        'DepartmentExtension.name' => [
            'label' => __('Name of department'),
        ],
    ];
    echo $this->Filter->createFilterForm($formInputs);
?>
            </thead>
        </table>
<?php
    echo $this->Filter->closeFilterForm();
    echo $this->Html->div('confirm-form-block', $this->fetch('confirm-form'));
?>
    </div>  
        
    <div class="employees-gallery">
<?php
    $rowData = '';
    $colCount = 0;
    $prevData = [
        0 => '',
        1 => '',
    ];
    $employeesGalleryLength = count($employeesGallery);
    $employeeNum = 0;
    $targetSelector = '.employees-gallery';
    $headerPaths = [
        'DepartmentExtension.name',
        'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_DIVISION
    ];
    foreach ($employeesGallery as $employee) {
        $employeeNum++;
        if (empty($employee['DepartmentExtension']['name'])) {
            $employee['DepartmentExtension']['name'] = $emptyDepartmentName;
        }
        foreach ($headerPaths as $headerIndex => $headerPath) {
            $headerData = Hash::get($employee, $headerPath);
            if ($prevData[$headerIndex] !== $headerData) {
                $prevData[$headerIndex] = $headerData;
                $colCount = 0;
                if (!empty($rowData)) {
                    echo $this->Html->div('row', $rowData);
                }
                $rowData = '';
                if (!empty($headerData)) {
                    echo $this->Html->div('row', $this->Html->div('col-xs-12 col-sm-12 col-md-12 col-lg-12', $this->Html->tag('h3', h($headerData))));
                }
            }
        }
        $colCount++;
        $employeePhotoData = Hash::get($employee, 'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO);
        $employeeInfo = $this->Html->div('panel panel-default', $this->Html->div('panel-body text-center', $this->EmployeeInfo->getPhotoImage($employeePhotoData, false, '200')) .
            $this->Html->div('panel-footer', $this->element('CakeLdap.infoEmployeeShort', compact('employee'))));
        $rowData .= $this->Html->div('col-xs-6 col-sm-4 col-md-4 col-lg-4', $employeeInfo);
        if (($colCount == 6) || ($employeeNum == $employeesGalleryLength)) {
            $colCount = 0;
            if (!empty($rowData)) {
                echo $this->Html->div('row', $rowData);
            }
            $rowData = '';
        }
    }
?>
    </div>
<?php
    echo $this->ViewExtension->buttonsPaging($targetSelector);
