<?php

class validate_openvz {

	function get_error($errmsg, $additional='') {
		global $app;
		if(isset($app->tform->wordbook[$errmsg])) {
			return $app->tform->wordbook[$errmsg].$additional."<br>\r\n";
		} else {
			return $errmsg."<br>".$additional."<br>\r\n";
		}
	}

	function check_custom($field_name, $field_value, $validator) {
		$template = file('../vm/templates/openvz.conf.tpl', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$custom_array = explode("\n", $field_value);
		$used_parameters = array();
		foreach ($template as $line) {
			$line = trim ($line);
			if (preg_match('/^[^#].*=\".*\"/', $line)) {
				$line = explode('=', $line, 2);
				$used_parameters[] = $line[0];
			}
		}
		foreach ($custom_array as $check) {
			$check = trim(strtoupper($check));
			$check = explode('=', trim($check), 2);
			$check = trim($check[0]);
			if (in_array($check, $used_parameters)) {
				return $this->get_error($validator['errmsg'], $check);
			}
		}
	}

}
