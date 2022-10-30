<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mail');

/* get the id of the mail (must be int!) */
if (!isset($_GET['id'])){
	die ("No E-Mail selected!");
}
$emailId = $app->functions->intval($_GET['id']);

/*
 * Get the data to connect to the database
 */
$dbData = $app->db->queryOneRecord("SELECT server_id FROM mail_user WHERE mailuser_id = ?", $emailId);
$serverId = $app->functions->intval($dbData['server_id']);
if ($serverId == 0){
	die ("No E-Mail - Server found!");
}

$serverData = $app->db->queryOneRecord("SELECT server_name FROM server WHERE server_id = ?", $serverId);

$app->uses('getconf');
$global_config = $app->getconf->get_global_config('mail');

if($global_config['webmail_url'] != '') {
	$webmail_url = $global_config['webmail_url'];
	$webmail_url = str_replace('[SERVERNAME]', $serverData['server_name'], $webmail_url);
	header('Location:' . $webmail_url);
} else {

	/*
 * We only redirect to the login-form, so there is no need, to check any rights
 */
	isset($_SERVER['HTTPS'])? $http = 'https' : $http = 'http';
	if($web_config['server_type'] == 'nginx') {
		header('Location: http://' . $serverData['server_name'] . ':8081/webmail');
	} else {
		header('Location: ' . $http . '://' . $serverData['server_name'] . '/webmail');
	}
	isset($_SERVER['HTTPS'])? $http = 'https' : $http = 'http';
}
exit;
?>
