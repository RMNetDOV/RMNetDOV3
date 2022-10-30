<?php

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//require_once('classes/class.crawler.php');
$app->load('aps_crawler');

if(!@ini_get('allow_url_fopen')) $app->error('allow_url_fopen is not enabled.');
if(!function_exists('curl_version')) $app->error('The PHP CURL extension is not available.');

$log_prefix = 'APS crawler cron: ';

$aps = new ApsCrawler($app, true); // true = Interface mode, false = Server mode

$app->log($log_prefix.'Used mem at begin: '.$aps->convertSize(memory_get_usage(true)));

$time_start = microtime(true);
$aps->startCrawler();
$aps->parseFolderToDB();
$aps->fixURLs();
$time = microtime(true) - $time_start;

$app->log($log_prefix.'Used mem at end: '.$aps->convertSize(memory_get_usage(true)));
$app->log($log_prefix.'Mem peak during execution: '.$aps->convertSize(memory_get_peak_usage(true)));
$app->log($log_prefix.'Execution time: '.round($time, 3).' seconds');

// Load the language file
$lngfile = 'lib/lang/'.$_SESSION['s']['language'].'_aps.lng';
$app->load_language_file('web/sites/'.$lngfile);

echo '<div id="OKMsg"><p>'.$app->lng('packagelist_update_finished_txt').'</p></div>';



?>
