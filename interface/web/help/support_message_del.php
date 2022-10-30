<?php

//* From and List definition files
$list_def_file = 'list/support_message.list.php';
$tform_def_file = 'form/support_message.tform.php';

//* Include the base libraries
require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('help');

//* Load the form
$app->uses('tform_actions');
$app->tform_actions->onDelete();

?>
