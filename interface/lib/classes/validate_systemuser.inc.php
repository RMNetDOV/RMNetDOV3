<?php

class validate_systemuser {
	
	function get_error($errmsg) {
		global $app;

		if(isset($app->tform->wordbook[$errmsg])) {
			return $app->tform->wordbook[$errmsg]."<br>\r\n";
		} else {
			return $errmsg."<br>\r\n";
		}
	}

	/*
		Validator function to check if a given user is ok.
	*/
	function check_sysuser($field_name, $field_value, $validator) {
		global $app;
		
		//* Skip Test if we have the placeholder input of the remote APi for the web_domain system_user field here.
		if($field_name == 'system_user' && $field_value == '-') return '';
		
		//* Check the input
		$errmsg = $validator['errmsg'];
		$check_names = (isset($validator['check_names']) && $validator['check_names'] == true)?true:false;

		if($app->functions->is_allowed_user(trim(strtolower($field_value)),$check_names) == false) {
			return $this->get_error($errmsg);
		}
	}
	
	/*
		Validator function to check if a given group is ok.
	*/
	function check_sysgroup($field_name, $field_value, $validator) {
		global $app;
		
		//* Skip Test if we have the placeholder input of the remote APi for the web_domain system_group field here.
		if($field_name == 'system_group' && $field_value == '-') return '';
		
		$errmsg = $validator['errmsg'];
		$check_names = (isset($validator['check_names']) && $validator['check_names'] == true)?true:false;

		if($app->functions->is_allowed_group(trim(strtolower($field_value)),$check_names) == false) {
			return $this->get_error($errmsg);
		}
	}

	/*
		Validator function to check if a given dir is ok.
	*/
	function shelluser_dir($field_name, $field_value, $validator) {
		global $app;
		
		$primary_id = (isset($app->tform->primary_id) && $app->tform->primary_id > 0)?$app->tform->primary_id:$app->remoting_lib->primary_id;
		$primary_id = $app->functions->intval($primary_id);
		
		if($primary_id == 0 && !isset($app->remoting_lib->dataRecord['parent_domain_id'])) {
			$errmsg = $validator['errmsg'];
			if(isset($app->tform->wordbook[$errmsg])) {
				return $app->tform->wordbook[$errmsg]."<br>\r\n";
			} else {
				return $errmsg."<br>\r\n";
			}
		}

		if($primary_id > 0) {
			//* get parent_domain_id from website
			$shell_data = $app->db->queryOneRecord("SELECT parent_domain_id FROM shell_user WHERE shell_user_id = ?", $primary_id);
			if(!is_array($shell_data) || $shell_data["parent_domain_id"] < 1) {
				$errmsg = $validator['errmsg'];
				if(isset($app->tform->wordbook[$errmsg])) {
					return $app->tform->wordbook[$errmsg]."<br>\r\n";
				} else {
					return $errmsg."<br>\r\n";
				}
			} else {
				$parent_domain_id = $shell_data["parent_domain_id"];
			}
		} else {
			//* get parent_domain_id from dataRecord when we have a insert operation trough remote API
			$parent_domain_id = $app->functions->intval($app->remoting_lib->dataRecord['parent_domain_id']);
		}

		$domain_data = $app->db->queryOneRecord("SELECT domain_id, document_root FROM web_domain WHERE domain_id = ?", $parent_domain_id);
		if(!is_array($domain_data) || $domain_data["domain_id"] < 1) {
			$errmsg = $validator['errmsg'];
			if(isset($app->tform->wordbook[$errmsg])) {
				return $app->tform->wordbook[$errmsg]."<br>\r\n";
			} else {
				return $errmsg."<br>\r\n";
			}
		}

		$doc_root = $domain_data["document_root"];
		$is_ok = false;
		if($doc_root == $field_value) $is_ok = true;

		$doc_root .= "/";
		if(substr($field_value, 0, strlen($doc_root)) == $doc_root) $is_ok = true;

		if(stristr($field_value, '..') or stristr($field_value, './') or stristr($field_value, '/.')) $is_ok = false;

		//* Final check if docroot path of website is >= 5 chars
		if(strlen($doc_root) < 5) $is_ok = false;

		if($is_ok == false) {
			$errmsg = $validator['errmsg'];
			if(isset($app->tform->wordbook[$errmsg])) {
				return $app->tform->wordbook[$errmsg]."<br>\r\n";
			} else {
				return $errmsg."<br>\r\n";
			}
		}
	}

}
