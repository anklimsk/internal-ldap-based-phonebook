<?php
/**
 * This file is the view file of the application. Used for render
 *  information about log
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

if (!isset($log)) {
    $log = [];
}

if (!isset($fieldsLabel)) {
    $fieldsLabel = [];
}

if (!isset($fieldsConfig)) {
    $fieldsConfig = [];
}

if (empty($log)) {
    return;
}
?>      
<dl class="dl-horizontal">
<?php
    echo $this->Html->tag('dt', __('Name of user') . ':');
    echo $this->Html->tag('dd', $this->ViewExtension->showEmpty(
        $log['User']['id'],
        $this->element('CakeLdap.infoEmployeeShort', ['employee' => ['Employee' => $log['User']]])
    ));
    echo $this->Html->tag('dt', __('Name of employee') . ':');
    echo $this->Html->tag('dd', $this->element('CakeLdap.infoEmployeeShort', ['employee' => $log]));
    echo $this->Html->tag('dt', __('Created') . ':');
    echo $this->Html->tag('dd', $this->Time->i18nFormat($log['Log']['created'], '%x %X'));
?>
</dl>
<hr>
<dl class="dl-horizontal">
<?php
if (!is_array($log['Log']['data'])) {
        echo $this->Html->div('alert alert-danger', __('Data of log record is broken'));
} else {
    echo $this->Deferred->getDeferredInfo($log['Log']['data']['changed']['EmployeeEdit'], $log['Log']['data']['current']['EmployeeEdit'], $fieldsLabel, $fieldsConfig);
}
?>
</dl>
