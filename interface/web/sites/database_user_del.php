<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/database_user.list.php";
$tform_def_file = "form/database_user.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->uses("tform_actions");

class page_action extends tform_actions {
	function onBeforeDelete() {
		global $app; $conf;
		if($app->tform->checkPerm($this->id, 'd') == false) $app->error($app->lng('error_no_delete_permission'));

		$old_record = $app->tform->getDataRecord($this->id);

		/* we cannot use datalogDelete here, as we need to set server_id to 0 */
		$app->db->query("DELETE FROM `web_database_user` WHERE `database_user_id` = ?", $this->id);
		$new_rec = array();
		$old_record['server_id'] = 0;
		$app->db->datalogSave('web_database_user', 'DELETE', 'database_user_id', $this->id, $old_record, $new_rec);
	}

	function onAfterDelete() { // this has to be done on AFTER delete, because we need the db user still in the database when the server plugin processes the datalog
		global $app; $conf;

		//* Update all records that belog to this user
		$records = $app->db->queryAllRecords("SELECT database_id FROM web_database WHERE database_user_id = ?", $this->id);
		foreach($records as $rec) {
			$app->db->datalogUpdate('web_database', array('database_user_id' => null), 'database_id', $rec['database_id']);

		}
		$records = $app->db->queryAllRecords("SELECT database_id FROM web_database WHERE database_ro_user_id = ?", $this->id);
		foreach($records as $rec) {
			$app->db->datalogUpdate('web_database', array('database_ro_user_id' => null), 'database_id', $rec['database_id']);
		}
	}

}

$page = new page_action;
$page->onDelete();

?>
