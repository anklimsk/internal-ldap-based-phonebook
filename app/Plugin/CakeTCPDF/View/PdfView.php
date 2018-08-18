<?php
/**
 * This file is the view file of the application for creating PDF responses.
 *
 * CakeTCPDF: Generate PDF files with CakePHP.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.View
 */

App::uses('View', 'View');
App::uses('XTCPDF', 'CakeTCPDF.Utility');

/**
 * A view class that is used for creating PDF responses.
 *
 * @package plugin.View
 */
class PdfView extends View
{

    /**
     * Stores the XTCPDF() instance
     *
     * @var object
     */
    public $tcpdf = null;

    /**
     * Array of options
     *
     * @var array
     */
    public $options = [
        'useHTMLcells' => true,
        'useNonBreakingCells' => true,
    ];

    /**
     * Default file name
     *
     * @var string
     */
    protected $_fileName = 'Report';

    /**
     * Array of height for table rows
     *
     * @var array
     */
    protected $_rowHeight = [
        'TableRow' => 10,
        'TableHeader' => 8,
        'SubHeader' => 10,
    ];

    /**
     * Constructor
     *
     * @param Controller $controller A controller object to pull View::_passedVars from.
     * @return void
     */
    public function __construct(Controller $controller = null)
    {
        parent::__construct($controller);

        $this->response->type('pdf');
        $this->tcpdf = new XTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $optionsDefault = [
            'useHTMLcells' => true,
            'useNonBreakingCells' => true,
        ];
        $this->options += $optionsDefault;
    }

    /**
     * Set file name
     *
     * @param string $fileName File name
     * @return bool Success
     */
    public function setFileName($fileName = null)
    {
        if (empty($fileName)) {
            return false;
        }

        $this->_fileName = (string)$fileName;

        return true;
    }

    /**
     * Return file name
     *
     * @return string Return file name.
     */
    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * Renders view for given view file and layout.
     *
     * @param string $action Name of view file to use
     * @param string $layout Layout to use.
     * @return string|null Rendered content or null if content already rendered and returned earlier.
     * @triggers View.beforeRender $this, array($viewFileName)
     * @triggers View.afterRender $this, array($viewFileName)
     * @throws CakeException If there is an error in the view.
     */
    public function render($action = null, $layout = null)
    {
        $this->viewPath .= DS . 'Pdf';

        $content = parent::render($action, false);
        if ($this->response->type() == 'text/html') {
            return $content;
        }

        $content = $this->tcpdf->Output('', 'S');
        $this->Blocks->set('content', $content);
        $fileName = $this->getFileName();
        $fileName .= '.pdf';
        $this->response->download($fileName);

        return $this->Blocks->get('content');
    }

    /**
     * Return state of flag using HTML cells.
     *
     * @return bool State of flag using POST request
     */
    protected function _getFlagUseHtmlCells()
    {
        return (bool)$this->options['useHTMLcells'];
    }

    /**
     * Return state of flag using non breaking cells.
     *
     * @return bool State of flag using non breaking cells
     */
    protected function _getFlagUseNonBreakingCells()
    {
        return (bool)$this->options['useNonBreakingCells'];
    }

    /**
     * Write a table cell using the `TCPDF::MultiCell()` method
     *
     * This function add page break, if needed.
     * @param array $cell_array Array of cells.
     * @return void
     */
    protected function _writeMulticellsMc($cell_array = [])
    {
        if (empty($cell_array) || !is_array($cell_array)) {
            return;
        }

        if (isset($cell_array[0][1])) {
            $height = $cell_array[0][1];
            if ($this->tcpdf->checkPageBreak($height, '', false)) {
                $this->tcpdf->AddPage();
            }
        }

        foreach ($cell_array as $row) {
            list($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, $stretch, $ishtml,
                $autopadding, $maxh, $valign, $fitcell) = $row;
            $this->tcpdf->MultiCell(
                $w,
                $h,
                $txt,
                $border,
                $align,
                $fill,
                $ln,
                $x,
                $y,
                $reseth,
                $stretch,
                $ishtml,
                $autopadding,
                $maxh,
                $valign,
                $fitcell
            );
        }
        $this->tcpdf->ln();
    }

