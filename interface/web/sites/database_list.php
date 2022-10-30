<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/database.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->load('listform_actions');


class list_action extends listform_actions {

	function onShow() {
		global $app, $conf;

		$app->uses('getconf');
		$global_config = $app->getconf->get_global_config('sites');

		if($global_config['dblist_phpmyadmin_link'] == 'y') {
			$app->tpl->setVar('dblist_phpmyadmin_link', 1);
		} else {
			$app->tpl->setVar('dblist_phpmyadmin_link', 0);
		}

		parent::onShow();
	}

}

$list = new list_action;
$list->SQLOrderBy = 'ORDER BY web_database.database_name';
$list->onLoad();


?>
