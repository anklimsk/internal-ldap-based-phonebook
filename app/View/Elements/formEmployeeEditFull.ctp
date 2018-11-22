<?php
/**
 * This file is the view file of the application. Used for render
 *  form for editing employee include photo
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

if (!isset($fieldsLabel)) {
	$fieldsLabel = [];
}

if (!isset($fieldsLabelAlt)) {
	$fieldsLabelAlt = [];
}

if (!isset($fieldsInputMask)) {
	$fieldsInputMask = [];
}

if (!isset($fieldsInputTooltip)) {
	$fieldsInputTooltip = [];
}

if (!isset($readOnlyFields)) {
	$readOnlyFields = [];
}

if (!isset($managers)) {
	$managers = [];
}

if (!isset($departments)) {
	$departments = [];
}

if (!isset($changedFields)) {
	$changedFields = [];
}

if (!isset($maxLinesMultipleValue)) {
	$maxLinesMultipleValue = (int)MULTIPLE_VALUE_FIELD_ROWS_LIMIT;
}

if (!isset($dn)) {
	$dn = '';
}

if (!isset($employeePhoto)) {
	$employeePhoto = null;
}

if (!isset($guid)) {
	$guid = null;
}

if (!isset($maxfilesize)) {
	$maxfilesize = (int)UPLOAD_FILE_SIZE_LIMIT;
}

if (!isset($acceptfiletypes)) {
	$acceptfiletypes = UPLOAD_FILE_TYPES;
}

if (!isset($forceDeferred)) {
	$forceDeferred = false;
}

$changedTabEmployeeInfo = false;
$changedTabPhoto = false;
if (count(array_diff($changedFields, ['EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO])) > 0) {
	$changedTabEmployeeInfo = true;
}
if (in_array('EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO, $changedFields)) {
	$changedTabPhoto = true;
}

if (isset($fieldsLabel['EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO])) : ?>
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active"><a href="#employeeInfo" aria-controls="employeeInfo" role="tab" data-toggle="tab"><?php echo __('Employee information') .
				($changedTabEmployeeInfo ? '&nbsp;' . $this->ViewExtension->iconTag('fas fa-info-circle fa-lg') : ''); ?></a></li>
				<li role="presentation"><a href="#employeePhoto" aria-controls="employeePhoto" role="tab" data-toggle="tab"><?php echo __('Employee photo') .
				($changedTabPhoto ? '&nbsp;' . $this->ViewExtension->iconTag('fas fa-info-circle fa-lg') : ''); ?></a></li>
			</ul>
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane active top-buffer" id="employeeInfo">
<?php endif;
		echo $this->element('formEmployeeEdit', compact(
			'dn',
			'managers',
			'departments',
			'fieldsLabel',
			'fieldsLabelAlt',
			'fieldsInputMask',
			'fieldsInputTooltip',
			'readOnlyFields',
			'maxLinesMultipleValue',
			'changedFields'
		));

		if (isset($fieldsLabel['EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO])) :
?>
				</div>
				<div role="tabpanel" class="tab-pane top-buffer" id="employeePhoto">
<?php
		echo $this->element('formEmployeePhotoUpload', compact(
			'maxfilesize',
			'acceptfiletypes',
			'employeePhoto',
			'changedFields',
			'fieldsInputTooltip',
			'guid',
			'forceDeferred'
		));
?>
				</div>
			</div>
	<?php
		endif;
