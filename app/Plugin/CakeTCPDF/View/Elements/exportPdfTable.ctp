<?php
/**
 * This file is the view file of the plugin. Used for rendering
 *  table of data in PDF file.
 *
 * CakeTCPDF: Generate PDF files with CakePHP.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package plugin.View.Elements
 */

if (!isset($exportConfig)) {
	$exportConfig = [];
}

if (!isset($exportData)) {
	$exportData = [];
}

if (empty($exportConfig) || empty($exportData)) {
	return;
}

extract($exportConfig);
if (!isset($orientation)) {
	$orientation = PDF_PAGE_ORIENTATION;
}

	$this->tcpdf->setPageOrientation($orientation, true, PDF_MARGIN_BOTTOM);

	$this->tcpdf->SetFont(PDF_FONT_NAME_DATA, 'B', PDF_FONT_SIZE_DATA);
	$this->tcpdf->setFontSubsetting(true);
	$this->tcpdf->setPrintFooter(true);

	$this->table($exportData, $width, $align, $header);
