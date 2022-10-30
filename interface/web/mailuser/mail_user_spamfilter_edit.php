<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_user_spamfilter.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mailuser');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onShow() {
		global $app;

		$this->id = $app->functions->intval($_SESSION['s']['user']['mailuser_id']);

		parent::onShow();

	}

	function onSubmit() {
		global $app;

		$this->id = $app->functions->intval($_SESSION['s']['user']['mailuser_id']);

		parent::onSubmit();

	}

	function onAfterUpdate() {
		global $app, $conf;

		$rec = $app->tform->getDataRecord($this->id);
		$email_parts = explode('@', $rec['email']);
		$email_domain = $email_parts[1];
		$domain = $app->db->queryOneRecord("SELECT sys_userid, sys_groupid, server_id FROM mail_domain WHERE domain = ?", $email_domain);

		// Spamfilter policy
		$policy_id = $app->functions->intval($this->dataRecord["policy"]);
		$tmp_user = $app->db->queryOneRecord("SELECT id FROM spamfilter_users WHERE email = ?", $rec["email"]);
		if($tmp_user["id"] > 0) {
			// There is already a record that we will update
			$app->db->datalogUpdate('spamfilter_users', array("policy_id" => $policy_id), 'id', $tmp_user["id"]);
		} else {
			// We create a new record
			$insert_data = array(
				"sys_userid" => $domain["sys_userid"],
				"sys_groupid" => $domain["sys_groupid"],
				"sys_perm_user" => 'riud',
				"sys_perm_group" => 'riud',
				"sys_perm_other" => '',
				"server_id" => $domain["server_id"],
				"priority" => 7,
				"policy_id" => $policy_id,
				"email" => $rec["email"],
				"fullname" => $rec["email"],
				"local" => 'Y'
			);
			$app->db->datalogInsert('spamfilter_users', $insert_data, 'id');
		} // endif spamfilter policy
	}

	function onShowEnd() {
		global $app, $conf;

		$rec = $app->tform->getDataRecord($this->id);
		$app->tpl->setVar("email", $app->functions->idn_decode($rec['email']), true);

		// Get the spamfilter policies for the user
		$tmp_user = $app->db->queryOneRecord("SELECT policy_id FROM spamfilter_users WHERE email = ?", $rec['email']);
		$sql = "SELECT id, policy_name FROM spamfilter_policy WHERE ".$app->tform->getAuthSQL('r');
		$policies = $app->db->queryAllRecords($sql);
		$policy_select = "<option value='0'".(($tmp_user['policy_id'] == 0)?" SELECTED>":">").$app->tform->lng("inherit_policy")."</option>";
		if(is_array($policies)) {
			foreach( $policies as $p) {
				$selected = ($p["id"] == $tmp_user["policy_id"])?'SELECTED':'';
				$policy_select .= "<option value='$p[id]' $selected>" . $app->functions->htmlentities($p['policy_name']) . "</option>\r\n";
			}
		}
		$app->tpl->setVar("policy", $policy_select);
		unset($policies);
		unset($policy_select);
		unset($tmp_user);

		parent::onShowEnd();
	}

}

$app->tform_actions = new page_action;
$app->tform_actions->onLoad();

?>
