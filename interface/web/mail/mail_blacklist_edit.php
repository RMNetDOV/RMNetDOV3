<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_blacklist.tform.php";

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

	protected $client_allowed_types = array( 'recipient', 'sender' );

	function onShowNew() {
		global $app;

		if($_SESSION["s"]["user"]["typ"] != 'admin') {
			if(!$app->tform->checkClientLimit('limit_mail_wblist')) {
				$app->error($app->tform->wordbook["limit_mail_wblist_txt"]);
			}
			if(!$app->tform->checkResellerLimit('limit_mail_wblist')) {
				$app->error('Reseller: '.$app->tform->wordbook["limit_mail_wblist_txt"]);
			}
		}

		parent::onShowNew();
	}

	function onBeforeUpdate() {
		global $app, $conf;

		//* Check if the server has been changed
		$rec = $app->db->queryOneRecord("SELECT server_id from mail_access WHERE access_id = ?", $this->id);
		if($rec['server_id'] != $this->dataRecord["server_id"]) {
			//* Add a error message and switch back to old server
			$app->tform->errorMessage .= $app->lng('The Server can not be changed.');
			$this->dataRecord["server_id"] = $rec['server_id'];
		}
		unset($rec);
	}

	function onSubmit() {
		global $app, $conf;

		// Non-admin checks
		if($_SESSION["s"]["user"]["typ"] != 'admin') {
			// Non-admin can only use type 'sender' or 'recipient'
			if(! in_array($this->dataRecord["type"], $this->client_allowed_types)) {
				$app->tform->errorMessage .= $app->lng('Blacklist type requires admin permissions');
			}

			// Address must be valid email
			if(! filter_var( $this->dataRecord["source"], FILTER_VALIDATE_EMAIL )) {
				$app->tform->errorMessage .= $app->lng('Invalid address: must be a valid email address');
			}

			// Address must belong to the client's domains
			$tmp = explode('@', $this->dataRecord["source"]);
			$domain = trim( array_pop($tmp) );
			$AUTHSQL = $app->tform->getAuthSQL('r');
			$rec = $app->db->queryOneRecord("SELECT domain_id from mail_domain WHERE ${AUTHSQL} AND domain = ?", $domain);
			if(! (is_array($rec) && isset($rec['domain_id']) && is_numeric($rec['domain_id']))) {
				$app->tform->errorMessage .= $app->lng('Invalid address: you have no permission for this domain.');
			}
			unset($rec);

			// Check the client limits
			$client_group_id = $app->functions->intval($_SESSION["s"]["user"]["default_group"]);
			$client = $app->db->queryOneRecord("SELECT limit_mail_wblist FROM sys_group, client WHERE sys_group.client_id = client.client_id and sys_group.groupid = ?", $client_group_id);
			if($this->id == 0 && $client["limit_mail_wblist"] >= 0) {
				$TYPES_LIST = "('" . join("', '", $this->client_allowed_types) . "')";
				$tmp = $app->db->queryOneRecord("SELECT count(access_id) as number FROM mail_access WHERE ${AUTHSQL} AND type in ${TYPES_LIST}");
				if($tmp["number"] >= $client["limit_mail_wblist"]) {
					$app->tform->errorMessage .= $app->tform->wordbook["limit_mail_wblist_txt"]."<br>";
				}
				unset($tmp);
			}
		}

		if(substr($this->dataRecord['source'], 0, 1) === '@') $this->dataRecord['source'] = substr($this->dataRecord['source'], 1);

		$rec = $app->db->queryOneRecord("SELECT access_id from mail_access WHERE server_id = ? AND source = ? and type = ?", $this->dataRecord["server_id"], $this->dataRecord["source"], $this->dataRecord["type"]);
		if(is_array($rec) && isset($rec['access_id'])) {
			$app->tform->errorMessage .= $app->tform->wordbook["mail_access_unique"]."<br>";
		}
		unset($rec);

		parent::onSubmit();
	}

}

$app->tform_actions = new page_action;
$app->tform_actions->onLoad();

?>
