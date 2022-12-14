<?php

die('deprecated');

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_spamfilter.tform.php";

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

	function onShowEnd() {
		global $app, $conf;

		// Getting email from data record
		$email = $this->dataRecord["email"];
		$email_parts = explode("@", $email);
		$app->tpl->setVar("email_local_part", $email_parts[0]);

		// Getting Domains of the user
		$sql = "SELECT domain FROM mail_domain WHERE type = 'local' AND ".$app->tform->getAuthSQL('r');
		$domains = $app->db->queryAllRecords($sql);
		$domain_select = '';
		foreach( $domains as $domain) {
			$selected = ($domain["domain"] == $email_parts[1])?'SELECTED':'';
			$domain_select .= "<option value='" . $app->functions->htmlentities($domain['domain']) . "' $selected>" . $app->functions->htmlentities($domain['domain']) . "</option>\r\n";
		}
		$app->tpl->setVar("email_domain", $domain_select);

		// calculate scores
		if(count($this->dataRecord) > 0) {
			$app->tpl->setVar("spam_rewrite_score_int", number_format($this->dataRecord["spam_rewrite_score_int"] / 100, 2, '.', ''));
			$app->tpl->setVar("spam_redirect_score_int", number_format($this->dataRecord["spam_redirect_score_int"] / 100, 2, '.', ''));
			$app->tpl->setVar("spam_delete_score_int", number_format($this->dataRecord["spam_delete_score_int"] / 100, 2, '.', ''));
		}

		// Changing maildir to mailbox_id
		$sql = "SELECT mailbox_id FROM mail_box WHERE maildir = ? AND ".$app->tform->getAuthSQL('r');
		$mailbox = $app->db->queryOneRecord($sql, $this->dataRecord["spam_redirect_maildir"]);
		$this->dataRecord["spam_redirect_maildir"] = $mailbox["mailbox_id"];

		parent::onShowEnd();
	}

	function onSubmit() {
		global $app, $conf;

		// Check if Domain belongs to user
		$domain = $app->db->queryOneRecord("SELECT server_id, domain FROM mail_domain WHERE domain = ? AND ".$app->tform->getAuthSQL('r'), $_POST["email_domain"]);
		if($domain["domain"] != $_POST["email_domain"]) $app->tform->errorMessage .= $app->tform->wordbook["no_domain_perm"];

		// compose the email field
		if($_POST["email_local_part"] != '') {
			$this->dataRecord["email"] = $_POST["email_local_part"]."@".$_POST["email_domain"];
		} else {
			$this->dataRecord["email"] = $_POST["email_domain"];
		}
		// Set the server id of the mailbox = server ID of mail domain.
		$this->dataRecord["server_id"] = $domain["server_id"];

		unset($this->dataRecord["email_local_part"]);
		unset($this->dataRecord["email_domain"]);

		// calculate scores
		$this->dataRecord["spam_rewrite_score_int"]  = $_POST["spam_rewrite_score_int"] * 100;
		$this->dataRecord["spam_redirect_score_int"]  = $_POST["spam_redirect_score_int"] * 100;
		$this->dataRecord["spam_delete_score_int"]   = $_POST["spam_delete_score_int"] * 100;

		// Changing mailbox_id to maildir
		$sql = "SELECT maildir FROM mail_box WHERE mailbox_id = ? AND ".$app->tform->getAuthSQL('r');
		$mailbox = $app->db->queryOneRecord($sql, $_POST["spam_redirect_maildir"]);
		$this->dataRecord["spam_redirect_maildir"] = $mailbox["maildir"];

		parent::onSubmit();
	}

}

$app->tform_actions = new page_action;
$app->tform_actions->onLoad();


?>
