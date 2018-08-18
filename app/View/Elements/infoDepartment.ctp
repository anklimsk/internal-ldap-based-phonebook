<?php
/**
 * This file is the view file of the application. Used for render
 *  information about department
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Elements
 */

if (!isset($department)) {
    $department = [];
}

if (empty($department)) {
    return;
}
?>      
<dl class="dl-horizontal">
<?php
    echo $this->Html->tag('dt', __('Short name of department') . ':');
    echo $this->Html->tag('dd', h($department['Department']['value']));
    echo $this->Html->tag('dt', __('Extension name of department') . ':');
    echo $this->Html->tag('dd', h($department['DepartmentExtension']['name']));
    echo $this->Html->tag('dt', __('Used') . ':');
    echo $this->Html->tag('dd', $this->ViewExtension->yesNo(!$department['Department']['block']));
?>
</dl>
