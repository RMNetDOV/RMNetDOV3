<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/web_vhost_domain.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->load('listform_actions');

//* Get and set the vhost domain type - store in session
$query_type = 'vhost';
$show_type = 'domain';
if(isset($_GET['type']) && $_GET['type'] == 'subdomain') {
	$show_type = 'subdomain';
	$query_type = 'vhostsubdomain';
} elseif(isset($_GET['type']) && $_GET['type'] == 'aliasdomain') {
	$show_type = 'aliasdomain';
	$query_type = 'vhostalias';
} elseif(!isset($_GET['type']) && isset($_SESSION['s']['var']['vhostdomain_type']) && $_SESSION['s']['var']['vhostdomain_type'] == 'subdomain') {
	$show_type = 'subdomain';
	$query_type = 'vhostsubdomain';
} elseif(!isset($_GET['type']) && isset($_SESSION['s']['var']['vhostdomain_type']) && $_SESSION['s']['var']['vhostdomain_type'] == 'aliasdomain') {
	$show_type = 'aliasdomain';
	$query_type = 'vhostalias';
}

$_SESSION['s']['var']['vhostdomain_type'] = $show_type;

class list_action extends listform_actions {
	function onShow() {
		global $app;
		$app->tpl->setVar('vhostdomain_type', $_SESSION['s']['var']['vhostdomain_type'], true);
		
		parent::onShow();
	}

}

$list = new list_action;
$list->SQLExtWhere = "web_domain.type = '" . $query_type . "'" . ($show_type == 'domain' ? " AND web_domain.parent_domain_id = '0'" : "");
$list->SQLOrderBy = 'ORDER BY web_domain.domain';
$list->onLoad();

?>
