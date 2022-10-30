<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/dns_txt.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
require_once './dns_edit_base.php';

// Loading classes
class page_action extends dns_page_action {
	function onLoad() {
		parent::onLoad();
		
		// The SPF wizard has a button to edit a record as TXT. We need this to prevent a redirect loop.
		if (!empty($_GET['edit_raw'])) {
			return;
		}

		// Redirect to SPF wizard if we detect a SPF record
		if ('GET' === $_SERVER['REQUEST_METHOD'] && !empty($this->dataRecord['data'])) {
			if ('v=spf1' === mb_substr($this->dataRecord['data'], 0, 6)) {
				header(sprintf('Location: dns_spf_edit.php?id=%d', $this->dataRecord['id']));
				exit;
			}
		}
	}
}

$page = new page_action;
$page->onLoad();

?>
