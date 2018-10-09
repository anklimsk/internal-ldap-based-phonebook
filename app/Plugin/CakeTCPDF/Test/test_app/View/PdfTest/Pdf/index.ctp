<?php
	$orientation = PDF_PAGE_ORIENTATION;
	$fileName = 'TestFile';
	$this->setFileName($fileName);

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
	$this->tcpdf->SetY(50);

	$this->tcpdf->SetFont('', 'B', 28);
	$this->tcpdf->Write(0, 'Some text...', '', 0, 'C', true, 0, false, false, 0);
	$this->tcpdf->Ln();
