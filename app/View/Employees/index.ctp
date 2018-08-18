<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  full list of employees.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Employees
     */

    $this->assign('title', __('Information retrieval'));

    echo $this->AssetCompress->css('CakeTheme.tree', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.tree', ['block' => 'script']);
    echo $this->AssetCompress->css('CakeTheme.fileupload', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-image-min', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-image', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-i18n-' . $uiLcid2, ['block' => 'script']);
?>
    <div class="container container-table"> 
        <div class="row vertical-center-row">
<?php if (!empty($birthdays)) : ?>      
            <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
<?php echo $this->element('infoBirthday', compact('birthdays')); ?>
            </div>
<?php endif; ?>
            <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
<?php
    echo $this->Search->createFormSearch($search_targetFields, $search_targetFieldsSelected, $search_urlActionSearch, $search_targetDeep, $search_querySearchMinLength);
?>          
            </div>
            <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
<?php
    echo $this->element('infoStatistics', compact('countEmployees', 'lastUpdate'));
?>              
            </div>
        </div>
    </div>
