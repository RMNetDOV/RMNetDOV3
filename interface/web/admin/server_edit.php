<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/server.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('admin');
$app->auth->check_security_permissions('admin_allow_server_services');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onShowEnd() {
		global $app, $conf;

		// Getting Servers
		$sql = "SELECT server_id,server_name FROM server WHERE server_id != ? AND mirror_server_id != ? ORDER BY server_name";
		$mirror_servers = $app->db->queryAllRecords($sql, $this->id, $this->id);
		$mirror_server_select = '<option value="0">'.$app->tform->lng('- None -').'</option>';
		if(is_array($mirror_servers)) {
			foreach( $mirror_servers as $mirror_server) {
				$selected = ($mirror_server["server_id"] == $this->dataRecord['mirror_server_id'])?'SELECTED':'';
				$mirror_server_select .= "<option value='$mirror_server[server_id]' $selected>" . $app->functions->htmlentities($mirror_server['server_name']) . "</option>\r\n";
			}
		}
		$app->tpl->setVar("mirror_server_id", $mirror_server_select);

		parent::onShowEnd();
	}

	function onSubmit() {
		global $app;

		//* We do not want to mirror the the server itself and the master can not be a mirror
		if($this->id == $this->dataRecord['mirror_server_id'] || $this->id == 1) $this->dataRecord['mirror_server_id'] = 0;

		parent::onSubmit();

	}

}

$page = new page_action;
$page->onLoad();

?>
