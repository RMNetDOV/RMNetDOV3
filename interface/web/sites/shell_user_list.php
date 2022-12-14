<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/shell_user.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->uses('listform_actions');

// Limit the results to alias domains
//$app->listform_actions->SQLExtWhere = "type = 'subdomain'";

$app->listform_actions->SQLOrderBy = 'ORDER BY shell_user.username';
$app->listform_actions->onLoad();


?>
