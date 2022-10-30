<?php

class validate_server {

	function get_error($errmsg) {
		global $app;
		if(isset($app->tform->wordbook[$errmsg])) {
			return $app->tform->wordbook[$errmsg]."<br>\r\n";
		} else {
			 return $errmsg."<br>\r\n";
		}
	}

	/**
	 * Validator function for server-ip
	*/
	function check_server_ip($field_name, $field_value, $validator) {
		global $app;

		$type=(isset($app->remoting_lib->dataRecord['ip_type']))?$app->remoting_lib->dataRecord['ip_type']:$_POST['ip_type'];
		
		if($type == 'IPv4') {
			if(!filter_var($field_value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
				return $this->get_error($validator['errmsg']);
			}
		} elseif ($type == 'IPv6') {
			if(!filter_var($field_value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
				return $this->get_error($validator['errmsg']);
			}
		} else return $this->get_error($validator['errmsg']);
	}

}

