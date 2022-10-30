<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/client_template.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('client');
if($_SESSION["s"]["user"]["typ"] != 'admin' && !$app->auth->has_clients($_SESSION['s']['user']['userid'])) die('Client-Templates are for Admins and Resellers only.');

$app->uses('listform_actions');
$app->listform_actions->SQLOrderBy = 'ORDER BY client_template.template_name';
$app->listform_actions->onLoad();
?>
