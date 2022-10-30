<?php

class cronjob_letsencrypt extends cronjob {

	// job schedule
	protected $_schedule = '0 3 * * *';

	/* this function is optional if it contains no custom code */
	public function onPrepare() {
		parent::onPrepare();
	}

	/* this function is optional if it contains no custom code */
	public function onBeforeRun() {
		global $app;

		$app->modules->loadModules('web_module');
		return parent::onBeforeRun();
	}

	public function onRunJob() {
		global $app, $conf;
		
		$server_config = $app->getconf->get_server_config($conf['server_id'], 'server');
		if(!isset($server_config['migration_mode']) || $server_config['migration_mode'] != 'y') {			
			$acme = $app->letsencrypt->get_acme_script();
			if($acme) {
				// skip letsencrypt
				parent::onRunJob();
				return;
			}
			
			$letsencrypt = $app->letsencrypt->get_certbot_script();
			if($letsencrypt) {
				$ret = null;
				$val = 0;
				$matches = array();
				$version = exec($letsencrypt . ' --version  2>&1', $ret, $val);
				if(preg_match('/^(\S+|\w+)\s+(\d+(\.\d+)+)$/', $version, $matches)) {
					$type = strtolower($matches[1]);
					$version = $matches[2];
					if(($type != 'letsencrypt' && $type != 'certbot') || version_compare($version, '0.7.0', '<')) {
						exec($letsencrypt . ' -n renew');
						$app->services->restartServiceDelayed('httpd', 'force-reload');
					} else {
						$marker_file = '/usr/local/rmnetdov/server/le.restart';
						$cmd = "echo '1' > " . $marker_file;
						$app->system->exec_safe($letsencrypt . ' -n renew --post-hook ?', $cmd);
						if(file_exists($marker_file) && trim(file_get_contents($marker_file)) == '1') {
							unlink($marker_file);
							$app->services->restartServiceDelayed('httpd', 'force-reload');
						}
					}
				} else {
					exec($letsencrypt . ' -n renew');
					$app->services->restartServiceDelayed('httpd', 'force-reload');
				}
			}
		} else {
			$app->log('Migration mode active, not running Let\'s Encrypt renewal.', LOGLEVEL_DEBUG);
		}
		
		parent::onRunJob();
	}

	/* this function is optional if it contains no custom code */
	public function onAfterRun() {
		parent::onAfterRun();
	}

}