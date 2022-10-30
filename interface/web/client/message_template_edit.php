<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/message_template.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('client');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {
	
	function onSubmit() {
		global $app, $conf;
		
		// Check for duplicates
		if($this->dataRecord['template_type'] == 'welcome') {
			$client_group_id = $app->functions->intval($_SESSION["s"]["user"]["default_group"]);
			$sql = "SELECT count(client_message_template_id) as number FROM client_message_template WHERE template_type = 'welcome' AND sys_groupid = ?";
			if($this->id > 0) {
				$sql .= " AND client_message_template_id != ?";
			}
			$tmp = $app->db->queryOneRecord($sql, $client_group_id, $this->id);
			if($tmp['number'] > 0) $app->tform->errorMessage .= $app->tform->lng('duplicate_welcome_error');
		}
		
		parent::onSubmit();
	}
	
	function onShowEnd() {
		global $app, $conf;
	
		//message variables
		$message_variables = '';
		$sql = "SHOW COLUMNS FROM client WHERE Field NOT IN ('client_id', 'sys_userid', 'sys_groupid', 'sys_perm_user', 'sys_perm_group', 'sys_perm_other', 'parent_client_id', 'id_rsa', 'ssh_rsa', 'created_at', 'default_mailserver', 'default_webserver', 'web_php_options', 'ssh_chroot', 'default_dnsserver', 'default_dbserver', 'template_master', 'template_additional', 'force_suexec', 'default_slave_dnsserver', 'usertheme', 'locked', 'canceled', 'can_use_api', 'tmp_data', 'customer_no_template', 'customer_no_start', 'customer_no_counter', 'added_date', 'added_by') AND Field NOT LIKE 'limit_%'";
		$field_names = $app->db->queryAllRecords($sql);
		if(!empty($field_names) && is_array($field_names)){
			foreach($field_names as $field_name){
				if($field_name['Field'] != ''){
					if($field_name['Field'] == 'gender'){
						$message_variables .= '<a href="javascript:void(0);" class="addPlaceholder">{salutation}</a> ';
					} else {
						$message_variables .= '<a href="javascript:void(0);" class="addPlaceholder">{'.$app->functions->htmlentities($field_name['Field']).'}</a> ';
					}
				}
			}
		}
		$app->tpl->setVar('message_variables', trim($message_variables));

		parent::onShowEnd();
	}
	
}

$page = new page_action;
$page->onLoad();

?>
