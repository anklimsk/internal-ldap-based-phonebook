<?php
/**
 * This file is the view file of the application. Used for render
 *  information of deferred save use in e-mail
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Elements
 */

if (!isset($deferredSave)) {
    $deferredSave = [];
}

if (!isset($fieldsLabel)) {
    $fieldsLabel = [];
}

if (!isset($fieldsConfig)) {
    $fieldsConfig = [];
}

if (empty($deferredSave)) {
    return;
}
?>  
<dl class="dl-horizontal">
<?php
    $deferredInfoText = $this->Deferred->getDeferredInfo($deferredSave, [], $fieldsLabel, $fieldsConfig);
    echo $this->Text->stripLinks($deferredInfoText);
?>
</dl>