    /**
     * Write a table cell using the `TCPDF::writeHTMLCell()` method
     *
     * This function add page break, if needed.
     * @param array $cell_array Array of cells.
     * @return void
     */
    protected function _writeMulticellsHtml($cell_array = [])
    {
        if (empty($cell_array) || !is_array($cell_array)) {
            return;
        }

        $maxHeight = 0;
        $copyTcpdf = TCPDF_STATIC::objclone($this->tcpdf);
        $copyTcpdf->setAutoPageBreak(false);
        $copyTcpdf->AddPage();
        foreach ($cell_array as $row) {
            list($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, $stretch, $ishtml,
                $autopadding, $maxh, $valign, $fitcell) = $row;
            $ln = 1;
            $startY = $copyTcpdf->getY();
            $copyTcpdf->writeHTMLCell(
                $w,
                $h,
                $x,
                $y,
                $txt,
                $border,
                $ln,
                $fill,
                $reseth,
                $align,
                $autopadding
            );
            $endY = $copyTcpdf->getY();
            $currHeight = $endY - $startY;
            if ($currHeight > $maxHeight) {
                $maxHeight = $currHeight;
            }
        }
        $copyTcpdf->_destroy(true, true);

        if ($this->tcpdf->checkPageBreak($maxHeight, '', false)) {
            $this->tcpdf->AddPage();
        }

        foreach ($cell_array as $row) {
            list($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, $stretch, $ishtml,
                $autopadding, $maxh, $valign, $fitcell) = $row;
            $h = $maxHeight;
            $this->tcpdf->writeHTMLCell(
                $w,
                $h,
                $x,
                $y,
                $txt,
                $border,
                $ln,
                $fill,
                $reseth,
                $align,
                $autopadding
            );
        }
        $this->tcpdf->ln();
    }

    /**
     * Write a table cell, by option `useHTMLcells`
     *
     * @param array $cell_array Array of cells.
     * @return void
     */
    protected function _writeMulticells($cell_array = [])
    {
        $methodName = '_writeMulticellsMc';
        if ($this->_getFlagUseHtmlCells()) {
            $methodName = '_writeMulticellsHtml';
        }
        $this->$methodName($cell_array);
    }

    /**
     * Write a subheader for table
     *
     * @param string $subheader Text of subheader.
     * @param array $width Array of width for table cells.
     * @param int $level Current level of subheader (start from 0).
     * @return void
     */
    protected function _createSubHeader($subheader = '', $width = [], $level = 0)
    {
        if (empty($width)) {
            return;
        }

        $this->tcpdf->SetTextColor(0);
        $this->tcpdf->SetDrawColor(128, 0, 0);
        $this->tcpdf->SetLineWidth(0.3);
        if ($level > 3) {
            $level = 3;
        }
        $fontSize = 20 * (1 - $level / 4);

        $this->tcpdf->SetFont('', 'BI', $fontSize);
        $height = $this->_rowHeight['SubHeader'];
        $cells = [];
        $cells[] = [array_sum($width), $height, $subheader, 0, 'L', false, 0, '', '',
            true, 0, false, true, $height, 'M', true];
        $this->_writeMulticells($cells);
    }

    /**
     * Write a header for table cells
     *
     * @param array $header Array of header for table cells.
     * @param array $width Array of width for table cells.
     * @return void
     */
    protected function _createTableHeader($header = [], $width = [])
    {
        if (empty($header) || empty($width)) {
            return;
        }

        $this->tcpdf->SetFillColor(255, 0, 0);
        $this->tcpdf->SetTextColor(255);
        $this->tcpdf->SetDrawColor(128, 0, 0);
        $this->tcpdf->SetLineWidth(0.3);
        $this->tcpdf->SetFont('', 'B', 10);
        $height = $this->_rowHeight['TableHeader'];
        $cells = [];

        $num_headers = count($header);
        for ($i = 0; $i < $num_headers; ++$i) {
            $widthColumn = (isset($width[$i]) ? $width[$i] : 0);
            $headerColumn = (isset($header[$i]) ? $header[$i] : '');
            $cells[] = [$widthColumn, $height, $headerColumn, 'LTRB', 'C', true, 0, '', '',
                true, 0, false, true, $height, 'M', true];
        }
        $this->_writeMulticells($cells);
    }

