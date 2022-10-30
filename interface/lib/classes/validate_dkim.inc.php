<?php

class validate_dkim {

	function get_error($errmsg) {
		global $app;
		if(isset($app->tform->wordbook[$errmsg])) {
			return $app->tform->wordbook[$errmsg]."<br>\r\n";
		} else {
			return $errmsg."<br>\r\n";
		}
	}


	/**
	 * Validator function for private DKIM-Key
	 */
	function check_private_key($field_name, $field_value, $validator) {
		global $app;
		
		$dkim_enabled=$_POST['dkim'];
		if ($dkim_enabled == 'y') {
			if (empty($field_value)) return $this->get_error($validator['errmsg']);
			$app->system->exec_safe('echo ?|openssl rsa -check', $field_value);
			$result = $app->system->last_exec_retcode();
			if($result != 0) return $this->get_error($validator['errmsg']);
		}
	}

	/**
	 * Check function for DNS-Template
	 */
	function check_template($field_name, $field_value, $validator) {
		$dkim=false;
		if(is_array($field_value) && !empty($field_value)){
			foreach($field_value as $field ) { if($field == 'DKIM') $dkim=true; }
			if ($dkim && $field_value[0]!='DOMAIN') return $this->get_error($validator['errmsg']);
		}
	}


	/**
	 * Validator function for $_POST
	 *
	 * @return boolean - true if $POST contains a real key-file
	 */
	function validate_post($key, $value, $dkim_strength) {
		$value=str_replace(array("\n", "-----BEGIN RSA PRIVATE KEY-----", "-----END RSA PRIVATE KEY-----", " "), "", $value);
		switch ($key) {
		case 'public':
			if (preg_match("/(^-----BEGIN PUBLIC KEY-----)[a-zA-Z0-9\r\n\/\+=]{1,221}(-----END PUBLIC KEY-----(\n|\r)?$)/", $value) === 1) { return true; } else { return false; }
			break;
		case 'private':
			if ( $dkim_strength == 1024 ) $range = "{812,816}";
			if ( $dkim_strength == 2048 ) $range = "{1588,1592}";
			if ( $dkim_strength == 4096 ) $range = "{3132,3136}";
			if ( preg_match("/^[a-zA-Z0-9\/\+=]".$range."$/", $value ) === 1) return true; else return false;
			break;
		}
	}

}
