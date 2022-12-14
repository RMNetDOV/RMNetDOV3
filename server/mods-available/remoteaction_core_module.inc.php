<?php

class remoteaction_core_module {
	var $module_name = 'remoteaction_core_module';
	var $class_name = 'remoteaction_core_module';
	/* No actions at this time. maybe later... */
	var $actions_available = array();
	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	function onInstall() {
		return false;
	}

	/*
        This function is called when the module is loaded
	*/
	function onLoad() {
		/*
       	 * Check for actions to execute
		*/
		//* This module has been replaced by the new action framework.
		// $this->_execActions();
	}

	/*
     This function is called when a change in one of the registered tables is detected.
     The function then raises the events for the plugins.
	*/
	function process($tablename, $action, $data) {
		// not needed
	} // end function

	private function _actionDone($id, $state) {
		/*
		 * First set the state
		 */
		global $app;
		$sql = "UPDATE sys_remoteaction SET action_state = ? WHERE action_id = ?";
		$app->dbmaster->query($sql, $state, $id);

		/*
		 * Then save the maxid for the next time...
		 */
		$fp = fopen(dirname(__FILE__) .  "/../lib/remote_action.inc.php", 'wb');
		$content = '<?php' . "\n" . '$maxid_remote_action = ' . $id . ';' . "\n?>";
		fwrite($fp, $content);
		fclose($fp);
	}


	/**
	 * This method searches for scheduled actions and exec then
	 */


	private function _execActions() {
		global $app;
		global $conf;

		/* the id of the server as int */
		$server_id = intval($conf["server_id"]);

		/*
		 * First we (till and i, oliver) thought, it was enough to write
		 * "select from where action_status = 'pending'" and then execute this actions.
		 * But it is not!
		 * If a hacker can hack into a server, she can change the valus of action_status
		 * and so re-exec a action, executed some days bevore. So she can (for example)
		 * stop a service, a admin stopped some days before! To avoid this, we ignore
		 * the status (it is only for the interface to show) and use our own maxid
		*/
		include_once SCRIPT_PATH."/lib/remote_action.inc.php";

		/*
		 * Get all actions this server should execute
		*/
		$sql = "SELECT action_id, action_type, action_param FROM sys_remoteaction WHERE server_id = ? AND action_id > ? ORDER BY action_id";
		$actions = $app->dbmaster->queryAllRecords($sql, $server_id, $maxid_remote_action);

		/*
		 * process all actions
		*/
		if(is_array($actions)) {
			foreach ($actions as $action) {
				if ($action['action_type'] == 'os_update') {
					/* do the update */
					$this->_doOsUpdate($action);
					/* this action takes so much time,
					* we stop executing the actions not to waste more time */
					return;
				}

				if ($action['action_type'] == 'rmnetdov_update') {
					/* do the update */
					// Update function has been removed
					// $this->_doRMNetDOVUpdate($action);
					/* this action takes so much time,
					* we stop executing the actions not to waste more time */
					$this->_actionDone($action['action_id'], 'ok');
				}
				if ($action['action_type'] == 'openvz_start_vm') {
					$veid = intval($action['action_param']);
					if($veid > 0) {
						exec("vzctl start $veid");
					}
					$this->_actionDone($action['action_id'], 'ok');
				}
				if ($action['action_type'] == 'openvz_stop_vm') {
					$veid = intval($action['action_param']);
					if($veid > 0) {
						exec("vzctl stop $veid");
					}
					$this->_actionDone($action['action_id'], 'ok');
				}
				if ($action['action_type'] == 'openvz_restart_vm') {
					$veid = intval($action['action_param']);
					if($veid > 0) {
						exec("vzctl restart $veid");
					}
					$this->_actionDone($action['action_id'], 'ok');
				}
				if ($action['action_type'] == 'openvz_create_ostpl') {
					$parts = explode(':', $action['action_param']);
					$veid = intval($parts[0]);
					$template_cache_dir = '/vz/template/cache/';
					$template_name = $parts[1];
					if($veid > 0 && $template_name != '' && is_dir($template_cache_dir)) {
						$command = "vzdump --suspend --compress --stdexcludes --dumpdir ? ?";
						$app->system->exec_safe($command, $template_cache_dir, $veid);
						$app->system->exec_safe("mv ?*.tgz ?", $template_cache_dir."vzdump-openvz-".$veid, $template_cache_dir.$template_name.".tar.gz");
						$app->system->exec_safe("rm -f ?*.log", $template_cache_dir."vzdump-openvz-".$veid);
					}
					$this->_actionDone($action['action_id'], 'ok');
					/* this action takes so much time,
					* we stop executing the actions not to waste more time */
					return;
				}


			}
		}
	}

	private function _doOsUpdate($action) {
		/*
		 * Do the update
		 */
		 //Guess this is not wanted here?
		 //exec("aptitude update");
		 //exec("aptitude safe-upgrade -y");

		//TODO : change this when distribution information has been integrated into server record
		if(file_exists('/etc/gentoo-release')) {
			exec("glsa-check -f --nocolor affected");
		} elseif(file_exists('/etc/redhat-release')) {
			exec("which dnf &> /dev/null && dnf -y update || yum -y update");
		} else {
			exec("apt-get update");
			exec("apt-get -y upgrade");
		}

		/*
		 * All well done!
		 */
		$this->_actionDone($action['action_id'], 'ok');
	}

	private function _doRMNetDOVUpdate($action) {
		global $app;

		// Ensure that this code is not executed twice as this would cause a loop in case of a failure
		$this->_actionDone($action['action_id'], 'ok');

		/*
		 * Get the version-number of the newest version
		 */
		$new_version = @file_get_contents('https://github.com/RMNetDOV/RMNetDOV3/blob/master/rmnetdov3_version.txt');
		$new_version = trim($new_version);

		/*
		 * Do the update
		 */

		/* jump into the temporary dir */
		$oldDir = getcwd();
		chdir("/tmp");

		/* delete the old files (if there are any...) */
		$app->system->exec_safe("rm ?", "/tmp/RMNetDOV-" . $new_version . ".tar.gz");
		exec("rm /tmp/rmnetdov3_install -R");

		/* get the newest version */
		$app->system->exec_safe("wget ?", "https://github.com/RMNetDOV/RMNetDOV3/releases/tag/" . $new_version . ".tar.gz");

		/* extract the files */
		$app->system->exec_safe("tar xvfz ?", "RMNetDOV-" . $new_version . ".tar.gz");

		/*
		 * Initialize the automated update
		 * (the update is then done next start of server.sh
		 */
		chdir("/tmp/rmnetdov3_install/install");
		exec("touch autoupdate");

		/*
		 * do some clean-up
		 */
		$app->system->exec_safe("rm ?", "/tmp/RMNetDOV-" . $new_version . ".tar.gz");

		/*
		 * go back to the "old path"
		 */
		chdir($oldDir);

		/*
		 * All well done!
		 */
		//$this->_actionDone($action['action_id'], 'ok');
	}

}

?>
