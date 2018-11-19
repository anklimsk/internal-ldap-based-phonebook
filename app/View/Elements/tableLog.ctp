<?php
/**
 * This file is the view file of the application. Used for render
 *  table of logs
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

if (!isset($logs)) {
    $logs = [];
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
        'Log.id' => [
            'label' => 'ID',
            'disabled' => true,
            'class-header' => 'action',
            'not-use-input' => true
        ],
        'User.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
            'label' => __('Name of user'),
        ],
        'Employee.' . CAKE_LDAP_LDAP_ATTRIBUTE_NAME => [
            'label' => __('Name of employee'),
        ],
        'Log.data' => [
            'label' => __('Data of log'),
            'disabled' => true
        ],
        'Log.created' => [
            'label' => __('Created'),
        ],
    ];
    echo $this->Filter->createFilterForm($formInputs, null, true);
?>
            </thead>
<?php if (!empty($logs)) : ?>
            <tfoot>
<?php
    echo $this->Filter->createGroupActionControls($formInputs, $groupActions, true);
?>          
            </tfoot>
<?php endif; ?>             
            <tbody> 
<?php
foreach ($logs as $log) {
    $tableRow = [];
    $actions = $this->ViewExtension->buttonLink(
        'fas fa-undo-alt',
        'btn-warning',
        ['controller' => 'logs', 'action' => 'restore', $log['Log']['id']],
        [
                'title' => __('Restore data from log'), 'action-type' => 'confirm-post',
                'data-confirm-msg' => __('Are you sure you wish to restore this data from log?')
            ]
    ) .
        $this->ViewExtension->buttonLink(
            'fas fa-trash-alt',
            'btn-danger',
            ['controller' => 'logs', 'action' => 'delete', $log['Log']['id']],
            [
                'title' => __('Delete record of log'), 'action-type' => 'confirm-post',
                'data-confirm-msg' => __('Are you sure you wish to delete this record of log?')
            ]
        );
    if (is_array($log['Log']['data'])) {
        $logData = $this->Deferred->getDeferredInfo(
            $log['Log']['data']['changed']['EmployeeEdit'],
            [],
            $fieldsLabel,
            $fieldsConfig
        );
    } else {
        $logData = $this->Html->tag('span', $this->Html->tag(
            'strong',
            __('Data of log record is broken'),
            ['class' => 'text-danger']
        ));
    }

    $tableRow[] = [$this->Filter->createFilterRowCheckbox('Log.id', $log['Log']['id']),
        ['class' => 'action text-center']];
    $tableRow[] = $this->ViewExtension->showEmpty(
        $log['User']['id'],
        $this->element('CakeLdap.infoEmployeeShort', ['employee' => ['Employee' => $log['User']]])
    );
    $tableRow[] = $this->element('CakeLdap.infoEmployeeShort', ['employee' => $log]);
    $tableRow[] = $logData;
    $tableRow[] = $this->ViewExtension->popupModalLink(
        $this->Time->i18nFormat($log['Log']['created'], '%x %X'),
        ['controller' => 'logs', 'action' => 'view', $log['Log']['id']]
    );
    $tableRow[] = [$this->ViewExtension->showEmpty($actions), ['class' => 'action text-center']];

    echo $this->Html->tableCells([$tableRow]);
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
