<?php
/**
 * This file is the view file of the application. Used for render
 *  table of result search employees
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

	$highlightOptions = [
		'format' => '<mark>\1</mark>',
		'html' => true];

	if (!isset($query)) {
		$query = '';
	}

	if (!isset($queryCorrect)) {
		$queryCorrect = '';
	}

	if (!isset($correct)) {
		$correct = false;
	}

	if (!isset($result)) {
		$result = [];
	}

	if (!empty($queryCorrect) && !$correct) {
		$query = $queryCorrect;
	}

	if (empty($result) || !isset($result['Employee']['data']) ||
		empty($result['Employee']['data'])) {
		return;
	}
?>
	<div class="table-responsive">
		<table class="table table-hover table-striped table-condensed">
			<thead>
<?php
	$tableHeader = [];
foreach ($paginatorOptions as $fieldName => $fieldInfo) {
	if (isset($fieldInfo['disabled']) && $fieldInfo['disabled']) {
		$tableHeader[] = $fieldInfo['label'];
	} else {
		$tableHeader[] = $this->ViewExtension->paginationSortPjax($fieldName, $fieldInfo['label']);
	}
}
	$tableHeader[] = [__('Actions') => ['class' => 'action']];
	echo $this->Html->tableHeaders($tableHeader);
?>
			</thead>
			<tbody> 
<?php
	$tableBody = [];
	$guidField = 'includedFields.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID;
	$userGuid = $this->UserInfo->getUserField($guidField);
foreach ($result['Employee']['data'] as $employee) {
	$actions = $this->ViewExtension->buttonLink(
		'fas fa-sitemap',
		'btn-info',
		['controller' => 'employees', 'action' => 'tree', $employee['Employee']['id']],
		['title' => __('Tree of subordinate employees'),
			'action-type' => 'modal',
			'class' => 'app-tour-btn-subordinates'
		]
	);
	if (isset($employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID])) {
		if ($this->UserInfo->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true)) {
			$actions .= $this->ViewExtension->buttonLink(
				'fas fa-pencil-alt',
				'btn-warning',
				['controller' => 'employees', 'action' => 'edit', $employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID]],
				[
					'title' => __('Edit information of this employee'),
					'action-type' => 'modal',
					'class' => 'app-tour-btn-edit'
				]
			) .
			$this->ViewExtension->buttonLink(
				'fas fa-sync-alt',
				'btn-primary',
				['controller' => 'employees', 'action' => 'sync', $employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID]],
				[
					'title' => __('Synchronize information of this employee with LDAP server'),
					'class' => 'app-tour-btn-sync',
					'data-toggle' => 'request-only'
				]
			);
		} elseif (!empty($userGuid) && ($employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID] === $userGuid)) {
			$actions .= $this->ViewExtension->buttonLink(
				'fas fa-pencil-alt',
				'btn-warning',
				['controller' => 'employees', 'action' => 'edit', $employee['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID]],
				[
					'title' => __('Edit information of this employee'),
					'action-type' => 'modal'
				]
			);
		}
	}

	$tableRow = $this->EmployeeInfo->getInfo($employee, $paginatorOptions, $fieldsConfig, [], true);
	foreach ($tableRow as &$tableRowItem) {
		if (is_array($tableRowItem)) {
			if (isset($tableRowItem[0])) {
				$tableRowItem[0] = $this->Text->highlight($tableRowItem[0], $query, $highlightOptions);
			}
		} else {
			$tableRowItem = $this->Text->highlight($tableRowItem, $query, $highlightOptions);
		}
	}
	$tableRow[] = [$this->ViewExtension->showEmpty($actions), ['class' => 'action text-center']];
	$tableBody[] = $tableRow;
}
	echo $this->Html->tableCells($tableBody);
?>
			</tbody>
		</table>
	</div>
<?php
	echo $this->ViewExtension->buttonsPaging();
