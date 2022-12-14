<?php

class cronjob_monitor_openvz extends cronjob {

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


		$type = 'openvz_veinfo';

		/*
			Fetch the data into a array
		 */
		$app->load('openvz_tools');
		$openVzTools = new openvz_tools();
		$data = $openVzTools->getOpenVzVeInfo();

		/* the VE-Info has no state. It is, what it is */
		$state = 'no_state';

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

		/** The type of the data */
		$type = 'openvz_beancounter';

		/*
			Fetch the data into a array
		 */
		$app->load('openvz_tools');
		$openVzTools = new openvz_tools();
		$data = $openVzTools->getOpenVzVeBeanCounter();

		/* calculate the state of the beancounter */
		if ($data == '') {
			$state = 'no_state';
		} else {
			$state = 'ok';

			/* transfer this output-string into a array */
			$test = explode("\n", $data);

			/* the first list of the output is not needed */
			array_shift($test);

			/* now process all items of the rest */
			foreach ($test as $item) {
				/*
				 * eliminate all doubled spaces and spaces at the beginning and end
				 */
				while (strpos($item, '  ') !== false) {
					$item = str_replace('  ', ' ', $item);
				}
				$item = trim($item);

				/*
				 * The failcounter is the LAST
				 */
				if ($item != '') {
					$tmp = explode(' ', $item);
					$failCounter = $tmp[sizeof($tmp) - 1];
					if ($failCounter > 0)
						$state = 'info';
					if ($failCounter > 50)
						$state = 'warning';
					if ($failCounter > 200)
						$state = 'critical';
					if ($failCounter > 10000)
						$state = 'error';
				}
			}
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
