<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/mail_user.list.php";
$tform_def_file = "form/mail_user.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mail');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onBeforeDelete() {
		global $app; $conf;

		$tmp_user = $app->db->queryOneRecord("SELECT id FROM spamfilter_users WHERE email = ?", $this->dataRecord["email"]);
		if (is_array($tmp_user) && isset($tmp_user['id'])) {
			$tmp_wblists = $app->db->queryAllRecords("SELECT wblist_id FROM spamfilter_wblist WHERE rid = ?", $tmp_user['id']);
			if(is_array($tmp_wblists)) {
				foreach($tmp_wblists as $tmp) {
					$app->db->datalogDelete('spamfilter_wblist', 'wblist_id', $tmp['wblist_id']);
				}
			}
		}
		$app->db->datalogDelete('spamfilter_users', 'id', $tmp_user["id"]);

		// delete mail_forwardings with destination == email ?

		$tmp_filters = $app->db->queryAllRecords("SELECT filter_id FROM mail_user_filter WHERE mailuser_id = ?", $this->id);
		if(is_array($tmp_filters)) {
			foreach($tmp_filters as $tmp) {
				$app->db->datalogDelete('mail_user_filter', 'filter_id', $tmp["filter_id"]);
			}
		}

	}

}

$page = new page_action;
$page->onDelete();

