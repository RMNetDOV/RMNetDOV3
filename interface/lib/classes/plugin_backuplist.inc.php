<?php

class plugin_backuplist extends plugin_base {

	var $module;
	var $form;
	var $tab;
	var $record_id;
	var $formdef;
	var $options;

	/**
	 * Process request to make a backup. This request is triggered manually by the user in the RM-Net - DOV CP interface.
	 * @param string $message
	 * @param string $error
	 * @param string[] $wb language text
	 * @author Ramil Valitov <ramilvalitov@gmail.com>
	 * @uses backup_plugin::make_backup_callback() this method is called later in the plugin to run the backup
	 */
	protected function makeBackup(&$message, &$error, $wb)
	{
		global $app;

		$mode = $_GET['make_backup'];
		$action_type = ($mode == 'web') ? 'backup_web_files' : 'backup_database';
		$domain_id = intval($this->form->id);

		$sql = "SELECT count(action_id) as number FROM sys_remoteaction WHERE action_state = 'pending' AND action_type = ? AND action_param = ?";
		$tmp = $app->db->queryOneRecord($sql, $action_type, $domain_id);
		if ($tmp['number'] == 0) {
			if($action_type === 'backup_database') {
				// get all server ids of databases for this domain
				$sql = 'SELECT DISTINCT `server_id` FROM `web_database` WHERE `parent_domain_id` = ?';
				$result = $app->db->query($sql, $domain_id);
				while(($cur = $result->get())) {
					$server_id = $cur['server_id'];
					$sql = "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) VALUES (?, UNIX_TIMESTAMP(), ?, ?, 'pending', '')";
					$app->db->query($sql, $server_id, $action_type, $domain_id);
				}
				$result->free();
			} else {
				$server_id = $this->form->dataRecord['server_id'];
				$sql = "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) VALUES (?, UNIX_TIMESTAMP(), ?, ?, 'pending', '')";
				$app->db->query($sql, $server_id, $action_type, $domain_id);
			}
			$message .= $wb['backup_info_txt'];
		} else {
			$error .= $wb['backup_pending_txt'];
		}
	}

