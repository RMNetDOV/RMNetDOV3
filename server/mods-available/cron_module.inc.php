<?php

class cron_module {

	var $module_name = 'cron_module';
	var $class_name = 'cron_module';
	var $actions_available = array( 'cron_insert',
		'cron_update',
		'cron_delete');

	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	function onInstall() {
		global $conf;

		return true;

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

		$app->modules->registerTableHook('cron', $this->module_name, 'process');

	}

	/*
	 This function is called when a change in one of the registered tables is detected.
	 The function then raises the events for the plugins.
	*/

	function process($tablename, $action, $data) {
		global $app;

		switch ($tablename) {
		case 'cron':
			if($action == 'i') $app->plugins->raiseEvent('cron_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('cron_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('cron_delete', $data);
			break;
		} // end switch
	} // end function


} // end class

?>
