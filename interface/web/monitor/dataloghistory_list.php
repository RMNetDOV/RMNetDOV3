<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/dataloghistory.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('monitor');

$app->uses('listform_actions');

$app->listform_actions->SQLOrderBy = "ORDER BY sys_datalog.tstamp DESC, sys_datalog.datalog_id DESC";

$app->listform_actions->onLoad();


?>
