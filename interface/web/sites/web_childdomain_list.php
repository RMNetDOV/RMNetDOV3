<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/web_childdomain.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->uses('listform_actions');

//* Get and set the child domain type - store in session
$show_type = 'aliasdomain';
if(isset($_GET['type']) && $_GET['type'] == 'subdomain') $show_type = 'subdomain';
elseif(!isset($_GET['type']) && isset($_SESSION['s']['var']['childdomain_type']) && $_SESSION['s']['var']['childdomain_type'] == 'subdomain') $show_type = 'subdomain';

$_SESSION['s']['var']['childdomain_type'] = $show_type;

class list_action extends listform_actions {
	function onShow() {
		global $app;
		$app->tpl->setVar('childdomain_type', $_SESSION['s']['var']['childdomain_type'], true);
		
		parent::onShow();
	}
}


$list = new list_action;
// Limit the results to alias domains
$list->SQLExtWhere = "web_domain.type = '" . ($show_type == 'subdomain' ? 'subdomain' : 'alias') . "'";
$list->SQLOrderBy = 'ORDER BY web_domain.domain';
$list->onLoad();


?>
