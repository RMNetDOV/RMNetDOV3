<?php

/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/dns_sshfp.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';
require_once './dns_edit_base.php';

// Loading classes
class page_action extends dns_page_action {

}

$page = new page_action;
$page->onLoad();

?>
