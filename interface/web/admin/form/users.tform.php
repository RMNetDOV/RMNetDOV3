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

$form['title']   = 'users_txt';
//$form['description']  = 'Form to edit systemusers.';
$form['name']   = 'users';
$form['action']  = 'users_edit.php';
$form['db_table'] = 'sys_user';
$form['db_table_idx'] = 'userid';
$form["db_history"] = "no";
$form['tab_default'] = 'users';
$form['list_default'] = 'users_list.php';
$form['auth']  = 'yes';

//* 0 = id of the user, > 0 id must match with id of current user
$form['auth_preset']['userid']  = 0;
//* 0 = default groupid of the user, > 0 id must match with groupid of current user
$form['auth_preset']['groupid'] = 0;

//** Permissions are: r = read, i = insert, u = update, d = delete
$form['auth_preset']['perm_user']  = 'riud';
$form['auth_preset']['perm_group'] = 'riud';
$form['auth_preset']['perm_other'] = '';

//* Pick out modules
$modules_list = array();
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

//* Pick out groups
$groups_list = array();
$tmp_records = $app->db->queryAllRecords('SELECT groupid, name FROM sys_group ORDER BY name');
if(is_array($tmp_records)) {
	foreach($tmp_records as $tmp_rec) {
		$groups_list[$tmp_rec['groupid']] = $tmp_rec['name'];
	}
}

