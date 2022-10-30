<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_get.tform.php";

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
			if(!$app->tform->checkClientLimit('limit_fetchmail')) {
				$app->error($app->tform->wordbook["limit_fetchmail_txt"]);
			}
			if(!$app->tform->checkResellerLimit('limit_fetchmail')) {
				$app->error('Reseller: '.$app->tform->wordbook["limit_fetchmail_txt"]);
			}
		}

		parent::onShowNew();
	}

	function onSubmit() {
		global $app, $conf;

		//* Check if destination email belongs to user
		if(isset($_POST["destination"])) {
			$email = $app->db->queryOneRecord("SELECT email FROM mail_user WHERE email = ? AND ".$app->tform->getAuthSQL('r'), $app->functions->idn_encode($_POST["destination"]));
			if($email["email"] != $app->functions->idn_encode($_POST["destination"])) $app->tform->errorMessage .= $app->tform->lng("no_destination_perm");
		}

		// Check the client limits, if user is not the admin
		if($_SESSION["s"]["user"]["typ"] != 'admin') { // if user is not admin
			// Get the limits of the client
			$client_group_id = $app->functions->intval($_SESSION["s"]["user"]["default_group"]);
			$client = $app->db->queryOneRecord("SELECT limit_fetchmail FROM sys_group, client WHERE sys_group.client_id = client.client_id and sys_group.groupid = ?", $client_group_id);

			// Check if the user may add another transport.
			if($this->id == 0 && $client["limit_fetchmail"] >= 0) {
				$tmp = $app->db->queryOneRecord("SELECT count(mailget_id) as number FROM mail_get WHERE sys_groupid = ?", $client_group_id);
				if($tmp["number"] >= $client["limit_fetchmail"]) {
					$app->tform->errorMessage .= $app->tform->wordbook["limit_fetchmail_txt"]."<br>";
				}
				unset($tmp);
			}
		} // end if user is not admin


		// Set the server ID according to the selected destination
		$tmp = $app->db->queryOneRecord("SELECT server_id FROM mail_user WHERE email = ?", $this->dataRecord["destination"]);
		$this->dataRecord["server_id"] = $tmp["server_id"];
		unset($tmp);

		//* Check that no illegal combination of options is set
		if((!isset($this->dataRecord['source_delete']) || @$this->dataRecord['source_delete'] == 'n') && $this->dataRecord['source_read_all'] == 'y') {
			$app->tform->errorMessage .= $app->tform->lng('error_delete_read_all_combination')."<br>";
		}

		parent::onSubmit();
	}

	function onAfterInsert() {
		global $app;

		$tmp = $app->db->queryOneRecord("SELECT sys_groupid FROM mail_user WHERE email = ?", $this->dataRecord["destination"]);
		$app->db->query("update mail_get SET sys_groupid = ? WHERE mailget_id = ?", $tmp['sys_groupid'], $this->id);

	}

}

$page = new page_action;
$page->onLoad();

?>
