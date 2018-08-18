<?php
    /**
     * This file is the view file of the plugin. Used for rendering
     *  item of tree subordinate employees.
     *
     * CakeLdap: Authentication of users by member group of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package plugin.View.Elements
     */

    $this->Tree->addItemAttribute('data-id', $data['SubordinateDb']['id']);
if (!empty($data['children'])) {
    $this->Tree->addItemAttribute('class', 'parent');
}
    echo $this->element('CakeLdap.infoEmployeeShort', ['employee' => $data]);
