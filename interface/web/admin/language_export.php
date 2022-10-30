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
$app->tpl->setInclude('content_tpl', 'templates/language_export.htm');

//* reading languages
$language_option = '';
$error = '';
$msg = '';
$selected_language = (isset($_REQUEST['lng_select']))?substr($_REQUEST['lng_select'], 0, 2):'en';
if(!preg_match("/^[a-z]{2}$/i", $selected_language)) die('unallowed characters in selected language name.');

$handle = opendir(RMNETDOV_ROOT_PATH.'/lib/lang/');
while ($file = readdir($handle)) {
	if ($file != '.' && $file != '..') {
		$tmp_lng = substr($file, 0, -4);
		if($tmp_lng !='') {
			$selected = ($tmp_lng == $selected_language)?'SELECTED':'';
			$language_option .= "<option value='$tmp_lng' $selected>$tmp_lng</option>";
			//if(isset($_POST['lng_new']) && $_POST['lng_new'] == $tmp_lng) $error = 'Language exists already.';
		}
	}
}
$app->tpl->setVar('language_option', $language_option);
$app->tpl->setVar('error', $error);

// Export the language file
if(isset($_POST['lng_select']) && $error == '') {
	//$lng_select = $_POST['lng_select'];
	//if(!preg_match("/^[a-z]{2}$/i", $lng_select)) die('unallowed characters in language name.');

	// This variable contains the content of the language files
	$content = '';
	$content .= "---|RM-Net - DOV CP Language File|".$conf["app_version"]."|".$selected_language."\n";

	//* get the global language file
	$content .= "--|global|".$selected_language."|".$selected_language.".lng\n";
	$content .= file_get_contents(RMNETDOV_LIB_PATH."/lang/".$selected_language.".lng")."\n";

	//* Get the global file of the module
	//$content .= "---|$module|$selected_language|\n";
	//copy(RMNETDOV_WEB_PATH."/$module/lib/lang/$selected_language.lng",RMNETDOV_WEB_PATH."/$module/lib/lang/$lng_new.lng");
	$bgcolor = '#FFFFFF';
	$language_files_list = array();
	$handle = @opendir(RMNETDOV_WEB_PATH);
	while ($file = @readdir($handle)) {
		if ($file != '.' && $file != '..') {
			if(@is_dir(RMNETDOV_WEB_PATH.'/'.$file.'/lib/lang')) {
				$handle2 = opendir(RMNETDOV_WEB_PATH.'/'.$file.'/lib/lang');
				while ($lang_file = @readdir($handle2)) {
					if ($lang_file != '.' && $lang_file != '..' && substr($lang_file, 0, 2) == $selected_language) {
						$content .= "--|".$file."|".$selected_language."|".$lang_file."\n";
						$content .= file_get_contents(RMNETDOV_WEB_PATH.'/'.$file.'/lib/lang/'.$lang_file)."\n";
						$msg .= 'Exported language file '.$lang_file.'<br />';
					}
				}
			}
		}
	}

	$content .= '---|EOF';

	// Write the language file
	file_put_contents(RMNETDOV_WEB_TEMP_PATH.'/'.$selected_language.'.lng', $content);

	$msg = "Exported language file to: <a href='temp/$selected_language.lng' target='_blank'>/temp/".$selected_language.'.lng</a>';

	//$msg = nl2br($content);
}

$app->tpl->setVar('msg', $msg);

//* load language file
$lng_file = 'lib/lang/'.$app->functions->check_language($_SESSION['s']['language']).'_language_export.lng';
include $lng_file;
$app->tpl->setVar($wb);

$app->tpl_defaults();
$app->tpl->pparse();


?>
