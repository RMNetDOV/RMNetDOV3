<?php

$liste['name'] = 'aps_packages'; // Name of the list
$liste['table'] = 'aps_packages'; // Database table
$liste['table_idx'] = 'id'; // Table index
$liste["search_prefix"] = 'search_'; // Search field prefix
$liste['records_per_page'] = 15; // Records per page
$liste['file'] = 'aps_availablepackages_list.php'; // Script file for this list
$liste['edit_file']    = ''; // Script file to edit
$liste['delete_file'] = ''; // Script file to delete
$liste['paging_tpl'] = 'templates/paging.tpl.htm'; // Paging template
$liste['auth'] = 'no'; // Handling it myself (check for admin)

// Search fields
$liste["item"][] = array('field'    => 'name',
	'datatype' => 'VARCHAR',
	'formtype' => 'TEXT',
	'op'       => 'like',
	'prefix'   => '%',
	'suffix'   => '%',
	'width'    => '',
	'value'    => '');

$liste["item"][] = array('field'    => 'version',
	'datatype' => 'VARCHAR',
	'formtype' => 'TEXT',
	'op'       => 'like',
	'prefix'   => '%',
	'suffix'   => '%',
	'width'    => '',
	'value'    => '');

$liste["item"][] = array('field'    => 'category',
	'datatype' => 'VARCHAR',
	'formtype' => 'SELECT',
	'op'       => '=',
	'prefix'   => '',
	'suffix'   => '',
	'datasource' => array('type' => 'SQL',
		'querystring' => 'SELECT category FROM aps_packages ORDER BY category',
		'keyfield' => 'category',
		'valuefield' => 'category'),
	'width'    => '',
	'value'    => '');

if($_SESSION['s']['user']['typ'] == 'admin')
{
	$liste['item'][] = array('field'    => 'package_status',
		'datatype' => 'VARCHAR',
		'formtype' => 'SELECT',
		'op'       => '=',
		'prefix'   => '',
		'suffix'   => '',
		'width'    => '',
		'value'    => array(PACKAGE_ENABLED => $app->lng('Yes'),
			PACKAGE_LOCKED => $app->lng('No')));
}
?>
