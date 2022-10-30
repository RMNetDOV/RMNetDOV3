<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/xmpp_user.tform.php";

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
			if(!$app->tform->checkClientLimit('limit_xmpp_user')) {
				$app->error($app->tform->wordbook["limit_xmpp_user_txt"]);
			}
			if(!$app->tform->checkResellerLimit('limit_xmpp_user')) {
				$app->error('Reseller: '.$app->tform->wordbook["limit_xmpp_user_txt"]);
			}
		}

		parent::onShowNew();
	}

	function onShowEnd() {
		global $app, $conf;

		$jid = $this->dataRecord["jid"];
		$jid_parts = explode("@", $jid);
		$app->tpl->setVar("jid_local_part", $jid_parts[0]);
		$jid_parts[1] = $app->functions->idn_decode($jid_parts[1]);

		// Getting Domains of the user
		$sql = "SELECT domain, server_id FROM xmpp_domain WHERE ".$app->tform->getAuthSQL('r')." ORDER BY domain";
		$domains = $app->db->queryAllRecords($sql);
		$domain_select = '';
		if(is_array($domains)) {
			foreach( $domains as $domain) {
				$domain['domain'] = $app->functions->idn_decode($domain['domain']);
				$selected = ($domain["domain"] == @$jid_parts[1])?'SELECTED':'';
				$domain_select .= "<option value='" . $app->functions->htmlentities($domain['domain']) . "' $selected>" . $app->functions->htmlentities($domain['domain']) . "</option>\r\n";
			}
		}
		$app->tpl->setVar("jid_domain", $domain_select);
		unset($domains);
		unset($domain_select);


		parent::onShowEnd();
	}

	function onSubmit() {
		global $app, $conf;
		//* Check if Domain belongs to user
		if(isset($_POST["jid_domain"])) {
			$domain = $app->db->queryOneRecord("SELECT server_id, domain FROM xmpp_domain WHERE domain = ? AND ".$app->tform->getAuthSQL('r'), $app->functions->idn_encode($_POST["jid_domain"]));
			if($domain["domain"] != $app->functions->idn_encode($_POST["jid_domain"])) $app->tform->errorMessage .= $app->tform->lng("no_domain_perm");
		}


		//* if its an insert, check that the password is not empty
		if($this->id == 0 && $_POST["password"] == '') {
			$app->tform->errorMessage .= $app->tform->lng("error_no_pwd")."<br>";
		}

		//* Check the client limits, if user is not the admin
		if($_SESSION["s"]["user"]["typ"] != 'admin') { // if user is not admin
			// Get the limits of the client
			$client_group_id = $app->functions->intval($_SESSION["s"]["user"]["default_group"]);
			$client = $app->db->queryOneRecord("SELECT limit_xmpp_user, parent_client_id FROM sys_group, client WHERE sys_group.client_id = client.client_id and sys_group.groupid = ?", $client_group_id);


			// Check if the user may add another xmpp user.
			if($this->id == 0 && $client["limit_xmpp_user"] >= 0) {
				$tmp = $app->db->queryOneRecord("SELECT count(xmppuser_id) as number FROM xmpp_user WHERE sys_groupid = ?", $client_group_id);
				if($tmp["number"] >= $client["limit_xmpp_user"]) {
					$app->tform->errorMessage .= $app->tform->lng("limit_xmpp_user_txt")."<br>";
				}
				unset($tmp);
			}
		} // end if user is not admin


		$app->uses('getconf');
		$xmpp_config = $app->getconf->get_server_config(!empty($domain["server_id"]) ? $domain["server_id"] : '', 'xmpp');

		//* compose the xmpp field
		if(isset($_POST["jid_local_part"]) && isset($_POST["jid_domain"])) {
			$this->dataRecord["jid"] = strtolower($_POST["jid_local_part"]."@".$app->functions->idn_encode($_POST["jid_domain"]));

			// Set the server id of the xmpp user = server ID of xmpp domain.
			$this->dataRecord["server_id"] = $domain["server_id"];

			unset($this->dataRecord["jid_local_part"]);
			unset($this->dataRecord["jid_domain"]);

		}

		parent::onSubmit();
	}

	function onAfterInsert() {
		global $app, $conf;

		// Set the domain owner as xmpp user owner
		$domain = $app->db->queryOneRecord("SELECT sys_groupid, server_id FROM xmpp_domain WHERE domain = ? AND ".$app->tform->getAuthSQL('r'), $app->functions->idn_encode($_POST["jid_domain"]));
		$app->db->query("UPDATE xmpp_user SET sys_groupid = ? WHERE xmppuser_id = ?", $domain["sys_groupid"], $this->id);

	}

	function onAfterUpdate() {
		global $app, $conf;

		// Set the domain owner as mailbox owner
		if(isset($_POST["xmpp_domain"])) {
			$domain = $app->db->queryOneRecord("SELECT sys_groupid, server_id FROM xmpp_domain WHERE domain = ? AND ".$app->tform->getAuthSQL('r'), $app->functions->idn_encode($_POST["jid_domain"]));
			$app->db->query("UPDATE xmpp_user SET sys_groupid = ? WHERE xmppuser_id = ?", $domain["sys_groupid"], $this->id);

		}
	}

}

$app->tform_actions = new page_action;
$app->tform_actions->onLoad();

?>
