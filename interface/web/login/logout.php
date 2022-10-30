<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

/*
 * Check if the logout is forced
 */
$forceLogout = false;
if (isset($_GET['l']) && ($_GET['l']== 1)) $forceLogout = true;

/*
 * if the admin is logged in as client, then ask, if the admin want't to
 * "re-login" as admin again
 */
if ((isset($_SESSION['s_old']) && ($_SESSION['s_old']['user']['typ'] == 'admin' || $app->auth->has_clients($_SESSION['s_old']['user']['userid']))) &&
	(!$forceLogout)){
	$utype = ($_SESSION['s_old']['user']['typ'] == 'admin' ? 'admin' : 'reseller');
	$lng_file = 'lib/lang/'.$app->functions->check_language($_SESSION['s']['language']).'_login_as.lng';
	include $lng_file;
	echo '
		<br /> <br />	<br /> <br />
		'.str_replace('{UTYPE}', $utype, $wb['login_as_or_logout_txt']).'<br />
		<input type="hidden" name="username" value="' . $_SESSION['s_old']['user']['username'] . '" />
		<input type="hidden" name="password" value="' . $_SESSION['s_old']['user']['passwort'] .'" />
		<input type="hidden" name="s_mod" value="login" />
		<input type="hidden" name="s_pg" value="index" />
		<input type="hidden" name="login_as" value="1" />
	    <div class="wf_actions buttons">
			  <button class="btn btn-default formbutton-success" type="button" value="' . sprintf($wb['btn_reloginas_txt'], $utype) . '" data-submit-form="pageForm" data-form-action="/login/index.php"><span>' . sprintf($wb['btn_reloginas_txt'], $utype) . '</span></button>
				<button class="btn btn-default formbutton-default" type="button" value="' . $wb['btn_nologout_txt'] . '" data-load-content="login/logout.php?l=1"><span>' . $wb['btn_nologout_txt'] . '</span></button>
			</div>
	';
	exit;
}

$app->plugin->raiseEvent('logout', true);

$_SESSION["s"]["user"] = null;
$_SESSION["s"]["module"] = null;
$_SESSION['s_old'] = null;

//header("Location: ../index.php?phpsessid=".$_SESSION["s"]["id"]);

if($_SESSION["s"]["site"]["logout"] != '') {
	echo 'URL_REDIRECT:'.$_SESSION["s"]["site"]["logout"];
} else {
	if($conf["interface_logout_url"] != '') {
		echo 'URL_REDIRECT:'.$conf["interface_logout_url"];
	} else {
		echo 'URL_REDIRECT:index.php';
	}
}
// Destroy the session completely now
$_SESSION = array();
session_destroy();
session_write_close();
?>
