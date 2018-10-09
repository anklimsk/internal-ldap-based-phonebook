# CakePHP 2.x Generate PDF files plugin
[![Build Status](https://travis-ci.com/anklimsk/cakephp-tcpdf.svg?branch=master)](https://travis-ci.com/anklimsk/cakephp-tcpdf)
[![Coverage Status](https://codecov.io/gh/anklimsk/cakephp-tcpdf/branch/master/graph/badge.svg)](https://codecov.io/gh/anklimsk/cakephp-tcpdf)
[![Latest Stable Version](https://poser.pugx.org/anklimsk/cakephp2-tcpdf/v/stable)](https://packagist.org/packages/anklimsk/cakephp2-tcpdf)
[![License](https://poser.pugx.org/anklimsk/cakephp2-tcpdf/license)](https://packagist.org/packages/anklimsk/cakephp2-tcpdf)

Generate PDF files with the CakePHP

## This plugin provides next features:

- Generate PDF files

## Installation

1. Install the Plugin using composer: `composer require anklimsk/cakephp2-tcpdf`
2. Add the next line to the end of the file `app/Config/bootstrap.php`:

   ```php
   CakePlugin::load('CakeTCPDF', ['bootstrap' => true, 'routes' => true]);
   ```

## Using this plugin

1. In your `Model`:
   - Create the following methods:

      ```php
      public function getExportConfig() {
          $header = [__('Field label 1'), __('Field label 2'), __('Field label 3'), __('Field label 4')];
          $width = [35, 20, 10, 15];
          $align = ['L', 'L', 'C', 'R'];
          $fileName = __('Export file');

          return compact('header', 'width', 'align', 'fileName');
      }

      public function getExportData($conditions = []) {
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
   - Add the `RequestHandler` component to `AppController`, and map `pdf` to 
     the CakeTCPDF plugin, e.g.:

      ```php
      public $components = [
          ...,
          'RequestHandler' => [
              'viewClassMap' => [
                  'pdf' => 'CakeTCPDF.Pdf'
              ]
          ]
      );
      ```

   - Add to your controller action:

      ```php
      public export($id = null) {
          if (!$this->RequestHandler->prefers('pdf')) {
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
   - Create a link to the a action with the extension `.pdf`, e.g.:

      ```php
      $this->Html->link('PDF file', ['ext' => 'pdf']);
      ```

   - Place the View templates in the subdirectory `Pdf`, e.g.:
     `app/View/Invoices/Pdf/index.ctp`
   - Use the `CakeTCPDF.exportPdfTable` element in your View file, e.g.:

      ```php
      if (!empty($exportConfig)) {
          extract($exportConfig);
      }

      if (!isset($orientation)) {
          $orientation = PDF_PAGE_ORIENTATION;
      }

      if (isset($fileName)) {
          $this->setFileName($fileName);
      }

      $this->tcpdf->setPageOrientation($orientation, TRUE, PDF_MARGIN_BOTTOM);
      $this->tcpdf->options['footerText'] = $this->tcpdf->getAliasNumPage();

      // set font
      $this->tcpdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
      // set default font subsetting mode
      $this->tcpdf->setFontSubsetting(true);

      //set margins
      $this->tcpdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $this->tcpdf->SetHeaderMargin(PDF_MARGIN_HEADER);
      $this->tcpdf->SetFooterMargin(PDF_MARGIN_FOOTER);

      $this->tcpdf->AddPage();
      $this->tcpdf->setPrintFooter(true);
      echo $this->element('CakeTCPDF.exportPdfTable', compact('exportConfig', 'exportData'));
      ```

   - Use the `CakeTCPDF.exportPdfTableContent` element in your View file, e.g.:

      ```php
      echo $this->element('CakeTCPDF.exportPdfTableContent', compact('exportConfig'));
      ```
