<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/web_folder_user.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('sites');

// Loading classes
$app->uses('tpl,tform,tform_actions,validate_cron');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onSubmit() {
		global $app, $conf;

		// Get the record of the parent domain
		$folder = $app->db->queryOneRecord("select * FROM web_folder WHERE web_folder_id = ? AND ".$app->tform->getAuthSQL('r'), @$this->dataRecord["web_folder_id"]);
		if(!$folder || $folder['web_folder_id'] != @$this->dataRecord['web_folder_id']) $app->tform->errorMessage .= $app->tform->lng("no_folder_perm");

		// Set a few fixed values
		$this->dataRecord["server_id"] = $folder["server_id"];
		
		// make sure this folder/user combination does not exist already
		if($this->id > 0){
			$user = $app->db->queryOneRecord("SELECT * FROM web_folder_user WHERE web_folder_id = ? AND username = ? AND web_folder_user_id != ?", $this->dataRecord['web_folder_id'], $this->dataRecord['username'], $this->id);
		} else {
			$user = $app->db->queryOneRecord("SELECT * FROM web_folder_user WHERE web_folder_id = ? AND username = ?", $this->dataRecord['web_folder_id'], $this->dataRecord['username']);
		}
		if(is_array($user) && !empty($user)) $app->tform->errorMessage .= $app->tform->lng('error_user_exists_already_txt');

		parent::onSubmit();
	}
	
	function onAfterInsert() {
		global $app, $conf;

		$folder = $app->db->queryOneRecord("SELECT * FROM web_folder WHERE web_folder_id = ?", $this->dataRecord["web_folder_id"]);

		// The web folder user entry shall be owned by the same group as the web folder
		$sys_groupid = $app->functions->intval($folder['sys_groupid']);

		$sql = "UPDATE web_folder_user SET sys_groupid = ? WHERE web_folder_user_id = ?";
		$app->db->query($sql, $sys_groupid, $this->id);
	}
	
	function onAfterUpdate() {
		global $app, $conf;

		//* When the web folder has been changed
		if(isset($this->dataRecord['web_folder_id']) && $this->oldDataRecord['web_folder_id'] != $this->dataRecord['web_folder_id']) {
			$folder = $app->db->queryOneRecord("SELECT * FROM web_folder WHERE web_folder_id = ?", $this->dataRecord["web_folder_id"]);

			// The web folder user entry shall be owned by the same group as the web folder
			$sys_groupid = $app->functions->intval($folder['sys_groupid']);

			$sql = "UPDATE web_folder_user SET sys_groupid = ? WHERE web_folder_user_id = ?";
			$app->db->query($sql, $sys_groupid, $this->id);
		}

	}

}

$page = new page_action;
$page->onLoad();

?>
