<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_aliasdomain.tform.php";

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
			if(!$app->tform->checkClientLimit('limit_mailaliasdomain', "type = 'aliasdomain'")) {
				$app->error($app->tform->wordbook["limit_mailaliasdomain_txt"]);
			}
			if(!$app->tform->checkResellerLimit('limit_mailaliasdomain', "type = 'aliasdomain'")) {
				$app->error('Reseller: '.$app->tform->wordbook["limit_mailaliasdomain_txt"]);
			}
		}

		parent::onShowNew();
	}

	function onShowEnd() {
		global $app, $conf;

		$source_domain = $app->functions->idn_decode(substr($this->dataRecord["source"], 1));
		$destination_domain = $app->functions->idn_decode(substr($this->dataRecord["destination"], 1));

		// Getting Domains of the user
		$sql = "SELECT domain FROM mail_domain WHERE ".$app->tform->getAuthSQL('r').' ORDER BY domain';
		$domains = $app->db->queryAllRecords($sql);

		$source_select = '';
		$destination_select = '';
		if(is_array($domains)) {
			foreach( $domains as $domain) {
				$domain['domain'] = $app->functions->idn_decode($domain['domain']);
				$selected = ($domain["domain"] == @$source_domain)?'SELECTED':'';
				$source_select .= "<option value='" . $app->functions->htmlentities($domain['domain']) . "' $selected>" . $app->functions->htmlentities($domain['domain']) . "</option>\r\n";
				$selected = ($domain["domain"] == @$destination_domain)?'SELECTED':'';
				$destination_select .= "<option value='" . $app->functions->htmlentities($domain['domain']) . "' $selected>" . $app->functions->htmlentities($domain['domain']) . "</option>\r\n";
			}
		}
		$app->tpl->setVar("source_domain", $source_select);
		$app->tpl->setVar("destination_domain", $destination_select);

		parent::onShowEnd();
	}

	function onSubmit() {
		global $app, $conf;

		// Check if source Domain belongs to user
		$domain = $app->db->queryOneRecord("SELECT server_id, domain FROM mail_domain WHERE domain = ? AND ".$app->tform->getAuthSQL('r'), $app->functions->idn_encode($_POST["source"]));
		if($domain["domain"] != $app->functions->idn_encode($_POST["source"])) $app->tform->errorMessage .= $app->tform->wordbook["no_domain_perm"];

		// Check if the destination domain belongs to the user
		$domain = $app->db->queryOneRecord("SELECT server_id, domain FROM mail_domain WHERE domain = ? AND ".$app->tform->getAuthSQL('r'), $app->functions->idn_encode($_POST["destination"]));
		if($domain["domain"] != $app->functions->idn_encode($_POST["destination"])) $app->tform->errorMessage .= $app->tform->wordbook["no_domain_perm"];

		// Check the client limits, if user is not the admin
		if($_SESSION["s"]["user"]["typ"] != 'admin') { // if user is not admin
			if(!$app->tform->checkClientLimit('limit_mailaliasdomain', "type = 'aliasdomain'")) {
				$app->error($app->tform->wordbook["limit_mailaliasdomain_txt"]);
			}
			if(!$app->tform->checkResellerLimit('limit_mailaliasdomain', "type = 'aliasdomain'")) {
				$app->error('Reseller: '.$app->tform->wordbook["limit_mailaliasdomain_txt"]);
			}
		} // end if user is not admin

		if($this->dataRecord["source"] == $this->dataRecord["destination"]) $app->tform->errorMessage .= $app->tform->wordbook["source_destination_identical_txt"];
		
		/* TODO: check if this quoting is correkt! */
		// compose the source and destination field
		$this->dataRecord["source"] = "@".$this->dataRecord["source"];
		$this->dataRecord["destination"] = "@".$this->dataRecord["destination"];
		// Set the server id of the mailbox = server ID of mail domain.
		$this->dataRecord["server_id"] = $app->functions->intval($domain["server_id"]);

		parent::onSubmit();
	}

	function onAfterInsert() {
		global $app;

		$domain = $app->db->queryOneRecord("SELECT sys_groupid FROM mail_domain WHERE domain = ? AND ".$app->tform->getAuthSQL('r'), $app->functions->idn_encode($_POST["destination"]));
		$app->db->query("update mail_forwarding SET sys_groupid = ? WHERE forwarding_id = ?", $domain['sys_groupid'], $this->id);

	}


}

$page = new page_action;
$page->onLoad();

?>
