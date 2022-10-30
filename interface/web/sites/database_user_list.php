<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/database_user.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->load('listform_actions');


class list_action extends listform_actions {

	function onShow() {
		global $app, $conf;

		parent::onShow();
	}

}

$list = new list_action;
$list->SQLOrderBy = 'ORDER BY web_database_user.database_user';
$list->onLoad();


?>
