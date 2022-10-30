<?php

class cronjob_monitor_database_size extends cronjob {

	// job schedule
	protected $_schedule = '*/5 * * * *';
	protected $_run_at_new = true;

	private $_tools = null;



	/**
	 * this function is optional if it contains no custom code
	 */
	public function onPrepare() {
		global $app;

		parent::onPrepare();
	}



	/**
	 * this function is optional if it contains no custom code
	 */
	public function onBeforeRun() {
		global $app;

		return parent::onBeforeRun();
	}

	public function onRunJob() {
		global $app, $conf;

		/* used for all monitor cronjobs */
		$app->load('monitor_tools');
		$this->_tools = new monitor_tools();
		/* end global section for monitor cronjobs */

		/* the id of the server as int */
		$server_id = intval($conf['server_id']);

		/** The type of the data */
		$type = 'database_size';

		/** The state of the database-usage */
		$state = 'ok';

		/** Fetch the data of all databases into an array */
		$databases = $app->db->queryAllRecords("SELECT database_id, database_name, sys_groupid, database_quota, quota_exceeded FROM web_database WHERE server_id = ? ORDER BY sys_groupid, database_name ASC", $server_id);

		if(is_array($databases) && !empty($databases)) {

			$data = array();

			for ($i = 0; $i < sizeof($databases); $i++) {
				$rec = $databases[$i];
				
				$data[$i]['database_name']= $rec['database_name'];
				$data[$i]['size'] = $app->db->getDatabaseSize($rec['database_name']);
				$data[$i]['sys_groupid'] = $rec['sys_groupid'];

				$quota = $rec['database_quota'] * 1024 * 1024;
				if(!is_numeric($quota)) continue;
				
				if($quota < 1 || $quota > $data[$i]['size']) {
					//print 'database ' . $rec['database_name'] . ' size does not exceed quota: ' . ($quota < 1 ? 'unlimited' : $quota) . ' (quota) > ' . $data[$i]['size'] . " (used)\n";
					if($rec['quota_exceeded'] == 'y') {
						$app->dbmaster->datalogUpdate('web_database', array('quota_exceeded' => 'n'), 'database_id', $rec['database_id']);
					}
				} elseif($rec['quota_exceeded'] == 'n') {
					//print 'database ' . $rec['database_name'] . ' size exceeds quota: ' . $quota . ' (quota) < ' . $data[$i]['size'] . " (used)\n";
					$app->dbmaster->datalogUpdate('web_database', array('quota_exceeded' => 'y'), 'database_id', $rec['database_id']);
				}
			}

			$res = array();
			$res['server_id'] = $server_id;
			$res['type'] = $type;
			$res['data'] = $data;
			$res['state'] = $state;

			//* Insert the data into the database
			$sql = 'REPLACE INTO monitor_data (server_id, type, created, data, state) ' .
				'VALUES (?, ?, UNIX_TIMESTAMP(), ?, ?)';
			$app->dbmaster->query($sql, $res['server_id'], $res['type'], serialize($res['data']), $res['state']);

			//* The new data is written, now we can delete the old one
			$this->_tools->delOldRecords($res['type'], $res['server_id']);
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
