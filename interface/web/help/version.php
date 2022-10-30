<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/user_settings.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('help');

if($_SESSION['s']['user']['typ'] == 'admin') {
	echo '<p>&nbsp;</p><p>&nbsp;</p><p class="frmTextHead" style="text-align:center;">'.$app->lng('..::RM-Net - DOV Control Panel::.. Verzija:').' '.RMNETDOV_APP_VERSION.'</p>';
}

?>