	function onShow() {

		global $app;

		$listTpl = new tpl;
		$listTpl->newTemplate('templates/web_backup_list.htm');

		//* Loading language file
		$lng_file = "lib/lang/".$app->functions->check_language($_SESSION["s"]["language"])."_web_backup_list.lng";
		include $lng_file;
		$listTpl->setVar($wb);

		$message = '';
		$error = '';

		if (isset($_GET['make_backup'])) {
			$this->makeBackup($message, $error, $wb);
		}

		if(isset($_GET['backup_action'])) {
			$backup_id = $app->functions->intval($_GET['backup_id']);

			//* check if the user is  owner of the parent domain
			$domain_backup = $app->db->queryOneRecord("SELECT parent_domain_id FROM web_backup WHERE backup_id = ?", $backup_id);

			$check_perm = 'u';
			if($_GET['backup_action'] == 'download') $check_perm = 'r'; // only check read permissions on download, not update permissions

			$get_domain = $app->db->queryOneRecord("SELECT domain_id FROM web_domain WHERE domain_id = ? AND ".$app->tform->getAuthSQL($check_perm), $domain_backup["parent_domain_id"]);
			if(empty($get_domain) || !$get_domain) {
				$app->error($app->tform->lng('no_domain_perm'));
			}

			if($_GET['backup_action'] == 'download' && $backup_id > 0) {
				$server_id = $this->form->dataRecord['server_id'];
				$backup = $app->db->queryOneRecord("SELECT * FROM web_backup WHERE backup_id = ?", $backup_id);
				if($backup['server_id'] > 0) $server_id = $backup['server_id'];
				$sql = "SELECT count(action_id) as number FROM sys_remoteaction WHERE action_state = 'pending' AND action_type = 'backup_download' AND action_param = ?";
				$tmp = $app->db->queryOneRecord($sql, $backup_id);
				if($tmp['number'] == 0) {
					$message .= $wb['download_info_txt'];
					$sql =  "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
						"VALUES (?, UNIX_TIMESTAMP(), 'backup_download', ?, 'pending', '')";
					$app->db->query($sql, $server_id, $backup_id);
				} else {
					$error .= $wb['download_pending_txt'];
				}
			}
			if($_GET['backup_action'] == 'restore' && $backup_id > 0) {
				$server_id = $this->form->dataRecord['server_id'];
				$backup = $app->db->queryOneRecord("SELECT * FROM web_backup WHERE backup_id = ?", $backup_id);
				if($backup['server_id'] > 0) $server_id = $backup['server_id'];
				$sql = "SELECT count(action_id) as number FROM sys_remoteaction WHERE action_state = 'pending' AND action_type = 'backup_restore' AND action_param = ?";
				$tmp = $app->db->queryOneRecord($sql, $backup_id);
				if($tmp['number'] == 0) {
					$message .= $wb['restore_info_txt'];
					$sql =  "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
						"VALUES (?, UNIX_TIMESTAMP(), 'backup_restore', ?, 'pending', '')";
					$app->db->query($sql, $server_id, $backup_id);
				} else {
					$error .= $wb['restore_pending_txt'];
				}
			}
			if($_GET['backup_action'] == 'delete' && $backup_id > 0) {
				$server_id = $this->form->dataRecord['server_id'];
				$backup = $app->db->queryOneRecord("SELECT * FROM web_backup WHERE backup_id = ?", $backup_id);
				if($backup['server_id'] > 0) $server_id = $backup['server_id'];
				$sql = "SELECT count(action_id) as number FROM sys_remoteaction WHERE action_state = 'pending' AND action_type = 'backup_delete' AND action_param = ?";
				$tmp = $app->db->queryOneRecord($sql, $backup_id);
				if($tmp['number'] == 0) {
					$message .= $wb['delete_info_txt'];
					$sql =  "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
						"VALUES (?, UNIX_TIMESTAMP(), 'backup_delete', ?, 'pending', '')";
					$app->db->query($sql, $server_id, $backup_id);
				} else {
					$error .= $wb['delete_pending_txt'];
				}
			}

		}

		//* Get the data
		$server_ids = array();
		$web = $app->db->queryOneRecord("SELECT server_id, backup_format_web, backup_format_db, backup_password, backup_encrypt FROM web_domain WHERE domain_id = ?", $this->form->id);
		$databases = $app->db->queryAllRecords("SELECT server_id FROM web_database WHERE parent_domain_id = ?", $this->form->id);
		if($app->functions->intval($web['server_id']) > 0) $server_ids[] = $app->functions->intval($web['server_id']);
		if(is_array($databases) && !empty($databases)){
			foreach($databases as $database){
				if($app->functions->intval($database['server_id']) > 0) $server_ids[] = $app->functions->intval($database['server_id']);
			}
		}
		$server_ids = array_unique($server_ids);
		$sql = "SELECT * FROM web_backup WHERE parent_domain_id = ? AND server_id IN ? ORDER BY tstamp DESC, backup_type ASC";
		$records = $app->db->queryAllRecords($sql, $this->form->id, $server_ids);

		$bgcolor = "#FFFFFF";
		if(is_array($records)) {
			foreach($records as $rec) {

				// Change of color
				$bgcolor = ($bgcolor == "#FFFFFF")?"#EEEEEE":"#FFFFFF";
				$rec["bgcolor"] = $bgcolor;

				$rec['date'] = date($app->lng('conf_format_datetime'), $rec['tstamp']);

				$backup_format = $rec['backup_format'];
				$backup_mode = $rec['backup_mode'];
				if ($backup_mode === 'borg') {
					// Get backup format from domain config
					switch ($rec['backup_type']) {
						case 'mysql':
							$backup_format = $web['backup_format_db'];
							if (empty($backup_format) || $backup_format == 'default') {
								$backup_format = self::getDefaultBackupFormat('rootgz', 'mysql');
							}
							$rec['filename'] .= self::getBackupDbExtension($backup_format);
							break;
						case 'web':
							$backup_format = $web['backup_format_web'];
							if (empty($backup_format) || $backup_format == 'default') {
								$backup_format = self::getDefaultBackupFormat($backup_mode, 'web');
							}
							$rec['filename'] .= self::getBackupWebExtension($backup_format);
							break;
						default:
							$app->log('Unsupported backup type "' . $rec['backup_type'] . '" for backup id ' . $rec['backup_id'], LOGLEVEL_ERROR);
							break;
					}
					$rec['backup_password'] = $web['backup_encrypt'] == 'y' ? trim($web['backup_password']) : '';

				} elseif (empty($backup_format)) {
					//We have a backup from old version of RM-Net - DOV CP
					switch ($rec['backup_type']) {
						case 'mysql':
							$backup_format = 'gzip';
							break;
						case 'web':
							$backup_format = ($rec['backup_mode'] == 'userzip') ? 'zip' : 'tar_gzip';
							break;
						default:
							$app->log('Unsupported backup type "' . $rec['backup_type'] . '" for backup id ' . $rec['backup_id'], LOGLEVEL_ERROR);
							break;
					}
				}
				$rec['backup_type'] = $wb[('backup_type_' . $rec['backup_type'])];
				$backup_format = (!empty($backup_format)) ? $wb[('backup_format_' . $backup_format . '_txt')] : $wb["backup_format_unknown_txt"];
				if (empty($backup_format))
					$backup_format = $wb["backup_format_unknown_txt"];

				$rec['backup_format'] = $backup_format;
				$rec['backup_encrypted'] = empty($rec['backup_password']) ? $wb["no_txt"] : $wb["yes_txt"];
				$backup_manual_prefix = 'manual-';
				$rec['backup_job'] = (substr($rec['filename'], 0, strlen($backup_manual_prefix)) == $backup_manual_prefix) ? $wb["backup_job_manual_txt"] : $wb["backup_job_auto_txt"];

				$rec['download_available'] = true;
				if($rec['server_id'] != $web['server_id']) $rec['download_available'] = false;

				if($rec['filesize'] > 0){
					$rec['filesize'] = $app->functions->currency_format($rec['filesize']/(1024*1024), 'client').'&nbsp;MB';
					if($backup_mode === "borg") {
						$rec['filesize'] = '<a href="javascript:void(0)" data-toggle="tooltip" title="'
											. $wb['final_size_txt']
											. '"><strong>*</strong></a>'
											. $rec['filesize'];
					}
				}

				$records_new[] = $rec;
			}
		}

		$listTpl->setLoop('records', @$records_new);

		$listTpl->setVar('parent_id', $this->form->id);
		$listTpl->setVar('msg', $message);
		$listTpl->setVar('error', $error);

		// Setting Returnto information in the session
		$list_name = 'backup_list';
		// $_SESSION["s"]["list"][$list_name]["parent_id"] = $app->tform_actions->id;
		$_SESSION["s"]["list"][$list_name]["parent_id"] = $this->form->id;
		$_SESSION["s"]["list"][$list_name]["parent_name"] = $app->tform->formDef["name"];
		$_SESSION["s"]["list"][$list_name]["parent_tab"] = $_SESSION["s"]["form"]["tab"];
		$_SESSION["s"]["list"][$list_name]["parent_script"] = $app->tform->formDef["action"];
		$_SESSION["s"]["form"]["return_to"] = $list_name;

		return $listTpl->grab();
	}

