<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/server_ip.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('admin');

$app->uses('listform_actions');

$app->listform_actions->SQLOrderBy = "ORDER BY server_ip.server_id, server_ip.ip_address";

$app->listform_actions->onLoad();


?>
