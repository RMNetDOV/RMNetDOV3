<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/openvz_ostemplate.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('vm');
if($_SESSION["s"]["user"]["typ"] != 'admin') die('permission denied');

$app->uses('listform_actions');

// $app->listform_actions->SQLOrderBy = 'ORDER BY company_name, contact_name, client_id';
// $app->listform_actions->SQLExtWhere = "limit_client = 0";
$app->listform_actions->onLoad();


?>
