<?php

class validate_mail_transport {

	function get_error($errmsg) {
		global $app;

		if(isset($app->tform->wordbook[$errmsg])) {
			return $app->tform->wordbook[$errmsg]."<br>\r\n";
		} else {
			return $errmsg."<br>\r\n";
		}
	}

	/* Validator function for checking the 'domain' of a mail transport */
	function validate_domain($field_name, $field_value, $validator) {
		global $app, $conf;

		if(isset($app->remoting_lib->primary_id)) {
			$id = $app->remoting_lib->primary_id;
		} else {
			$id = $app->tform->primary_id;
		}

		// mail_transport.domain (could also be an email address) must be unique per server
		$sql = "SELECT transport_id, domain FROM mail_transport WHERE domain = ? AND server_id = ? AND transport_id != ?";
		$domain_check = $app->db->queryOneRecord($sql, $field_value, $app->tform_actions->dataRecord['server_id'], $id);

		if($domain_check) return $this->get_error('domain_error_unique');
	}

}
