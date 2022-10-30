<?php

class cronjob_cleanup extends cronjob {

	// job schedule
	protected $_schedule = '*/5 * * * *';
	protected $_run_at_new = true;

	private $_tools = null;

	/* this function is optional if it contains no custom code */
	public function onPrepare() {
		global $app;

		parent::onPrepare();
	}

	/* this function is optional if it contains no custom code */
	public function onBeforeRun() {
		global $app;

		return parent::onBeforeRun();
	}

	public function onRunJob() {
		global $app, $conf;

		// run this only on the master
		if($conf['server_id'] == 1) {
			$records = $app->db->queryAllRecords("SELECT s.instance_id, s.name, s.value FROM `aps_instances_settings` as s INNER JOIN `aps_instances` as i ON (i.id = s.instance_id) WHERE s.value != '' AND s.name IN ('main_database_password', 'admin_password') AND i.instance_status > 1");
			if(is_array($records)) {
				foreach($records as $rec) {
					$tmp = $app->db->queryOneRecord("SELECT id FROM aps_instances_settings WHERE instance_id = ? AND name = ?", $rec['instance_id'], $rec['name']);
					$app->db->datalogUpdate('aps_instances_settings', array("value" => ''), 'id', $tmp['id']);
				}
			}
		}

		parent::onRunJob();
	}

	/* this function is optional if it contains no custom code */
	public function onAfterRun() {
		global $app;

		parent::onAfterRun();
	}

}

?>
