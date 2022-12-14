<?php

class cronjob_monitor_raid extends cronjob {

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


		$type = 'raid_state';

		/*
		 * We support several RAID types, but if we can't find any of them, we have no data
		 */
		$state = 'no_state';
		$data['output'] = '';

		/*
		 * Check, if Software-RAID is enabled
		 */
		if (file_exists('/proc/mdstat')) {
			/*
			 * Fetch the output
			 */
			$data['output'] = shell_exec('cat /proc/mdstat');

			/*
			 * Then calc the state.
			 */
			$tmp = explode("\n", $data['output']);
			$state = 'ok';
			for ($i = 0; $i < sizeof($tmp); $i++) {
				/* fetch the next line */
				$line = $tmp[$i];

				if ((strpos($line, 'U_]') !== false) || (strpos($line, '[_U') !== false) || (strpos($line, 'U_U') !== false)) {
					/* One Disk is not working.
					 * if the next line starts with "[>" or "[=" then
					 * recovery (resync) is in state and the state is
					 * information instead of critical
					 */
					$nextLine = $tmp[$i + 1];
					if ((strpos($nextLine, '[>') === false) && (strpos($nextLine, '[=') === false)) {
						$state = $this->_tools->_setState($state, 'critical');
					} else {
						$state = $this->_tools->_setState($state, 'info');
					}
				}
				if (strpos($line, '[__]') !== false) {
					/* both Disk are not working */
					$state = $this->_tools->_setState($state, 'error');
				}
				if (strpos($line, '[UU]') !== false) {
					/* The disks are OK.
					 * if the next line starts with "[>" or "[=" then
					 * recovery (resync) is in state and the state is
					 * information instead of ok
					 */
					$nextLine = $tmp[$i + 1];
					if ((strpos($nextLine, '[>') === false) && (strpos($nextLine, '[=') === false)) {
						$state = $this->_tools->_setState($state, 'ok');
					} else {
						$state = $this->_tools->_setState($state, 'info');
					}
				}
			}
		}
		/*
		 * Check, if we have mpt-status installed (LSIsoftware-raid)
		 */
		if (file_exists('/proc/mpt/summary')) {
			if ($app->system->is_installed('mpt-status')) {
				/*
				 * Fetch the output
				 */
				$data['output'] = shell_exec('mpt-status --autoload');

				/*
				 * Then calc the state.
				 */
				$state = 'ok';
				if(is_array($data['output'])) {
					foreach ($data['output'] as $item) {
						/*
						* The output contains information for every RAID and every HDD.
						* We only need the state of the RAID
						*/
						if (strpos($item, 'state ') !== false) {
							/*
							* We found a raid, process the state of it
							*/
							if (strpos($item, ' ONLINE ') !== false) {
								$this->_tools->_setState($state, 'ok');
							} elseif (strpos($item, ' OPTIMAL ') !== false) {
								$this->_tools->_setState($state, 'ok');
							} elseif (strpos($item, ' INITIAL ') !== false) {
								$this->_tools->_setState($state, 'info');
							} elseif (strpos($item, ' INACTIVE ') !== false) {
								$this->_tools->_setState($state, 'critical');
							} elseif (strpos($item, ' RESYNC ') !== false) {
								$this->_tools->_setState($state, 'info');
							} elseif (strpos($item, ' DEGRADED ') !== false) {
								$this->_tools->_setState($state, 'critical');
							} else {
								/* we don't know the state. so we set the state to critical, that the
								* admin is warned, that something is wrong
								*/
								$this->_tools->_setState($state, 'critical');
							}
						}
					}
				}
			}
		}

