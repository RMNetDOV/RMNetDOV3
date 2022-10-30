<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_user_password.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mailuser');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onSubmit() {
		global $app, $conf;
		
		$this->id = $app->functions->intval($_SESSION['s']['user']['mailuser_id']);

		parent::onSubmit();

	}

	function onShowEnd() {
		global $app, $conf;

		$rec = $app->tform->getDataRecord($_SESSION['s']['user']['mailuser_id']);
		$app->tpl->setVar("email", $app->functions->idn_decode($rec['email']), true);

		parent::onShowEnd();
	}

}

$app->tform_actions = new page_action;
$app->tform_actions->onLoad();

?>
