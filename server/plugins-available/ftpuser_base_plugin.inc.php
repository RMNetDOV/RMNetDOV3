<?php

class ftpuser_base_plugin {

	var $plugin_name = 'ftpuser_base_plugin';
	var $class_name = 'ftpuser_base_plugin';

	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	function onInstall() {
		global $conf;

		if($conf['services']['web'] == true) {
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

		$app->plugins->registerEvent('ftp_user_insert', $this->plugin_name, 'insert');
		$app->plugins->registerEvent('ftp_user_update', $this->plugin_name, 'update');
		$app->plugins->registerEvent('ftp_user_delete', $this->plugin_name, 'delete');


	}


	function insert($event_name, $data) {
		global $app, $conf;

		$app->uses('system');

		if(!is_dir($data['new']['dir'])) {
			$app->log("FTP User directory '".$data['new']['dir']."' does not exist. Creating it now.", LOGLEVEL_DEBUG);

			$web = $app->db->queryOneRecord("SELECT * FROM web_domain WHERE domain_id = ?", $data['new']['parent_domain_id']);

			//* Check if the resulting path is inside the docroot
			if(substr($data['new']['dir'], 0, strlen($web['document_root'])) != $web['document_root']) {
				$app->log("User dir '".$data['new']['dir']."' is outside of docroot.", LOGLEVEL_WARN);
				return false;
			}

			$app->system->web_folder_protection($web['document_root'], false);
			$app->system->mkdirpath($data['new']['dir'], 0755, $web["system_user"], $web["system_group"]);
			$app->system->web_folder_protection($web['document_root'], true);

			$app->log("Added ftpuser_dir: ".$data['new']['dir'], LOGLEVEL_DEBUG);
		}

	}

	function update($event_name, $data) {
		global $app, $conf;

		$app->uses('system');

		if(!is_dir($data['new']['dir'])) {
			$app->log("FTP User directory '".$data['new']['dir']."' does not exist. Creating it now.", LOGLEVEL_DEBUG);

			$web = $app->db->queryOneRecord("SELECT * FROM web_domain WHERE domain_id = ?", $data['new']['parent_domain_id']);

			//* Check if the resulting path is inside the docroot
			if(substr($data['new']['dir'], 0, strlen($web['document_root'])) != $web['document_root']) {
				$app->log("User dir '".$data['new']['dir']."' is outside of docroot.", LOGLEVEL_WARN);
				return false;
			}

			$app->system->web_folder_protection($web['document_root'], false);
			$app->system->mkdirpath($data['new']['dir'], 0755, $web["system_user"], $web["system_group"]);
			$app->system->web_folder_protection($web['document_root'], true);
			
			

			$app->log("Added ftpuser_dir: ".$data['new']['dir'], LOGLEVEL_DEBUG);
		}
		
		// When the directory has changed, delete the old .ftpquota file
		if($data['old']['dir'] != '' && $data['old']['dir'] != $data['new']['dir']) {
			if(is_file($data['old']['dir'].'/.ftpquota')) unlink($data['old']['dir'].'/.ftpquota');
		}
		
	}

	function delete($event_name, $data) {
		global $app, $conf;
		
		// Delete the .ftpquota file
		if(is_file($data['old']['dir'].'/.ftpquota')) unlink($data['old']['dir'].'/.ftpquota');

		$app->log("Ftpuser:".$data['new']['username']." deleted.", LOGLEVEL_DEBUG);

	}




} // end class

?>
