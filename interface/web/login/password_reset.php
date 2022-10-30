<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$app->load('getconf');

$security_config = $app->getconf->get_security_config('permissions');
if($security_config['password_reset_allowed'] != 'yes') die('Password reset function has been disabled.');

// Loading the template
$app->uses('tpl');
$app->tpl->newTemplate('main_login.tpl.htm');
$app->tpl->setInclude('content_tpl', 'templates/password_reset.htm');

$app->tpl_defaults();

include RMNETDOV_ROOT_PATH.'/web/login/lib/lang/'.$app->functions->check_language($conf['language']).'.lng';
$app->tpl->setVar($wb);
$continue = true;

if(isset($_POST['username']) && is_string($_POST['username']) && $_POST['username'] != '' && isset($_POST['email']) && is_string($_POST['email']) && $_POST['email'] != '' && $_POST['username'] != 'admin') {
	if(!preg_match("/^[\w\.\-\_]{1,64}$/", $_POST['username'])) {
		$app->tpl->setVar("error", $wb['user_regex_error']);
		$continue = false;
	}
	if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$app->tpl->setVar("error", $wb['email_error']);
		$continue = false;
	}

	$username = $_POST['username'];
	$email = $_POST['email'];

	if($continue) {
		$client = $app->db->queryOneRecord("SELECT client.*, sys_user.lost_password_function, sys_user.lost_password_hash, IF(sys_user.lost_password_reqtime IS NOT NULL AND DATE_SUB(NOW(), INTERVAL 15 MINUTE) < sys_user.lost_password_reqtime, 1, 0) as `lost_password_wait` FROM client,sys_user WHERE client.username = ? AND client.email = ? AND client.client_id = sys_user.client_id", $username, $email);
	}

	if($client && $client['lost_password_function'] == 0) {
		$app->tpl->setVar("error", $wb['lost_password_function_disabled_txt']);
	} elseif($client && $client['lost_password_wait'] == 1) {
		$app->tpl->setVar("error", $wb['lost_password_function_wait_txt']);
	} elseif ($continue) {
		if($client['client_id'] > 0) {
			$username = $client['username'];
			$password_hash = sha1(random_bytes(20));
			$app->db->query("UPDATE sys_user SET lost_password_reqtime = NOW(), lost_password_hash = ? WHERE username = ?", $password_hash, $username);

			$server_domain = (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST']);
			if($server_domain == '_') {
				$tmp = explode(':',$_SERVER["HTTP_HOST"]);
				$server_domain = $tmp[0];
				unset($tmp);
			}
			if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') $server_domain = 'http://' . $server_domain;
			else $server_domain = 'https://' . $server_domain;

			if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '443') $server_domain .= ':' . $_SERVER['SERVER_PORT'];

			$app->uses('getconf,rmnetdovmail');
			$server_config_array = $app->getconf->get_global_config();
			$mail_config = $server_config_array['mail'];
			if($mail_config['smtp_enabled'] == 'y') {
				$mail_config['use_smtp'] = true;
				$app->rmnetdovmail->setOptions($mail_config);
			}
			$app->rmnetdovmail->setSender($mail_config['admin_mail'], $mail_config['admin_name']);
			$app->rmnetdovmail->setSubject($wb['pw_reset_act_mail_title']);
			$app->rmnetdovmail->setMailText($wb['pw_reset_act_mail_msg'].$server_domain . '/login/password_reset.php?username=' . urlencode($username) . '&hash=' . urlencode($password_hash));
			$send_result = $app->rmnetdovmail->send(array($client['contact_name'] => $client['email']));
			$app->rmnetdovmail->finish();

			if($send_result !== false) {
				$app->tpl->setVar("msg", $wb['pw_reset_act']);
			} else {
				$app->tpl->setVar("error", $wb['pw_reset_error_smtp_connection']);
			}
		} else {
			$app->tpl->setVar("error", $wb['pw_error']);
		}
	}
} elseif(isset($_GET['username']) && $_GET['username'] != '' && $_GET['hash'] != '') {

	if(!preg_match("/^[\w\.\-\_]{1,64}$/", $_GET['username'])) {
		$app->tpl->setVar("error", $wb['user_regex_error']);
		$continue = false;
	}

	$username = $_GET['username'];
	$hash = $_GET['hash'];

	$client = $app->db->queryOneRecord("SELECT client.*, sys_user.lost_password_function, sys_user.lost_password_hash, IF(sys_user.lost_password_reqtime IS NULL OR DATE_SUB(NOW(), INTERVAL 1 DAY) > sys_user.lost_password_reqtime, 1, 0) as `lost_password_expired` FROM client,sys_user WHERE client.username = ? AND client.client_id = sys_user.client_id", $username);

	if($client['lost_password_function'] == 0) {
		$app->tpl->setVar("error", $wb['lost_password_function_disabled_txt']);
	} elseif($client['lost_password_expired'] == 1) {
		$app->tpl->setVar("error", $wb['lost_password_function_expired_txt']);
	} elseif($client['lost_password_hash'] != $hash) {
		$app->tpl->setVar("error", $wb['lost_password_function_denied_txt']);
	} elseif ($continue) {
		if($client['client_id'] > 0) {
			$server_config_array = $app->getconf->get_global_config();
			$min_password_length = $app->auth->get_min_password_length();

			$new_password = $app->auth->get_random_password($min_password_length, true);
			$new_password_encrypted = $app->auth->crypt_password($new_password);

			$username = $client['username'];
			$app->db->query("UPDATE sys_user SET passwort = ?, lost_password_hash = '', lost_password_reqtime = NULL WHERE username = ?", $new_password_encrypted, $username);
			$app->db->query("UPDATE client SET password = ? WHERE username = ?", $new_password_encrypted, $username);

			$app->uses('getconf,rmnetdovmail');
			$mail_config = $server_config_array['mail'];
			if($mail_config['smtp_enabled'] == 'y') {
				$mail_config['use_smtp'] = true;
				$app->rmnetdovmail->setOptions($mail_config);
			}
			$app->rmnetdovmail->setSender($mail_config['admin_mail'], $mail_config['admin_name']);
			$app->rmnetdovmail->setSubject($wb['pw_reset_mail_title']);
			$app->rmnetdovmail->setMailText($wb['pw_reset_mail_msg'].$new_password);
			$send_result = $app->rmnetdovmail->send(array($client['contact_name'] => $client['email']));
			$app->rmnetdovmail->finish();

			$app->plugin->raiseEvent('password_reset', true);

			if($send_result !== false) {
				$app->tpl->setVar("msg", $wb['pw_reset']);
				$app->tpl->setInclude('content_tpl', 'templates/index.htm');
			} else {
				$app->tpl->setVar("error", $wb['pw_reset_error_smtp_connection']);
			}

		} else {
			$app->tpl->setVar("error", $wb['pw_error']);
		}
	}
} else {
	if(isset($_POST) && count($_POST) > 0) $app->tpl->setVar("msg", $wb['pw_error_noinput']);
}

$app->tpl->setVar('current_theme', isset($_SESSION['s']['theme']) ? $_SESSION['s']['theme'] : 'default', true);

// Logo
$logo = $app->db->queryOneRecord("SELECT * FROM sys_ini WHERE sysini_id = 1");
if($logo['custom_logo'] != ''){
	$base64_logo_txt = $logo['custom_logo'];
} else {
	$base64_logo_txt = $logo['default_logo'];
}
$tmp_base64 = explode(',', $base64_logo_txt, 2);
$logo_dimensions = $app->functions->getimagesizefromstring(base64_decode($tmp_base64[1]));
$app->tpl->setVar('base64_logo_width', $logo_dimensions[0].'px');
$app->tpl->setVar('base64_logo_height', $logo_dimensions[1].'px');
$app->tpl->setVar('base64_logo_txt', $base64_logo_txt);

// Title
$sys_config = $app->getconf->get_global_config('misc');
if (!empty($sys_config['company_name'])) {
	$app->tpl->setVar('company_name', $sys_config['company_name']. ' :: ');
}

$app->tpl_defaults();
$app->tpl->pparse();





?>
