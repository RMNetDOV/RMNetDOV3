<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/shell_user.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('sites');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onShowNew() {
		global $app, $conf;

		// we will check only users, not admins
		if($_SESSION["s"]["user"]["typ"] == 'user') {
			if(!$app->tform->checkClientLimit('limit_shell_user')) {
				$app->error($app->tform->wordbook["limit_shell_user_txt"]);
			}
			if(!$app->tform->checkResellerLimit('limit_shell_user')) {
				$app->error('Reseller: '.$app->tform->wordbook["limit_shell_user_txt"]);
			}
		}

		parent::onShowNew();
	}

	function onShowEnd() {
		global $app, $conf, $interfaceConf;
		/*
		 * If the names are restricted -> remove the restriction, so that the
		 * data can be edited
		 */

		$app->uses('getconf,tools_sites');
		$global_config = $app->getconf->get_global_config('sites');
		$system_config = $app->getconf->get_global_config();
		$shelluser_prefix = $app->tools_sites->replacePrefix($global_config['shelluser_prefix'], $this->dataRecord);

		if ($this->dataRecord['username'] != ""){
			/* REMOVE the restriction */
			$app->tpl->setVar("username", $app->tools_sites->removePrefix($this->dataRecord['username'], $this->dataRecord['username_prefix'], $shelluser_prefix), true);
		}

		if($this->dataRecord['username'] == "") {
			$app->tpl->setVar("username_prefix", $shelluser_prefix, true);
		} else {
			$app->tpl->setVar("username_prefix", $app->tools_sites->getPrefix($this->dataRecord['username_prefix'], $shelluser_prefix, $global_config['shelluser_prefix']), true);
		}

		if($this->id > 0) {
			//* we are editing a existing record
			$app->tpl->setVar("edit_disabled", 1);
			$app->tpl->setVar("parent_domain_id_value", $this->dataRecord["parent_domain_id"], true);
		} else {
			$app->tpl->setVar("edit_disabled", 0);
		}

		$app->tpl->setVar('ssh_authentication', $system_config['sites']['ssh_authentication']);

		parent::onShowEnd();
	}

	function onSubmit() {
		global $app, $conf;

		// Get the record of the parent domain
		if(isset($this->dataRecord["parent_domain_id"])) {
			$parent_domain = $app->db->queryOneRecord("select * FROM web_domain WHERE domain_id = ? AND ".$app->tform->getAuthSQL('r'), @$this->dataRecord["parent_domain_id"]);
			if(!$parent_domain || $parent_domain['domain_id'] != @$this->dataRecord['parent_domain_id']) $app->tform->errorMessage .= $app->tform->lng("no_domain_perm");
		} else {
			$tmp = $app->tform->getDataRecord($this->id);
			$parent_domain = $app->db->queryOneRecord("select * FROM web_domain WHERE domain_id = ? AND ".$app->tform->getAuthSQL('r'), $tmp["parent_domain_id"]);
			if(!$parent_domain) $app->tform->errorMessage .= $app->tform->lng("no_domain_perm");
			unset($tmp);
		}

		// Set a few fixed values
		$this->dataRecord["server_id"] = $parent_domain["server_id"];

		if(isset($this->dataRecord['username']) && trim($this->dataRecord['username']) == '') $app->tform->errorMessage .= $app->tform->lng('username_error_empty').'<br />';
		if(isset($this->dataRecord['username']) && empty($this->dataRecord['parent_domain_id'])) $app->tform->errorMessage .= $app->tform->lng('parent_domain_id_error_empty').'<br />';
		if(isset($this->dataRecord['dir']) && stristr($this->dataRecord['dir'], '..')) $app->tform->errorMessage .= $app->tform->lng('dir_dot_error').'<br />';
		if(isset($this->dataRecord['dir']) && stristr($this->dataRecord['dir'], './')) $app->tform->errorMessage .= $app->tform->lng('dir_slashdot_error').'<br />';

		if(isset($this->dataRecord['ssh_rsa'])) $this->dataRecord['ssh_rsa'] = trim($this->dataRecord['ssh_rsa']);

		$system_config = $app->getconf->get_global_config();

		if($system_config['misc']['ssh_authentication'] == 'password') {
			$this->dataRecord['ssh_rsa'] = null;
		}

		if($system_config['misc']['ssh_authentication'] == 'key') {
			$this->dataRecord['password'] = null;
			$this->dataRecord['repeat_password'] = null;
		}

		parent::onSubmit();
	}

	function onBeforeInsert() {
		global $app, $conf, $interfaceConf;

		// check if the username is not blacklisted
		$blacklist = file(RMNETDOV_LIB_PATH.'/shelluser_blacklist');
		foreach($blacklist as $line) {
			if(strtolower(trim($line)) == strtolower(trim($this->dataRecord['username']))){
				$app->tform->errorMessage .= $app->tform->lng('username_not_allowed_txt');
			}
		}
		unset($blacklist);

		if($app->functions->is_allowed_user(trim(strtolower($this->dataRecord['username']))) == false) $app->tform->errorMessage .= $app->tform->lng('username_not_allowed_txt');

		/*
		 * If the names should be restricted -> do it!
		 */
		if ($app->tform->errorMessage == ''){

			$app->uses('getconf,tools_sites');
			$global_config = $app->getconf->get_global_config('sites');
			$shelluser_prefix = $app->tools_sites->replacePrefix($global_config['shelluser_prefix'], $this->dataRecord);

			$this->dataRecord['username_prefix'] = $shelluser_prefix;
			/* restrict the names */
			$this->dataRecord['username'] = $shelluser_prefix . $this->dataRecord['username'];

			if(strlen($this->dataRecord['username']) > 32) $app->tform->errorMessage .= $app->tform->lng("username_must_not_exceed_32_chars_txt");
		}
		parent::onBeforeInsert();
	}

	function onAfterInsert() {
		global $app, $conf;

		$web = $app->db->queryOneRecord("SELECT * FROM web_domain WHERE domain_id = ?", $this->dataRecord["parent_domain_id"]);

		$server_id = $app->functions->intval($web["server_id"]);
		$dir = $web["document_root"];
		$uid = $web["system_user"];
		$gid = $web["system_group"];

		// Check system user and group
		if($app->functions->is_allowed_user($uid) == false || $app->functions->is_allowed_group($gid) == false) {
			$app->error($app->tform->lng('invalid_system_user_or_group_txt'));
		}

		// The FTP user shall be owned by the same group then the website
		$sys_groupid = $app->functions->intval($web['sys_groupid']);

		$sql = "UPDATE shell_user SET server_id = ?, dir = ?, puser = ?, pgroup = ?, sys_groupid = ? WHERE shell_user_id = ?";
		$app->db->query($sql, $server_id, $dir, $uid, $gid, $sys_groupid, $this->id);

	}

	function onBeforeUpdate() {
		global $app, $conf, $interfaceConf;

		// check if the username is not blacklisted
		$blacklist = file(RMNETDOV_LIB_PATH.'/shelluser_blacklist');
		foreach($blacklist as $line) {
			if(strtolower(trim($line)) == strtolower(trim($this->dataRecord['username']))){
				$app->tform->errorMessage .= $app->tform->lng('username_not_allowed_txt');
			}
		}
		unset($blacklist);

		/*
		 * If the names should be restricted -> do it!
		 */
		if ($app->tform->errorMessage == '') {
			/*
			* If the names should be restricted -> do it!
			*/
			$app->uses('getconf,tools_sites');
			$global_config = $app->getconf->get_global_config('sites');
			$shelluser_prefix = $app->tools_sites->replacePrefix($global_config['shelluser_prefix'], $this->dataRecord);

			$old_record = $app->tform->getDataRecord($this->id);
			$shelluser_prefix = $app->tools_sites->getPrefix($old_record['username_prefix'], $shelluser_prefix);
			$this->dataRecord['username_prefix'] = $shelluser_prefix;

			/* restrict the names */
			$this->dataRecord['username'] = $shelluser_prefix . $this->dataRecord['username'];

			if(strlen($this->dataRecord['username']) > 32) $app->tform->errorMessage .= $app->tform->lng("username_must_not_exceed_32_chars_txt");
		}
	}

	function onAfterUpdate() {
		global $app, $conf;


	}

}

$page = new page_action;
$page->onLoad();

?>
