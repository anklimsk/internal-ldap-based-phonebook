<?php
/**
 * This file is the view file of the application. Used for render
 *  information about birthdays of employees
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Elements
 */

if (!isset($birthdays)) {
    $birthdays = [];
}

if (empty($birthdays)) {
    return;
}
?>      
<div class="alert alert-warning" role="alert">
    <h4><?php echo __('Today is the birthday of:'); ?></h4>
<?php
    $listBirthdays = [];
foreach ($birthdays as $birthday) {
    $employeeInfo = $this->EmployeeInfo->getFullName($birthday['Employee']);
    if (isset($birthday['Department']['value']) && !empty($birthday['Department']['value'])) {
        $employeeInfo .= ' (' . h($birthday['Department']['value']) . ')';
    }
    if (!empty($employeeInfo)) {
        $listBirthdays[] = $this->Html->tag('span', $this->ViewExtension->iconTag('fas fa-birthday-cake'), ['class' => 'fa-li']) . '&nbsp;' .
            $this->ViewExtension->popupModalLink(
                $employeeInfo,
                ['controller' => 'employees', 'action' => 'view', $birthday['Employee']['id']],
                ['class' => 'popup-link alert-link']
            );
    }
}

    $listBirthdaysShown = array_slice($listBirthdays, 0, BIRTHDAY_LIST_SHOW_LINES_LIMIT);
    $htmlListBirthdays = $this->Html->nestedList($listBirthdaysShown, ['class' => 'fa-ul'], [], 'ul');
if (count($listBirthdays) > BIRTHDAY_LIST_SHOW_LINES_LIMIT) {
    $listBirthdaysHidden = array_slice($listBirthdays, BIRTHDAY_LIST_SHOW_LINES_LIMIT);
    $htmlListBirthdays .= $this->Html->nestedList(
        $listBirthdaysHidden,
        [
            'class' => 'fa-ul collapse',
            'id' => 'birthdayListEnd'
        ],
        [],
        'ul'
    );
    $htmlListBirthdays .= $this->ViewExtension->button(
        'fas fa-angle-double-down',
        'btn-warning btn-block',
        [
            'class' => 'top-buffer',
            'title' => __('Show or hide full list'),
            'data-toggle' => 'collapse', 'data-target' => '#birthdayListEnd',
            'aria-expanded' => 'false',
            'data-toggle-icons' => 'fa-angle-double-down,fa-angle-double-up'
        ]
    );
}
    echo $htmlListBirthdays;
?>
</div>
