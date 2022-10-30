<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/ftp_user.list.php";

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

		if($global_config['webftp_url'] != '') {
			$app->tpl->setVar('webftp_link', 1);
			$app->tpl->setVar('webftp_url', $global_config['webftp_url']);
		} else {
			$app->tpl->setVar('webftp_link', 0);
		}

		parent::onShow();
	}

}

$list = new list_action;
$list->SQLOrderBy = 'ORDER BY ftp_user.username';
$list->onLoad();


?>
