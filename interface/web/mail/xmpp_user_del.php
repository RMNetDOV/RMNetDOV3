<?php

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/xmpp_user.list.php";
$tform_def_file = "form/xmpp_user.tform.php";

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

	function onBeforeDelete() {
		global $app, $conf;

        $jid_parts = explode("@", $this->dataRecord['jid']);
		$domain = $jid_parts[1];

        // check if domain is managed through mail domain
        // if yes, manual deletion is not allowed
        //$app->error('blubb');


	}

}

$page = new page_action;
$page->onDelete();

?>
