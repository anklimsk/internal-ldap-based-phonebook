<?php
/**
 * This file is the view file of the plugin. Used for rendering
 *  MS Excel file.
 *
 * CakeSpreadsheet: Additional elements of the appearance of the application
 * @copyright Copyright 2016, Andrey Klimov.
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

$this->table($exportData, $width, $align, $header);
