<?php
/**
 * This file is the view file of the application. Used for render
 *  table of exported files
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Elements
 */

if (!isset($exportInfo)) {
    $exportInfo = [];
}
?>
<div class="table-responsive table-filter">
<?php echo $this->Filter->openFilterForm(); ?>  
        <table class="table table-hover table-striped table-condensed">
            <thead>
<?php
    $tableHeader = [
        __('File'),
        __('Last update'),
        __('Actions')
    ];
    echo $this->Html->tableHeaders($tableHeader);
?>
            </thead>
            <tbody> 
<?php
    $prevExportFileType = '';
foreach ($exportInfo as $exportInfoItem) {
    if (!$this->UserInfo->checkUserRole([USER_ROLE_SECRETARY, USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true) &&
        ($exportInfoItem['extendViewState'])) {
        continue;
    }
    if ($exportInfoItem['fileType'] !== $prevExportFileType) {
        $prevExportFileType = $exportInfoItem['fileType'];
        $tableSubHeaderAttrRow = ['class' => 'warning'];
        $tableSubHeaderRow = [[[$this->Html->tag('em', $exportInfoItem['fileType']),
            ['colspan' => 3, 'class' => 'text-center']]]];
        echo $this->Html->tableCells($tableSubHeaderRow, $tableSubHeaderAttrRow, $tableSubHeaderAttrRow);
    }
    $tableRow = [];
    $actions = '';
    if ($this->UserInfo->checkUserRole([USER_ROLE_HUMAN_RESOURCES, USER_ROLE_ADMIN], true)) {
        $actions .= $this->ViewExtension->buttonLink(
            'fas fa-sync-alt',
            'btn-primary',
            ['controller' => 'employees', 'action' => 'generate', $exportInfoItem['viewType'], $exportInfoItem['generateType']],
            [
                'title' => __('Put the generation of the file in the queue'),
                'data-toggle' => 'request-only',
                'class' => 'app-tour-btn-generate'
            ]
        );
    }
    $actions .= $this->ViewExtension->buttonLink(
        $this->ViewExtension->getIconForExtension($exportInfoItem['fileExt']),
        'btn-info',
        ['controller' => 'employees', 'action' => 'download', $exportInfoItem['generateType'],
            $exportInfoItem['extendViewState'], 'ext' => $exportInfoItem['fileExt']],
        ['title' => __('Download file'), 'class' => 'app-tour-btn-download']
    );

    $fileName = $exportInfoItem['downloadFileName'];
    if (!$exportInfoItem['fileExists']) {
        $fileName = $this->Html->tag('del', $fileName);
    } else {
        $fileName = $fileName;
    }
    $lastUpdate = $this->ViewExtension->showEmpty(
        $exportInfoItem['fileCreate'],
        $this->ViewExtension->timeAgo($exportInfoItem['fileCreate'], '%x %X')
    );
    $tableRow[] = $fileName;
    $tableRow[] = [$lastUpdate, ['class' => 'text-center']];
    $tableRow[] = [$actions, ['class' => 'action text-center']];

    echo $this->Html->tableCells($tableRow);
}
?>
            </tbody>
        </table>
    </div>
