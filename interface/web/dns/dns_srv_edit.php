<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/dns_srv.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
require_once './dns_edit_base.php';

// Loading classes
class page_action extends dns_page_action {

	function onShowEnd() {
		global $app, $conf;

		// Split the 3 parts of the SRV Record apart
		$split = explode(' ', $this->dataRecord['data']);

		$app->tpl->setVar('weight', $split[0], true);
		$app->tpl->setVar('port', $split[1], true);
		$app->tpl->setVar('target', $split[2], true);

		parent::onShowEnd();
	}

	function onBeforeInsert() {
		$this->dataRecord['data'] = $this->dataRecord['weight'] .' '. $this->dataRecord['port'] .' '. $this->dataRecord['target'];
	}

	function onBeforeUpdate() {
		$this->dataRecord['data'] = $this->dataRecord['weight'] .' '. $this->dataRecord['port'] .' '. $this->dataRecord['target'];
	}

}

$page = new page_action;
$page->onLoad();

?>
