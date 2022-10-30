<?php

define('SCRIPT_PATH', dirname($_SERVER["SCRIPT_FILENAME"]));
require SCRIPT_PATH."/lib/config.inc.php";
require SCRIPT_PATH."/lib/app.inc.php";

set_time_limit(0);
ini_set('error_reporting', E_ALL & ~E_NOTICE);

// make sure server_id is always an int
$conf['server_id'] = intval($conf['server_id']);

// Load required base-classes
$app->uses('modules,plugins,ini_parser,file,services,getconf,system,cron,functions');
$app->load('libdatetime,cronjob');

// Path settings
$path = SCRIPT_PATH . '/lib/classes/cron.d';

//** Get commandline options
$cmd_opt = getopt('', array('cronjob::'));

if(isset($cmd_opt['cronjob']) && is_file($path.'/'.$cmd_opt['cronjob'])) {
	// Cronjob that shell be run
	$cronjob_file = $cmd_opt['cronjob'];
} else {
	die('Primer uporabe: php cron_debug.php --cronjob=100-mailbox_stats.inc.php'."\n");
}

// Load and run the cronjob
$name = substr($cronjob_file, 0, strpos($cronjob_file, '.'));
if(preg_match('/^\d+\-(.*)$/', $name, $match)) $name = $match[1]; // strip numerical prefix from file name
include $path . '/' . $cronjob_file;
$class_name = 'cronjob_' . $name;
$cronjob = new $class_name();
$cronjob->run(true);

die("dokončano odpravljanje napak cron.\n");

?>
