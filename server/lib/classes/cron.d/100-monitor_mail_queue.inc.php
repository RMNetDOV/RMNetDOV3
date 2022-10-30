<?php

class cronjob_monitor_mail_queue extends cronjob {

	// job schedule
	protected $_schedule = '*/5 * * * *';
	protected $_run_at_new = true;

	private $_tools = null;

	private function _getIntArray($line) {
		/** The array of float found */


		$res = array();
		/* First build a array from the line */
		$data = explode(' ', $line);
		/* then check if any item is a float */
		foreach ($data as $item) {
			if ($item . '' == (int) $item . '') {
				$res[] = $item;
			}
		}
		return $res;
	}

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
		$type = 'mailq';

		/* Get the data from the mailq */
		$data['output'] = shell_exec('mailq');

		/*
		 *  The last line has more informations
		 */
		$tmp = explode("\n", $data['output']);
		$more = $tmp[sizeof($tmp) - 1];
		$res = $this->_getIntArray($more);
		$data['bytes'] = $res[0];
		$data['requests'] = $res[1];

		/** The state of the mailq. */
		$state = 'ok';
		if ($data['requests'] > 2000)
			$state = 'info';
		if ($data['requests'] > 5000)
			$state = 'warning';
		if ($data['requests'] > 8000)
			$state = 'critical';
		if ($data['requests'] > 10000)
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
