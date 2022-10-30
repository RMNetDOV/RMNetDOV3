<?php

$list_def_file = "list/server_ip_map.list.php";
$tform_def_file = "form/server_ip_map.tform.php";

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('admin');
$app->auth->check_security_permissions('admin_allow_server_ip');

$app->uses("tform_actions");
$app->tform_actions->onDelete();

?>
