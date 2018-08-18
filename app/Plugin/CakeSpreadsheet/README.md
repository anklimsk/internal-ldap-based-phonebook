# CakePHP 2.x Generate MS Excel files plugin
[![Build Status](https://travis-ci.com/anklimsk/cakephp-spreadsheet.svg?branch=master)](https://travis-ci.com/anklimsk/cakephp-spreadsheet)

Generate MS Excel files with the CakePHP

## This plugin provides next features:

- Generate MS Excel files

## Installation

1. Install the Plugin using composer: `composer require anklimsk/cakephp-spreadsheet`
2. Add the next line to the end of the file `app/Config/bootstrap.php`:
```php
CakePlugin::load('CakeSpreadsheet', ['bootstrap' => true, 'routes' => true]);
```

## Using this plugin

1. In your `Model`:
  - Create the following methods:
```php
public function getExportConfig()
{
    $header = [__('Field label 1'), __('Field label 2'), __('Field label 3'), __('Field label 4')];
    $width = [35, 20, 10, 15];
    $align = ['L', 'L', 'C', 'R'];
    $fileName = __('Export file');

    return compact('header', 'width', 'align', 'fileName');
}

public function getExportData($conditions = [])
{
    ...
    $result = [
        'Group header (List name)' => [
            'Sub header' => [
                [
                    'Field value 1',
                    'Field value 2',
                    'Field value 3',
                    'Field value 4',
                ]
            ]
        ]
    ];

    return $result;
}
```
2. In your `Controller`:
  - Add the `RequestHandler` component to `AppController`, and map `xlsx` to 
    the CakeSpreadsheet plugin, e.g.:
```php
public $components = [
    ...,
    'RequestHandler' => [
        'viewClassMap' => [
            'xlsx' => 'CakeSpreadsheet.Spreadsheet'
        ]
    ]
);
```
  - Add to your controller action:
```php
public export($id = null)
{
    if (!$this->RequestHandler->prefers('xlsx')) {
        throw new BadRequestException(__('Invalid export type');
    }

    $conditions = [];
    if (!empty($id)) {
        $conditions['Model.id'] = $id;
    }
    $exportConfig = $this->Model->getExportConfig();
    $exportData = $this->Model->getExportData();

    $this->set(compact('exportConfig', 'exportData'));
}
```
3. In your `View`:
  - Create a link to the a action with the extension `.xlsx`, e.g.:
```php
$this->Html->link('Excel file', ['ext' => 'xlsx']);
```
  - Place the View templates in the subdirectory `Spreadsheet`, e.g.:
    `app/View/Invoices/Spreadsheet/index.ctp`
```php
if (isset($fileName)) {
    $this->setFileName($fileName);
}

$this->Spreadsheet->getDefaultStyle()->applyFromArray([
    'font' => [
        'name' => 'Arial Cyr',
        'size' => 10,
    ],
]);
```
  - Use the `CakeSpreadsheet.exportExcelTable` element in your View file, e.g.:
```php
echo $this->element('CakeSpreadsheet.exportExcelTable', compact('exportConfig', 'exportData'));
```
