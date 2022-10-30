<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/datalog.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('monitor');

$app->uses('listform_actions');

$servers = $app->db->queryAllRecords("SELECT server_id, updated FROM server");

$sql = '(';
foreach($servers as $sv) {
	$sql .= " (sys_datalog.datalog_id > ".$sv['updated']." AND sys_datalog.server_id = ".$sv['server_id'].") OR ";
}
$sql = substr($sql, 0, -4);
$sql .= ')';

$app->listform_actions->SQLExtWhere = $sql;
$app->listform_actions->SQLOrderBy = "ORDER BY sys_datalog.tstamp DESC, sys_datalog.datalog_id DESC";

$app->listform_actions->onLoad();


?>
