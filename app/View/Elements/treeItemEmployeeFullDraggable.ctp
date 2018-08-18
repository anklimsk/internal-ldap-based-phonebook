<?php
    /**
     * This file is the view file of the plugin. Used for rendering
     *  item of tree subordinate employees with buttons for move this item.
     *
     * CakeLdap: Authentication of users by member group of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package plugin.View.Elements
     */

    $employeeInfo = $this->element('CakeLdap.treeItemEmployeeFull', compact('data'));
    $idControls = uniqid('controls_');

    $url = ['controller' => 'employees', 'action' => 'move', $data['SubordinateDb']['id']];
    $actions = $this->ViewExtension->button(
        'far fa-caret-square-right',
        'btn-default',
        ['title' => __d('cake_ldap', 'Show or hide buttons'),
        'data-toggle' => 'collapse', 'data-target' => '#' . $idControls,
        'data-toggle-icons' => 'fa-caret-square-right,fa-caret-square-left',
        'aria-expanded' => 'false']
    ) .
    $this->Html->tag(
        'span',
        $this->ViewExtension->buttonsMove($url),
        [
            'id' => $idControls,
            'class' => 'collapse collapse-display-inline'
        ]
    ) .
    $this->ViewExtension->buttonLink(
        'fas fa-sync-alt',
        'btn-primary',
        ['controller' => 'employees', 'action' => 'sync', $data['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID]],
        [
            'title' => __('Synchronize information of this employee with LDAP server'),
            'data-toggle' => 'request-only'
        ]
    ) .
    $this->ViewExtension->buttonLink(
        'fas fa-pencil-alt',
        'btn-xs btn-warning',
        ['controller' => 'employees', 'action' => 'edit', $data['Employee'][CAKE_LDAP_LDAP_ATTRIBUTE_OBJECT_GUID]],
        [
            'title' => __('Edit information of this employee'),
            'action-type' => 'modal'
        ]
    );
    $employeeInfo = $this->Html->tag('span', $employeeInfo) . '&nbsp;' . $this->Html->tag('span', $actions, ['class' => 'action hide-popup']);

    echo $employeeInfo;
