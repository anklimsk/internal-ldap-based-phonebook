<?php
	$fileName = 'TestFile';
	$this->setFileName($fileName);

	$sheet = $this->Spreadsheet->getActiveSheet();
	$sheet->setCellValue('A1', 'Some text...');
