<?php

define('SCRIPT_PATH', dirname($_SERVER["SCRIPT_FILENAME"]));
require SCRIPT_PATH."/lib/config.inc.php";
require SCRIPT_PATH."/lib/app.inc.php";

set_time_limit(0);
ini_set('error_reporting', E_ALL & ~E_NOTICE);

/**
 * Prints usage info
 * @author Ramil Valitov <ramilvalitov@gmail.com>
 */
function printUsageInfo(){
    echo <<<EOT
Usage:
	php backup-now.php --id=<4> [--type=<all>]
Options:
	--id		ID spletnega mesta za varnostno kopiranje.
	--type		vrsta varnostne kopije: vse, splet ali mysql. Privzeto je vse.

EOT;
}

/**
 * Makes a backup
 * @param int $domain_id id of the domain
 * @param string $type type: mysql, web or all
 * @return bool true if success
 * @uses backup::run_backup() to make backups
 * @author Ramil Valitov <ramilvalitov@gmail.com>
 */
function makeBackup($domain_id, $type)
{
    global $app;

    echo "Izdelava varnostne kopije spletne strani id=" . $domain_id . ", vrsta=" . $type . ", prosim počakaj...\n";

    // Load required class
    $app->load('backup');

    switch ($type) {
        case "all":
            $success = backup::run_backup($domain_id, "web", "manual");
            $success = $success && backup::run_backup($domain_id, "mysql", "manual");
            break;
        case "mysql":
            $success = backup::run_backup($domain_id, "mysql", "manual");
            break;
        case "web":
            $success = backup::run_backup($domain_id, "web", "manual");
            break;
        default:
            echo "Neznana oblika=" . $type . "\n";
            printUsageInfo();
            $success = false;
    }
    return $success;
}

//** Get commandline options
$cmd_opt = getopt('', array('id::', 'type::'));
$id = filter_var($cmd_opt['id'], FILTER_VALIDATE_INT);;
if (!isset($cmd_opt['id']) || !is_int($id)) {
    printUsageInfo();
    exit(1);
}

if (isset($cmd_opt['type']) && !empty($cmd_opt['type'])) {
    $type = $cmd_opt['type'];
} else
    $type = "all";

$success = makeBackup($id, $type);

echo "Vse operacije končane, stanje " . ($success ? "success" : "failed") . ".\n";

exit($success ? 0 : 2);

?>
