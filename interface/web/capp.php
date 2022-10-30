<?php

require_once '../lib/config.inc.php';
require_once '../lib/app.inc.php';

include_once 'common.php';

//* Import module variable
$mod = $_REQUEST["mod"];
//* If we click on a search result, load that one instead of the module's start page
$redirect = (isset($_REQUEST["redirect"]) ? $_REQUEST["redirect"] : '');

//* Check if user is logged in
if ($_SESSION["s"]["user"]['active'] != 1) {
	die("URL_REDIRECT: /index.php");
	//die();
}

if (!preg_match("/^[a-z]{2,20}$/i", $mod)) die('module name contains unallowed chars.');
if ($redirect != '' && !preg_match("/^[a-z0-9]+\/[a-z0-9_\.\-]+\?id=[0-9]{1,9}(\&type=[a-z0-9_\.\-]+)?$/i", $redirect)) die('redirect contains unallowed chars.');

//* Check if user may use the module.
$user_modules = explode(",", $_SESSION["s"]["user"]["modules"]);

if (!in_array($mod, $user_modules)) $app->error($app->lng(301));

//* Load module configuration into the session.
if (is_file($mod."/lib/module.conf.php")) {
	include_once $mod."/lib/module.conf.php";

	$menu_dir = RMNETDOV_WEB_PATH.'/'.$mod.'/lib/menu.d';
	include_menu_dir_files($menu_dir);

	$_SESSION["s"]["module"] = $module;
	session_write_close();
	if ($redirect == '') {
		echo "HEADER_REDIRECT:".$_SESSION["s"]["module"]["startpage"];
	} else {
		//* If we click on a search result, load that one instead of the module's start page
		echo "HEADER_REDIRECT:".$redirect;
	}
} else {
	$app->error($app->lng(302));
}
?>
