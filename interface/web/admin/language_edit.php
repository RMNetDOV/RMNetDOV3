<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('admin');
$app->auth->check_security_permissions('admin_allow_langedit');

//* This is only allowed for administrators
if(!$app->auth->is_admin()) die('only allowed for administrators.');
if($conf['demo_mode'] == true) $app->error('This function is disabled in demo mode.');

$app->uses('tpl');

$app->tpl->newTemplate('form.tpl.htm');
$app->tpl->setInclude('content_tpl', 'templates/language_edit.htm');

$lang = $_REQUEST['lang'];
$module = $_REQUEST['module'];
$lang_file = $_REQUEST['lang_file'];

if(!preg_match("/^[a-z]+$/i", $lang)) die('unallowed characters in language name.');
if(!preg_match("/^[a-z_]+$/i", $module)) die('unallowed characters in module name.');
if(!preg_match("/^[a-z\._]+$/i", $lang_file) || strpos($lang_file,'..') !== false || substr($lang_file,-4) != '.lng') die('unallowed characters in language file name.');

$msg = '';

//* Save data
if(isset($_POST['records']) && is_array($_POST['records'])) {
	
	//* CSRF Check
	$app->auth->csrf_token_check();
	
	$file_content = "<?php\n";
	foreach($_POST['records'] as $key => $val) {
		$val = stripslashes($val);
		$val = preg_replace('/(^|[^\\\\])((\\\\\\\\)*)"/', '$1$2\\"', $val);
		$val = str_replace('$', '', $val);
		$file_content .= '$wb['."'$key'".'] = "'.$val.'";'."\n";
		$msg = 'File saved.';
	}
	$file_content .= "?>\n";
	if($module == 'global') {
		file_put_contents(RMNETDOV_LIB_PATH."/lang/$lang_file" , $file_content);
	} else {
		file_put_contents(RMNETDOV_WEB_PATH."/$module/lib/lang/$lang_file" , $file_content);
	}
}


$app->tpl->setVar(array('module' => $module, 'lang_file' => $lang_file, 'lang' => $lang, 'msg' => $msg));

if($module == 'global') {
	include RMNETDOV_LIB_PATH."/lang/$lang_file";
	$file_path = RMNETDOV_LIB_PATH."/lang/$lang_file";
} else {
	include RMNETDOV_WEB_PATH."/$module/lib/lang/$lang_file";
	$file_path = RMNETDOV_WEB_PATH."/$module/lib/lang/$lang_file";
}
$app->tpl->setVar("file_path", $file_path);

$keyword_list = array();
if(isset($wb) && is_array($wb)) {
	foreach($wb as $key => $val) {
		$keyword_list[] = array('key' => $key, 'val' => htmlentities($val, ENT_COMPAT | ENT_HTML401, 'UTF-8'));
	}

	$app->tpl->setLoop('records', $keyword_list);
	unset($wb);
}

//* SET csrf token
$csrf_token = $app->auth->csrf_token_get('language_edit');
$app->tpl->setVar('_csrf_id',$csrf_token['csrf_id']);
$app->tpl->setVar('_csrf_key',$csrf_token['csrf_key']);


//* load language file
$lng_file = 'lib/lang/'.$app->functions->check_language($_SESSION['s']['language']).'_language_edit.lng';
include $lng_file;
$app->tpl->setVar($wb);

$app->tpl_defaults();
$app->tpl->pparse();


?>
