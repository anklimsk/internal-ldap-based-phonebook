<?php
/**
 * This file is the view file of the application. Used for editing
 *  information of department in modal window.
 *
 * InternalPhonebook: Internal phone book based on content of Active Directory.
 * @copyright Copyright 2017-2018, Andrey Klimov.
 * @license https://opensource.org/licenses/mit-license.php MIT License
 * @package app.View.Departments
 */

	echo $this->ViewExtension->headerPage($pageHeader);
?>
	<div class="row">
		<div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-10 col-lg-offset-1">
<?php
		echo $this->element('formDepartmentBase', compact('fieldInputMask'));
?>
		</div>
	</div>
