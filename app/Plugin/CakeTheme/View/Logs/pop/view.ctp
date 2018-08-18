<?php
/**
 * This file is the view file of the plugin. Used for rendering
 *  informations of log.
 *
 * CakeTheme: Set theme for application.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.View.Logs.pop
 */

?> 
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">       
<?php
    echo $this->element('CakeTheme.infoLog', compact('log'));
?>              
            </div>
        </div>
    </div>
