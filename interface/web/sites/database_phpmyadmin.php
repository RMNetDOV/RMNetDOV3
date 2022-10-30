<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('sites');

/*
 *  get the id of the database (must be int!)
 */
if (!isset($_GET['id'])){
	die ("No DB selected!");
}
$databaseId = $app->functions->intval($_GET['id']);

/*
 * Get the data to connect to the database
 */
$dbData = $app->db->queryOneRecord("SELECT server_id, database_name FROM web_database WHERE database_id = ?", $databaseId);
$serverId = $app->functions->intval($dbData['server_id']);
if ($serverId == 0){
	die ("No DB-Server found!");
}
$serverData = $app->db->queryOneRecord("SELECT server_name FROM server WHERE server_id = ?", $serverId);

$app->uses('getconf');
$global_config = $app->getconf->get_global_config('sites');
$web_config = $app->getconf->get_server_config($serverId, 'web');

/*
 * We only redirect to the login-form, so there is no need, to check any rights
 */

if($global_config['phpmyadmin_url'] != '') {
	$phpmyadmin_url = $global_config['phpmyadmin_url'];
	$phpmyadmin_url = str_replace(array('[SERVERNAME]', '[DATABASENAME]'), array($serverData['server_name'], $dbData['database_name']), $phpmyadmin_url);
	header('Location: '.$phpmyadmin_url);
} else {
	isset($_SERVER['HTTPS'])? $http = 'https' : $http = 'http';
	if($web_config['server_type'] == 'nginx') {
		header('Location: http://' . $serverData['server_name'] . ':8081/phpmyadmin');
	} else {
		header('Location: ' . $http . '://' . $serverData['server_name'] . '/phpmyadmin');
	}
}
exit;
?>
