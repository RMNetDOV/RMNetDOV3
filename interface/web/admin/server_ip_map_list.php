<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$list_def_file = "list/server_ip_map.list.php";

//* Check permissions for module
$app->auth->check_module_permissions('admin');

$app->uses('listform_actions');

//$app->listform_actions->SQLOrderBy = "ORDER BY server_ip.server_id, server_ip.ip_address";
$app->listform_actions->SQLOrderBy = "";

$app->listform_actions->onLoad();

?>
