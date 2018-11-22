<?php
/**
 * This file is the view file of the application. Used for render
 *  information about deferred save
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

if (!isset($deferredSave)) {
	$deferredSave = [];
}

if (!isset($fieldsLabel)) {
	$fieldsLabel = [];
}

if (!isset($fieldsConfig)) {
	$fieldsConfig = [];
}

if (empty($deferredSave)) {
	return;
}
?>
<dl class="dl-horizontal">
<?php
	echo $this->Html->tag('dt', __('Name of employee') . ':');
	echo $this->Html->tag('dd', $this->element('CakeLdap.infoEmployeeShort', ['employee' => $deferredSave]));
	echo $this->Html->tag('dt', __('Created') . ':');
	echo $this->Html->tag('dd', $this->ViewExtension->timeAgo($deferredSave['Deferred']['created'], '%x %X'));
	echo $this->Html->tag('dt', __('Modified') . ':');
	echo $this->Html->tag('dd', $this->ViewExtension->timeAgo($deferredSave['Deferred']['modified'], '%x %X'));
?>
</dl>
<hr>
<dl class="dl-horizontal">
<?php
if (!is_array($deferredSave['Deferred']['data'])) {
	echo $this->Html->div('alert alert-danger', __('Data of deferred save is broken'));
} else {
	echo $this->Deferred->getDeferredInfo($deferredSave['Deferred']['data']['changed']['EmployeeEdit'], $deferredSave['Deferred']['data']['current']['EmployeeEdit'], $fieldsLabel, $fieldsConfig);
}
?>
</dl>
