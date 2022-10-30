<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/web_childdomain.list.php";
$tform_def_file = "form/web_childdomain.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('sites');

//* Get and set the child domain type - store in session
$show_type = 'aliasdomain';
if(isset($_GET['type']) && $_GET['type'] == 'subdomain') $show_type = 'subdomain';
elseif(!isset($_GET['type']) && isset($_SESSION['s']['var']['childdomain_type']) && $_SESSION['s']['var']['childdomain_type'] == 'subdomain') $show_type = 'subdomain';

$_SESSION['s']['var']['childdomain_type'] = $show_type;

$app->uses("tform_actions");
$app->tform_actions->onDelete();

?>
