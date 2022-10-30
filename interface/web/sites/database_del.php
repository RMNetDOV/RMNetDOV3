<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/database.list.php";
$tform_def_file = "form/database.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->uses("tform_actions");
class page_action extends tform_actions {
	function onBeforeDelete() {
		global $app; $conf;
		if($app->tform->checkPerm($this->id, 'd') == false) $app->error($app->lng('error_no_delete_permission'));

		$app->uses('sites_database_plugin');
		//$app->sites_database_plugin->processDatabaseDelete($this->id);
	}

}

$page = new page_action;
$page->onDelete();

?>
