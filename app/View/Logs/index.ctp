<?php
/**
 * This file is the view file of the application. Used for viewing
 *  full list of logs.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Logs
 */

	echo $this->AssetCompress->css('CakeTheme.tree', ['block' => 'css']);
	echo $this->AssetCompress->script('CakeTheme.tree', ['block' => 'script']);
	echo $this->AssetCompress->css('CakeTheme.fileupload', ['block' => 'css']);
	echo $this->AssetCompress->script('CakeTheme.fileupload-image-min', ['block' => 'script']);
	echo $this->AssetCompress->script('CakeTheme.fileupload', ['block' => 'script']);
	echo $this->AssetCompress->script('CakeTheme.fileupload-image', ['block' => 'script']);
	echo $this->AssetCompress->script('CakeTheme.fileupload-i18n-' . $uiLcid2, ['block' => 'script']);

	$this->assign('title', $pageHeader);
	$this->ViewExtension->addBreadCrumbs($breadCrumbs);
?>
<div class="container">
<?php
	echo $this->ViewExtension->headerPage($pageHeader, $headerMenuActions);
	echo $this->element('tableLog', compact('logs', 'fieldsLabel', 'fieldsConfig'));
?>
</div>
