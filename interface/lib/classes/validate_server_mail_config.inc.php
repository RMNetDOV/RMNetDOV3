<?php

class validate_server_mail_config {

	function get_error($errmsg) {
		global $app;

		if(isset($app->tform->wordbook[$errmsg])) {
			return $app->tform->wordbook[$errmsg]."<br>\r\n";
		} else {
			return $errmsg."<br>\r\n";
		}
	}

	/* Validator function to check for changing virtual_uidgid_maps */
	function mailbox_virtual_uidgid_maps($field_name, $field_value, $validator) {
		global $app, $conf;

		if (empty($field_value)) $field_value = 'n';
		$app->uses('getconf,system,db');
		$mail_config = $app->getconf->get_server_config($conf['server_id'], 'mail');
		
		// try to activat the function -> only if only one mailserver out there and if dovecot is installed
		if ($field_value == 'y') {
			// if this count is more then 1, there is more than 1 webserver, more than 1 mailserver or different web+mailserver -> so this feature isn't possible
			$num_rec = $app->db->queryOneRecord("SELECT count(*) as number FROM server WHERE mail_server=1 OR web_server=1");
			if($num_rec['number'] > 1) {
				return $this->get_error('mailbox_virtual_uidgid_maps_error_nosingleserver');
			}
		}
		
		// Value can only be changed if there is no mailuser set
		if ($mail_config["mailbox_virtual_uidgid_maps"] != $field_value) {
			$num_rec = $app->db->queryOneRecord("SELECT count(*) as number FROM mail_user");
			if($num_rec['number'] > 0) {
				return $this->get_error('mailbox_virtual_uidgid_maps_error_alreadyusers');
			}
		}
	}

}
