<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/groups.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('admin');
$app->auth->check_security_permissions('admin_allow_cpuser_group');

// Loading classes
$app->uses('tpl,tform,tform_actions');

// let tform_actions handle the page
$app->tform_actions->onLoad();

?>
