<?php

$form["title"]    = "server_ip_map_title";
//$form["description"]  = "server_ip_map_desc";
$form["name"]    = "server_ip_map";
$form["action"]   = "server_ip_map_edit.php";
$form["db_table"]  = "server_ip_map";
$form["db_table_idx"] = "server_ip_map_id";
$form["db_history"]  = "yes";
$form["tab_default"] = "server_ip_map";
$form["list_default"] = "server_ip_map_list.php";
$form["auth"]   = 'yes';

$form["auth_preset"]["userid"]  = 0; // 0 = id of the user, > 0 id must match with id of current user
$form["auth_preset"]["groupid"] = 0; // 0 = default groupid of the user, > 0 id must match with groupid of current user
$form["auth_preset"]["perm_user"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_group"] = 'riud'; //r = read, i = insert, u = update, d = delete
$form["auth_preset"]["perm_other"] = ''; //r = read, i = insert, u = update, d = delete

$form["tabs"]['server_ip_map'] = array (
	'title'  => "server_ip_map_title",
	'width'  => 80,
	'template'  => "templates/server_ip_map_edit.htm",
	'fields'  => array (
		'server_id' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'SELECT',
			'default' => '',
			'value'  => ''
		),
		'source_ip' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'SELECT',
			'validators' => array (
				0 => array ( 'type' => 'NOTEMPTY', 'errmsg'=> 'source_ip_empty'),
			),
			'default' => '',
			'value'  => ''
		),
		'destination_ip' => array (
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'validators' => array (
				0 => array ( 'type' => 'ISIPV4', 'errmsg'=> 'ip_error_wrong'),
				1 => array ( 'type' => 'NOTEMPTY', 'errmsg'=> 'destination_ip_empty'),
            ),
			'default' => '',
			'value'  => '',
			'separator' => '',
			'width'  => '15',
			'maxlength' => '15',
			'rows'  => '',
			'cols'  => '',
			'searchable' => 1
		),
		'active' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'CHECKBOX',
			'default' => 'y',
			'value' => array(0 => 'n', 1 => 'y')
		),
	)
);
?>
