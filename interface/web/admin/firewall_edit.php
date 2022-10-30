<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/firewall.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('admin');
$app->auth->check_security_permissions('admin_allow_firewall_config');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {
	
	function onShowEnd() {
		global $app;

		if($this->id ==0) { //* new record
			$server_list = $app->db->queryAllRecords("SELECT server_id, server_name FROM server WHERE server_id NOT IN (SELECT server_id FROM firewall) ORDER BY server_name");
			if(is_array($server_list)) {
				foreach( $server_list as $server) $server_select .= "<option value='$server[server_id]' >" . $app->functions->htmlentities($server['server_name']) . "</option>\r\n";
			}
			$app->tpl->setVar('server_id', $server_select);
		}
		parent::onShowEnd();
	}


	function onBeforeUpdate() {
		global $app, $conf;

		//* Check if the server has been changed
		// We do this only for the admin or reseller users, as normal clients can not change the server ID anyway
		if($_SESSION["s"]["user"]["typ"] == 'admin' || $app->auth->has_clients($_SESSION['s']['user']['userid'])) {
			$rec = $app->db->queryOneRecord("SELECT server_id from firewall WHERE firewall_id = ?", $this->id);
			if($rec['server_id'] != $this->dataRecord["server_id"]) {
				//* Add a error message and switch back to old server
				$app->tform->errorMessage .= $app->lng('The Server can not be changed.');
				$this->dataRecord["server_id"] = $rec['server_id'];
			}
			unset($rec);
		}
	}

}

$page = new page_action;
$page->onLoad();

?>
