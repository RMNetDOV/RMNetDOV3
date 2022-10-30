<?php

define('SCRIPT_PATH', dirname($_SERVER["SCRIPT_FILENAME"]));
require SCRIPT_PATH."/lib/config.inc.php";

// Check whether another instance of this script is already running
if (is_file($conf['temppath'] . $conf['fs_div'] . '.rmnetdov_lock')) {
	clearstatcache();
	$pid = trim(file_get_contents($conf['temppath'] . $conf['fs_div'] . '.rmnetdov_lock'));
	if(preg_match('/^[0-9]+$/', $pid)) {
		if(file_exists('/proc/' . $pid)) {
			print @date('d.m.Y-H:i').' - OPOZORILO – Primerek server.php se že izvaja s pid ' . $pid . '.' . "\n";
			exit;
		}
	}
	print @date('d.m.Y-H:i').' - OPOZORILO - Datoteka zaklepanja je že nastavljena, vendar se s tem pidom ne izvaja noben proces (' . $pid . '). Continuing.' . "\n";
}

// Set Lockfile
@file_put_contents($conf['temppath'] . $conf['fs_div'] . '.rmnetdov_lock', getmypid());

if($conf['log_priority'] <= LOGLEVEL_DEBUG) print 'Nastavite zaklepanje: ' . $conf['temppath'] . $conf['fs_div'] . '.rmnetdov_lock' . "\n";

require SCRIPT_PATH."/lib/app.inc.php";

$app->setCaller('server');

set_time_limit(0);
ini_set('error_reporting', E_ALL & ~E_NOTICE);

// make sure server_id is always an int
$conf['server_id'] = intval($conf['server_id']);

/*
 * Try to Load the server configuration from the master-db
 */
if ($app->dbmaster->testConnection()) {
	$server_db_record = $app->dbmaster->queryOneRecord("SELECT * FROM server WHERE server_id = ?", $conf['server_id']);

	if(!is_array($server_db_record)) die('Ni mogoče naložiti konfiguracije strežnika iz baze podatkov.');

	//* Get the number of the last processed datalog_id, if the id of the local server
	//* is > then the one of the remote system, then use the local ID as we might not have
	//* reached the remote server during the last run then.
	$local_server_db_record = $app->db->queryOneRecord("SELECT * FROM server WHERE server_id = ?", $conf['server_id']);
	$conf['last_datalog_id'] = (int) max($server_db_record['updated'], $local_server_db_record['updated']);
	unset($local_server_db_record);

	$conf['mirror_server_id'] = (int) $server_db_record['mirror_server_id'];

	// Load the ini_parser
	$app->uses('ini_parser');

	// Get server configuration
	$conf['serverconfig'] = $app->ini_parser->parse_ini_string(stripslashes($server_db_record['config']));

	// Set the loglevel
	$conf['log_priority'] = intval($conf['serverconfig']['server']['loglevel']);

	// Set level from which admin should be notified by email
	if(!isset($conf['serverconfig']['server']['admin_notify_events']) || $conf['serverconfig']['server']['admin_notify_events'] == '') $conf['serverconfig']['server']['admin_notify_events'] = 3;
	$conf['admin_notify_priority'] = intval($conf['serverconfig']['server']['admin_notify_events']);

	// we do not need this variable anymore
	unset($server_db_record);

	// retrieve admin email address for notifications
	$sys_ini = $app->db->queryOneRecord("SELECT * FROM sys_ini WHERE sysini_id = 1");
	$conf['sys_ini'] = $app->ini_parser->parse_ini_string(stripslashes($sys_ini['config']));
	$conf['admin_mail'] = $conf['sys_ini']['mail']['admin_mail'];
	unset($sys_ini);

	/*
	 * Save the rescue-config, maybe we need it (because the database is down)
	 */
	$tmp['serverconfig']['server']['loglevel'] = $conf['log_priority'];
	$tmp['serverconfig']['rescue'] = $conf['serverconfig']['rescue'];
	file_put_contents(dirname(__FILE__) . "/temp/rescue_module_serverconfig.ser.txt", serialize($tmp));
	unset($tmp);

	// protect the file
	chmod(dirname(__FILE__) . "/temp/rescue_module_serverconfig.ser.txt", 0600);

} else {
	/*
	 * The master-db is not available.
	 * Problem: because we need to start the rescue-module (to rescue the DB if this IS the
	 * server, the master-db is running at) we have to initialize some config...
	 */

	/*
	 * If there is a temp-file with the data we could get from the database, then we use it
	 */
	$tmp = array();
	if (file_exists(dirname(__FILE__) . "/temp/rescue_module_serverconfig.ser.txt")){
		$tmp = unserialize(file_get_contents(dirname(__FILE__) . "/temp/rescue_module_serverconfig.ser.txt"));
	}

	// maxint at 32 and 64 bit systems
	$conf['last_datalog_id'] = intval('9223372036854775807');

	// no mirror
	$conf['mirror_server_id'] = 0;

	// Set the loglevel
	$conf['log_priority'] = (isset($tmp['serverconfig']['server']['loglevel']))? $tmp['serverconfig']['server']['loglevel'] : LOGLEVEL_ERROR;
	/*
	 * Set the configuration to rescue the database
	 */
	if (isset($tmp['serverconfig']['rescue'])){
		$conf['serverconfig']['rescue'] = $tmp['serverconfig']['rescue'];
	}
	else{
		$conf['serverconfig']['rescue']['try_rescue'] = 'n';
	}
	// we do not need this variable anymore
	unset($tmp);
}

