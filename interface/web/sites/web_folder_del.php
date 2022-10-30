<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/web_folder.list.php";
$tform_def_file = "form/web_folder.tform.php";

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

		// Delete all users that belong to this folder.
		$records = $app->db->queryAllRecords("SELECT web_folder_user_id FROM web_folder_user WHERE web_folder_id = ?", $this->id);
		foreach($records as $rec) {
			$app->db->datalogDelete('web_folder_user', 'web_folder_user_id', $rec['web_folder_user_id']);
		}
		unset($records);
	}

}

$page = new page_action;
$page->onDelete();

?>
