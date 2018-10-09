<?php
/**
 * This file is the view file of the plugin. Used for rendering
 *  informations of employee in modal window.
 *
 * CakeLdap: Authentication of users by member group of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package plugin.View.Employees
 */

	$linkOpt = ['close-modal-link'];
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
