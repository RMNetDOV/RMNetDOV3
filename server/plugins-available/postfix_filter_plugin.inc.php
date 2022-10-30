<?php

class postfix_filter_plugin {

	var $plugin_name = 'postfix_filter_plugin';
	var $class_name = 'postfix_filter_plugin';


	var $postfix_config_dir = '/etc/postfix';

	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	function onInstall() {
		global $conf;

		if($conf['services']['mail'] == true) {
			return true;
		} else {
			return false;
		}

	}

	/*
	 	This function is called when the plugin is loaded
	*/

	function onLoad() {
		global $app;

		/*
		Register for the events
		*/

		$app->plugins->registerEvent('mail_content_filter_insert', 'postfix_filter_plugin', 'insert');
		$app->plugins->registerEvent('mail_content_filter_update', 'postfix_filter_plugin', 'update');
		$app->plugins->registerEvent('mail_content_filter_delete', 'postfix_filter_plugin', 'delete');



	}

	function insert($event_name, $data) {
		global $app, $conf;

		$this->update($event_name, $data);

	}

	function update($event_name, $data) {
		global $app, $conf;

		$type = $data["new"]["type"];
		$restart = false;

		if($type != '') {
			$sql = "SELECT * FROM mail_content_filter WHERE server_id = ? AND type = ? AND active = 'y'";
			$rules = $app->db->queryAllRecords($sql, $conf["server_id"], $type);
			$content = '';
			foreach($rules as $rule) {
				$content .= $rule["pattern"];
				$content .= "  ".$rule["action"]." ".$rule["data"]."\n";
			}

			if($type == 'header') {
				file_put_contents('/etc/postfix/header_checks', $content);
				$app->log("Writing /etc/postfix/header_checks", LOGLEVEL_DEBUG);
				$restart = true;
			}

			if($type == 'mime_header') {
				file_put_contents('/etc/postfix/mime_header_checks', $content);
				$app->log("Writing /etc/postfix/mime_header_checks", LOGLEVEL_DEBUG);
				$restart = true;
			}

			if($type == 'nested_header') {
				file_put_contents('/etc/postfix/nested_header_checks', $content);
				$app->log("Writing /etc/postfix/nested_header_checks", LOGLEVEL_DEBUG);
				$restart = true;
			}

			if($type == 'body') {
				file_put_contents('/etc/postfix/body_checks', $content);
				$app->log("Writing /etc/postfix/body_checks", LOGLEVEL_DEBUG);
				$restart = true;
			}
		}

		$type = $data["old"]["type"];
		if($type != '') {
			$sql = "SELECT * FROM mail_content_filter WHERE server_id = ? AND type = ? AND active = 'y'";
			$rules = $app->db->queryAllRecords($sql, $conf["server_id"], $type);
			$content = '';
			foreach($rules as $rule) {
				$content .= $rule["pattern"];
				$content .= "  ".$rule["action"]." ".$rule["data"]."\n";
			}

			if($type == 'header') {
				file_put_contents('/etc/postfix/header_checks', $content);
				$app->log("Writing /etc/postfix/header_checks", LOGLEVEL_DEBUG);
				$restart = true;
			}

			if($type == 'mime_header') {
				file_put_contents('/etc/postfix/mime_header_checks', $content);
				$app->log("Writing /etc/postfix/mime_header_checks", LOGLEVEL_DEBUG);
				$restart = true;
			}

			if($type == 'nested_header') {
				file_put_contents('/etc/postfix/nested_header_checks', $content);
				$app->log("Writing /etc/postfix/nested_header_checks", LOGLEVEL_DEBUG);
				$restart = true;
			}

			if($type == 'body') {
				file_put_contents('/etc/postfix/body_checks', $content);
				$app->log("Writing /etc/postfix/body_checks", LOGLEVEL_DEBUG);
				$restart = true;
			}
		}
		if($restart) exec('postfix reload');
	}

	function delete($event_name, $data) {
		global $app, $conf;

		$this->update($event_name, $data);
	}


} // end class

?>
