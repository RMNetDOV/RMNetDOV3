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

//* Load modules
$modules_list = array();
$handle = @opendir(RMNETDOV_WEB_PATH);
while ($file = @readdir($handle)) {
	if ($file != "." && $file != "..") {
		if(@is_dir(RMNETDOV_WEB_PATH."/$file")) {
			if(is_file(RMNETDOV_WEB_PATH."/$file/lib/module.conf.php") and $file != 'login') {
				$modules_list[$file] = $file;
			}
		}
	}
}
closedir($handle);

//* read data bases in with more activated db_history.
$db_tables = array();
foreach($modules_list as $md) {
	$handle = @opendir(RMNETDOV_WEB_PATH."/$md/form");
	while ($file = @readdir($handle)) {
		if ($file != '.' && $file != '..' && substr($file, 0, 1) != '.') {
			include_once RMNETDOV_WEB_PATH."/$md/form/$file";
			if(isset($form['db_history']) && $form['db_history'] == 'yes') {
				$tmp_id = $form['db_table'];
				$db_tables[$tmp_id] = $form['db_table'];
			}
			unset($form);
		}
	}
	closedir($handle);
}
unset($form);


$form['title']          = 'DB sync';
//$form['description']    = 'RM-Net - DOV CP database synchronisation tool.';
$form['name']           = 'dbsync';
$form['action']         = 'dbsync_edit.php';
$form['db_table']       = 'sys_dbsync';
$form['db_table_idx']   = 'id';
$form['tab_default']    = 'dbsync';
$form['list_default']   = 'dbsync_list.php';
$form['auth']           = 'no';


$form['tabs']['dbsync'] = array (
	'title'     => 'DB sync',
	'width'     => 80,
	'template'  => 'templates/dbsync_edit.htm',
	'fields'    => array (
		//#################################
		// Beginn Datenbankfelder
		//#################################
		'jobname' => array (
			'datatype'  => 'VARCHAR',
			'formtype'  => 'TEXT',
			'regex'     => '/^.{1,30}$/',
			'errmsg'    => 'jobname_err',
			'default'   => '',
			'value'     => '',
			'separator' => '',
			'width'     => '15',
			'maxlength' => '255',
			'rows'      => '',
			'cols'      => ''
		),
		'sync_interval_minutes' => array (
			'datatype'  => 'INTEGER',
			'formtype'  => 'TEXT',
			'regex'     => '',
			'errmsg'    => '',
			'default'   => '',
			'value'     => '',
			'separator' => '',
			'width'     => '15',
			'maxlength' => '255',
			'rows'      => '',
			'cols'      => ''
		),
		'db_type' => array (
			'datatype'  => 'VARCHAR',
			'formtype'  => 'SELECT',
			'regex'     => '',
			'errmsg'    => '',
			'default'   => '',
			'value'     => array('mysql' => 'mysql'),
			'separator' => '',
			'width'     => '30',
			'maxlength' => '255',
			'rows'      => '',
			'cols'      => ''
		),
		'db_host' => array (
			'datatype'  => 'VARCHAR',
			'formtype'  => 'TEXT',
			'regex'     => '',
			'errmsg'    => '',
			'default'   => '',
			'value'     => '',
			'separator' => '',
			'width'     => '30',
			'maxlength' => '255',
			'rows'      => '',
			'cols'      => ''
		),
		'db_name' => array (
			'datatype'  => 'VARCHAR',
			'formtype'  => 'TEXT',
			'regex'     => '',
			'errmsg'    => '',
			'default'   => '',
			'value'     => '',
			'separator' => '',
			'width'     => '30',
			'maxlength' => '255',
			'rows'      => '',
			'cols'      => ''
		),
		'db_username' => array (
			'datatype'  => 'VARCHAR',
			'formtype'  => 'TEXT',
			'regex'     => '',
			'errmsg'    => '',
			'default'   => '',
			'value'     => '',
			'separator' => '',
			'width'     => '30',
			'maxlength' => '255',
			'rows'      => '',
			'cols'      => ''
		),
		'db_password' => array (
			'datatype'  => 'VARCHAR',
			'formtype'  => 'TEXT',
			'regex'     => '',
			'errmsg'    => '',
			'default'   => '',
			'value'     => '',
			'separator' => '',
			'width'     => '30',
			'maxlength' => '255',
			'rows'      => '',
			'cols'      => ''
		),
		'db_tables' => array (
			'datatype'  => 'VARCHAR',
			'formtype'  => 'CHECKBOXARRAY',
			'regex'     => '',
			'errmsg'    => '',
			'default'   => 'admin,forms',
			'value'     => $db_tables,
			'separator' => ',',
			'width'     => '30',
			'maxlength' => '255',
			'rows'      => '',
			'cols'      => ''
		),
		'empty_datalog' => array (
			'datatype'  => 'INTEGER',
			'formtype'  => 'CHECKBOX',
			'regex'     => '',
			'errmsg'    => '',
			'default'   => '',
			'value'     => array(0 => 0, 1 => 1),
			'separator' => '',
			'width'     => '30',
			'maxlength' => '255',
			'rows'      => '',
			'cols'      => ''
		),
		'sync_datalog_external' => array (
			'datatype'  => 'INTEGER',
			'formtype'  => 'CHECKBOX',
			'regex'     => '',
			'errmsg'    => '',
			'default'   => '',
			'value'     => array(0 => 0, 1 => 1),
			'separator' => '',
			'width'     => '30',
			'maxlength' => '255',
			'rows'      => '',
			'cols'      => ''
		),
		'active' => array (
			'datatype'  => 'INTEGER',
			'formtype'  => 'CHECKBOX',
			'regex'     => '',
			'errmsg'    => '',
			'default'   => '1',
			'value'     => array(0 => 0, 1 => 1),
			'separator' => '',
			'width'     => '30',
			'maxlength' => '255',
			'rows'      => '',
			'cols'      => ''
		)
		//#################################
		// ENDE Datenbankfelder
		//#################################
	)
);

?>
