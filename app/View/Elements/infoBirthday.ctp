<?php
/**
 * This file is the view file of the application. Used for render
 *  information about birthdays of employees
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
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
    <h4 class="text-center"><?php echo __('Today is the birthday of:'); ?></h4>
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

    echo $this->ViewExtension->collapsibleList($listBirthdays, BIRTHDAY_LIST_SHOW_LINES_LIMIT, 'fa-ul', 'ul');
?>
</div>
