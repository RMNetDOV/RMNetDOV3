<?php

class cronjob_monitor_fail2ban extends cronjob {

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


		$type = 'log_fail2ban';

		/* This monitoring is only available if fail2ban is installed */
		if ($app->system->is_installed('fail2ban-client')	// Debian, Ubuntu, Fedora
			|| $app->system->is_installed('fail2ban')) {	// CentOS
			/*  Get the data of the log */
			$data = $this->_tools->_getLogData($type);

			/*
			 * At this moment, there is no state (maybe later)
			 */
			$state = 'no_state';
		} else {
			/*
			 * fail2ban is not installed, so there is no data and no state
			 *
			 * no_state, NOT unknown, because "unknown" is shown as state
			 * inside the GUI. no_state is hidden.
			 *
			 * We have to write NO DATA inside the DB, because the GUI
			 * could not know, if there is any dat, or not...
			 */
			$state = 'no_state';
			$data = '';
		}

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
