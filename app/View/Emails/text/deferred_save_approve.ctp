<?php
/**
 * This file is the view file of the application. Used for render
 *  e-mail content about approve deferred save in text format
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Emails.text
 */

	echo __('Changing information') . "\n";
	echo str_repeat('=', 40) . "\n\n";
	echo __('Changing your information is approved') . "\n";
	echo $this->element('mailDeferredSave', compact('deferredSave', 'fieldsLabel', 'fieldsConfig'));
