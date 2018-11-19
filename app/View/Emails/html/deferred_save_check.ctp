<?php
/**
 * This file is the view file of the application. Used for render
 *  e-mail content about found new deferred save in HTML format
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Emails.html
 */
?> 
<div class="container">
<?php
    $number = 1;
    $moreRecords = $countNewDeferredSave - count($deferredSaves);
    $numberText = $this->Number->format($countNewDeferredSave, ['thousands' => ' ', 'before' => '', 'places' => 0]);
    $numberText = mb_ereg_replace('\b' . __('one') . '\b', __x('deferred_save', 'one'), $numberText);
    $params = [];
if (!empty($prefix)) {
    $params = ['prefix' => $prefix, $prefix => true];
}
    echo $this->Html->div('page-header', $this->Html->tag('h2', __('Found new deferred saves')));
    echo $this->Html->div('alert alert-warning text-center', __('Found %s %s', $this->Html->tag(
        'strong',
        $numberText
    ), __n('new deferred save', 'new deferred saves', $countNewDeferredSave)));
    foreach ($deferredSaves as $employeeName => $deferredSave) {
        echo $this->Html->div('page-header', $this->Html->tag('h3', $number . '. ' . h($employeeName)));
        echo $this->element('mailDeferredSave', compact('deferredSave', 'fieldsLabel', 'fieldsConfig'));
        $number++;
    }
    if ($moreRecords > 0) {
        echo $this->Html->para('text-right', $this->Html->tag('em', __(
            '...And %s more %s',
            $this->Number->format($moreRecords, ['thousands' => ' ', 'before' => '', 'places' => 0]),
            __n('record', 'records', $moreRecords)
        )));
    }
    echo $this->Html->tag('br', '');
    echo $this->Html->para('text-right', __(
        'For management list of deferred saves, click \'%s\'',
        $this->Html->link(__('here'), ['controller' => 'deferred', 'action' => 'index', 'full_base' => true] + $params)
    ));
?>
</div>
