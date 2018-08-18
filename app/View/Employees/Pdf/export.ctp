<?php
    /**
     * This file is the view file of the application. Used for render
     *  PDF file of phone book.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Employees.Pdf
     */

    App::uses('CakeTime', 'Utility');
    App::uses('Router', 'Routing');

    $timestamp = time();
if (!isset($exportConfig)) {
    $exportConfig = [];
}

if (!empty($exportConfig)) {
    extract($exportConfig);
}

if (!isset($orientation)) {
    $orientation = PDF_PAGE_ORIENTATION;
}

if (!isset($company)) {
    $company = '';
}

if (!isset($titletext)) {
    $titletext = __('Phonebook');
}

if (!isset($createDate)) {
    $createDate = __('Created %s', CakeTime::i18nFormat($timestamp, "%x"));
}

if (isset($fileName)) {
    $this->setFileName($fileName);
}

    $this->tcpdf->setPageOrientation($orientation, true, PDF_MARGIN_BOTTOM);
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
    $this->tcpdf->setPrintFooter(false);
if ($orientation == PDF_PAGE_ORIENTATION) {
    $this->tcpdf->SetY(100);
} else {
    $this->tcpdf->SetY(60);
}

if (file_exists(IMAGES . ORG_LOGO_IMAGE)) {
    $y = $this->tcpdf->GetY();
    $this->tcpdf->SetY($y - 40);
    $mainUrl = Router::url('/', true);
    $this->tcpdf->Image(IMAGES . ORG_LOGO_IMAGE, '', '', 0, 0, 'PNG', $mainUrl, 'N', true, 150, 'C', false, false, 0, false, false, false);
    $y = $this->tcpdf->GetY();
    $this->tcpdf->SetY($y + 10);
} else {
    $this->tcpdf->SetY(100);
}

if (!empty($company)) {
    $this->tcpdf->SetFont('', 'B', 28);
    $this->tcpdf->Write(0, $company, '', 0, 'C', true, 0, false, false, 0);
    $this->tcpdf->Ln();
}
if (!empty($titletext)) {
    $this->tcpdf->SetFont('', '', 18);
    $this->tcpdf->Write(0, $titletext, '', 0, 'C', true, 0, false, false, 0);
    $this->tcpdf->Ln();
}
    $this->tcpdf->SetFont('', 'I', 12);
    $this->tcpdf->Write(0, $createDate, '', 0, 'C', true, 0, false, false, 0);

    $this->tcpdf->AddPage();
    $this->tcpdf->setPrintFooter(true);
    echo $this->element('CakeTCPDF.exportPdfTable', compact('exportConfig', 'exportData'));
    echo $this->element('CakeTCPDF.exportPdfTableContent', compact('exportConfig'));
