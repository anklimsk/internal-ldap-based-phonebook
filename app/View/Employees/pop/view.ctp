<?php
/**
 * This file is the view file of the application. Used for viewing
 *  information of employee in popup window.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Employees.pop
 */
?>  
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<?php
    echo $this->element('CakeLdap.infoEmployeeFull', compact('employee', 'fieldsLabel', 'fieldsLabelExtend', 'fieldsConfig'));
?>  
        </div>
    </div>
