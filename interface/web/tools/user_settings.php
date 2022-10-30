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
$app->auth->check_module_permissions('tools');

// Loading classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

class page_action extends tform_actions {

	function onLoad() {
		global $app, $conf, $tform_def_file;

		// Loading template classes and initialize template
		if(!is_object($app->tpl)) $app->uses('tpl');
		if(!is_object($app->tform)) $app->uses('tform');

		$app->tpl->newTemplate("tabbed_form.tpl.htm");

		// Load table definition from file
		$app->tform->loadFormDef($tform_def_file);

		// Importing ID
		$this->id = $app->functions->intval($_SESSION['s']['user']['userid']);
		$_POST['id'] = $_SESSION['s']['user']['userid'];

		if(count($_POST) > 1) {
			$this->dataRecord = $_POST;
			$this->onSubmit();
		} else {
			$this->onShow();
		}
	}

	function onInsert() {
		die('No inserts allowed.');
	}

	function onBeforeUpdate() {
		global $app, $conf;

		if($conf['demo_mode'] == true && $this->id <= 3) $app->tform->errorMessage .= 'This function is disabled in demo mode.';

		if($_POST['passwort'] != $_POST['repeat_password']) {
			$app->tform->errorMessage = $app->tform->lng('password_mismatch');
		}

		$language = $app->functions->check_language($_POST['language']);
		$_SESSION['s']['user']['language'] = $language;
		$_SESSION['s']['language'] = $language;
	}

	function onAfterUpdate() {
		global $app;

		if($_POST['passwort'] != '') {
			$tmp_user = $app->db->queryOneRecord("SELECT passwort FROM sys_user WHERE userid = ?", $_SESSION['s']['user']['userid']);
			$_SESSION['s']['user']['passwort'] = $tmp_user['passwort'];
			unset($tmp_user);
		}
		$this->updateSessionTheme();

		if($this->_theme_changed == true) {
			// not the best way, but it works
			header('Content-Type: text/html');
			print '<script type="text/javascript">document.location.reload();</script>';
			exit;
		}
	}
	var $_theme_changed = false;

	function updateSessionTheme() {
		global $app, $conf;

		if($this->dataRecord['app_theme'] != 'default') {
			$tmp_path = RMNETDOV_THEMES_PATH."/".$this->dataRecord['app_theme'];
			if(!@is_dir($tmp_path) || (@file_exists($tmp_path."/rmnetdov_version") && trim(file_get_contents($tmp_path."/rmnetdov_version")) != RMNETDOV_APP_VERSION)) {
				// fall back to default theme if this one is not compatible with current rmnetdov version
				$this->dataRecord['app_theme'] = 'default';
			}
		}
		if($this->dataRecord['app_theme'] != $_SESSION['s']['user']['theme']) $this->_theme_changed = true;
		$_SESSION['s']['theme'] = $this->dataRecord['app_theme'];
		$_SESSION['s']['user']['theme'] = $_SESSION['s']['theme'];
		$_SESSION['s']['user']['app_theme'] = $_SESSION['s']['theme'];
	}

	function onAfterInsert() {
		$this->onAfterUpdate();
	}

}

$page = new page_action;
$page->onLoad();

?>
