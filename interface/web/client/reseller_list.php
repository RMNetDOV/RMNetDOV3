<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/reseller.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('client');

if($_SESSION["s"]["user"]["typ"] != 'admin') die('Access only for administrators.');

$app->uses('listform_actions');

$app->listform_actions->SQLOrderBy = 'ORDER BY client.company_name, client.contact_name, client.client_id';
$app->listform_actions->SQLExtWhere = "(client.limit_client > 0 or client.limit_client = -1)";
$app->listform_actions->SQLExtSelect = ', LOWER(client.country) as countryiso';
$app->listform_actions->onLoad();


?>
