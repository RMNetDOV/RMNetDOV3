<?php

class webmail_symlink_plugin {

	var $plugin_name = 'webmail_symlink_plugin';
	var $class_name = 'webmail_symlink_plugin';

	var $action;

	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	function onInstall() {
		global $conf;

		return false;

	}


	/*
	 	This function is called when the plugin is loaded
	*/

	function onLoad() {
		global $app;

		/*
		Register for the events
		*/

		$app->plugins->registerEvent('web_domain_insert', $this->plugin_name, 'insert');
		$app->plugins->registerEvent('web_domain_update', $this->plugin_name, 'update');
	}

	function insert($event_name, $data) {
		global $app, $conf;

		$this->action = 'insert';
		// just run the update function
		$this->update($event_name, $data);
	}

	function update($event_name, $data) {
		global $app, $conf;

		if($this->action != 'insert') $this->action = 'update';

		if($data["new"]["type"] != "vhost" && $data["new"]["parent_domain_id"] > 0) {

			$old_parent_domain_id = intval($data["old"]["parent_domain_id"]);
			$new_parent_domain_id = intval($data["new"]["parent_domain_id"]);

			// If the parent_domain_id has been chenged, we will have to update the old site as well.
			if($this->action == 'update' && $data["new"]["parent_domain_id"] != $data["old"]["parent_domain_id"]) {
				$tmp = $app->db->queryOneRecord("SELECT * FROM web_domain WHERE domain_id = ? AND active = 'y'", $old_parent_domain_id);
				$data["new"] = $tmp;
				$data["old"] = $tmp;
				$this->action = 'update';
				$this->update($event_name, $data);
			}

			// This is not a vhost, so we need to update the parent record instead.
			$tmp = $app->db->queryOneRecord("SELECT * FROM web_domain WHERE domain_id = ? AND active = 'y'", $new_parent_domain_id);
			$data["new"] = $tmp;
			$data["old"] = $tmp;
			$this->action = 'update';
		}

		if($data["new"]["document_root"] == '') {
			$app->log("document_root not set", LOGLEVEL_WARN);
			return 0;
		}

		$symlink = true;
		if($data["new"]["php"] == "suphp") $symlink = false;
		elseif($data["new"]["php"] == "cgi" && $data["new"]["suexec"] == "y") $symlink = false;
		elseif($data["new"]["php"] == "fast-cgi" && $data["new"]["suexec"] == "y") $symlink = false;


		if(!is_dir($data["new"]["document_root"]."/web")) mkdir($data["new"]["document_root"].'/web', 0755, true);
		if($symlink == false) {
			if(is_link($data["new"]["document_root"].'/web/webmail')) unlink($data["new"]["document_root"].'/web/webmail');
		} else {
			if(!is_link($data["new"]["document_root"]."/web/webmail")) symlink('/var/www/webmail', $data["new"]["document_root"].'/web/webmail');
			else symlink('/var/www/webmail', $data["new"]["document_root"].'/web/webmail');
		}
	}


} // end class

?>
