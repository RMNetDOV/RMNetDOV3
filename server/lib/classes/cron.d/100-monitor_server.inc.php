<?php

class cronjob_monitor_server extends cronjob {

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

		/* used for all monitor cronjobs */
		$app->load('monitor_tools');
		$this->_tools = new monitor_tools();
		/* end global section for monitor cronjobs */

		/* the id of the server as int */
		$server_id = intval($conf['server_id']);

		/** The type of the data */


		$type = 'server_load';

		/*
			Fetch the data into a array
		 */
		$procUptime = shell_exec("cat /proc/uptime | cut -f1 -d' '");
		$data['up_days'] = floor($procUptime / 86400);
		$data['up_hours'] = floor(($procUptime - $data['up_days'] * 86400) / 3600);
		$data['up_minutes'] = floor(($procUptime - $data['up_days'] * 86400 - $data['up_hours'] * 3600) / 60);

		$data['uptime'] = shell_exec('uptime');

		$tmp = explode(',', $data['uptime'], 4);
		$tmpUser = explode(' ', trim($tmp[2]));
		$data['user_online'] = intval($tmpUser[0]);

		//* New Load Average code to fix "always zero" bug in non-english distros. NEEDS TESTING
		$loadTmp = shell_exec("cat /proc/loadavg | cut -f1-3 -d' '");
		$load = explode(' ', $loadTmp);
		$data['load_1'] = floatval(str_replace(',', '.', $load[0]));
		$data['load_5'] = floatval(str_replace(',', '.', $load[1]));
		$data['load_15'] = floatval(str_replace(',', '.', $load[2]));

		/** The state of the server-load. */
		$state = 'ok';
		if ($data['load_1'] > 20)
			$state = 'info';
		if ($data['load_1'] > 50)
			$state = 'warning';
		if ($data['load_1'] > 100)
			$state = 'critical';
		if ($data['load_1'] > 150)
			$state = 'error';

		$res = array();
		$res['server_id'] = $server_id;
		$res['type'] = $type;
		$res['data'] = $data;
		$res['state'] = $state;

		/*
		 * Insert the data into the database
		 */
		$sql = 'REPLACE INTO monitor_data (server_id, type, created, data, state) ' .
			'VALUES (?, ?, UNIX_TIMESTAMP(), ?, ?)';
		$app->dbmaster->query($sql, $res['server_id'], $res['type'], serialize($res['data']), $res['state']);

		/* The new data is written, now we can delete the old one */
		$this->_tools->delOldRecords($res['type'], $res['server_id']);

		parent::onRunJob();
	}

	/* this function is optional if it contains no custom code */
	public function onAfterRun() {
		global $app;

		parent::onAfterRun();
	}

}

?>
