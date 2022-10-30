<?php

class validate_database {

	/*
		Validator function to check if a given list of ips is ok.
	*/
	function valid_ip_list($field_name, $field_value, $validator) {
		global $app;

		if($_POST["remote_access"] == "y") {
			if(trim($field_value) == "") return;

			$values = explode(",", $field_value);
			foreach($values as $cur_value) {
				$cur_value = trim($cur_value);
				$valid = true;
				if(function_exists('filter_var')) {
					if(!filter_var($cur_value, FILTER_VALIDATE_IP)) {
						$valid = false;
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




}
