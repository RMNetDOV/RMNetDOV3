<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('monitor');

$app->uses('tools_monitor');

// Loading the template
$app->uses('tpl');
$app->tpl->newTemplate("form.tpl.htm");
$app->tpl->setInclude('content_tpl', 'templates/show_monit.htm');

$monTransSrv = $app->lng("monitor_settings_server_txt");
$title = 'Monit ('. $monTransSrv .' : ' . $_SESSION['monitor']['server_name'] . ')';

$app->tpl->setVar("list_head_txt", $title);

if($_SESSION["s"]["user"]["typ"] == 'admin'){

	$app->uses('getconf');
	$server_config = $app->getconf->get_server_config($_SESSION['monitor']['server_id'], 'server');

	$monit_url = trim($server_config['monit_url']);
	if($monit_url != ''){
		$monit_url = str_replace('[SERVERNAME]', $_SESSION['monitor']['server_name'], $monit_url);
		$monit_user = trim($server_config['monit_user']);
		$monit_password = trim($server_config['monit_password']);
		$auth_string = '';
		if($monit_user != ''){
			$auth_string = rawurlencode($monit_user);
		}
		if($monit_user != '' && $monit_password != ''){
			$auth_string .= ':'.rawurlencode($monit_password);
		}
		if($auth_string != '') $auth_string .= '@';

		$monit_url_parts = parse_url($monit_url);

		$monit_url = $monit_url_parts['scheme'].'://'.$auth_string.$monit_url_parts['host'].(isset($monit_url_parts['port']) ? ':' . $monit_url_parts['port'] : '').(isset($monit_url_parts['path']) ? $monit_url_parts['path'] : '').(isset($monit_url_parts['query']) ? '?' . $monit_url_parts['query'] : '').(isset($monit_url_parts['fragment']) ? '#' . $monit_url_parts['fragment'] : '');

		$app->tpl->setVar("monit_url", $monit_url);
	} else {
		$app->tpl->setVar("no_monit_url_defined_txt", $app->lng("no_monit_url_defined_txt"));
	}
} else {
	$app->tpl->setVar("no_permissions_to_view_monit_txt", $app->lng("no_permissions_to_view_monit_txt"));
}

$app->tpl_defaults();
$app->tpl->pparse();
?>
