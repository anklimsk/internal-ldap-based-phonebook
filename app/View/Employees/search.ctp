<?php
    /**
     * This file is the view file of the application. Used for viewing
     *  result of search employees.
     *
     * InternalPhonebook: Internal phone book based on content of Active Directory.
     * @copyright Copyright 2017, Andrey Klimov.
     * @package app.View.Employees
     */

    $optUrl = [
        'controller' => 'employees',
        'action' => 'search',
        '?' => http_build_query(compact('target') +
            ['correct' => true, 'query' => (!empty($queryCorrect) ? $queryCorrect : $query)])];
    $this->Paginator->options([
        'url' => $optUrl
    ]);

    echo $this->AssetCompress->css('CakeTheme.tree', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.tree', ['block' => 'script']);
    echo $this->AssetCompress->css('CakeTheme.fileupload', ['block' => 'css']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-image-min', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-image', ['block' => 'script']);
    echo $this->AssetCompress->script('CakeTheme.fileupload-i18n-' . $uiLcid2, ['block' => 'script']);

    $this->assign('title', $pageTitle);

    $this->Html->addCrumb(__('Search information'), [
        'plugin' => 'cake_search_info',
        'controller' => 'search',
        'action' => 'index']);
    if (isset($result['total']) && ($result['total'] > 0)) {
        $this->Html->addCrumb(__('Results of search'));
    } else {
        $this->Html->addCrumb(__('New search'));
    }
?>
<div class="container-fluid">
<?php
    echo $this->Search->correctQuery($query, $queryCorrect, $target, $optUrl);
    echo $this->element('tableSearchResults', compact(
        'query',
        'queryCorrect',
        'correct',
        'result',
        'fieldsConfig'
    ));
?>
</div>
