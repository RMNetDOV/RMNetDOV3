<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_content_filter.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mail');

if($_SESSION["s"]["user"]["typ"] != 'admin') die('These Filters are only for Admins.');


// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {
	function onBeforeUpdate() {
		global $app, $conf;

		//* Check if the server has been changed
		// We do this only for the admin or reseller users, as normal clients can not change the server ID anyway
		if($_SESSION["s"]["user"]["typ"] == 'admin' || $app->auth->has_clients($_SESSION['s']['user']['userid'])) {
			$rec = $app->db->queryOneRecord("SELECT server_id from mail_content_filter WHERE content_filter_id = ?", $this->id);
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
