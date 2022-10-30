<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/groups.list.php";
$tform_def_file = "form/groups.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('admin');
$app->auth->check_security_permissions('admin_allow_cpuser_group');

$app->uses("tform_actions");
$app->tform_actions->onDelete();

?>
