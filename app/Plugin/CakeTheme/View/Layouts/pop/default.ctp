<?php
    /**
     * This file is the layout file of view the plugin. Used for render
     *  popup window view.
     *
     * CakeTheme: Set theme for application.
     * @copyright Copyright 2016, Andrey Klimov.
     * @package plugin.View.Layouts.pop
     */

    $this->loadHelper('Text');
?> 
<div id="content-popup">
<?php echo $this->Text->stripLinks($this->fetch('content')); ?>
</div>