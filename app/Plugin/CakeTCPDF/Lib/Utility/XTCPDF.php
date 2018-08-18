<?php
/**
 * This file is the file extends class TCPDF.
 *
 * CakeTCPDF: Generate PDF files with CakePHP.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package plugin.Vendor
 */
App::import(
    'Vendor',
    'CakeTCPDF.tcpdf',
    ['file' => 'tcpdf' . DS . 'autoload.php']
);

/**
 * Extends class TCPDF used for creation PDF file.
 *
 * @package plugin.Vendor
 */
class XTCPDF extends TCPDF
{

    /**
     * Array of options
     *
     * @var array
     */
    public $options = [
        'headerFont' => PDF_FONT_NAME_DATA,
        'headerFont' => PDF_FONT_NAME_DATA,
        'headerFontSize' => PDF_FONT_SIZE_DATA,
        'footerFontSize' => PDF_FONT_SIZE_DATA,
        'headerAlign' => 'R',
        'footerAlign' => 'R',
        'headerText' => '',
        'footerText' => '',
    ];

    /**
     * Overwrites the default header
     *
     * @return void
     */
    public function Header()
    {
        if (empty($this->options['headerText'])) {
            return;
        }

        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->options['headerFont'], '', $this->options['headerFontSize']);
        $this->Cell(0, 20, $this->options['headerText'], 0, 1, $this->options['headerAlign'], 0);
    }

    /**
     * Overwrites the default footer
     *
     * @return void
     */
    public function Footer()
    {
        if (empty($this->options['footerText'])) {
            return;
        }

        $this->SetY(-20);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->options['headerFont'], '', $this->options['footerFontSize']);
        $this->Cell(0, 10, $this->options['footerText'], 0, false, $this->options['footerAlign'], 0);
    }

    /**
     * Add page if needed.
     * @param float $h Cell height. Default value: 0.
     * @param mixed $y Starting y position, leave empty for current position.
     * @param bool $addpage If true add a page, otherwise only return the true/false state
     * @return bool True in case of page break, False otherwise.
     * @since 3.2.000 (2008-07-01)
     * @protected
     */
    public function checkPageBreak($h = 0, $y = '', $addpage = true)
    {
        return parent::checkPageBreak($h, $y, $addpage);
    }
}
