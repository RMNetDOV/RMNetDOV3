<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/dns_alias.tform.php";

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
		$tmp = $app->db->queryOneRecord("SELECT count(id) as number FROM dns_rr WHERE (type = 'A' AND name = ? AND zone = ? and id != ?) OR (type = 'AAAA' AND name = ? AND zone = ? and id != ?) OR (type = 'CNAME' AND name = ? AND zone = ? and id != ?) OR (type = 'DNAME' AND name = ? AND zone = ? and id != ?) OR (type = 'ALIAS' AND name = ? AND zone = ? and id != ?)", $this->dataRecord["name"], $this->dataRecord["zone"], $this->id, $this->dataRecord["name"], $this->dataRecord["zone"], $this->id, $this->dataRecord["name"], $this->dataRecord["zone"], $this->id, $this->dataRecord["name"], $this->dataRecord["zone"], $this->id, $this->dataRecord["name"], $this->dataRecord["zone"], $this->id);
		if($tmp['number'] > 0) return true;
		return false;
	}

  function onSubmit() {
		global $app, $conf;
		// Get the parent soa record of the domain
		$soa = $app->db->queryOneRecord("SELECT * FROM dns_soa WHERE id = ? AND " . $app->tform->getAuthSQL('r'), $_POST["zone"]);
		// Replace @ to example.com. in data field
		if($this->dataRecord["data"] === '@') {
			$this->dataRecord["data"] = $soa['origin'];
		}
		parent::onSubmit();
	}
}

$page = new page_action;
$page->onLoad();

?>
