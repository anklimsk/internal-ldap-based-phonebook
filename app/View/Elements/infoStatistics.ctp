<?php
/**
 * This file is the view file of the application. Used for render
 *  statistic information about phone book
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

if (!isset($lastUpdate)) {
    $lastUpdate = null;
}

if (!isset($countEmployees)) {
    $countEmployees = 0;
}

    $statisticsList = [
        $this->Html->tag('samp', __('The number of employees') . ':') .
            $this->Html->tag('span', $this->Number->format(
                $countEmployees,
                ['thousands' => ' ', 'before' => '', 'places' => 0]
            ), ['class' => 'badge', 'id' => 'countEmployees']),
        $this->Html->tag('samp', __('Last update') . ':') .
            $this->Html->tag('span', $this->ViewExtension->showEmpty(
                $lastUpdate,
                $this->Time->i18nFormat($lastUpdate, '%x')
            ), ['class' => 'badge', 'id' => 'lastUpdate']),
    ];
    echo $this->Html->nestedList($statisticsList, ['class' => 'list-group list-wo-border list-statistics'], ['class' => 'list-group-item'], 'ul');
