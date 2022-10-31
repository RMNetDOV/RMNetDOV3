<?php

class cronjob_file_cleanup extends cronjob {

	// job schedule
	protected $_schedule = '* * * * *';
	protected $_run_at_new = true;

	public function onBeforeRun() {
		global $app;

		/* currently we only cleanup rspamd config files, so bail if not needed */
		if (! is_dir("/etc/rspamd/local.d/users/")) {
			return false;
		}

		return parent::onBeforeRun();
	}

	public function onRunJob() {
		global $app, $conf;

		$server_id = $conf['server_id'];

		/* rspamd config file cleanup */
		if (is_dir("/etc/rspamd/local.d/users/")) {
			$mail_access = array();
			$sql = "SELECT access_id as id FROM mail_access WHERE active = 'y' AND server_id = ?";
			$records = $app->db->queryAllRecords($sql, $server_id);
			if(is_array($records)) {
				foreach($records as $rec){
					$mail_access[$rec['id']] = $rec['id'];
				}
			}

			$spamfilter_wblist = array();
			$sql = "SELECT wblist_id as id FROM spamfilter_wblist WHERE active = 'y' AND server_id = ?";
			$records = $app->db->queryAllRecords($sql, $server_id);
			if(is_array($records)) {
				foreach($records as $rec){
					$spamfilter_wblist[$rec['id']] = $rec['id'];
				}
			}

			$spamfilter_users = array();
			$sql = "SELECT id FROM spamfilter_users WHERE policy_id != 0 AND server_id = ?";
			$records = $app->db->queryAllRecords($sql, $server_id);
			if(is_array($records)) {
				foreach($records as $rec){
					$spamfilter_users[$rec['id']] = $rec['id'];
				}
			}

			$mail_user = array();
			$sql = "SELECT mailuser_id as id FROM mail_user WHERE postfix = 'y' AND server_id = ?";
			$records = $app->db->queryAllRecords($sql, $server_id);
			if(is_array($records)) {
				foreach($records as $rec){
					$mail_user[$rec['id']] = $rec['id'];
				}
			}

			$mail_forwarding = array();
			$sql = "SELECT forwarding_id as id FROM mail_forwarding WHERE active = 'y' AND server_id = ?";
			$records = $app->db->queryAllRecords($sql, $server_id);
			if(is_array($records)) {
				foreach($records as $rec){
					$mail_forwarding[$rec['id']] = $rec['id'];
				}
			}

			foreach (glob('/etc/rspamd/local.d/users/*.conf') as $file) {
				if($handle = fopen($file, 'r')) {
					if(($line = fgets($handle)) !== false) {
						if(preg_match('/^((?:global|spamfilter)_wblist|rmnetdov_(spamfilter_user|mail_user|mail_forwarding))[_-](\d+)\s/', $line, $matches)) {
							switch($matches[1]) {
							case 'global_wblist':
								$remove = isset($mail_access[$matches[3]]) ? false : true;
								break;
							case 'spamfilter_wblist':
								$remove = isset($spamfilter_wblist[$matches[3]]) ? false : true;
								break;
							case 'rmnetdov_spamfilter_user':
								$remove = isset($spamfilter_users[$matches[3]]) ? false : true;
								break;
							case 'rmnetdov_mail_user':
								$remove = isset($mail_user[$matches[3]]) ? false : true;
								break;
							case 'rmnetdov_mail_forwarding':
								$remove = isset($mail_forwarding[$matches[3]]) ? false : true;
								break;
							default:
								$app->log("conf file has unhandled rule naming convention, ignoring: $file", LOGLEVEL_DEBUG);
								$remove = false;
							}
							if($remove) {
								$app->log("$matches[1] id $matches[3] not found, removing $file", LOGLEVEL_DEBUG);
								unlink($file);
								$this->restartServiceDelayed('rspamd', 'reload');
							}
						} else {
							$app->log("conf file has unknown rule naming convention, ignoring: $file", LOGLEVEL_DEBUG);
						}
					}

					fclose($handle);
				}
			}
		}

		parent::onRunJob();
	}

}

