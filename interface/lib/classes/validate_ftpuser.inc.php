<?php

class validate_ftpuser {

	/*
		Validator function to check if a given dir is ok.
	*/
	function ftp_dir($field_name, $field_value, $validator) {
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
			$ftp_data = $app->db->queryOneRecord("SELECT parent_domain_id FROM ftp_user WHERE ftp_user_id = ?", $primary_id);
			if(!is_array($ftp_data) || $ftp_data["parent_domain_id"] < 1) {
				$errmsg = $validator['errmsg'];
				if(isset($app->tform->wordbook[$errmsg])) {
					return $app->tform->wordbook[$errmsg]."<br>\r\n";
				} else {
					return $errmsg."<br>\r\n";
				}
			} else {
				$parent_domain_id = $ftp_data["parent_domain_id"];
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
