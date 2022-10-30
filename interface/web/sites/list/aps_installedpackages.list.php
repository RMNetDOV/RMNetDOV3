<?php

// Load the APS language file
$lngfile = 'lib/lang/'.$app->functions->check_language($_SESSION['s']['language']).'_aps.lng';
require_once $lngfile;
$app->tpl->setVar($wb);
$app->load_language_file('web/sites/'.$lngfile);

$liste['name'] = 'aps_instances'; // Name of the list
$liste['table'] = 'aps_instances'; // Database table
$liste['table_idx'] = 'id'; // Table index

// if multiple tables are involved, list the additional tables here (comma separated)
$liste["additional_tables"] = "aps_packages";

// if multiple tables are involved, specify sql to join these tables
$liste["join_sql"] = " aps_instances.package_id = aps_packages.id";

$liste["search_prefix"] = 'search_'; // Search field prefix
$liste['records_per_page'] = 15; // Records per page
$liste['file'] = 'aps_installedpackages_list.php'; // Script file for this list
$liste['edit_file']    = ''; // Script file to edit
$liste['delete_file'] = ''; // Script file to delete
$liste['paging_tpl'] = 'templates/paging.tpl.htm'; // Paging template
$liste['auth'] = 'no'; // Handling it myself (check for admin)

// Search fields
$liste["item"][] = array('field'    => 'name',
	'datatype' => 'VARCHAR',
	'formtype' => 'TEXT',
	'op'       => 'LIKE',
	'prefix'   => '%',
	'suffix'   => '%',
	'width'    => '',
	'value'    => '',
	'table' => 'aps_packages');

$liste["item"][] = array('field'    => 'version',
	'datatype' => 'VARCHAR',
	'formtype' => 'TEXT',
	'op'       => 'like',
	'prefix'   => '%',
	'suffix'   => '%',
	'width'    => '',
	'value'    => '',
	'table' => 'aps_packages');

/*
$liste["item"][] = array('field'    => 'customer_id',
                         'datatype' => 'INTEGER',
                         'formtype' => 'SELECT',
                         'op'       => '=',
                         'prefix'   => '',
                         'suffix'   => '',
                         'width'    => '',
                         'value'    => '');
*/

$liste["item"][] = array('field'    => 'instance_status',
	'datatype' => 'VARCHAR',
	'formtype' => 'SELECT',
	'op'       => '=',
	'prefix'   => '',
	'suffix'   => '',
	'width'    => '',
	'value'    => array(INSTANCE_INSTALL => $app->lng('installation_task_txt'),
		INSTANCE_ERROR => $app->lng('installation_error_txt'),
		INSTANCE_SUCCESS => $app->lng('installation_success_txt'),
		INSTANCE_REMOVE => $app->lng('installation_remove_txt')),
	'table' => 'aps_instances');
?>
