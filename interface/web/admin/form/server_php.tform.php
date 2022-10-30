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

$form["title"]    = "Additional PHP Versions";
//$form["description"]  = "Form to edit additional PHP versions";
$form["name"]    = "server_php";
$form["action"]   = "server_php_edit.php";
$form["db_table"]  = "server_php";
$form["db_table_idx"] = "server_php_id";
$form["db_history"]  = "yes";
$form["tab_default"] = "php_name";
$form["list_default"] = "server_php_list.php";
$form["auth"]   = 'yes';

$form["auth_preset"]["userid"]  = 0; // 0 = id of the user, > 0 id must match with id of current user
$form["auth_preset"]["groupid"] = 0; // 0 = default groupid of the user, > 0 id must match with groupid of current user
$form["auth_preset"]["perm_user"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_group"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_other"] = ''; //r = read, i = insert, u = update, d = delete

$form["tabs"]['php_name'] = array (
	'title'  => "Name",
	'width'  => 80,
	'template'  => "templates/server_php_name_edit.htm",
	'fields'  => array (
		//#################################
		// Beginn Datenbankfelder
		//#################################
		'server_id' => array (
			'datatype' => 'INTEGER',
			'formtype' => 'SELECT',
			'default' => '',
			'datasource' => array (  'type' => 'SQL',
				'querystring' => 'SELECT server_id,server_name FROM server WHERE mirror_server_id = 0 AND web_server = 1 AND {AUTHSQL} ORDER BY server_name',
				'keyfield'=> 'server_id',
				'valuefield'=> 'server_name'
			),
			'value'  => ''
		),
		'client_id' => array (
			'datatype' => 'INTEGER',
			'formtype' => 'SELECT',
			'default' => '',
			'datasource' => array (  'type' => 'SQL',
				'querystring' => "(SELECT 0 AS client_id, '' AS name) UNION ALL (SELECT client_id,CONCAT(IF(client.company_name != '', CONCAT(client.company_name, ' :: '), ''), client.contact_name, ' (', client.username, IF(client.customer_no != '', CONCAT(', ', client.customer_no), ''), ')') as name FROM client WHERE {AUTHSQL} ORDER BY contact_name)",
				'keyfield'=> 'client_id',
				'valuefield'=> 'name'
			),
			'value'  => array(0 => ' ')
		),
		'name' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'validators' => array(0 => array('type' => 'NOTEMPTY',
					'errmsg' => 'server_php_name_error_empty'),
			),
			'filters'   => array(
					0 => array( 'event' => 'SAVE',
					'type' => 'STRIPTAGS'),
					1 => array( 'event' => 'SAVE',
					'type' => 'STRIPNL')
			),
			'default' => '',
			'value'  => '',
			'separator' => '',
			'width'  => '40',
			'maxlength' => '255'
		),
		'active' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'CHECKBOX',
			'default' => 'y',
			'value'  => array(0 => 'n', 1 => 'y')
		),

		//#################################
		// ENDE Datenbankfelder
		//#################################
	)
);

$form["tabs"]['php_fastcgi'] = array(
	'title' => "FastCGI Settings",
	'width' => 80,
	'template' => "templates/server_php_fastcgi_edit.htm",
	'fields' => array(
		//#################################
		// Begin Datatable fields
		//#################################
		'php_fastcgi_binary' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'filters'   => array(
					0 => array( 'event' => 'SAVE',
					'type' => 'STRIPTAGS'),
					1 => array( 'event' => 'SAVE',
					'type' => 'STRIPNL')
			),
			'default' => '',
			'value' => '',
			'width' => '40',
			'maxlength' => '255'
		),
		'php_fastcgi_ini_dir' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'filters'   => array(
					0 => array( 'event' => 'SAVE',
					'type' => 'STRIPTAGS'),
					1 => array( 'event' => 'SAVE',
					'type' => 'STRIPNL')
			),
			'default' => '',
			'value' => '',
			'width' => '40',
			'maxlength' => '255'
		),
		//#################################
		// END Datatable fields
		//#################################
	)
);

$form["tabs"]['php_fpm'] = array(
	'title' => "PHP-FPM Settings",
	'width' => 80,
	'template' => "templates/server_php_fpm_edit.htm",
	'fields' => array(
		//#################################
		// Begin Datatable fields
		//#################################
		'php_fpm_init_script' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'filters'   => array(
					0 => array( 'event' => 'SAVE',
					'type' => 'STRIPTAGS'),
					1 => array( 'event' => 'SAVE',
					'type' => 'STRIPNL')
			),
			'default' => '',
			'value' => '',
			'width' => '40',
			'maxlength' => '255'
		),
		'php_fpm_ini_dir' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'filters'   => array(
					0 => array( 'event' => 'SAVE',
					'type' => 'STRIPTAGS'),
					1 => array( 'event' => 'SAVE',
					'type' => 'STRIPNL')
			),
			'default' => '',
			'value' => '',
			'width' => '40',
			'maxlength' => '255'
		),
		'php_fpm_pool_dir' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'filters'   => array(
					0 => array( 'event' => 'SAVE',
					'type' => 'STRIPTAGS'),
					1 => array( 'event' => 'SAVE',
					'type' => 'STRIPNL')
			),
			'default' => '',
			'value' => '',
			'width' => '40',
			'maxlength' => '255'
		),
		'php_fpm_socket_dir' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'filters'   => array(
					0 => array( 'event' => 'SAVE',
					'type' => 'STRIPTAGS'),
					1 => array( 'event' => 'SAVE',
					'type' => 'STRIPNL')
			),
			'default' => '',
			'value' => '',
			'width' => '40',
			'maxlength' => '255'
		),
		//#################################
		// END Datatable fields
		//#################################
	)
);
?>
