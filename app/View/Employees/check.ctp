<?php
/**
 * This file is the view file of the application. Used for checking
 *  state tree of employees.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Employees
 */

	$this->assign('title', $pageHeader);
	$this->ViewExtension->addBreadCrumbs($breadCrumbs);
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
