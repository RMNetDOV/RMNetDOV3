<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_relay_domain.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('mail');

//* Only administrators allowed
if(! $app->auth->is_admin()) { die( $app->lng("non_admin_error") ); }

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onSubmit() {
		global $app, $conf;

		//* make sure that the email domain is lowercase
		if(isset($this->dataRecord["domain"])){
			$this->dataRecord["domain"] = $app->functions->idn_encode($this->dataRecord["domain"]);
			$this->dataRecord["domain"] = strtolower($this->dataRecord["domain"]);
		}

		//* server_id must be > 0
		if(isset($this->dataRecord["server_id"]) && $this->dataRecord["server_id"] < 1) {
			$app->tform->errorMessage .= $app->lng("server_id_0_error_txt");
		}

		parent::onSubmit();
	}

}

$app->tform_actions = new page_action;
$app->tform_actions->onLoad();

