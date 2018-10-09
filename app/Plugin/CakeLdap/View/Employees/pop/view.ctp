<?php
/**
 * This file is the view file of the plugin. Used for rendering
 *  informations of employee in popup window.
 *
 * CakeLdap: Authentication of users by member group of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package plugin.View.Employees
 */
?>
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<?php
	echo $this->element('CakeLdap.infoEmployeeFull', compact('employee', 'fieldsLabel', 'fieldsLabelExtend', 'fieldsConfig'));
?>
		</div>
	</div>
