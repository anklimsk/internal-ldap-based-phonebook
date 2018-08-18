<?php
/**
 * This file is the view file of the application. Used for render
 *  form for editing department
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Elements
 */

if (!isset($isAddAction)) {
    $isAddAction = false;
}

if (!isset($fieldInputMask)) {
    $fieldInputMask = [];
}

$inputOptions = [];
foreach ($fieldInputMask as $dataAttr => $mask) {
    if (ctype_digit((string)$dataAttr)) {
        continue;
    }

    $inputOptions[$dataAttr] = $mask;
}
    echo $this->Form->create('Department', $this->ViewExtension->getFormOptions());
?>
    <fieldset>
        <legend><?php echo __('Name of department'); ?></legend>
<?php
if (!$isAddAction) {
    $hiddenFields = [
        'Department.id',
        'Department.block',
        'DepartmentExtension.id',
        'DepartmentExtension.department_id',
        'DepartmentExtension.parent_id',
        'DepartmentExtension.lft',
        'DepartmentExtension.rght'
    ];
    echo $this->Form->hiddenFields($hiddenFields);
}
    echo $this->Form->input('Department.value', ['label' => __('Short name of department') . ':', 'title' => __('Short name of department'),
        'type' => 'text', 'data-toggle' => 'tooltip', 'autocomplete' => 'off', 'autofocus' => true] + $inputOptions);
    echo $this->Form->input('DepartmentExtension.name', ['label' => __('Extended name of department') . ':', 'title' => __('Extended name of department (full)'),
        'type' => 'text', 'data-toggle' => 'tooltip', 'autocomplete' => 'off'] + $inputOptions);
?>
    </fieldset>
<?php
    echo $this->Form->submit(__('Save'), ['class' => 'btn btn-success  btn-md', 'div' => 'form-group text-center']);
    echo $this->Form->end();
