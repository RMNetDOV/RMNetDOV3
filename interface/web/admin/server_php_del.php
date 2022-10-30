<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/server_php.list.php";
$tform_def_file = "form/server_php.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('admin');
$app->auth->check_security_permissions('admin_allow_server_php');

$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onBeforeDelete() {
		global $app;

 		$sql = 'SELECT domain_id FROM web_domain WHERE server_id = ? AND server_php_id = ?';
 		$web_domains = $app->db->queryAllRecords($sql, $this->dataRecord['server_id'], $this->id);

		 if(!empty($web_domains)) {
			$app->error($app->tform->lng('php_in_use_error'));
		}
	}

}

$page = new page_action;
$page->onDelete();

