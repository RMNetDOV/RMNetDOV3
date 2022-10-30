<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/spamfilter_users.list.php";
$tform_def_file = "form/spamfilter_users.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mail');

$app->uses('tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

        function onBeforeDelete() {
                global $app; $conf;

		$tmp_wblists = $app->db->queryAllRecords("SELECT wblist_id FROM spamfilter_wblist WHERE rid = ?", $this->id);
		if(is_array($tmp_wblists)) {
			foreach($tmp_wblists as $tmp) {
				$app->db->datalogDelete('spamfilter_wblist', 'wblist_id', $tmp['wblist_id']);
			}
		}
        }

}

$page = new page_action;
$page->onDelete();

