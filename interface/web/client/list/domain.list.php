<?php

/*
	Datatypes:
	- INTEGER
	- DOUBLE
	- CURRENCY
	- VARCHAR
	- TEXT
	- DATE
*/



// Name of the list
$liste["name"]     = "domain";

// Database table
$liste["table"]    = "domain";

// Index index field of the database table
$liste["table_idx"]   = "domain_id";

// Search Field Prefix
$liste["search_prefix"]  = "search_";

// Records per page
$liste["records_per_page"]  = 15;

// Script File of the list
$liste["file"]    = "domain_list.php";

// Script file of the edit form
$liste["edit_file"]   = "domain_edit.php";

// Script File of the delete script
$liste["delete_file"]  = "domain_del.php";

// Paging Template
$liste["paging_tpl"]  = "templates/paging.tpl.htm";

// Enable auth
$liste["auth"]    = "yes";


/*****************************************************
* Suchfelder
*****************************************************/
$liste["item"][] = array( 'field'  => "domain",
	'datatype' => "VARCHAR",
	'filters'   => array( 0 => array( 'event' => 'SHOW',
			'type' => 'IDNTOUTF8')
	),
	'formtype' => "TEXT",
	'op'  => "LIKE",
	'prefix' => "%",
	'suffix' => "%",
	'width'  => "",
	'value'  => "");

$liste["item"][] = array( 'field'  => "sys_groupid",
	'datatype' => "VARCHAR",
	'formtype' => "SELECT",
	'op'  => "=",
	'prefix' => "",
	'suffix' => "",
	'datasource' => array (  'type' => 'SQL',
		//'querystring' => 'SELECT a.groupid, a.name FROM sys_group a, domain b WHERE (a.groupid = b.sys_groupid) AND ({AUTHSQL-B}) ORDER BY name',
		'querystring' => "SELECT sys_group.groupid,CONCAT(IF(client.company_name != '', CONCAT(client.company_name, ' :: '), ''), IF(client.contact_firstname != '', CONCAT(client.contact_firstname, ' '), ''), client.contact_name, ' (', client.username, IF(client.customer_no != '', CONCAT(', ', client.customer_no), ''), ')') as name FROM sys_group, client WHERE sys_group.groupid != 1 AND sys_group.client_id = client.client_id AND {AUTHSQL} ORDER BY client.company_name, client.contact_name",
		'keyfield'=> 'groupid',
		'valuefield'=> 'name'
	),
	'width'  => "",
	'value'  => "");

?>
