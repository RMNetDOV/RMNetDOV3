<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_user_filter.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mail');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onShowNew() {
		global $app, $conf;

		// we will check only users, not admins
		if($_SESSION["s"]["user"]["typ"] == 'user') {
			if(!$app->tform->checkClientLimit('limit_mailfilter', "")) {
				$app->error($app->tform->lng("limit_mailfilter_txt"));
			}
			if(!$app->tform->checkResellerLimit('limit_mailfilter', "")) {
				$app->error('Reseller: '.$app->tform->lng("limit_mailfilter_txt"));
			}
		}

		parent::onShowNew();
	}

	function onSubmit() {
		global $app, $conf;

		// Get the parent mail_user record
		$mailuser = $app->db->queryOneRecord("SELECT * FROM mail_user WHERE mailuser_id = ? AND ".$app->tform->getAuthSQL('r'), $_REQUEST["mailuser_id"]);

		// Check if Domain belongs to user
		if($mailuser["mailuser_id"] != $_POST["mailuser_id"]) $app->tform->errorMessage .= $app->tform->wordbook["no_mailuser_perm"];

		// Set the mailuser_id
		$this->dataRecord["mailuser_id"] = $mailuser["mailuser_id"];

		// Remove leading dots
		if(substr($this->dataRecord['target'], 0, 1) == '.') $this->dataRecord['target'] = substr($this->dataRecord['target'], 1);

		// Check the client limits, if user is not the admin
		if($_SESSION["s"]["user"]["typ"] != 'admin') { // if user is not admin
			// Get the limits of the client
			$client_group_id = $app->functions->intval($_SESSION["s"]["user"]["default_group"]);
			$client = $app->db->queryOneRecord("SELECT limit_mailfilter FROM sys_group, client WHERE sys_group.client_id = client.client_id and sys_group.groupid = ?", $client_group_id);

			// Check if the user may add another filter
			if($this->id == 0 && $client["limit_mailfilter"] >= 0) {
				$tmp = $app->db->queryOneRecord("SELECT count(filter_id) as number FROM mail_user_filter WHERE sys_groupid = ?", $client_group_id);
				if($tmp["number"] >= $client["limit_mailfilter"]) {
					$app->tform->errorMessage .= $app->tform->lng("limit_mailfilter_txt")."<br>";
				}
				unset($tmp);
			}
		} // end if user is not admin

		parent::onSubmit();
	}

}

$page = new page_action;
$page->onLoad();

?>
