<?php

class database_module {

	var $module_name = 'database_module';
	var $class_name = 'database_module';
	var $actions_available = array( 'database_insert',
		'database_update',
		'database_delete',
		'database_user_insert',
		'database_user_update',
		'database_user_delete'
	);

	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	function onInstall() {
		global $conf;

		if($conf['services']['db'] == true) {
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

		$app->modules->registerTableHook('web_database', 'database_module', 'process');
		$app->modules->registerTableHook('web_database_user', 'database_module', 'process');

		// Register service
		//$app->services->registerService('httpd','web_module','restartHttpd');

	}

	/*
	 This function is called when a change in one of the registered tables is detected.
	 The function then raises the events for the plugins.
	*/

	function process($tablename, $action, $data) {
		global $app;

		switch ($tablename) {
		case 'web_database':
			if($action == 'i') $app->plugins->raiseEvent('database_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('database_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('database_delete', $data);
			break;

		case 'web_database_user':
			if($action == 'i') $app->plugins->raiseEvent('database_user_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('database_user_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('database_user_delete', $data);
			break;

		} // end switch
	} // end function

} // end class

?>
