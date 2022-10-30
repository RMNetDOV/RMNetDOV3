<?php

if(defined('RMNETDOV_ROOT_PATH')) include_once RMNETDOV_ROOT_PATH.'/lib/classes/aps_installer.inc.php';
//require_once(RMNETDOV_ROOT_PATH.'/lib/classes/class.installer.php');

class aps_plugin
{
	public $plugin_name = 'aps_plugin';
	public $class_name = 'aps_plugin';

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

	/**
	 * This method gets called when the plugin is loaded
	 */


	public function onLoad()
	{
		global $app;

		// Register the available events
		$app->plugins->registerEvent('aps_instance_insert', $this->plugin_name, 'install');
		$app->plugins->registerEvent('aps_instance_update', $this->plugin_name, 'install');
		$app->plugins->registerEvent('aps_instance_delete', $this->plugin_name, 'delete');
	}



	/**
	 * (Re-)install a package
	 */
	public function install($event_name, $data)
	{
		global $app, $conf;

		//* dont run the installer on a mirror server to prevent
		//  that the pplication gets installed twice.
		if($conf['mirror_server_id'] > 0) return true;

		$app->log("Starting APS install", LOGLEVEL_DEBUG);
		if(!isset($data['new']['id'])) return false;
		$instanceid = $data['new']['id'];

		if($data['new']['instance_status'] == INSTANCE_INSTALL) {
			$aps = new ApsInstaller($app);
			$app->log("Running installHandler", LOGLEVEL_DEBUG);
			$aps->installHandler($instanceid, 'install');
		}

		if($data['new']['instance_status'] == INSTANCE_REMOVE) {
			$aps = new ApsInstaller($app);
			$app->log("Running installHandler", LOGLEVEL_DEBUG);
			$aps->installHandler($instanceid, 'delete');
		}
	}



	/**
	 * Update an existing instance (currently unused)
	 */
	/*
    public function update($event_name, $data)
    {
    }
	*/



	/**
	 * Uninstall an instance
	 */
	public function delete($event_name, $data)
	{
		global $app, $conf;

		if(!isset($data['new']['id'])) return false;
		$instanceid = $data['new']['id'];

		if($data['new']['instance_status'] == INSTANCE_REMOVE) {
			$aps = new ApsInstaller($app);
			$aps->installHandler($instanceid, 'install');
		}
	}

}

?>
