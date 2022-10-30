<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/dns_template.list.php";
$tform_def_file = "form/dns_template.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('dns');

$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

}

$page = new page_action;
$page->onDelete();

?>
