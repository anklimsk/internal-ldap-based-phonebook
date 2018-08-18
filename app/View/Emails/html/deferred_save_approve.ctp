<?php
/**
 * This file is the view file of the application. Used for render
 *  e-mail content about approve deferred save in HTML format
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Emails.html
 */
?> 
<div class="container">
<?php
    echo $this->Html->div('page-header', $this->Html->tag('h2', __('Changing information')));
    echo $this->Html->div('alert alert-success text-center', __('Changing your information is approved'));
    echo $this->element('mailDeferredSave', compact('deferredSave', 'fieldsLabel', 'fieldsConfig'));

