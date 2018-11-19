<?php
/**
 * This file is the view file of the application. Used for render
 *  MS Excel file of phone book.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Employees.Excel
 */

if (isset($fileName)) {
    $this->setFileName($fileName);
}
$this->Spreadsheet->getDefaultStyle()->applyFromArray([
    'font' => [
        'name' => 'Arial Cyr',
        'size' => 10,
    ],
]);
echo $this->element('CakeSpreadsheet.exportExcelTable', compact('exportConfig', 'exportData'));
