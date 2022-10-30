<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/spamfilter_policy.tform.php";

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

	private $record_has_changed = false;

	function onShowNew() {
		global $app;

		// we will check only users, not admins
		if($_SESSION["s"]["user"]["typ"] == 'user') {
			if(!$app->tform->checkClientLimit('limit_spamfilter_policy')) {
				$app->error($app->tform->wordbook["limit_spamfilter_policy_txt"]);
			}
			if(!$app->tform->checkResellerLimit('limit_spamfilter_policy')) {
				$app->error('Reseller: '.$app->tform->wordbook["limit_spamfilter_policy_txt"]);
			}
		}

		parent::onShowNew();
	}

	function onSubmit() {
		global $app;

		// Check the client limits, if user is not the admin
		if($_SESSION["s"]["user"]["typ"] != 'admin') { // if user is not admin
			// Get the limits of the client
			$client_group_id = $app->functions->intval($_SESSION["s"]["user"]["default_group"]);
			$client = $app->db->queryOneRecord("SELECT limit_spamfilter_policy FROM sys_group, client WHERE sys_group.client_id = client.client_id and sys_group.groupid = ?", $client_group_id);

			// Check if the user may add another mailbox.
			if($this->id == 0 && $client["limit_spamfilter_policy"] >= 0) {
				$tmp = $app->db->queryOneRecord("SELECT count(id) as number FROM spamfilter_policy WHERE sys_groupid = ?", $client_group_id);
				if($tmp["number"] >= $client["limit_spamfilter_policy"]) {
					$app->tform->errorMessage .= $app->tform->wordbook["limit_spamfilter_policy_txt"]."<br>";
				}
				unset($tmp);
			}
		} // end if user is not admin

		parent::onSubmit();
	}

	function onAfterUpdate() {
		$this->record_has_changed = false;
		foreach($this->dataRecord as $key => $val) {
			if(isset($this->oldDataRecord[$key]) && @$this->oldDataRecord[$key] != $val) {
				// Record has changed
				$this->record_has_changed = true;
			}
		}
	}

	function onAfterDatalogSave($insert = false) {
		global $app;

		if(!$insert && $this->record_has_changed){
			$spamfilter_users = $app->db->queryAllRecords("SELECT * FROM spamfilter_users WHERE policy_id = ?", intval($this->id));

			if(is_array($spamfilter_users) && !empty($spamfilter_users)){
				foreach($spamfilter_users as $spamfilter_user){
					$app->db->datalogUpdate('spamfilter_users', $spamfilter_user, 'id', $spamfilter_user["id"], true);

					// check if this is an email domain
					if(substr($spamfilter_user['email'],0,1) == '@') {
						$domain = substr($spamfilter_user['email'],1);

						// Nothing special to do for a domain
					}
				}
			}
		}
	}
}

$app->tform_actions = new page_action;
$app->tform_actions->onLoad();