$form['tabs']['users'] = array (
	'title'  => 'users_txt',
	'width'  => 80,
	'template'  => 'templates/users_user_edit.htm',
	'fields'  => array (
		//#################################
		// Beginn Datenbankfelder
		//#################################
		'username' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'validators' => array (  0 => array (    'type' => 'NOTEMPTY',
					'errmsg'=> 'username_empty'),
				1 => array (    'type' => 'UNIQUE',
					'errmsg'=> 'username_unique'),
				2 => array (    'type' => 'REGEX',
					'regex' => '/^[\w\.\-\_]{0,64}$/',
					'errmsg'=> 'username_err'),
				3 => array ( 'type' => 'CUSTOM',
					'class' => 'validate_client',
					'function' => 'username_collision',
					'errmsg'=> 'username_error_collision'),
			),
			'regex'  => '',
			'errmsg' => '',
			'default' => '',
			'value'  => '',
			'separator' => '',
			'width'  => '15',
			'maxlength' => '30',
			'rows'  => '',
			'cols'  => ''
		),
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
			'encryption'    => 'CRYPT',
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
		'modules' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'CHECKBOXARRAY',
			'regex'  => '',
			'errmsg' => '',
			'default' => 'admin,forms',
			'value'  => $modules_list,
			'separator' => ',',
			'width'  => '30',
			'maxlength' => '255',
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
			'formtype' => 'RADIO',
			'regex'  => '',
			'errmsg' => '',
			'default' => 'default',
			'value'  => $themes_list,
			'separator' => '',
			'width'  => '30',
			'maxlength' => '255',
			'rows'  => '',
			'cols'  => ''
		),
		'typ' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'RADIO',
			'regex'  => '',
			'errmsg' => '',
			'default' => 'user',
			'value'  => array ('user' => 'user', 'admin' => 'admin'),
			'separator' => '',
			'width'  => '30',
			'maxlength' => '255',
			'rows'  => '',
			'cols'  => ''
		),
		'active' => array (
			'datatype' => 'INTEGER',
			'formtype' => 'CHECKBOX',
			'regex'  => '',
			'errmsg' => '',
			'default' => '',
			'value'  => array(0 => 0, 1 => 1),
			'separator' => '',
			'width'  => '30',
			'maxlength' => '255',
			'rows'  => '',
			'cols'  => ''
		),
		'language' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'SELECT',
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
		'lost_password_function' => array (
			'datatype' => 'INTEGER',
			'formtype' => 'CHECKBOX',
			'regex'  => '',
			'errmsg' => '',
			'default' => 1,
			'value'  => array(0 => 0, 1 => 1),
			'separator' => '',
			'width'  => '30',
			'maxlength' => '255',
			'rows'  => '',
			'cols'  => ''
		)
		//#################################
		// ENDE Datenbankfelder
		//#################################
	)
);
/*
$form['tabs']['address'] = array (
	'title' 	=> 'Address',
	'width' 	=> 80,
	'template' 	=> 'templates/users_address_edit.htm',
	'fields' 	=> array (
	##################################
	# Beginn Datenbankfelder
	##################################
		'name' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'regex'		=> '',
			'errmsg'	=> '',
			'default'	=> '',
			'value'		=> '',
			'separator'	=> '',
			'width'		=> '30',
			'maxlength'	=> '255',
			'rows'		=> '',
			'cols'		=> ''
		),
		'vorname' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'regex'		=> '',
			'errmsg'	=> '',
			'default'	=> '',
			'value'		=> '',
			'separator'	=> '',
			'width'		=> '30',
			'maxlength'	=> '255',
			'rows'		=> '',
			'cols'		=> ''
		),
		'unternehmen' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'regex'		=> '',
			'errmsg'	=> '',
			'default'	=> '',
			'value'		=> '',
			'separator'	=> '',
			'width'		=> '30',
			'maxlength'	=> '255',
			'rows'		=> '',
			'cols'		=> ''
		),
		'strasse' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'regex'		=> '',
			'errmsg'	=> '',
			'default'	=> '',
			'value'		=> '',
			'separator'	=> '',
			'width'		=> '30',
			'maxlength'	=> '255',
			'rows'		=> '',
			'cols'		=> ''
		),
		'ort' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'regex'		=> '',
			'errmsg'	=> '',
			'default'	=> '',
			'value'		=> '',
			'separator'	=> '',
			'width'		=> '30',
			'maxlength'	=> '255',
			'rows'		=> '',
			'cols'		=> ''
		),
		'plz' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'regex'		=> '',
			'errmsg'	=> '',
			'default'	=> '',
			'value'		=> '',
			'separator'	=> '',
			'width'		=> '30',
			'maxlength'	=> '255',
			'rows'		=> '',
			'cols'		=> ''
		),
		'land' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'regex'		=> '',
			'errmsg'	=> '',
			'default'	=> '',
			'value'		=> '',
			'separator'	=> '',
			'width'		=> '30',
			'maxlength'	=> '255',
			'rows'		=> '',
			'cols'		=> ''
		),
		'email' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'regex'		=> '',
			'errmsg'	=> '',
			'default'	=> '',
			'value'		=> '',
			'separator'	=> '',
			'width'		=> '30',
			'maxlength'	=> '255',
			'rows'		=> '',
			'cols'		=> ''
		),
		'url' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'regex'		=> '',
			'errmsg'	=> '',
			'default'	=> '',
			'value'		=> '',
			'separator'	=> '',
			'width'		=> '30',
			'maxlength'	=> '255',
			'rows'		=> '',
			'cols'		=> ''
		),
		'telefon' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'regex'		=> '',
			'errmsg'	=> '',
			'default'	=> '',
			'value'		=> '',
			'separator'	=> '',
			'width'		=> '30',
			'maxlength'	=> '255',
			'rows'		=> '',
			'cols'		=> ''
		),
		'fax' => array (
			'datatype'	=> 'VARCHAR',
			'formtype'	=> 'TEXT',
			'regex'		=> '',
			'errmsg'	=> '',
			'default'	=> '',
			'value'		=> '',
			'separator'	=> '',
			'width'		=> '30',
			'maxlength'	=> '255',
			'rows'		=> '',
			'cols'		=> ''
		)

	##################################
	# ENDE Datenbankfelder
	##################################
	)
);
*/

$form['tabs']['groups'] = array (
	'title'  => 'Groups',
	'width'  => 80,
	'template'  => 'templates/users_groups_edit.htm',
	'fields'  => array (
		//#################################
		// Beginn Datenbankfelder
		//#################################
		'default_group' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'SELECT',
			'regex'  => '',
			'errmsg' => '',
			'default' => '',
			'value'  => $groups_list,
			'separator' => ',',
			'width'  => '30',
			'maxlength' => '255',
			'rows'  => '',
			'cols'  => ''
		),
		'groups' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'CHECKBOXARRAY',
			'regex'  => '',
			'errmsg' => '',
			'default' => '',
			'value'  => $groups_list,
			'separator' => ',',
			'width'  => '30',
			'maxlength' => '255',
			'rows'  => '',
			'cols'  => ''
		)

		//#################################
		// ENDE Datenbankfelder
		//#################################
	)
);

?>
