<?php
/**
 * This file is the view file of the application. Used for render
 *  e-mail content about reject deferred save in HTML format
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Emails.html
 */
?> 
<div class="container">
<?php
	echo $this->Html->div('page-header', $this->Html->tag('h2', __('Changing information')));
	echo $this->Html->div('alert alert-danger text-center', __('Changing your information is rejected'));
	echo $this->element('mailDeferredSave', compact('deferredSave', 'fieldsLabel', 'fieldsConfig'));
?>
</div>
