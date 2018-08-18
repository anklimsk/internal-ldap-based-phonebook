<?php
/**
 * This file is the view file of the application. Used for viewing
 *  information of deferred save in popup window.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Deferred.pop
 */
?>  
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<?php
    echo $this->element('infoDeferred', compact('deferredSave', 'fieldsLabel', 'fieldsConfig'));
?>  
        </div>
    </div>
