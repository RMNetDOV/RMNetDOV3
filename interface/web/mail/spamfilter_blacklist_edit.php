<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/spamfilter_blacklist.tform.php";

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
			if(!$app->tform->checkClientLimit('limit_spamfilter_wblist')) {
				$app->error($app->tform->wordbook["limit_spamfilter_wblist_txt"]);
			}
			if(!$app->tform->checkResellerLimit('limit_spamfilter_wblist')) {
				$app->error('Reseller: '.$app->tform->wordbook["limit_spamfilter_wblist_txt"]);
			}
		}

		parent::onShowNew();
	}

	function onSubmit() {
		global $app, $conf;

		// Check the client limits, if user is not the admin
		if($_SESSION["s"]["user"]["typ"] != 'admin') { // if user is not admin
			// Get the limits of the client
			$client_group_id = $app->functions->intval($_SESSION["s"]["user"]["default_group"]);
			$client = $app->db->queryOneRecord("SELECT limit_spamfilter_wblist FROM sys_group, client WHERE sys_group.client_id = client.client_id and sys_group.groupid = ?", $client_group_id);

			// Check if the user may add another mailbox.
			if($this->id == 0 && $client["limit_spamfilter_wblist"] >= 0) {
				$tmp = $app->db->queryOneRecord("SELECT count(wblist_id) as number FROM spamfilter_wblist WHERE sys_groupid = ?", $client_group_id);
				if($tmp["number"] >= $client["limit_spamfilter_wblist"]) {
					$app->tform->errorMessage .= $app->tform->wordbook["limit_spamfilter_wblist_txt"]."<br>";
				}
				unset($tmp);
			}
		} // end if user is not admin

		// Select and set the server_id so it matches the server_id of the spamfilter_users record
		$tmp = $app->db->queryOneRecord("SELECT server_id FROM spamfilter_users WHERE id = ?", $this->dataRecord["rid"]);
		$this->dataRecord["server_id"] = $tmp["server_id"];
		unset($tmp);

		parent::onSubmit();
	}

}

$app->tform_actions = new page_action;
$app->tform_actions->onLoad();


?>
