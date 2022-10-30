<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/client_circle.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('client');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

}

$page = new page_action;
$page->onLoad();

?>
