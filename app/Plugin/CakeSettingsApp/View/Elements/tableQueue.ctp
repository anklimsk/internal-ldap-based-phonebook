<?php
/**
 * This file is the view file of the plugin. Used for rendering table
 *  a queue of task.
 *
 * CakeSettingsApp: Manage settings of application.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.View.Elements
 */

if (!isset($queue)) {
    $queue = [];
}

if (!isset($groupActions)) {
    $groupActions = [];
}

if (!isset($taskStateList)) {
    $taskStateList = [];
}

if (!isset($usePost)) {
    $usePost = true;
}

?>
    <div class="table-responsive table-filter">
<?php echo $this->Filter->openFilterForm($usePost); ?>
        <table class="table table-hover table-striped table-condensed">
            <thead>
<?php
    $formInputs = [
        'ExtendQueuedTask.id' => [
            'label' => 'ID',
            'disabled' => true,
            'class-header' => 'action',
            'not-use-input' => true
        ],
        'ExtendQueuedTask.jobtype' => [
            'label' => __d('cake_settings_app', 'Job type'),
        ],
        'ExtendQueuedTask.created' => [
            'label' => __d('cake_settings_app', 'Created'),
        ],
        'ExtendQueuedTask.fetched' => [
            'label' => __d('cake_settings_app', 'Fetched'),
        ],
        'ExtendQueuedTask.completed' => [
            'label' => __d('cake_settings_app', 'Completed'),
        ],
        'ExtendQueuedTask.progress' => [
            'label' => __d('cake_settings_app', 'Progress'),
        ],
        'ExtendQueuedTask.reference' => [
            'label' => __d('cake_settings_app', 'Reference'),
        ],
        'ExtendQueuedTask.failed' => [
            'label' => __d('cake_settings_app', 'Failed num.'),
        ],
        'ExtendQueuedTask.failure_message' => [
            'label' => __d('cake_settings_app', 'Failure mess.'),
        ],
        'ExtendQueuedTask.status' => [
            'label' => __d('cake_settings_app', 'Status'),
            'type' => 'select',
            'options' => $taskStateList,
            'data-style' => 'btn-xs btn-default',
            'data-width' => 'fit',
        ],
    ];
    echo $this->Filter->createFilterForm($formInputs, 'Queue');
?>
            </thead>
<?php if (!empty($queue) && $usePost) : ?>
            <tfoot>
<?php
    echo $this->Filter->createGroupActionControls($formInputs, $groupActions, true);
?>          
            </tfoot>
<?php endif; ?>             
            <tbody> 
<?php
foreach ($queue as $queueItem) {
    $tableRow = [];
    $attrRow = [];

    switch ($queueItem['ExtendQueuedTask']['status']) {
        case 'NOT_READY':
            $attrRow['class'] = 'warning';
            break;
        case 'IN_PROGRESS':
            $attrRow['class'] = 'info';
            break;
        case 'FAILED':
            $attrRow['class'] = 'danger';
            break;
        case 'COMPLETED':
            $attrRow['class'] = 'success';
            break;
        case 'UNKNOWN':
            $attrRow['class'] = 'danger';
            break;
        case 'NOT_STARTED':
        default:
            $attrRow['class'] = '';
    }
    $stateText = $queueItem['ExtendQueuedTask']['status'];
    if (isset($taskStateList[$stateText])) {
        $stateText = $taskStateList[$stateText];
    }

    $actions = __d('cake_settings_app', '&lt;None&gt;');
    if ($queueItem['ExtendQueuedTask']['status'] !== 'IN_PROGRESS') {
        $actions = $this->ViewExtension->buttonLink(
            'fas fa-trash-alt',
            'btn-danger',
            ['controller' => 'settings', 'action' => 'delete', 'plugin' => 'cake_settings_app', '?' => $queueItem['ExtendQueuedTask']],
            [
                'title' => __d('cake_settings_app', 'Delete task'),
                'action-type' => 'confirm-post',
                'data-confirm-msg' => __d(
                    'cake_settings_app',
                    'Are you sure you wish to delete task \'%s\' created %s?',
                    h($queueItem['ExtendQueuedTask']['jobtype']),
                    $this->Time->i18nFormat($queueItem['ExtendQueuedTask']['created'], '%x %X')
                ),
            ]
        );
    }

    $tableRow[] = [$this->Filter->createFilterRowCheckbox('ExtendQueuedTask.id', $queueItem['ExtendQueuedTask']['id']),
        ['class' => 'action text-center']];
    $tableRow[] = h($queueItem['ExtendQueuedTask']['jobtype']);
    $tableRow[] = $this->Time->i18nFormat($queueItem['ExtendQueuedTask']['created'], '%x %X');
    $tableRow[] = (!is_null($queueItem['ExtendQueuedTask']['fetched']) ? $this->Time->i18nFormat($queueItem['ExtendQueuedTask']['fetched'], '%x %X') : __d('cake_settings_app', '&lt;None&gt;'));
    $tableRow[] = (!is_null($queueItem['ExtendQueuedTask']['completed']) ? $this->Time->i18nFormat($queueItem['ExtendQueuedTask']['completed'], '%x %X') : __d('cake_settings_app', '&lt;None&gt;'));
    $tableRow[] = (!is_null($queueItem['ExtendQueuedTask']['progress']) ? ($queueItem['ExtendQueuedTask']['progress'] * 100) . ' %' : __d('cake_settings_app', '&lt;None&gt;'));
    $tableRow[] = (!is_null($queueItem['ExtendQueuedTask']['reference']) ? h($queueItem['ExtendQueuedTask']['reference']) : __d('cake_settings_app', '&lt;None&gt;'));
    $tableRow[] = $this->Number->format($queueItem['ExtendQueuedTask']['failed']);
    $tableRow[] = (!is_null($queueItem['ExtendQueuedTask']['failure_message']) ? $this->ViewExtension->truncateText(nl2br($queueItem['ExtendQueuedTask']['failure_message']), 30) : __d('cake_settings_app', '&lt;None&gt;'));
    $tableRow[] = h($stateText);
    $tableRow[] = [$actions, ['class' => 'action text-center']];

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
