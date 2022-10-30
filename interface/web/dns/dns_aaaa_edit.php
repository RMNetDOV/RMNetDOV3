<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/dns_aaaa.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
require_once './dns_edit_base.php';

// Loading classes
class page_action extends dns_page_action {

	protected function checkDuplicate() {
		global $app;
		//* Check for duplicates where IP and hostname are the same
		$tmp = $app->db->queryOneRecord("SELECT count(id) as number FROM dns_rr WHERE (type = 'AAAA' AND name = ? AND zone = ? and data = ? and id != ?) OR (type = 'CNAME' AND name = ? AND zone = ? and id != ?) OR (type = 'ALIAS' AND name = ? AND zone = ? and id != ?)", $this->dataRecord["name"], $this->dataRecord["zone"], $this->dataRecord["data"], $this->id, $this->dataRecord["name"], $this->dataRecord["zone"], $this->id, $this->dataRecord["name"], $this->dataRecord["zone"], $this->id);
		if($tmp['number'] > 0) return true;
		return false;
	}

}

$page = new page_action;
$page->onLoad();

?>
