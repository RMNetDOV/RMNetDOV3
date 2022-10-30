<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/mail_domain.list.php";
$tform_def_file = "form/mail_domain.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mail');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onBeforeDelete() {
		global $app; $conf;

		$domain = $this->dataRecord['domain'];

		// Before we delete the email domain,
		// we will delete all depending records.

		// Delete all forwardings where the source or destination belongs to this domain
		$records = $app->db->queryAllRecords("SELECT forwarding_id as id FROM mail_forwarding WHERE source like ? OR (destination like ? AND type != 'forward')", '%@' . $domain, '%@' . $domain);
		foreach($records as $rec) {
			$app->db->datalogDelete('mail_forwarding', 'forwarding_id', $rec['id']);
		}

		// Delete all fetchmail accounts where destination belongs to this domain
		$records = $app->db->queryAllRecords("SELECT mailget_id as id FROM mail_get WHERE destination like ?", '%@' . $domain);
		foreach($records as $rec) {
			$app->db->datalogDelete('mail_get', 'mailget_id', $rec['id']);
		}

		// Delete all mailboxes where destination belongs to this domain
		$records = $app->db->queryAllRecords("SELECT mailuser_id as id FROM mail_user WHERE email like ?", '%@' . $domain);
		foreach($records as $rec) {
			$app->db->datalogDelete('mail_user', 'mailuser_id', $rec['id']);
		}

		// Delete all spamfilters that belong to this domain
		$records = $app->db->queryAllRecords("SELECT id FROM spamfilter_users WHERE email like ?", '%@' . $domain);
		foreach($records as $rec) {
			$wblists = $app->db->queryAllRecords("SELECT wblist_id FROM spamfilter_wblist WHERE rid = ?", $rec['id']);
			foreach($wblists as $wblist) {
				$app->db->datalogDelete('spamfilter_wblist', 'wblist_id', $wblist['wblist_id']);
			}
			$app->db->datalogDelete('spamfilter_users', 'id', $rec['id']);
		}

		// Delete all mailinglists that belong to this domain
		$records = $app->db->queryAllRecords("SELECT mailinglist_id FROM mail_mailinglist WHERE domain = ?", $domain);
		foreach($records as $rec) {
			$app->db->datalogDelete('mail_mailinglist', 'mailinglist_id', $rec['id']);
		}

	}

}

$page = new page_action;
$page->onDelete();

?>
