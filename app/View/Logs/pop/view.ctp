<?php
/**
 * This file is the view file of the application. Used for viewing
 *  information of log in popup window.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017, Andrey Klimov.
 * @package app.View.Logs.pop
 */
?>  
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<?php
    echo $this->element('infoLog', compact('deferredSave', 'fieldsLabel', 'fieldsConfig'));
?>  
        </div>
    </div>
