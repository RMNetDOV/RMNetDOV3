<?php

class dns_module {

	var $module_name = 'dns_module';
	var $class_name = 'dns_module';
	var $actions_available = array( 'dns_soa_insert',
		'dns_soa_update',
		'dns_soa_delete',
		'dns_slave_insert',
		'dns_slave_update',
		'dns_slave_delete',
		'dns_rr_insert',
		'dns_rr_update',
		'dns_rr_delete');

	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	function onInstall() {
		global $conf;

		if($conf['services']['dns'] == true) {
			return true;
		} else {
			return false;
		}

	}

	/*
	 	This function is called when the module is loaded
	*/

	function onLoad() {
		global $app;

		/*
		Annonce the actions that where provided by this module, so plugins
		can register on them.
		*/

		$app->plugins->announceEvents($this->module_name, $this->actions_available);

		/*
		As we want to get notified of any changes on several database tables,
		we register for them.

		The following function registers the function "functionname"
 		to be executed when a record for the table "dbtable" is
 		processed in the sys_datalog. "classname" is the name of the
 		class that contains the function functionname.
		*/

		$app->modules->registerTableHook('dns_soa', $this->module_name, 'process');
		$app->modules->registerTableHook('dns_slave', $this->module_name, 'process');
		$app->modules->registerTableHook('dns_rr', $this->module_name, 'process');


		// Register service
		$app->services->registerService('bind', 'dns_module', 'restartBind');
		$app->services->registerService('powerdns', 'dns_module', 'restartPowerDNS');

	}

	/*
	 This function is called when a change in one of the registered tables is detected.
	 The function then raises the events for the plugins.
	*/

	function process($tablename, $action, $data) {
		global $app;

		switch ($tablename) {
		case 'dns_soa':
			if($action == 'i') $app->plugins->raiseEvent('dns_soa_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('dns_soa_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('dns_soa_delete', $data);
			break;
		case 'dns_slave':
			if($action == 'i') $app->plugins->raiseEvent('dns_slave_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('dns_slave_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('dns_slave_delete', $data);
			break;
		case 'dns_rr':
			if($action == 'i') $app->plugins->raiseEvent('dns_rr_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('dns_rr_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('dns_rr_delete', $data);
			break;
		} // end switch
	} // end function


	function restartBind($action = 'restart') {
		global $app, $conf;

		$app->uses('system');

		$daemon = '';
		if(is_file($conf['init_scripts'] . '/' . 'bind9')) {
			$daemon = 'bind9';
		} else {
			$daemon = 'named';
		}

		$retval = array('output' => '', 'retval' => 0);
		if($action == 'restart') {
			exec($app->system->getinitcommand($daemon, 'restart').' 2>&1', $retval['output'], $retval['retval']);
		} else {
			exec($app->system->getinitcommand($daemon, 'reload').' 2>&1', $retval['output'], $retval['retval']);
		}
		return $retval;
	}

	function restartPowerDNS($action = 'restart') {
		global $app, $conf;

		$app->uses('system');
		$app->log("restartPDNS called.", LOGLEVEL_DEBUG);

		/**     Since PowerDNS does not currently allow to limit AXFR for specific zones to specific
		 *  IP addresses, we create a list of IPs allowed of AXFR transfers from our PowerDNS,
		 *  however any of these IPs is allowed to AXFR transfer any of the zones we are masters
		 *  for.
		 */


		$tmps = $app->db->queryAllRecords("SELECT DISTINCT xfer FROM dns_soa WHERE active = 'Y' UNION SELECT DISTINCT xfer FROM dns_slave WHERE active = 'Y' ");

		//* Make sure the list is never empty
		$options='127.0.0.1';
		foreach($tmps as $tmp) {
			if (trim($tmp['xfer'])!='') {
				if ($options=='') {
					$options.=$tmp['xfer'];
				} else {
					$options=$options.",".$tmp['xfer'];
				}
			}
		}

		//* Remove duplicate IPs from the array
		$options = "allow-axfr-ips=".implode(",", array_unique(explode(",", $options)));
		$app->log("".$options, LOGLEVEL_DEBUG);



		/**  Not an ideal way to use a hardcoded path like that, but currently
		 *  we have no way to find out where powerdns' configuration files are
		 *  located, so we have to work on assumption.
		 */
		file_put_contents('/etc/powerdns/pdns.d/pdns.rmnetdov-axfr', $options."\n");

		$daemon= '';
		if (is_file($conf['init_scripts'] . '/' . 'powerdns')) {
			$daemon = 'powerdns';
		} else {
			$daemon = 'pdns';
		}

		$retval = array('output' => '', 'retval' => 0);
		exec($app->system->getinitcommand($daemon, 'restart').' 2>&1', $retval['output'], $retval['retval']);

		//     unset $tmps;
		return $retval;

	}


} // end class

?>
