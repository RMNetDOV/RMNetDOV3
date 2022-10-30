<?php

class cronjob_purge_mailboxes extends cronjob {

	// should run before quota notify and backup
	// quota notify and backup is both '0 0 * * *' 
	
	// job schedule
	protected $_schedule = '30 23 * * *';

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

		$sql = "SELECT email FROM mail_user WHERE maildir_format = 'mdbox' AND server_id = ?";
		$records = $app->db->queryAllRecords($sql, $server_id);
		
		if(is_array($records)) {
			foreach($records as $rec){
				$app->system->exec_safe("su -c ?", 'doveadm purge -u "' . $rec["email"] . '"');
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
