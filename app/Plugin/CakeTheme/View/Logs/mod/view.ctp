<?php
/**
 * This file is the view file of the plugin. Used for rendering
 *  informations of log.
 *
 * CakeTheme: Set theme for application.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.View.Logs.mod
 */

?>  
    <div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">   
<?php
    echo $this->element('CakeTheme.infoLog', compact('log'));
?>              
        </div>
    </div>
