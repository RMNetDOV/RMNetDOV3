<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/cron.list.php";
$tform_def_file = "form/cron.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onBeforeDelete() {
		global $app; $conf;

		if($app->tform->checkPerm($this->id, 'd') == false) $app->error($app->lng('error_no_delete_permission'));
	}

}

$page = new page_action;
$page->onDelete();

?>
