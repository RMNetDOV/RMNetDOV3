<?php

class validate_server_directive_snippets {

	function get_error($errmsg) {
		global $app;

		if(isset($app->tform->wordbook[$errmsg])) {
			return $app->tform->wordbook[$errmsg]."<br>\r\n";
		} else {
			return $errmsg."<br>\r\n";
		}
	}

	function validate_snippet($field_name, $field_value, $validator) {
		global $app;
        $id = (isset($app->remoting_lib->dataRecord['directive_snippets_id']))?$app->remoting_lib->dataRecord['directive_snippets_id']:$_POST['id'];
		$type=(isset($app->remoting_lib->dataRecord['type']))?$app->remoting_lib->dataRecord['type']:$_POST['type'];
        $types = array('apache','nginx','php','proxy');
        if(!in_array($type,$types)) return $this->get_error('directive_snippets_invalid_type');
		$check = $app->db->queryAllRecords('SELECT * FROM directive_snippets WHERE name = ? AND type = ? AND directive_snippets_id != ?', $field_value, $type, $id);
		if(!empty($check)) return $this->get_error('directive_snippets_name_error_unique');
	}

}
