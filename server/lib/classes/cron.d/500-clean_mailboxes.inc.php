<?php

class cronjob_clean_mailboxes extends cronjob {

	// should run before quota notify and backup
	// quota notify and backup is both '0 0 * * *' 
	
	// job schedule
	protected $_schedule = '00 22 * * *';

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

		$trash_names=array('Trash', 'Papierkorb', 'Deleted Items', 'Deleted Messages', 'INBOX.Trash', 'INBOX.Papierkorb', 'INBOX.Deleted Messages', 'Corbeille');
		$junk_names=array('Junk', 'Junk Email', 'SPAM', 'INBOX.SPAM');

		$expunge_cmd = 'doveadm expunge -u ? mailbox ? sentbefore ';
		$purge_cmd = 'doveadm purge -u ?';
		$recalc_cmd = 'doveadm quota recalc -u ?';

		$server_id = intval($conf['server_id']);
		$records = $app->db->queryAllRecords("SELECT email, maildir, purge_trash_days, purge_junk_days FROM mail_user WHERE maildir_format = 'maildir' AND disableimap = 'n' AND server_id = ? AND (purge_trash_days > 0 OR purge_junk_days > 0)", $server_id);
		
		if(is_array($records) && !empty($records)) {
			foreach($records as $email) {
				if($email['purge_trash_days'] > 0) {
					foreach($trash_names as $trash) {
						if(is_dir($email['maildir'].'/Maildir/.'.$trash)) {
							$app->system->exec_safe($expunge_cmd.intval($email['purge_trash_days']).'d', $email['email'], $trash);
						}
					}
				}
				if($email['purge_junk_days'] > 0) {
					foreach($junk_names as $junk) {
						if(is_dir($email['maildir'].'/Maildir/.'.$junk)) {
							$app->system->exec_safe($expunge_cmd.intval($email['purge_junk_days']).'d', $email['email'], $junk);
						}
					}
				}
				$app->system->exec_safe($purge_cmd, $email['email']);
				$app->system->exec_safe($recalc_cmd, $email['email']);
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
