<?php

require_once '../lib/config.inc.php';
require_once '../lib/app.inc.php';

/*
$module = $_REQUEST["s_mod"];
$page = $_REQUEST["s_pg"];
*/

$module = 'dashboard';
$page = 'dashboard';

if(!preg_match("/^[a-z]{2,20}$/i", $module)) die('module name contains unallowed chars.');
if(!preg_match("/^[a-z]{2,20}$/i", $page)) die('page name contains unallowed chars.');

if(is_file(RMNETDOV_WEB_PATH."/$module/$page.php")) {

	include_once RMNETDOV_WEB_PATH."/$module/$page.php";

	$classname = $module.'_'.$page;
	$page = new $classname();

	$content = $page->render();
	if($page->status == 'OK') {
		echo $content;
	} elseif($page->status == 'REDIRECT') {
		$target_parts = explode(':', $page->target);
		$module = $target_parts[0];
		$page = $target_parts[1];
		if(!preg_match("/^[a-z]{2,20}$/i", $module)) die('target module name contains unallowed chars.');
		if(!preg_match("/^[a-z]{2,20}$/i", $page)) die('target page name contains unallowed chars.');

		if(is_file(RMNETDOV_WEB_PATH."/$module/$page.php")) {
			include_once RMNETDOV_WEB_PATH."/$module/$page.php";

			$classname = $module.'_'.$page;
			$page = new $classname();

			$content = $page->render();
			if($page->status == 'OK') {
				echo $content;
			}
		}

	}

} elseif (is_array($_SESSION["s"]['user']) or is_array($_SESSION["s"]["module"])) {
	// If the user is logged in, we try to load the default page of the module
	die('- error -');
} else {
	die('Page does not exist.');
}

?>
