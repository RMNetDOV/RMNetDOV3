<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/client_template.list.php";
$tform_def_file = "form/client_template.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('client');
if($_SESSION["s"]["user"]["typ"] != 'admin' && !$app->auth->has_clients($_SESSION['s']['user']['userid'])) die('Client-Templates are for Admins and Resellers only.');

$app->uses('tpl,tform');
$app->load('tform_actions');

class page_action extends tform_actions {
	function onBeforeDelete() {
		global $app;

		// check new style
		$rec = $app->db->queryOneRecord("SELECT count(client_id) as number FROM client_template_assigned WHERE client_template_id = ?", $this->id);
		if($rec['number'] > 0) {
			$app->error($app->tform->lng('template_del_aborted_txt'));
		}

		// check old style
		$rec = $app->db->queryOneRecord("SELECT count(client_id) as number FROM client WHERE template_master = ? OR template_additional like ?", $this->id, '%/".$this->id."/%');
		if($rec['number'] > 0) {
			$app->error($app->tform->lng('template_del_aborted_txt'));
		}

	}

}

$page = new page_action;
$page->onDelete()

?>
