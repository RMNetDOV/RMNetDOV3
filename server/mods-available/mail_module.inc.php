<?php

class mail_module {

	var $module_name = 'mail_module';
	var $class_name = 'mail_module';
	var $actions_available = array( 'mail_domain_insert',
		'mail_domain_update',
		'mail_domain_delete',
		'mail_user_insert',
		'mail_user_update',
		'mail_user_delete',
		'mail_access_insert',
		'mail_access_update',
		'mail_access_delete',
		'mail_forwarding_insert',
		'mail_forwarding_update',
		'mail_forwarding_delete',
		'mail_transport_insert',
		'mail_transport_update',
		'mail_transport_delete',
		'mail_get_insert',
		'mail_get_update',
		'mail_get_delete',
		'mail_content_filter_insert',
		'mail_content_filter_update',
		'mail_content_filter_delete',
		'mail_mailinglist_insert',
		'mail_mailinglist_update',
		'mail_mailinglist_delete',
		'spamfilter_users_insert',
		'spamfilter_users_update',
		'spamfilter_users_delete',
		'spamfilter_wblist_insert',
		'spamfilter_wblist_update',
		'spamfilter_wblist_delete'
		);

	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	function onInstall() {
		global $conf;

		if($conf['services']['mail'] == true) {
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

		$app->modules->registerTableHook('mail_access', 'mail_module', 'process');
		$app->modules->registerTableHook('mail_domain', 'mail_module', 'process');
		$app->modules->registerTableHook('mail_forwarding', 'mail_module', 'process');
		$app->modules->registerTableHook('mail_transport', 'mail_module', 'process');
		$app->modules->registerTableHook('mail_user', 'mail_module', 'process');
		$app->modules->registerTableHook('mail_get', 'mail_module', 'process');
		$app->modules->registerTableHook('mail_content_filter', 'mail_module', 'process');
		$app->modules->registerTableHook('mail_mailinglist', 'mail_module', 'process');
		$app->modules->registerTableHook('spamfilter_users', 'mail_module', 'process');
		$app->modules->registerTableHook('spamfilter_wblist', 'mail_module', 'process'); 

		$app->services->registerService('rspamd', 'mail_module', 'restartRspamd');
		$app->services->registerService('postfix', 'mail_module', 'restartPostfix');
	}

	/*
	 This function is called when a change in one of the registered tables is detected.
	 The function then raises the events for the plugins.
	*/

	function process($tablename, $action, $data) {
		global $app;

		switch ($tablename) {
		case 'mail_access':
			if($action == 'i') $app->plugins->raiseEvent('mail_access_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('mail_access_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('mail_access_delete', $data);
			break;
		case 'mail_domain':
			if($action == 'i') $app->plugins->raiseEvent('mail_domain_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('mail_domain_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('mail_domain_delete', $data);
			break;
		case 'mail_forwarding':
			if($action == 'i') $app->plugins->raiseEvent('mail_forwarding_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('mail_forwarding_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('mail_forwarding_delete', $data);
			break;
		case 'mail_transport':
			if($action == 'i') $app->plugins->raiseEvent('mail_transport_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('mail_transport_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('mail_transport_delete', $data);
			break;
		case 'mail_user':
			if($action == 'i') $app->plugins->raiseEvent('mail_user_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('mail_user_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('mail_user_delete', $data);
			break;
		case 'mail_get':
			if($action == 'i') $app->plugins->raiseEvent('mail_get_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('mail_get_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('mail_get_delete', $data);
			break;
		case 'mail_content_filter':
			if($action == 'i') $app->plugins->raiseEvent('mail_content_filter_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('mail_content_filter_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('mail_content_filter_delete', $data);
			break;
		case 'mail_mailinglist':
			if($action == 'i') $app->plugins->raiseEvent('mail_mailinglist_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('mail_mailinglist_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('mail_mailinglist_delete', $data);
			break;
		case 'spamfilter_users':
			if($action == 'i') $app->plugins->raiseEvent('spamfilter_users_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('spamfilter_users_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('spamfilter_users_delete', $data);
			break;
		case 'spamfilter_wblist':
			if($action == 'i') $app->plugins->raiseEvent('spamfilter_wblist_insert', $data);
			if($action == 'u') $app->plugins->raiseEvent('spamfilter_wblist_update', $data);
			if($action == 'd') $app->plugins->raiseEvent('spamfilter_wblist_delete', $data);
			break;
		} // end switch
	} // end function

	function restartRspamd($action = 'reload') {
		global $app;

		$app->uses('system');

		$daemon = 'rspamd';

		$retval = array('output' => '', 'retval' => 0);
		if($action == 'restart') {
			exec($app->system->getinitcommand($daemon, 'restart').' 2>&1', $retval['output'], $retval['retval']);
		} else {
			exec($app->system->getinitcommand($daemon, 'reload').' 2>&1', $retval['output'], $retval['retval']);
		}
		return $retval;
	}
	
	function restartPostfix($action = 'reload') {
		global $app;

		$app->uses('system');

		$daemon = 'postfix';

		$retval = array('output' => '', 'retval' => 0);
		if($action == 'restart') {
			exec($app->system->getinitcommand($daemon, 'restart').' 2>&1', $retval['output'], $retval['retval']);
		} else {
			exec($app->system->getinitcommand($daemon, 'reload').' 2>&1', $retval['output'], $retval['retval']);
		}
		return $retval;
	}
} // end class

?>
