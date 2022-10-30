<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/dns_slave.list.php";
$tform_def_file = "form/dns_slave.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('dns');

$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onBeforeDelete() {
		global $app; $conf;

		if($app->tform->checkPerm($this->id, 'd') == false) $app->error($app->lng('error_no_delete_permission'));

		// Delete all records that belog to this zone.
		$records = $app->db->queryAllRecords("SELECT id FROM dns_slave WHERE zone = ?", $this->id);
		foreach($records as $rec) {
			$app->db->datalogDelete('dns_slave', 'id', $rec['id']);
		}
	}

}

$page = new page_action;
$page->onDelete();

?>
