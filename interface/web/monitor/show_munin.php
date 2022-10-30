<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('monitor');

$app->uses('tools_monitor');

// Loading the template
$app->uses('tpl');
$app->tpl->newTemplate("form.tpl.htm");
$app->tpl->setInclude('content_tpl', 'templates/show_munin.htm');

$monTransSrv = $app->lng("monitor_settings_server_txt");
$title = 'Munin ('. $monTransSrv .' : ' . $_SESSION['monitor']['server_name'] . ')';

$app->tpl->setVar("list_head_txt", $title);

if($_SESSION["s"]["user"]["typ"] == 'admin'){

	$app->uses('getconf');
	$server_config = $app->getconf->get_server_config($_SESSION['monitor']['server_id'], 'server');

	$munin_url = trim($server_config['munin_url']);
	if($munin_url != ''){
		$munin_url = str_replace('[SERVERNAME]', $_SESSION['monitor']['server_name'], $munin_url);
		$munin_user = trim($server_config['munin_user']);
		$munin_password = trim($server_config['munin_password']);
		$auth_string = '';
		if($munin_user != ''){
			$auth_string = rawurlencode($munin_user);
		}
		if($munin_user != '' && $munin_password != ''){
			$auth_string .= ':'.rawurlencode($munin_password);
		}
		if($auth_string != '') $auth_string .= '@';

		$munin_url_parts = parse_url($munin_url);

		$munin_url = $munin_url_parts['scheme'].'://'.$auth_string.$munin_url_parts['host'].(isset($munin_url_parts['port']) ? ':' . $munin_url_parts['port'] : '').(isset($munin_url_parts['path']) ? $munin_url_parts['path'] : '').(isset($munin_url_parts['query']) ? '?' . $munin_url_parts['query'] : '').(isset($munin_url_parts['fragment']) ? '#' . $munin_url_parts['fragment'] : '');

		$app->tpl->setVar("munin_url", $munin_url);
	} else {
		$app->tpl->setVar("no_munin_url_defined_txt", $app->lng("no_munin_url_defined_txt"));
	}
} else {
	$app->tpl->setVar("no_permissions_to_view_munin_txt", $app->lng("no_permissions_to_view_munin_txt"));
}

$app->tpl_defaults();
$app->tpl->pparse();
?>
