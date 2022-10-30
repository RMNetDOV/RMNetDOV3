<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/remote_user.list.php";
$tform_def_file = "form/remote_user.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('admin');
$app->auth->check_security_permissions('admin_allow_remote_users');

$app->uses('tpl,tform');
$app->load('tform_actions');

// Create a class page_action that extends the tform_actions base class

class page_action extends tform_actions {


	// Customisations for the page actions will be defined here

}

$page = new page_action;
$page->onDelete();

?>
