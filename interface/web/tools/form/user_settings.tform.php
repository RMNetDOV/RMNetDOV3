<?php

/*
	Form Definition

	Tabellendefinition

	Datentypen:
	- INTEGER (Wandelt Ausdr�cke in Int um)
	- DOUBLE
	- CURRENCY (Formatiert Zahlen nach W�hrungsnotation)
	- VARCHAR (kein weiterer Format Check)
	- TEXT (kein weiterer Format Check)
	- DATE (Datumsformat, Timestamp Umwandlung)

	Formtype:
	- TEXT (normales Textfeld)
	- TEXTAREA (normales Textfeld)
	- PASSWORD (Feldinhalt wird nicht angezeigt)
	- SELECT (Gibt Werte als option Feld aus)
	- RADIO
	- CHECKBOX
	- CHECKBOXARRAY
	- FILE

	VALUE:
	- Wert oder Array

	Hinweis:
	Das ID-Feld ist nicht bei den Table Values einzuf�gen.


*/

$form['title']    = 'User Settings';
//*$form['description']  = 'Form to edit the user password and language.';
$form['name']    = 'usersettings';
$form['action']   = 'user_settings.php';
$form['db_table']  = 'sys_user';
$form['db_table_idx'] = 'userid';
$form["db_history"]  = "no";
$form['tab_default'] = 'users';
$form['list_default'] = 'user_settings.php';
$form['auth']   = 'no';

//* 0 = id of the user, > 0 id must match with id of current user
$form['auth_preset']['userid']  = 0;
//* 0 = default groupid of the user, > 0 id must match with groupid of current user
$form['auth_preset']['groupid'] = 0;

//** Permissions are: r = read, i = insert, u = update, d = delete
$form['auth_preset']['perm_user']  = 'riud';
$form['auth_preset']['perm_group'] = 'riud';
$form['auth_preset']['perm_other'] = '';

//* Languages
$language_list = array();
$handle = @opendir(RMNETDOV_ROOT_PATH.'/lib/lang');
while ($file = @readdir($handle)) {
	if ($file != '.' && $file != '..') {
		if(@is_file(RMNETDOV_ROOT_PATH.'/lib/lang/'.$file) and substr($file, -4, 4) == '.lng') {
			$tmp = substr($file, 0, 2);
			$language_list[$tmp] = $tmp;
		}
	}
}
//* Pick out modules
//* TODO: limit to activated modules of the user
$modules_list = array();
if($_SESSION["s"]["user"]["typ"] == 'admin') {
	$handle = @opendir(RMNETDOV_WEB_PATH);
	while ($file = @readdir($handle)) {
		if ($file != '.' && $file != '..') {
			if(@is_dir(RMNETDOV_WEB_PATH."/$file")) {
				if(is_file(RMNETDOV_WEB_PATH."/$file/lib/module.conf.php") and $file != 'login' && $file != 'designer' && $file != 'mailuser') {
					$modules_list[$file] = $file;
				}
			}
		}
	}
} else {
	$tmp = $app->db->queryOneRecord("SELECT * FROM sys_user where username = ?", $_SESSION["s"]["user"]['username']);
	$modules = $tmp['modules'];
	//$modules = $conf['interface_modules_enabled'];
	if($_SESSION["s"]["user"]["typ"] != 'admin' && $app->auth->has_clients($_SESSION['s']['user']['userid'])) {
		$modules .= ',client';
	}
	$tmp = explode(',', $modules);
	foreach($tmp as $m) {
		$modules_list[$m] = $m;
	}
}

//* Load themes
$themes_list = array();
$handle = @opendir(RMNETDOV_THEMES_PATH);
while ($file = @readdir($handle)) {
	if (substr($file, 0, 1) != '.') {
		if(@is_dir(RMNETDOV_THEMES_PATH."/$file")) {
			if(!file_exists(RMNETDOV_THEMES_PATH."/$file/rmnetdov_version") || (@file_exists(RMNETDOV_THEMES_PATH."/$file/rmnetdov_version") && trim(@file_get_contents(RMNETDOV_THEMES_PATH."/$file/rmnetdov_version")) == RMNETDOV_APP_VERSION)) {
				$themes_list[$file] = $file;
			}
		}
	}
}


$form['tabs']['users'] = array (
	'title'  => 'Settings',
	'width'  => 80,
	'template'  => 'templates/user_settings.htm',
	'fields'  => array (
		//#################################
		// Beginn Datenbankfelder
		//#################################
		'passwort' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'PASSWORD',
			'validators' => array(
				0 => array(
					'type' => 'CUSTOM',
					'class' => 'validate_password',
					'function' => 'password_check',
					'errmsg' => 'weak_password_txt'
				)
			),
			'encryption'=> 'CRYPT',
			'regex'  => '',
			'errmsg' => '',
			'default' => '',
			'value'  => '',
			'separator' => '',
			'width'  => '15',
			'maxlength' => '100',
			'rows'  => '',
			'cols'  => ''
		),
		'language' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'SELECT',
			'validators' => array (  0 => array ( 'type' => 'NOTEMPTY',
					'errmsg'=> 'language_is_empty'),
				1 => array ( 'type' => 'REGEX',
					'regex' => '/^[a-z]{2}$/i',
					'errmsg'=> 'language_regex_mismatch'),
			),
			'regex'  => '',
			'errmsg' => '',
			'default' => '',
			'value'  => $language_list,
			'separator' => '',
			'width'  => '30',
			'maxlength' => '2',
			'rows'  => '',
			'cols'  => ''
		),
		'startmodule' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'SELECT',
			'validators' => array (  0 => array (    'type' => 'NOTEMPTY',
					'errmsg'=> 'startmodule_empty'),
				1 => array (    'type' => 'REGEX',
					'regex' => '/^[a-z0-9\_]{0,64}$/',
					'errmsg'=> 'startmodule_regex'),
			),
			'regex'  => '',
			'errmsg' => '',
			'default' => '',
			'value'  => $modules_list,
			'separator' => '',
			'width'  => '30',
			'maxlength' => '255',
			'rows'  => '',
			'cols'  => ''
		),
		'app_theme' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'SELECT',
			'validators' => array (  0 => array (    'type' => 'NOTEMPTY',
					'errmsg'=> 'app_theme_empty'),
				1 => array (    'type' => 'REGEX',
					'regex' => '/^[a-z0-9\_]{0,64}$/',
					'errmsg'=> 'app_theme_regex'),
			),
			'regex' => '',
			'errmsg' => '',
			'default' => 'default',
			'value' => $themes_list,
			'separator' => '',
			'width' => '30',
			'maxlength' => '255',
			'rows' => '',
			'cols' => ''
		)
		//#################################
		// ENDE Datenbankfelder
		//#################################
	)
);


?>
