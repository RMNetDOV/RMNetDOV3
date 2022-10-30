<?php

class validate_remote_user {

	function valid_remote_ip($field_name, $field_value, $validator) {
		global $app;

		if(trim($field_value) == '') return;

		$values = explode(',', $field_value);
		$regex = '/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/';
		foreach($values as $cur_value) {
			$cur_value = trim($cur_value);
			$valid = true;
			if(function_exists('filter_var')) {
				if(!filter_var($cur_value, FILTER_VALIDATE_IP)) {
					$valid = false;
					if(preg_match($regex, $cur_value)) $valid = true;
				}
			} else return "function filter_var missing <br />\r\n";

			if($valid == false) {
				$errmsg = $validator['errmsg'];
				if(isset($app->tform->wordbook[$errmsg])) {
					return $app->tform->wordbook[$errmsg]."<br>\r\n";
				} else {
					return $errmsg."<br>\r\n";
				}
			}
		}
	}

}
