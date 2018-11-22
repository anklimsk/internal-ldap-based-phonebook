<?php
/**
 * This file is the view file of the application. Used for render
 *  form for editing employee photo
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

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

if (!isset($changedFields)) {
	$changedFields = [];
}

if (!isset($fieldsInputTooltip)) {
	$fieldsInputTooltip = [];
}

if (!isset($forceDeferred)) {
	$forceDeferred = false;
}

if (!isset($redirecturl)) {
	$redirecturl = null;
} elseif (!empty($redirecturl)) {
	$redirecturl = $this->Html->url($redirecturl);
}

	echo $this->Form->createUploadForm('EmployeePhoto');
?>
		<fieldset>
<?php
	$labelClass = null;
	$divClass = 'form-control-static text-center';
if (in_array('EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO, $changedFields)) {
	$labelClass = 'text-warning';
	$divClass .= ' has-warning';
}

	$tooltip = __('Current photo of employee on LDAP server');
if (isset($fieldsInputTooltip['EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO])) {
	$tooltip = $fieldsInputTooltip['EmployeeEdit.' . CAKE_LDAP_LDAP_ATTRIBUTE_PHOTO];
}
	echo $this->Form->input('EmployeePhoto.' . CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID, ['type' => 'hidden']);
	echo $this->Form->input('EmployeePhoto.force_deferred', ['type' => 'hidden']);
	echo $this->Form->label('PhotoCurrent', __('Current photo') . '&nbsp;' . $this->Html->tag(
		'abbr',
		'[?]',
		['title' => $tooltip, 'data-toggle' => 'tooltip']
	) . ':', ['class' => $labelClass]) .
		$this->Html->div($divClass, $this->EmployeeInfo->getPhotoImage($employeePhoto, false));
	$url = $this->Html->url(['controller' => 'employees', 'action' => 'upload', 'ext' => 'json']);
	if ($this->UserInfo->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true)) {
		echo $this->Html->div('form-control-static text-center', $this->ViewExtension->buttonLink(
			$this->ViewExtension->iconTag('fas fa-trash-alt fa-lg') . '&nbsp;' . __('Delete photo'),
			'btn btn-danger btn-block',
			['controller' => 'employees', 'action' => 'delete_photo', $guid, $forceDeferred],
			[
				'title' => __('Delete current photo'), 'action-type' => 'confirm-post',
				'data-confirm-msg' => __('Are you sure you wish to delete current photo?')
			]
		));
	}
	$btnUploadTitle = $this->ViewExtension->iconTag('fas fa-file-upload fa-lg') . '&nbsp;' .
		$this->Html->tag('span', __('Update photo'));
	$btnUploadClass = 'btn-success btn-block';
	echo $this->Html->div('text-center', $this->Form->upload($url, $maxfilesize, $acceptfiletypes, $redirecturl, $btnUploadTitle, $btnUploadClass));
?>
		</fieldset>
<?php
	echo $this->Form->end();
	echo $this->fetch('confirm-form');
