<?php

$path = realpath(dirname(__FILE__) . '/..');
$iface_path = realpath(dirname(__FILE__) . '/../../interface/web');
$iface_base_path = realpath(dirname(__FILE__) . '/../../interface');

require $path . '/lib/mysql_clientdb.conf';

if(isset($argv[1])) $dbname = $argv[1];
else $dbname = 'dbrmnetdov';
if(!preg_match('/^[a-zA-Z0-9]+$/', $dbname)) die("Invalid database name\n");

$link = mysqli_init();
$con = mysqli_real_connect($link, $clientdb_host, $clientdb_user, $clientdb_password, $dbname);
if(!$con) die('DB CON ERROR' . "\n");

$qry = "SELECT username, passwort FROM sys_user WHERE active = '1'";
$result = mysqli_query($link, $qry);
if(!$result) die('Could not read users' . "\n");

$cont = '';
while($row = mysqli_fetch_assoc($result)) {
	$cont .= $row['username'] . ':' . $row['passwort'] . "\n";
}
mysqli_free_result($result);
mysqli_close($link);

if($cont == '') die('No users found' . "\n");

if(file_exists($iface_base_path . '/.htpasswd')) rename($iface_base_path . '/.htpasswd', $iface_base_path . '/.htpasswd.old');
file_put_contents($iface_base_path . '/.htpasswd', $cont);
chmod($iface_base_path . '/.htpasswd', 0644);

$cont = 'AuthType Basic
AuthName "Login"
AuthUserFile ' . $iface_base_path . '/.htpasswd
require valid-user';

if(file_exists($iface_path . '/.htaccess')) rename($iface_path . '/.htaccess', $iface_path . '/.htaccess.old');
file_put_contents($iface_path . '/.htaccess', $cont);
chmod($iface_path . '/.htaccess', 0644);
unset($cont);

print 'Data written. Please check, if everything is working correctly.' . "\n";
exit;

?>
