<?php
/**
 * This file is the view file of the application for creating PDF responses.
 *
 * CakeSpreadsheet: Additional elements of the appearance of the application
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.View
 */

App::uses('View', 'View');
App::import('Vendor', 'CakeSpreadsheet.Spreadsheet', ['file' => 'phpspreadsheet' . DS . 'autoload.php']);
App::uses('Language', 'CakeBasicFunctions.Utility');
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * A view class that is used for creating PDF responses.
 *
 * @package plugin.View
 */
class SpreadsheetView extends View
{

    /**
     * Stores the Spreadsheet() instance
     *
     * @var object
     */
    public $Spreadsheet = null;

    /**
     * Default file name
     *
     * @var string
     */
    protected $_fileName = 'Report';

    /**
     * Style of table header
     *
     * @var array
     */
    protected $_styleTableHeader = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_DOUBLE,
                'color' => ['argb' => '000000'],
            ],
            'inside' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '000000'],
            ],
        ],
        'font' => [
            'bold' => true,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrap' => false,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => [
                'argb' => 'C2FABD'
            ]
        ],
    ];

    /**
     * Style of table subheader
     *
     * @var array
     */
    protected $_styleTableSubhader = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_DOUBLE,
                'color' => ['argb' => '000000'],
            ],
            'inside' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '000000'],
            ],
        ],

        'font' => [
            'bold' => true,
            'italic' => true,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrap' => false,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => [
                'argb' => 'FFFFCC'
            ]
        ],
    ];

    /**
     * Style of table row
     *
     * @var array
     */
    protected $_styleTableRow = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '000000'],
            ],
        ],
        'alignment' => [
//          'horizontal' => Alignment::HORIZONTAL_LEFT,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrap' => true,
        ],
    ];

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
     * Constructor
     *
     * @param Controller $controller A controller object to pull View::_passedVars from.
     * @return void
     */
    public function __construct(Controller $controller = null)
    {
        parent::__construct($controller);

        $this->response->type(CAKE_SPREADSHEET_FILE_EXTENSION);
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder());
        $this->Spreadsheet = new Spreadsheet();
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
        $this->viewPath .= DS . 'Spreadsheet';

        $content = parent::render($action, false);
        if ($this->response->type() == 'text/html') {
            return $content;
        }

        ob_start();
        $writer = IOFactory::createWriter($this->Spreadsheet, CAKE_SPREADSHEET_PHPSPREADSHEET_WRITER);
        $writer->setPreCalculateFormulas(false);
        $writer->save('php://output');
        $content = ob_get_clean();

        $this->Blocks->set('content', $content);
        $fileName = $this->getFileName();
        $fileName .= '.' . CAKE_SPREADSHEET_FILE_EXTENSION;
        $this->response->download($fileName);

        return $this->Blocks->get('content');
    }

    /**
     * Return style of table header
     *
     * @return array Return array style of table header.
     */
    protected function _getStyleTableHeader()
    {
        return (array)$this->_styleTableHeader;
    }

    /**
     * Return style of table subheader
     *
     * @return array Return array style of table subheader.
     */
    protected function _getStyleTableSubheader()
    {
        return (array)$this->_styleTableSubhader;
    }
    /**
     * Return style of table row
     *
     * @return array Return array style of table row.
     */
    protected function _getStyleTableRow()
    {
        return (array)$this->_styleTableRow;
    }

    /**
     * Write a table
     *
     * @param array $data Array of rows for table.
     * @param array $width Array of width for table cells.
     * @param array $align Array of align position for table cells.
     * @param array $header Array of header for table cells.
     * @return void
     */
    public function table($data = [], $width = [], $align = [], $header = [])
    {
        $language = new Language();
        $locale = $language->getCurrentUIlang(true);
        if (empty($locale)) {
            $locale = 'en';
        }
        \PhpOffice\PhpSpreadsheet\Settings::setLocale($locale);

        $styleTableHeader = $this->_getStyleTableHeader();
        $styleTableSubheader = $this->_getStyleTableSubheader();
        $styleTableRow = $this->_getStyleTableRow();
        $sheetIndex = 0;
        if (empty($data)) {
            $sheet = $this->Spreadsheet->getSheet($sheetIndex);
            $sheet->setTitle(__d('view_extension', 'No data to display'));
            $sheet->setCellValue('B2', __d('view_extension', 'No data to display'));
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getStyle('B2')->applyFromArray($styleTableHeader);

            return;
        }

        // Render data
        foreach ($data as $group => $info) {
            if (empty($info)) {
                continue;
            }

            // Sheet - Group
            if ($sheetIndex > 0) {
                $this->Spreadsheet->createSheet();
            }
            $sheet = $this->Spreadsheet->getSheet($sheetIndex);
            $sheetTitle = mb_strimwidth(strip_tags($group), 0, 31, '...', 'UTF-8');
            if (empty($sheetTitle)) {
                $sheetTitle = 'Sheet_' . ($sheetIndex + 1);
            }
            $sheet->setTitle($sheetTitle);

            // Header
            $iRow = 2;
            $azLiters = range('A', 'Z');
            $azLitersCount = count($azLiters);
            $columnCache = [];
            foreach ($header as $i => $headerItem) {
                if ($i > ($azLitersCount - 1)) {
                    $part = floor($i / $azLitersCount);
                    $liter = $azLiters[$part - 1] . $azLiters[$i - ($part * $azLitersCount)];
                } else {
                    $liter = $azLiters[$i + 1];
                }
                $sheet->setCellValue($liter . $iRow, $headerItem);
                $sheet->getColumnDimension($liter)->setWidth($width[$i]);
                $columnCache[] = $liter;
            }
            $sheet->getStyle('B' . $iRow . ':' . $liter . $iRow)->applyFromArray($styleTableHeader);
            $iRow++;
            foreach ($info as $subGroup => $row) {
                if (is_int($subGroup)) {
                    foreach ($row as $i => $field) {
                        $sheet->setCellValue($columnCache[$i] . $iRow, strip_tags(preg_replace('#<br\s*/?>#i', "\n", $field)));
                        switch ($align[$i]) {
                            case 'R':
                                $alignStyle = Alignment::HORIZONTAL_RIGHT;
                                break;
                            case 'C':
                                $alignStyle = Alignment::HORIZONTAL_CENTER;
                                break;
                            case 'L':
                            default:
                                $alignStyle = Alignment::HORIZONTAL_LEFT;
                        }
                        $sheet->getStyle($columnCache[$i] . $iRow)->getAlignment()->setHorizontal($alignStyle);
                        $sheet->getStyle('B' . $iRow . ':' . $liter . $iRow)->applyFromArray($styleTableRow);
                    }
                } else {
                    if (!empty($subGroup)) {
                        $sheet->setCellValue('B' . $iRow, $subGroup);
                        $sheet->mergeCells('B' . $iRow . ':' . $liter . $iRow);
                        $sheet->getStyle('B' . $iRow . ':' . $liter . $iRow)->applyFromArray($styleTableSubheader);
                        $iRow++;
                    }
                    foreach ($row as $rowItem) {
                        foreach ($rowItem as $i => $field) {
                            $sheet->setCellValue($columnCache[$i] . $iRow, strip_tags(preg_replace('#<br\s*/?>#i', "\r\n", $field)));
                            switch ($align[$i]) {
                                case 'R':
                                    $alignStyle = Alignment::HORIZONTAL_RIGHT;
                                    break;
                                case 'C':
                                    $alignStyle = Alignment::HORIZONTAL_CENTER;
                                    break;
                                case 'L':
                                default:
                                    $alignStyle = Alignment::HORIZONTAL_LEFT;
                            }
                            $sheet->getStyle($columnCache[$i] . $iRow)->getAlignment()->setHorizontal($alignStyle);
                            $sheet->getStyle('B' . $iRow . ':' . $liter . $iRow)->applyFromArray($styleTableRow);
                        }
                        $iRow++;
                    }
                }
                $iRow++;
            }

            // Set AutoFilter
            $this->Spreadsheet->getActiveSheet()->setAutoFilter('B2:' . $liter . --$iRow);
            $sheetIndex++;
        }

        $this->Spreadsheet->setActiveSheetIndex(0);
    }
}
