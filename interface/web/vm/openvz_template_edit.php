<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/openvz_template.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('vm');
if($_SESSION["s"]["user"]["typ"] != 'admin') die('permission denied');

// Loading classes
$app->uses('tpl,tform');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onAfterInsert() {
		global $app, $conf;

		$this->onAfterUpdate();
	}

	function onAfterUpdate() {
		global $app, $conf;

		$guar_ram = $app->functions->intval($this->dataRecord['ram']*256);
		$burst_ram = $app->functions->intval($this->dataRecord['ram_burst']*256);
		$sql = "UPDATE openvz_template SET shmpages = ?,vmguarpages = ?, oomguarpages = ?,privvmpages = ? WHERE template_id = ?";
		$app->db->query($sql, $guar_ram . ':' . $guar_ram, $guar_ram . ':unlimited', $guar_ram . ':' . $guar_ram, $burst_ram . ':' . $burst_ram*1.0625, $this->id);
	}

}

$page = new page_action;
$page->onLoad();

?>
