<?php

define('SCRIPT_PATH', dirname($_SERVER["SCRIPT_FILENAME"]));
require SCRIPT_PATH."/lib/config.inc.php";

// Check whether another instance of this script is already running
$lockFile = $conf['temppath'] . $conf['fs_div'] . '.rmnetdov_cron_lock';
if(is_file($lockFile)) {
	clearstatcache();
	
// Check if the process id we have in the lock file is still present
	$pid = trim(file_get_contents($lockFile));
	if(preg_match('/^[0-9]+$/', $pid)) {
		if(is_dir('/proc/' . $pid)) {
			if(file_exists('/proc/' . $pid . '/cmdline')) {
				if(strpos(file_get_contents('/proc/' . $pid . '/cmdline'), 'cron.php') !== false) {
					if($conf['log_priority'] <= LOGLEVEL_WARN) print @date('d.m.Y-H:i').' - OPOZORILO - Obstaja že primerek cron.php, ki se izvaja s pid ' . $pid . '.' . "\n";
					exit;
				} else {
					if($conf['log_priority'] <= LOGLEVEL_WARN) print @date('d.m.Y-H:i').' - OPOZORILO - Obstaja proces, ki teče s pid ' . $pid . ' vendar se zdi, da ni cron.php, nadaljuje.' . "\n";
				}
			} else {
				if(filemtime($lockFile) < time() - 86400) {
					if($conf['log_priority'] <= LOGLEVEL_WARN) print @date('d.m.Y-H:i').' - OPOZORILO - Obstaja že primerek cron.php, ki se izvaja s pid ' . $pid . ' vendar je postopek starejši od 1 dneva. Nadaljevanje.' . "\n";
				} else {
					if($conf['log_priority'] <= LOGLEVEL_WARN) print @date('d.m.Y-H:i').' - OPOZORILO - Obstaja že primerek cron.php, ki se izvaja s pid ' . $pid . '.' . "\n";
					exit;
				}
			}
		} else {
			if($conf['log_priority'] <= LOGLEVEL_WARN) print @date('d.m.Y-H:i').' - OPOZORILO - Datoteka zaklepanja je že nastavljena, vendar se s tem pidom ne izvaja noben proces (' . $pid . '). Nadaljevanje.' . "\n";

		}
	}
}

// Set Lockfile
@file_put_contents($lockFile, getmypid());

if($conf['log_priority'] <= LOGLEVEL_DEBUG) print 'Set Lock: ' . $conf['temppath'] . $conf['fs_div'] . '.rmnetdov_cron_lock' . "\n";


require SCRIPT_PATH."/lib/app.inc.php";

set_time_limit(0);
ini_set('error_reporting', E_ALL & ~E_NOTICE);

// make sure server_id is always an int
$conf['server_id'] = intval($conf['server_id']);


// Load required base-classes
$app->uses('modules,ini_parser,file,services,getconf,system,cron,functions,plugins');
$app->load('libdatetime,cronjob');

// read all cron jobs
$path = SCRIPT_PATH . '/lib/classes/cron.d';
if(!is_dir($path)) die('Cron path missing!');
$files = array();
$d = opendir($path);
while($f = readdir($d)) {
	$file_path = $path . '/' . $f;
	if($f === '.' || $f === '..' || !is_file($file_path)) continue;
	if(substr($f, strrpos($f, '.')) !== '.php') continue;
	$files[] = $f;
}
closedir($d);

// sort in alphabetical order, so we can use prefixes like 000-xxx
sort($files);

foreach($files as $f) {
	$name = substr($f, 0, strpos($f, '.'));
	if(preg_match('/^\d+\-(.*)$/', $name, $match)) $name = $match[1]; // strip numerical prefix from file name

	include $path . '/' . $f;
	$class_name = 'cronjob_' . $name;

	if(class_exists($class_name, false)) {
		$cronjob = new $class_name();
		if(get_parent_class($cronjob) !== 'cronjob') {
			if($conf['log_priority'] <= LOGLEVEL_WARN) print 'Neveljaven razred ' . $class_name . ' brez razširitve razreda cronjob (' . get_parent_class($cronjob) . ')!' . "\n";
			unset($cronjob);
			continue;
		}
		if($conf['log_priority'] <= LOGLEVEL_DEBUG) print 'Vključeno ' . $class_name . ' od ' . $path . '/' . $f . ' -> bo zdaj opravil delo.' . "\n";

		$cronjob->run();

		if($conf['log_priority'] <= LOGLEVEL_DEBUG) print 'teči delo (' . $class_name . ') končano.' . "\n";

		unset($cronjob);
	}
}
unset($files);

$app->services->processDelayedActions();

// Remove lock
@unlink($conf['temppath'] . $conf['fs_div'] . '.rmnetdov_cron_lock');
$app->log('Remove Lock: ' . $conf['temppath'] . $conf['fs_div'] . '.rmnetdov_cron_lock', LOGLEVEL_DEBUG);

if($conf['log_priority'] <= LOGLEVEL_DEBUG) die("finished cron run.\n");

?>
