<?php

class plugin_backuplist_mail extends plugin_base {

	var $module;
	var $form;
	var $tab;
	var $record_id;
	var $formdef;
	var $options;

	function onShow() {
		global $app;
		
		$app->uses('functions');
		
		$listTpl = new tpl;
		$listTpl->newTemplate('templates/mail_user_backup_list.htm');
				
		//* Loading language file
		$lng_file = "lib/lang/".$app->functions->check_language($_SESSION["s"]["language"])."_mail_backup_list.lng";
		include($lng_file);
		$listTpl->setVar($wb);

		$message = '';
		$error = '';

		if(isset($_GET['backup_action'])) {
			$backup_id = $app->functions->intval($_GET['backup_id']);

			if($_GET['backup_action'] == 'restore_mail' && $backup_id > 0) {
				$sql = "SELECT count(action_id) as number FROM sys_remoteaction WHERE action_state = 'pending' AND action_type = 'backup_restore_mail' AND action_param = ?";
				$tmp = $app->db->queryOneRecord($sql, $backup_id);
				if($tmp['number'] == 0) {
					$message .= $wb['restore_info_txt'];
					$sql = 	"INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
					"VALUES (?, ?, 'backup_restore_mail', ?, 'pending','')";
					$app->db->query($sql, $this->form->dataRecord['server_id'], time(), $backup_id);
				} else {
					$error .= $wb['restore_pending_txt'];
				}
			}	
			
			if($_GET['backup_action'] == 'delete_mail' && $backup_id > 0) {
				$sql = "SELECT count(action_id) as number FROM sys_remoteaction WHERE action_state = 'pending' AND action_type = 'backup_delete_mail' AND action_param = '$backup_id'";
				$tmp = $app->db->queryOneRecord($sql);
				if($tmp['number'] == 0) {
					$message .= $wb['delete_info_txt'];
					$sql = 	"INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
					"VALUES (?, ?, 'backup_delete_mail', ?, 'pending', '')";
					$app->db->query($sql, $this->form->dataRecord['server_id'], time(), $backup_id);
				} else {
					$error .= $wb['delete_pending_txt'];
				}
			}				
		}
				
		//* Get the data
		$sql = "SELECT * FROM mail_backup WHERE mailuser_id = ? ORDER BY tstamp DESC";
		$records = $app->db->queryAllRecords($sql, $this->form->id);
		$bgcolor = "#FFFFFF";
		if(is_array($records)) {
			foreach($records as $rec) {
				// Change of color
				$bgcolor = ($bgcolor == "#FFFFFF")?"#EEEEEE":"#FFFFFF";
				$rec["bgcolor"] = $bgcolor;
				$rec['date'] = date($app->lng('conf_format_datetime'),$rec['tstamp']);
				$rec['backup_type'] = $wb[('backup_type_'.$rec['backup_type'])];
				$rec['filesize'] = $app->functions->formatBytes($rec['filesize']);
				$records_new[] = $rec;
			}
		}

		$listTpl->setLoop('records',@$records_new);

		$listTpl->setVar('parent_id',$this->form->id);
		$listTpl->setVar('msg',$message);
		$listTpl->setVar('error',$error);

		// Setting Returnto information in the session
		$list_name = 'backup_list';
		$_SESSION["s"]["list"][$list_name]["parent_id"] = $this->form->id;
		$_SESSION["s"]["list"][$list_name]["parent_name"] = $app->tform->formDef["name"];
		$_SESSION["s"]["list"][$list_name]["parent_tab"] = $_SESSION["s"]["form"]["tab"];
		$_SESSION["s"]["list"][$list_name]["parent_script"] = $app->tform->formDef["action"];
		$_SESSION["s"]["form"]["return_to"] = $list_name;
		return $listTpl->grab();
	} // end function
} // end class

?>
