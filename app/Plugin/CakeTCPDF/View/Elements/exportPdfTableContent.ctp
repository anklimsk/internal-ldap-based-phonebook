<?php
/**
 * This file is the view file of the plugin. Used for rendering
 *  table of content in PDF file.
 *
 * CakeTCPDF: Generate PDF files with CakePHP.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.View.Elements
 */

if (!isset($exportConfig)) {
    $exportConfig = [];
}

if (!empty($exportConfig)) {
    extract($exportConfig);
}

if (!isset($orientation)) {
    $orientation = PDF_PAGE_ORIENTATION;
}

    $this->tcpdf->setPageOrientation($orientation, true, PDF_MARGIN_BOTTOM);

    $this->tcpdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
    $this->tcpdf->setFontSubsetting(true);

    $this->tcpdf->addTOCPage();

    $this->tcpdf->SetFont('', 'B', 16);
    $this->tcpdf->MultiCell(0, 0, __('Content'), 0, 'C', 0, 1, '', '', true, 0);
    $this->tcpdf->Ln();

    $this->tcpdf->SetFont('', '', 12);
    $this->tcpdf->addTOC(2, '', '.', __('Content'), 'B', [128, 0, 0]);

    $this->tcpdf->endTOCPage();
