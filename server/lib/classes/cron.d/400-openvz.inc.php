<?php

class cronjob_openvz extends cronjob {

	// job schedule
	protected $_schedule = '0 0 * * *';

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

		//######################################################################################################
		// deactivate virtual servers (run only on the "master-server")
		//######################################################################################################

		if ($app->dbmaster == $app->db) {
			//* Check which virtual machines have to be deactivated
			$sql = "SELECT * FROM openvz_vm WHERE active = 'y' AND active_until_date IS NOT NULL AND active_until_date < CURDATE()";
			$records = $app->db->queryAllRecords($sql);
			if(is_array($records)) {
				foreach($records as $rec) {
					$app->dbmaster->datalogUpdate('openvz_vm', array("active" => 'n'), 'vm_id', $rec['vm_id']);
					$app->log('Virtual machine active date expired. Disabling VM '.$rec['veid'], LOGLEVEL_DEBUG);
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