    /**
     * Write a table rows
     *
     * @param array $rows Array of rows for table.
     * @param array $width Array of width for table cells.
     * @param array $align Array of align position for table cells.
     * @param array $header Array of header for table cells.
     * @param string $subHeader Text of subheader.
     * @param int $level Current level of subheader (start from 0).
     * @param string $prevHeader Text of prev subheader.
     * @param int $prevLevel Prev level of subheader (start from 0).
     * @return void
     */
    protected function _createTableRows($rows = [], $width = [], $align = [], $header = [], $subHeader = '', $level = 0, $prevHeader = '', $prevLevel = 0)
    {
        if (empty($rows) || empty($header) || empty($width)) {
            return;
        }

        if (empty($align)) {
            $align = [];
        }

        $height = $this->_rowHeight['TableRow'];
        foreach ($rows as $i => $row) {
            $cells = [];
            foreach ($row as $column => $cell) {
                $widthRow = (isset($width[$column]) ? $width[$column] : 0);
                $alignRow = (isset($align[$column]) ? $align[$column] : 'L');
                $cellRow = $cell;
                $cells[] = [$widthRow, $height, $cellRow, 'LTRB', $alignRow, false, 0, '', '',
                    true, 0, false, true, $height, 'M', true];
            }

            $this->tcpdf->SetTextColor(0);
            $this->tcpdf->SetFont('', '', 10);
            $this->_writeMulticells($cells);
            if (($i != 0) || (($i == 0) && !$this->_getFlagUseNonBreakingCells())) {
                continue;
            }

            if ($this->tcpdf->getY() > $this->tcpdf->getPageHeight() - PDF_MARGIN_BOTTOM) {
                $this->tcpdf->rollbackTransaction(true);
                $this->tcpdf->AddPage();
                if (!empty($prevHeader)) {
                    $this->_createSubHeader($prevHeader, $width, $prevLevel);
                    $this->tcpdf->Bookmark(strip_tags($prevHeader), $prevLevel, 0, '', 'B', [0, 64, 128]);
                    $prevHeader = '';
                }
                if (!empty($subHeader)) {
                    $this->_createSubHeader($subHeader, $width, $level);
                    $this->tcpdf->Bookmark(strip_tags($subHeader), $level, 0, '', 'B', [0, 64, 128]);
                }
                $this->_createTableHeader($header, $width);

                $this->tcpdf->SetTextColor(0);
                $this->tcpdf->SetFont('', '', 10);
                $height = $this->_rowHeight['TableRow'];
                $this->_writeMulticells($cells);
            }
            $this->tcpdf->commitTransaction();
            $this->tcpdf->setAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        }
    }

    /**
     * Write a table
     *
     * @param array $data Array of rows for table.
     * @param array $width Array of width for table cells.
     * @param array $align Array of align position for table cells.
     * @param array $header Array of header for table cells.
     * @param int $level Current level of subheader (start from 0).
     * @param string $prevHeader Text of prev subheader.
     * @param int $prevLevel Prev level of subheader (start from 0).
     * @return void
     */
    public function table($data = [], $width = [], $align = [], $header = [], $level = 0, $prevHeader = '', $prevLevel = 0)
    {
        if (empty($data)) {
            $subHeader = __d('view_extension', 'No data to display');
            $this->_createSubHeader($subHeader, $width, 0);
            $this->tcpdf->Bookmark($subHeader, 0, 0, '', 'B', [0, 0, 0]);

            return;
        }

        if (empty($header) || empty($width)) {
            return;
        }

        if (empty($align)) {
            $align = [];
        }

        foreach ($data as $subHeader => $rows) {
            if (!isAssoc($rows)) {
                if ($this->_getFlagUseNonBreakingCells()) {
                    $this->tcpdf->setAutoPageBreak(false);
                    $this->tcpdf->startTransaction();
                }
                if (!empty($prevHeader)) {
                    $this->_createSubHeader($prevHeader, $width, $prevLevel);
                    $this->tcpdf->Bookmark(strip_tags($prevHeader), $prevLevel, 0, '', 'B', [0, 64, 128]);
                }
                if (!empty($subHeader)) {
                    $this->_createSubHeader($subHeader, $width, $level);
                    $this->tcpdf->Bookmark(strip_tags($subHeader), $level, 0, '', 'B', [0, 64, 128]);
                }
                $this->_createTableHeader($header, $width);
                $this->_createTableRows(
                    $rows,
                    $width,
                    $align,
                    $header,
                    $subHeader,
                    $level,
                    $prevHeader,
                    $prevLevel
                );
                if (!empty($prevHeader)) {
                    $prevHeader = '';
                }
            } else {
                $subLevel = $level + 1;
                $this->table($rows, $width, $align, $header, $subLevel, $subHeader, $level);
            }

            $y = $this->tcpdf->GetY();
            $y += (12 / ($level + 1));
            $this->tcpdf->SetY($y);
        }
    }
}
