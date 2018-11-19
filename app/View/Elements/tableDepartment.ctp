<?php
/**
 * This file is the view file of the application. Used for render
 *  table of departments
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

if (!isset($departments)) {
    $departments = [];
}

    $dataUrl = $this->Html->url(['controller' => 'departments', 'action' => 'drop', 'ext' => 'json']);
?>
<div class="table-responsive table-filter">
<?php echo $this->Filter->openFilterForm(); ?>  
    <div data-toggle="draggable" data-url="<?php echo $dataUrl; ?>">
        <table class="table table-hover table-striped table-condensed">
            <thead>
<?php
    $formInputs = [
        'DepartmentExtension.lft' => [
            'label' => __('Position of department'),
            'not-use-input' => true,
        ],
        'Department.value' => [
            'label' => __('Name of department'),
        ],
        'DepartmentExtension.name' => [
            'label' => __('Extension name of department'),
        ],
        'Department.block' => [
            'label' => __('Used'),
            'options' => [
                1 => $this->ViewExtension->yesNo(false),
                0 => $this->ViewExtension->yesNo(true)
            ]
        ],
    ];
    echo $this->Filter->createFilterForm($formInputs);
?>
            </thead>
            <tbody> 
<?php
foreach ($departments as $department) {
    $tableRow = [];
    $attrRow = ['data-id' => $department['Department']['id']];
    $disabledState = $department['Department']['block'];
    $departmentName = h($department['Department']['value']);
    $actions = $this->ViewExtension->buttonsMove(['controller' => 'departments', 'action' => 'move', $department['Department']['id']]) .
        $this->ViewExtension->buttonLink(
            'fas fa-pencil-alt',
            'btn-warning',
            ['controller' => 'departments', 'action' => 'edit', $department['Department']['id']],
            [
                'title' => __('Edit department'),
                'action-type' => 'modal',
                'class' => 'app-tour-btn-edit'
            ]
        ) .
        $this->ViewExtension->buttonLink(
            'fas fa-trash-alt',
            'btn-danger',
            ['controller' => 'departments', 'action' => 'delete', $department['Department']['id']],
            [
                'title' => __('Delete department'), 'action-type' => 'confirm-post',
                'class' => 'app-tour-btn-delete' . ($disabledState ? '' : ' disabled'),
                'data-confirm-msg' => __('Are you sure you wish to delete department \'%s\'?', $departmentName),
            ]
        );

    $tableRow[] = [$this->Number->format(
        ($department['DepartmentExtension']['lft'] + 1) / 2,
        ['thousands' => ' ', 'before' => '', 'places' => 0]
    ), ['class' => 'text-center']];
    if ($disabledState) {
        $departmentName = $this->Html->tag('s', $departmentName);
    }
    $tableRow[] = $this->ViewExtension->truncateText(
        $this->ViewExtension->popupModalLink(
            $departmentName,
            ['controller' => 'departments', 'action' => 'view', $department['Department']['id']]
        )
    );
    $tableRow[] = $this->ViewExtension->truncateText(h($department['DepartmentExtension']['name']), 50);
    $tableRow[] = [$this->ViewExtension->yesNo(!$department['Department']['block']), ['class' => 'text-center']];
    $tableRow[] = [$this->ViewExtension->showEmpty($actions), ['class' => 'action text-center']];

    echo $this->Html->tableCells([$tableRow], $attrRow, $attrRow);
}
?>
            </tbody>
        </table>
        </div>
<?php
    echo $this->Filter->closeFilterForm();
    echo $this->Html->div('confirm-form-block', $this->fetch('confirm-form'));
?>
</div>
<?php
    echo $this->ViewExtension->buttonsPaging();