	/**
	 * Returns file extension for specified backup format
	 * @param string $format backup format
	 * @return string|null
	 * @author Ramil Valitov <ramilvalitov@gmail.com>
	 */
	protected static function getBackupDbExtension($format)
	{
		$prefix = '.sql';
		switch ($format) {
			case 'gzip':
				return $prefix . '.gz';
			case 'bzip2':
				return $prefix . '.bz2';
			case 'xz':
				return $prefix . '.xz';
			case 'zip':
			case 'zip_bzip2':
				return '.zip';
			case 'rar':
				return '.rar';
		}
		if (strpos($format, "7z_") === 0) {
			return $prefix . '.7z';
		}
		return null;
	}

	/**
	 * Returns file extension for specified backup format
	 * @param string $format backup format
	 * @return string|null
	 * @author Ramil Valitov <ramilvalitov@gmail.com>
	 */
	protected static function getBackupWebExtension($format)
	{
		switch ($format) {
			case 'tar_gzip':
				return '.tar.gz';
			case 'tar_bzip2':
				return '.tar.bz2';
			case 'tar_xz':
				return '.tar.xz';
			case 'zip':
			case 'zip_bzip2':
				return '.zip';
			case 'rar':
				return '.rar';
		}
		if (strpos($format, "tar_7z_") === 0) {
			return '.tar.7z';
		}
		return null;
	}

	protected static function getDefaultBackupFormat($backup_mode, $backup_type)
	{
		//We have a backup from old version of RM-Net - DOV CP
		switch ($backup_type) {
			case 'mysql':
				return 'gzip';
			case 'web':
				return ($backup_mode == 'userzip') ? 'zip' : 'tar_gzip';
		}
		return "";
	}

}

?>
