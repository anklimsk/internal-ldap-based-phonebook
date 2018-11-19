<?php
    /**
     * This file is the view file of the application. Used for render
     *  e-mail content about found new deferred save in text format
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
     * @package app.View.Emails.text
     */

    $number = 1;
    $moreRecords = $countNewDeferredSave - count($deferredSaves);
    $numberText = $this->Number->format($countNewDeferredSave, ['thousands' => ' ', 'before' => '', 'places' => 0]);
    $numberText = mb_ereg_replace('\b' . __('one') . '\b', __x('deferred_save', 'one'), $numberText);
    $params = [];
if (!empty($prefix)) {
    $params = ['prefix' => $prefix, $prefix => true];
}
    echo __('Found new deferred saves') . "\n";
    echo str_repeat('=', 40) . "\n\n";
    echo __('Found %s %s', $numberText, __n('new deferred save', 'new deferred saves', $countNewDeferredSave)) . "\n\n";
foreach ($deferredSaves as $employeeName => $deferredSave) {
    echo $number . '. ' . $employeeName;
    echo $this->element('mailDeferredSave', compact('deferredSave', 'fieldsLabel', 'fieldsConfig'));
    $number++;
}
if ($moreRecords > 0) {
    echo __(
        '...And %s more %s',
        $this->Number->format($moreRecords, ['thousands' => ' ', 'before' => '', 'places' => 0]),
        __n('record', 'records', $moreRecords)
    );
}
    echo "\n";
    echo __(
        'For management list of deferred saves, click \'%s\'',
        $this->Html->url(['controller' => 'deferred', 'action' => 'index', 'full_base' => true] + $params)
    );
