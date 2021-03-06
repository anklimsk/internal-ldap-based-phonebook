<?php
/**
 * This file is the view file of the application. Used for render
 *  table of deferred saves
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2019, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

if (!isset($deferredSaves)) {
	$deferredSaves = [];
}

if (!isset($groupActions)) {
	$groupActions = [];
}

if (!isset($fieldsLabel)) {
	$fieldsLabel = [];
}

if (!isset($fieldsConfig)) {
	$fieldsConfig = [];
}
?>
<div class="table-responsive table-filter">
<?php echo $this->Filter->openFilterForm(true); ?>
		<table class="table table-hover table-striped table-condensed">
			<thead>
<?php
	$formInputs = [
		'Deferred.id' => [
			'label' => 'ID',
			'disabled' => true,
			'class-header' => 'action',
			'not-use-input' => true
		],
		'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
			'label' => __('Name of employee'),
			'class-header' => 'fit',
			'style' => 'min-width: 180px'
		],
		'Deferred.data' => [
			'label' => __('Data of deferred save'),
			'disabled' => true
		],
		'Deferred.created' => [
			'label' => __('Created'),
			'class-header' => 'fit',
			'style' => 'min-width: 150px'
		],
		'Deferred.modified' => [
			'label' => __('Modified'),
			'class-header' => 'fit',
			'style' => 'min-width: 150px'
		],
	];
	echo $this->Filter->createFilterForm($formInputs, null, true);
?>
			</thead>
<?php if (!empty($deferredSaves)) : ?>
			<tfoot>
<?php
	echo $this->Filter->createGroupActionControls($formInputs, $groupActions, true);
?>
			</tfoot>
<?php endif; ?>
			<tbody>
<?php
foreach ($deferredSaves as $deferredSave) {
	$tableRow = [];
	$attrRow = [];
	$actions = '';
	$deferredData = $this->Html->tag('span', $this->Html->tag(
		'strong',
		__('Data of deferred save is broken'),
		['class' => 'text-danger']
	));

	if ($deferredSave['Deferred']['internal']) {
		$attrRow['class'] = 'warning';
	}

	if (is_array($deferredSave['Deferred']['data'])) {
		$deferredData = $this->Html->div(
			null,
			$this->Html->div(
				'pull-right',
				$this->ViewExtension->popupModalLink(
					'[+]',
					['controller' => 'deferred', 'action' => 'view', $deferredSave['Deferred']['id']]
				)
			) .
			$this->Html->div(
				'pull-left',
				$this->Deferred->getDeferredInfo(
					$deferredSave['Deferred']['data']['changed']['EmployeeEdit'],
					[],
					$fieldsLabel,
					$fieldsConfig
				)
			)
		);

		$actions .= $this->ViewExtension->buttonLink(
			'fas fa-pencil-alt',
			'btn-warning',
			['controller' => 'deferred', 'action' => 'edit', $deferredSave['Deferred']['id']],
			[
				'title' => __('Edit information of this deferred save'),
				'action-type' => 'modal',
				'class' => 'app-tour-btn-edit'
			]
		) .
		$this->ViewExtension->buttonLink(
			'fas fa-check',
			'btn-success',
			['controller' => 'deferred', 'action' => 'approve', $deferredSave['Deferred']['id']],
			[
				'title' => __('Approve deferred save data'), 'action-type' => 'confirm-post',
				'data-confirm-msg' => __('Are you sure you wish to approve this deferred save?'),
				'class' => 'app-tour-btn-approve'
			]
		) .
		$this->ViewExtension->buttonLink(
			'fas fa-times',
			'btn-danger',
			['controller' => 'deferred', 'action' => 'reject', $deferredSave['Deferred']['id']],
			[
				'title' => __('Reject deferred save data'), 'action-type' => 'confirm-post',
				'data-confirm-msg' => __('Are you sure you wish to reject this deferred save?'),
				'class' => 'app-tour-btn-reject'
			]
		);
	}
	$actions .= $this->ViewExtension->buttonLink(
		'fas fa-trash-alt',
		'btn-danger',
		['controller' => 'deferred', 'action' => 'delete', $deferredSave['Deferred']['id']],
		[
			'title' => __('Delete deferred save'), 'action-type' => 'confirm-post',
			'data-confirm-msg' => __('Are you sure you wish to delete this deferred save?'),
			'class' => 'app-tour-btn-delete'
		]
	);

	$tableRow[] = [
		$this->Filter->createFilterRowCheckbox('Deferred.id', $deferredSave['Deferred']['id']),
		['class' => 'action text-center']]
	;
	$tableRow[] = $this->element('CakeLdap.infoEmployeeShort', ['employee' => $deferredSave]);
	$tableRow[] = $deferredData;
	$tableRow[] = [
		$this->ViewExtension->timeAgo($deferredSave['Deferred']['created']),
		['class' => 'text-nowrap text-center']
	];
	$tableRow[] = [
		$this->ViewExtension->timeAgo($deferredSave['Deferred']['modified']),
		['class' => 'text-nowrap text-center']
	];
	$tableRow[] = [
		$this->ViewExtension->showEmpty($actions),
		['class' => 'action text-center']
	];

	echo $this->Html->tableCells([$tableRow], $attrRow, $attrRow);
}
?>
			</tbody>
		</table>
<?php
	echo $this->Filter->closeFilterForm();
	echo $this->Html->div('confirm-form-block', $this->fetch('confirm-form'));
?>
	</div>
<?php
	echo $this->ViewExtension->buttonsPaging();
