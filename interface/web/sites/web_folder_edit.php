<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/web_folder.tform.php";

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
		$parent_domain = $app->db->queryOneRecord("select * FROM web_domain WHERE domain_id = ? AND ".$app->tform->getAuthSQL('r'), @$this->dataRecord["parent_domain_id"]);
		if(!$parent_domain || $parent_domain['domain_id'] != @$this->dataRecord['parent_domain_id']) $app->tform->errorMessage .= $app->tform->lng("no_domain_perm");

		// Set a few fixed values
		$this->dataRecord["server_id"] = $parent_domain["server_id"];
		
		// make sure this folder isn't protected already
		if($this->id > 0){
			$folder = $app->db->queryOneRecord("SELECT * FROM web_folder WHERE parent_domain_id = ? AND path = ? AND web_folder_id != ?", $this->dataRecord['parent_domain_id'], $this->dataRecord['path'], $this->id);
		} else {
			$folder = $app->db->queryOneRecord("SELECT * FROM web_folder WHERE parent_domain_id = ? AND path = ?", $this->dataRecord['parent_domain_id'], $this->dataRecord['path']);
		}
		if(is_array($folder) && !empty($folder)) $app->tform->errorMessage .= $app->tform->lng('error_folder_already_protected_txt');

		parent::onSubmit();
	}
	
	function onAfterInsert() {
		global $app, $conf;

		$web = $app->db->queryOneRecord("SELECT * FROM web_domain WHERE domain_id = ?", $this->dataRecord["parent_domain_id"]);

		// The web folder entry shall be owned by the same group as the website
		$sys_groupid = $app->functions->intval($web['sys_groupid']);

		$sql = "UPDATE web_folder SET sys_groupid = ? WHERE web_folder_id = ?";
		$app->db->query($sql, $sys_groupid, $this->id);
	}
	
	function onAfterUpdate() {
		global $app, $conf;

		//* When the site of the web folder has been changed
		if(isset($this->dataRecord['parent_domain_id']) && $this->oldDataRecord['parent_domain_id'] != $this->dataRecord['parent_domain_id']) {
			$web = $app->db->queryOneRecord("SELECT * FROM web_domain WHERE domain_id = ?", $this->dataRecord["parent_domain_id"]);

			// The web folder entry shall be owned by the same group as the website
			$sys_groupid = $app->functions->intval($web['sys_groupid']);

			$sql = "UPDATE web_folder SET sys_groupid = ? WHERE web_folder_id = ?";
			$app->db->query($sql, $sys_groupid, $this->id);
		}

	}

}

$page = new page_action;
$page->onLoad();

?>
