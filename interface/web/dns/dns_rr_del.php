<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/dns_a.list.php";
$tform_def_file = "form/dns_a.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('dns');

$app->uses('tpl,tform,tform_actions,validate_dns');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onAfterDelete() {
		global $app; $conf;

		//* Update the serial number of the SOA record
		$soa = $app->db->queryOneRecord("SELECT * FROM dns_soa WHERE id = ? AND " . $app->tform->getAuthSQL('r'), $this->dataRecord["zone"]);
		$soa_id = $app->functions->intval($this->dataRecord["zone"]);
		$serial = $app->validate_dns->increase_serial($soa["serial"]);
		$app->db->datalogUpdate('dns_soa', array("serial" => $serial), 'id', $soa_id);
	}

}

$page = new page_action;
$page->onDelete();

?>
