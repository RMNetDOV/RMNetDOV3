<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/dns_mx.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
require_once './dns_edit_base.php';

// Loading classes
class page_action extends dns_page_action {

	function onInsert() {
		global $app, $conf;

		// Check if record is existing already
		$duplicate_mx = $app->db->queryOneRecord("SELECT * FROM dns_rr WHERE zone = ? AND name = ? AND type = ? AND data = ? AND ".$app->tform->getAuthSQL('r'), $this->dataRecord["zone"], $this->dataRecord["name"], $this->dataRecord["type"], $this->dataRecord["data"]);
		

		if(is_array($duplicate_mx) && !empty($duplicate_mx)) $app->error($app->tform->wordbook["duplicate_mx_record_txt"]);

		parent::onInsert();
	}

	function onUpdate() {
		global $app, $conf;

		// Check if record is existing already
		$duplicate_mx = $app->db->queryOneRecord("SELECT * FROM dns_rr WHERE zone = ? AND name = ? AND type = ? AND data = ? AND id != ? AND ".$app->tform->getAuthSQL('r'), $this->dataRecord["zone"], $this->dataRecord["name"], $this->dataRecord["type"], $this->dataRecord["data"], $this->dataRecord["id"]);

		if(is_array($duplicate_mx) && !empty($duplicate_mx)) $app->error($app->tform->wordbook["duplicate_mx_record_txt"]);

		parent::onUpdate();
	}

}

$page = new page_action;
$page->onLoad();

?>
