<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
//require_once('classes/class.base.php'); // for constants
$app->load('aps_base');

// Path to the list definition file
$list_def_file = "list/aps_availablepackages.list.php";

// Check the module permissions
$app->auth->check_module_permissions('sites');

// Load needed classes
$app->uses('tpl,listform_actions');

$app->listform_actions->SQLOrderBy = 'ORDER BY aps_packages.name, aps_packages.version';
// Show only unlocked packages to clients and (un-)lockable packages to admins
if($_SESSION['s']['user']['typ'] != 'admin') $app->listform_actions->SQLExtWhere = 'aps_packages.package_status = '.PACKAGE_ENABLED;
else $app->listform_actions->SQLExtWhere = '(aps_packages.package_status = '.PACKAGE_ENABLED.' OR aps_packages.package_status = '.PACKAGE_LOCKED.')';

// Get package amount
$pkg_count = $app->db->queryOneRecord("SELECT COUNT(*) FROM aps_packages");
$app->tpl->setVar("package_count", $pkg_count['COUNT(*)']);

// Start the form rendering and action handling
$app->listform_actions->onLoad();
?>
