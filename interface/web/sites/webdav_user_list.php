<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/webdav_user.list.php";

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->uses('listform_actions');
$app->listform_actions->SQLOrderBy = 'ORDER BY webdav_user.username';
$app->listform_actions->onLoad();


?>