		/*
		* 3ware Controller
		*/
		if($app->system->is_installed('tw_cli')) {

			// TYPOWORX FIX | Determine Controler-ID
			$availableControlers = shell_exec('tw_cli info | grep -Eo "c[0-9]+"');
			$data['output'] = shell_exec('tw_cli info ' . $availableControlers);

			$state = 'ok';
			if(is_array($data['output'])) {
				foreach ($data['output'] as $item) {
					if (strpos($item, 'RAID') !== false) {
						if (strpos($item, ' VERIFYING ') !== false) {
							$this->_tools->_setState($state, 'info');
						}
						else if (strpos($item, ' MIGRATE-PAUSED ') !== false) {
								$this->_tools->_setState($state, 'info');
							}
						else if (strpos($item, ' MIGRATING ') !== false) {
								$this->_tools->_setState($state, 'ok');
							}
						else if (strpos($item, ' INITIALIZING ') !== false) {
								$this->_tools->_setState($state, 'info');
							}
						else if (strpos($item, ' INIT-PAUSED ') !== false) {
								$this->_tools->_setState($state, 'info');
							}
						else if (strpos($item, ' REBUILDING ') !== false) {
								$this->_tools->_setState($state, 'info');
							}
						else if (strpos($item, ' REBUILD-PAUSED ') !== false) {
								$this->_tools->_setState($state, 'warning');
							}
						else if (strpos($item, ' RECOVERY ') !== false) {
								$this->_tools->_setState($state, 'warning');
							}
						else if (strpos($item, ' DEGRADED ') !== false) {
								$this->_tools->_setState($state, 'critical');
							}
						else if (strpos($item, ' UNKNOWN ') !== false) {
								$this->_tools->_setState($state, 'critical');
							}
						else if (strpos($item, ' OK ') !== false) {
								$this->_tools->_setState($state, 'ok');
							}
						else if (strpos($item, ' OPTIMAL ') !== false) {
								$this->_tools->_setState($state, 'ok');
							}
						else {
							$this->_tools->_setState($state, 'critical');
						}
					}
				}
			}
		}

		/*
		* HP Proliant
		*/
		if($app->system->is_installed('hpacucli')) {
			$state = 'ok';
			$data['output'] = shell_exec('/usr/sbin/hpacucli ctrl all show config');
			$tmp = explode("\n", $data['output']);
			if(is_array($tmp)) {
				foreach ($tmp as $item) {
					if (strpos($item, 'logicaldrive') !== false) {
						if (strpos($item, 'OK') !== false) {
							$this->_tools->_setState($state = 'ok');
						} elseif (strpos($item, 'Recovery Mode') !== false) {
							$this->_tools->_setState($state = 'critical');
							break;
						} elseif (strpos($item, 'Failed') !== false) {
							$this->_tools->_setState($state = 'error');
							break;
						} elseif (strpos($item, 'Recovering') !== false) {
							$this->_tools->_setState($state = 'info');
							break;
						} else {
							$this->_tools->_setState($state = 'critical');
						}
					}
					if (strpos($item, 'physicaldrive') !== false) {
						if (strpos($item, 'physicaldrive') !== false) {
							if (strpos($item, 'OK') !== false) {
								$this->_tools->_setState($state = 'ok');
							} elseif (strpos($item, 'Failed') !== false) {
								$this->_tools->_setState($state = 'critical');
								break;
							} elseif (strpos($item, 'Rebuilding') !== false) {
								$this->_tools->_setState($state = 'info');
								break;
							} else {
								$this->_tools->_setState($state = 'critical');
								break;
							}
						}
					}
				}
			}
		}

		/*
		* LSI MegaRaid
		*/
		$binary = FALSE;
		if ($app->system->is_installed('megacli')) {
			$binary = 'megacli';
		}
		if ($app->system->is_installed('megacli64')) {
			$binary = 'megacli64';
		}
		if($binary) {
			$state = 'ok';
			$data['output'] = shell_exec($binary.' -LDInfo -Lall -aAll -NoLog');
			if (strpos($data['output'], 'Optimal') !== false) {
				$this->_tools->_setState($state, 'ok');
			} else if (strpos($data['output'], 'Degraded') !== false) {
				$this->_tools->_setState($state, 'critical');
			} else if (strpos($data['output'], 'Offline') !== false) {
				$this->_tools->_setState($state, 'critical');
			} else {
				$this->_tools->_setState($state, 'critical');
			}
		}

		/*
		* Adaptec-RAID
		*/
		if($app->system->is_installed('arcconf')) {
			$state = 'ok';
			$data['output'] = shell_exec('arcconf GETCONFIG 1 LD');
			if(is_array($data['output'])) {
				foreach ($data['output'] as $item) {
					if (strpos($item, 'Logical device name                      : RAID') !== false) {
						if (strpos($item, 'Optimal') !== false) {
							$this->_tools->_setState($state, 'ok');
						} else {
							$this->_tools->_setState($state, 'critical');
						}
					}
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
