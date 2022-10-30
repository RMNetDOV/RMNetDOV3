<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_relay_recipient.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mail');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

}

$app->tform_actions = new page_action;
$app->tform_actions->onLoad();


?>
