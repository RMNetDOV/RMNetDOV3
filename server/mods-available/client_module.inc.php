<?php

class client_module {

	var $module_name = 'client_module';
	var $class_name = 'client_module';
	var $actions_available = array( 'client_insert',
		'client_update',
		'client_delete');

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

		$app->modules->registerTableHook('client', $this->module_name, 'process');

	}

	/*
	 This function is called when a change in one of the registered tables is detected.
	 The function then raises the events for the plugins.
	*/

	function process($tablename, $action, $data) {
		global $app;

		switch ($tablename) {
		case 'client':
			if($action == 'i') $app->plugins->raiseEvent('client_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('client_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('client_delete', $data);
			break;
		} // end switch
	} // end function


} // end class

?>
