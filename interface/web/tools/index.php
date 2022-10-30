<?php

global $app, $conf;

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('tools');

$app->uses('tpl');

$app->tpl->newTemplate('listpage.tpl.htm');
$app->tpl->setInclude('content_tpl', 'templates/index.htm');

$lng_file = 'lib/lang/'.$app->functions->check_language($_SESSION['s']['language']).'_index.lng';
include $lng_file;

$app->tpl->setVar($wb);

$app->tpl_defaults();
$app->tpl->pparse();
?>
