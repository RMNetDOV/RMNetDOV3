<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/openvz_vm.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('vm');

$app->uses('listform_actions');

// $app->listform_actions->SQLOrderBy = 'ORDER BY company_name, contact_name, client_id';
//$app->listform_actions->SQLExtWhere = "";
$app->listform_actions->onLoad();


?>
