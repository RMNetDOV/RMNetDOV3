<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_forward.tform.php";

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
			if(!$app->tform->checkClientLimit('limit_mailforward', "type = 'forward'")) {
				$app->error($app->tform->wordbook["limit_mailforward_txt"]);
			}
			if(!$app->tform->checkResellerLimit('limit_mailforward', "type = 'forward'")) {
				$app->error('Reseller: '.$app->tform->wordbook["limit_mailforward_txt"]);
			}
		}

		parent::onShowNew();
	}

	function onShowEnd() {
		global $app, $conf;

		$email = $this->dataRecord["source"];
		$email_parts = explode("@", $email);
		$app->tpl->setVar("email_local_part", $email_parts[0]);
		$email_parts[1] = $app->functions->idn_decode($email_parts[1]);

		// Getting Domains of the user
		//$sql = "SELECT domain FROM mail_domain WHERE ".$app->tform->getAuthSQL('r').' ORDER BY domain';
		$sql = "SELECT domain FROM mail_domain WHERE (".$app->tform->getAuthSQL('r').") AND domain NOT IN (SELECT SUBSTR(source,2) FROM mail_forwarding WHERE type = 'aliasdomain') ORDER BY domain";
		$domains = $app->db->queryAllRecords($sql);
		$domain_select = '';
		foreach( $domains as $domain) {
			$domain['domain'] = $app->functions->idn_decode($domain['domain']);
			$selected = (isset($email_parts[1]) && $domain["domain"] == $email_parts[1])?'SELECTED':'';
			$domain_select .= "<option value='" . $app->functions->htmlentities($domain['domain']) . "' $selected>" . $app->functions->htmlentities($domain['domain']) . "</option>\r\n";
		}
		$app->tpl->setVar("email_domain", $domain_select);

		parent::onShowEnd();
	}

	function onSubmit() {
		global $app, $conf;

		// Check if Domain belongs to user
		$domain = $app->db->queryOneRecord("SELECT server_id, domain FROM mail_domain WHERE domain = ? AND ".$app->tform->getAuthSQL('r'), $app->functions->idn_encode($_POST["email_domain"]));
		if($domain["domain"] != $app->functions->idn_encode($_POST["email_domain"])) $app->tform->errorMessage .= $app->tform->wordbook["no_domain_perm"];

		// Check the client limits, if user is not the admin
		if($_SESSION["s"]["user"]["typ"] != 'admin') { // if user is not admin
			// Get the limits of the client
			$client_group_id = $app->functions->intval($_SESSION["s"]["user"]["default_group"]);
			$client = $app->db->queryOneRecord("SELECT limit_mailforward FROM sys_group, client WHERE sys_group.client_id = client.client_id and sys_group.groupid = ?", $client_group_id);

			// Check if the user may add another mailbox.
			if($this->id == 0 && $client["limit_mailforward"] >= 0) {
				$tmp = $app->db->queryOneRecord("SELECT count(forwarding_id) as number FROM mail_forwarding WHERE sys_groupid = ? AND type = 'forward'", $client_group_id);
				if($tmp["number"] >= $client["limit_mailforward"]) {
					$app->tform->errorMessage .= $app->tform->wordbook["limit_mailforward_txt"]."<br>";
				}
				unset($tmp);
			}
		} // end if user is not admin

		// compose the email field
		$this->dataRecord["source"] = $_POST["email_local_part"]."@".$app->functions->idn_encode($_POST["email_domain"]);
		// Set the server id of the mailbox = server ID of mail domain.
		$this->dataRecord["server_id"] = $domain["server_id"];

		unset($this->dataRecord["email_local_part"]);
		unset($this->dataRecord["email_domain"]);

		if(trim($this->dataRecord['destination']) == '') {
			$app->tform->errorMessage .= $app->tform->lng('destination_error_empty') . '<br />';
		} else {
			$targets = preg_split('/[,;\s]+/', trim($this->dataRecord['destination']));
			foreach($targets as $target) {
				if(!$target || filter_var($target, FILTER_VALIDATE_EMAIL) === false) {
					$app->tform->errorMessage .= $app->tform->lng('destination_error_isemail') . '<br />';
					break;
				}
			}
			$this->dataRecord['destination'] = implode(', ', $targets);
		}

		//* Check if there is no active mailbox with this address
		$tmp = $app->db->queryOneRecord("SELECT count(mailuser_id) as number FROM mail_user WHERE postfix = 'y' AND email = ?", $this->dataRecord["source"]);
		if($tmp['number'] > 0) $app->tform->errorMessage .= $app->tform->lng("duplicate_mailbox_txt")."<br>";
		unset($tmp);

		parent::onSubmit();
	}

	function onAfterInsert() {
		global $app;

		$domain = $app->db->queryOneRecord("SELECT sys_groupid FROM mail_domain WHERE domain = ? AND ".$app->tform->getAuthSQL('r'), $app->functions->idn_encode($_POST["email_domain"]));
		$app->db->query("update mail_forwarding SET sys_groupid = ? WHERE forwarding_id = ?", $domain['sys_groupid'], $this->id);

	}

}

$page = new page_action;
$page->onLoad();

?>
