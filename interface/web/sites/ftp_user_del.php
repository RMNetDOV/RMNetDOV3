<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/ftp_user.list.php";
$tform_def_file = "form/ftp_user.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->uses("tform_actions");
$app->tform_actions->onDelete();

?>