/** Do we need to start the core-modules */


$needStartCore = true;

/*
 * Next we try to process the datalog
 */
if ($app->db->testConnection() && $app->dbmaster->testConnection()) {

	// Check if there is anything to update
	if ($conf['mirror_server_id'] > 0) {
		$tmp_rec = $app->dbmaster->queryOneRecord("SELECT count(server_id) as number from sys_datalog WHERE datalog_id > ? AND (server_id = ? OR server_id = ? OR server_id = 0)", $conf['last_datalog_id'], $conf['server_id'], $conf['mirror_server_id']);
	} else {
		$tmp_rec = $app->dbmaster->queryOneRecord("SELECT count(server_id) as number from sys_datalog WHERE datalog_id > ? AND (server_id = ? OR server_id = 0)", $conf['last_datalog_id'], $conf['server_id']);
	}

	$tmp_num_records = $tmp_rec['number'];
	unset($tmp_rec);

	//** Load required base-classes
	$app->uses('modules,plugins,file,services,system');
	//** Load the modules that are in the mods-enabled folder
	$app->modules->loadModules('all');
	//** Load the plugins that are in the plugins-enabled folder
	$app->plugins->loadPlugins('all');
	
	$app->plugins->raiseAction('server_plugins_loaded', '');
	
	if ($tmp_num_records > 0) {
		$app->log("Najdeno $tmp_num_records spremembe, začetek postopka posodobitve.", LOGLEVEL_DEBUG);
		//** Go through the sys_datalog table and call the processing functions
		//** from the modules that are hooked on to the table actions
		$app->modules->processDatalog();
	}
	//** Process actions from sys_remoteaction table
	$app->modules->processActions();
	//** Restart services that need to after configuration
	$app->services->processDelayedActions();
	//** All modules are already loaded and processed, so there is NO NEED to load the core once again...
	$needStartCore = false;

} else {
	if (!$app->db->connect->testConnection()) {
		$app->log('Ni mogoče vzpostaviti povezave z lokalnim strežnikom.' . $app->db->errorMessage, LOGLEVEL_WARN);
	} else {
		$app->log('Ni mogoče vzpostaviti povezave z glavnim strežnikom.' . $app->dbmaster->errorMessage, LOGLEVEL_WARN);
	}
}

/*
 * Under normal circumstances the system was loaded and all updates are done.
 * but if we do not have to update anything or if the database is not accessible, then we
 * have to start the core-system (if the database is accessible, we need the core because of the
 * monitoring. If the databse is NOT accessible, we need the core because of rescue the db...
 */
if ($needStartCore) {
	// Write the log
	$app->log('Posodobljenih zapisov ni bilo mogoče najti, zagon samo jedra.', LOGLEVEL_DEBUG);
	// Load required base-classes
	$app->uses('modules,plugins,file,services');
	// Load the modules that are im the mods-core folder
	$app->modules->loadModules('core');
	// Load the plugins that are in the f folder
	//$app->plugins->loadPlugins('core');
}


// Remove lock
@unlink($conf['temppath'] . $conf['fs_div'] . '.rmnetdov_lock');
$app->log('Remove Lock: ' . $conf['temppath'] . $conf['fs_div'] . '.rmnetdov_lock', LOGLEVEL_DEBUG);


die("finished server.php.\n");
?>
