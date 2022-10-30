<?php

class vm_module {

	var $module_name = 'vm_module';
	var $class_name = 'vm_module';
	var $actions_available = array( 'openvz_vm_insert',
		'openvz_vm_update',
		'openvz_vm_delete',
		'openvz_ip_insert',
		'openvz_ip_update',
		'openvz_ip_delete',
		'openvz_ostemplate_insert',
		'openvz_ostemplate_update',
		'openvz_ostemplate_delete');

	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	function onInstall() {
		global $conf;

		if($conf['services']['vserver'] == true) {
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

		$app->modules->registerTableHook('openvz_vm', $this->module_name, 'process');
		$app->modules->registerTableHook('openvz_ip', $this->module_name, 'process');
		$app->modules->registerTableHook('openvz_ostemplate', $this->module_name, 'process');

	}

	/*
	 This function is called when a change in one of the registered tables is detected.
	 The function then raises the events for the plugins.
	*/

	function process($tablename, $action, $data) {
		global $app;

		switch ($tablename) {
		case 'openvz_vm':
			if($action == 'i') $app->plugins->raiseEvent('openvz_vm_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('openvz_vm_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('openvz_vm_delete', $data);
			break;
		case 'openvz_ip':
			if($action == 'i') $app->plugins->raiseEvent('openvz_ip_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('openvz_ip_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('openvz_ip_delete', $data);
			break;
		case 'openvz_ostemplate':
			if($action == 'i') $app->plugins->raiseEvent('openvz_ostemplate_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('openvz_ostemplate_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('openvz_ostemplate_delete', $data);
			break;
		} // end switch
	} // end function


} // end class

?>
