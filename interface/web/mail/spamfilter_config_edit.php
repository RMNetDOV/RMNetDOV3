<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/spamfilter_config.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mail');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onShowEdit() {
		global $app, $conf;

		if($_SESSION["s"]["user"]["typ"] != 'admin') die('This function needs admin privileges');

		if($app->tform->errorMessage == '') {
			$app->uses('ini_parser,getconf');

			$section = $this->active_tab;
			$server_id = $this->id;

			$this->dataRecord = $app->getconf->get_server_config($server_id, $section);
		}

		$record = $app->tform->getHTML($this->dataRecord, $this->active_tab, 'EDIT');

		$record['id'] = $this->id;
		$app->tpl->setVar($record);
	}

	function onUpdateSave($sql) {
		global $app;

		if($_SESSION["s"]["user"]["typ"] != 'admin') die('This function needs admin privileges');
		$app->uses('ini_parser,getconf');

		$section = $app->tform->getCurrentTab();
		$server_id = $this->id;

		$server_config_array = $app->getconf->get_server_config($server_id);
		$server_config_array[$section] = $app->tform->encode($this->dataRecord, $section);
		$server_config_str = $app->ini_parser->get_ini_string($server_config_array);

		$sql = "UPDATE server SET config = ? WHERE server_id = ?";
		$app->db->query($sql, $server_config_str, $server_id);
	}

}

$app->tform_actions = new page_action;
$app->tform_actions->onLoad();


?>
