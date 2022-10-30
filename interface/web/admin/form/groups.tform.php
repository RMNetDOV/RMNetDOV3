<?php

/*
	Form Definition

	Tabellendefinition

	Datentypen:
	- INTEGER (Wandelt Ausdrücke in Int um)
	- DOUBLE
	- CURRENCY (Formatiert Zahlen nach Währungsnotation)
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
	- FILE

	VALUE:
	- Wert oder Array

	Hinweis:
	Das ID-Feld ist nicht bei den Table Values einzufügen.


*/

$form["title"]    = "Groups";
//$form["description"]  = "groups_description";
$form["name"]    = "groups";
$form["action"]   = "groups_edit.php";
$form["db_table"]  = "sys_group";
$form["db_table_idx"] = "groupid";
$form["db_history"]  = "yes";
$form["tab_default"] = "groups";
$form["list_default"] = "groups_list.php";
$form["auth"]   = 'no';

$form["tabs"]['groups'] = array (
	'title'  => "Groups",
	'width'  => 80,
	'template'  => "templates/groups_edit.htm",
	'fields'  => array (
		//#################################
		// Beginn Datenbankfelder
		//#################################
		'name' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'filters'   => array(
					0 => array( 'event' => 'SAVE',
					'type' => 'STRIPTAGS'),
					1 => array( 'event' => 'SAVE',
					'type' => 'STRIPNL')
			),
			'regex'  => '/^.{1,30}$/',
			'errmsg' => 'name_err',
			'default' => '',
			'value'  => '',
			'separator' => '',
			'width'  => '30',
			'maxlength' => '255',
			'rows'  => '',
			'cols'  => ''
		),
		'description' => array (
			'datatype' => 'TEXT',
			'formtype' => 'TEXTAREA',
			'filters'   => array(
					0 => array( 'event' => 'SAVE',
					'type' => 'STRIPTAGS')
			),
			'regex'  => '',
			'errmsg' => '',
			'default' => '',
			'value'  => '',
			'separator' => '',
			'width'  => '',
			'maxlength' => '',
			'rows'  => '5',
			'cols'  => '30'
		)
		//#################################
		// ENDE Datenbankfelder
		//#################################
	)
);
?>
