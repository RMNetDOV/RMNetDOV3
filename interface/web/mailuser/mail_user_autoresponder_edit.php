<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/mail_user_autoresponder.tform.php";

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

	function onShow() {

		$this->id = $_SESSION['s']['user']['mailuser_id'];

		parent::onShow();

	}

	function onSubmit() {

		$this->id = $_SESSION['s']['user']['mailuser_id'];

		//* if autoresponder checkbox not selected, do not save dates
		if (!isset($_POST['autoresponder']) && array_key_exists('autoresponder_start_date', $_POST)) {
			$this->dataRecord['autoresponder_start_date'] = array_map(create_function('$item', 'return 0;'), $this->dataRecord['autoresponder_start_date']);
			$this->dataRecord['autoresponder_end_date'] = array_map(create_function('$item', 'return 0;'), $this->dataRecord['autoresponder_end_date']);
			
			/* To be used when we go to PHP 7.x as min PHP version
			$this->dataRecord['autoresponder_start_date'] = array_map( function ('$item') { 'return 0;' }, $this->dataRecord['autoresponder_start_date']);
			$this->dataRecord['autoresponder_end_date'] = array_map( function ('$item') { 'return 0;' }, $this->dataRecord['autoresponder_end_date']);
			*/
			
		}

		parent::onSubmit();

	}

	function onShowEnd() {
		global $app;
		// Is autoresponder set?
		if ($this->dataRecord['autoresponder'] == 'y') {
			$app->tpl->setVar("ar_active", 'checked="checked"');
		} else {
			$app->tpl->setVar("ar_active", '');
		}

		if($this->dataRecord['autoresponder_subject'] == '') {
			$app->tpl->setVar('autoresponder_subject', $app->tform->lng('autoresponder_subject'));
		} else {
			$app->tpl->setVar('autoresponder_subject', $this->dataRecord['autoresponder_subject'], true);
		}

		parent::onShowEnd();
	}


}

$app->tform_actions = new page_action;
$app->tform_actions->onLoad();

?>
