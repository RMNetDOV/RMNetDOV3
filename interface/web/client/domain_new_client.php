<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('client');

/*
 * Just get the html and return it
 */
$app->uses('ini_parser,getconf');
$settings = $app->getconf->get_global_config('domains');
if ($settings['use_domain_module'] == 'y') {
	echo $settings['new_domain_html'];
}

?>
