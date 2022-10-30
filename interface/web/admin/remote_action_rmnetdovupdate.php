<?php

//die('Function has been removed.');

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('admin');

//* This is only allowed for administrators
if(!$app->auth->is_admin()) die('only allowed for administrators.');

$app->uses('tpl');

$app->tpl->newTemplate('form.tpl.htm');
$app->tpl->setInclude('content_tpl', 'templates/remote_action_rmnetdovupdate.htm');

//* load language file
$lng_file = 'lib/lang/'.$app->functions->check_language($_SESSION['s']['language']).'_remote_action.lng';
include $lng_file;

/*
 * We need a list of all Servers
 */

$sysServers = $app->db->queryAllRecords("SELECT server_id, server_name FROM server order by server_name");
$dropDown = "<option value='*'>" . $wb['select_all_server'] . "</option>";
foreach ($sysServers as $server) {
	$dropDown .= "<option value='" . $server['server_id'] . "'>" . $server['server_name'] . "</option>";
}
$app->tpl->setVar('server_option', $dropDown);

$msg = '';

/*
 * If the user wants to do the action, write this to our db
*/

//* Note: Disabled post action
if (1 == 0 && isset($_POST['server_select'])) {
	
	//* CSRF Check
	$app->auth->csrf_token_check();
	
	$server = $_POST['server_select'];
	$servers = array();
	if ($server == '*') {
		/* We need ALL Servers */
		foreach ($sysServers as $server) {
			$servers[] = $server['server_id'];
		}
	}
	else {
		/* We need only the selected Server */
		$servers[] = $_POST['server_select'];
	}
	foreach ($servers as $serverId) {
		$sql = "INSERT INTO sys_remoteaction (server_id, tstamp, action_type, action_param, action_state, response) " .
			"VALUES (?, UNIX_TIMESTAMP(), 'ispc_update', '', 'pending', '')";
		$app->db->query($sql, $serverId);
	}
	$msg = $wb['action_scheduled'];
}

$app->tpl->setVar('msg', $msg);

//* SET csrf token
$csrf_token = $app->auth->csrf_token_get('ispupdate');
$app->tpl->setVar('_csrf_id',$csrf_token['csrf_id']);
$app->tpl->setVar('_csrf_key',$csrf_token['csrf_key']);

$app->tpl->setVar($wb);

$app->tpl_defaults();
$app->tpl->pparse();


?>
