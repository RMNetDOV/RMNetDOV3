<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mail');

/* get the id of the mail (must be int!) */
if (!isset($_GET['id'])){
	die ("No List selected!");
}
$listId = $app->functions->intval($_GET['id']);

/*
 * Get the data to connect to the database
 */
$dbData = $app->db->queryAllRecords("SELECT server_id, listname FROM mail_mailinglist WHERE mailinglist_id = ?", $listId);
$serverId = $app->functions->intval($dbData[0]['server_id']);
if ($serverId == 0){
	die ("No List - Server found!");
}

$serverData = $app->db->queryOneRecord("SELECT server_name FROM server WHERE server_id = ?", $serverId);

$app->uses('getconf');
$global_config = $app->getconf->get_global_config('mail');

if($global_config['mailmailinglist_url'] != '') {
	header('Location:' . $global_config['mailmailinglist_url']);
} else {

	/*
 * We only redirect to the login-form, so there is no need, to check any rights
 */
	isset($_SERVER['HTTPS'])? $http = 'https' : $http = 'http';
	header('Location:' . $http . '://' . $serverData['server_name'] . '/cgi-bin/mailman/admin/' . $dbData[0]['listname']);
}
exit;
?>
