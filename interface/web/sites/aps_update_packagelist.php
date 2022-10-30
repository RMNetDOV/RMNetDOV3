<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->uses('tpl');

$app->tpl->newTemplate('form.tpl.htm');
$app->tpl->setInclude('content_tpl', 'templates/aps_update_packagelist.htm');
$msg = '';
$error = '';

//* load language file
$lng_file = 'lib/lang/'.$app->functions->check_language($_SESSION['s']['language']).'_aps_update_packagelist.lng';
include $lng_file;
$app->tpl->setVar($wb);



$app->tpl->setVar('msg', $msg);
$app->tpl->setVar('error', $error);

$app->tpl_defaults();
$app->tpl->pparse();


?>
