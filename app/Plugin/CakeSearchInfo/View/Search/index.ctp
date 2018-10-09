<?php
/**
 * This file is the view file of the plugin. Used for begin search.
 *
 * CakeSearchInfo: Search information in project database
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.View.Search
 */

	$this->assign('title', $pageTitle);
	$this->ViewExtension->addBreadCrumbs($breadCrumbs);
?>
	<div class="container container-table"> 
		<div class="row vertical-center-row">
			<div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
<?php
	echo $this->Search->createFormSearch($search_targetFields, $search_targetFieldsSelected, $search_urlActionSearch, $search_targetDeep, $search_querySearchMinLength);
?>
			</div>
		</div>
	</div>
