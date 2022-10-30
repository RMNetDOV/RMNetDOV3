<?php

//** Web-only
if( !empty($_SERVER['DOCUMENT_ROOT']) ) {

	header("Pragma: no-cache");
	header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
	header("Content-Type: text/html; charset=utf-8");
	header('X-Content-Type-Options: nosniff');

	ini_set('register_globals', 0);
}

//** SVN Revision
$svn_revision = '$Revision: 1525 $';
$revision = str_replace(array('Revision:', '$', ' '), '', $svn_revision);

//** Application
define('RMNETDOV_APP_TITLE', '..::RM-Net - DOV Control Panel::..');
define('RMNETDOV_APP_VERSION', '3.2dev');
define('DEVSYSTEM', 0);


//** Database
$conf['db_type'] = 'mysql';
$conf['db_host'] = 'localhost';
$conf['db_port'] = 3306;
$conf['db_database'] = 'rmnetdov3_305';
$conf['db_user'] = 'root';
$conf['db_password'] = '';
$conf['db_charset'] = 'utf8'; // same charset as html-charset - (HTML --> MYSQL: "utf-8" --> "utf8", "iso-8859-1" --> "latin1")
$conf['db_new_link'] = false;
$conf['db_client_flags'] = 0;

define('DB_TYPE', $conf['db_type']);
define('DB_HOST', $conf['db_host']);
define('DB_PORT', $conf['db_port']);
define('DB_DATABASE', $conf['db_database']);
define('DB_USER', $conf['db_user']);
define('DB_PASSWORD', $conf['db_password']);
define('DB_CHARSET', $conf['db_charset']);


//** Database settings for the master DB. This setting is only used in multiserver setups
$conf['dbmaster_type']   = 'mysql';
$conf['dbmaster_host']   = '{mysql_master_server_host}';
$conf['dbmaster_port']   = '{mysql_master_server_port}';
$conf['dbmaster_database']  = '{mysql_master_server_database}';
$conf['dbmaster_user']   = '{mysql_master_server_rmnetdov_user}';
$conf['dbmaster_password']  = '{mysql_master_server_rmnetdov_password}';
$conf['dbmaster_new_link']   = false;
$conf['dbmaster_client_flags']  = 0;


//** Paths
$conf['rmnetdov_log_dir'] = '/var/log/rmnetdov';
define('RMNETDOV_ROOT_PATH', realpath(dirname(__FILE__).'/../')); // The main ROOT is the parent directory to this file, ie Interface/. NO trailing slashes.
define('RMNETDOV_LIB_PATH', RMNETDOV_ROOT_PATH.'/lib');
define('RMNETDOV_CLASS_PATH', RMNETDOV_ROOT_PATH.'/lib/classes');
define('RMNETDOV_WEB_PATH', RMNETDOV_ROOT_PATH.'/web');
define('RMNETDOV_THEMES_PATH', RMNETDOV_ROOT_PATH.'/web/themes');
define('RMNETDOV_WEB_TEMP_PATH', RMNETDOV_WEB_PATH.'/temp'); // Path for downloads, accessible via browser
define('RMNETDOV_CACHE_PATH', RMNETDOV_ROOT_PATH.'/cache');

//** Paths (Do not change!)
$conf['rootpath'] = substr(dirname(__FILE__), 0, -4);
$conf['fs_div'] = '/'; // File system separator (divider), "\\" on Windows and "/" on Linux and UNIX
$conf['classpath'] = $conf['rootpath'].$conf['fs_div'].'lib'.$conf['fs_div'].'classes';
$conf['temppath'] = $conf['rootpath'].$conf['fs_div'].'temp';

define('FS_DIV', $conf['fs_div']);
define('SERVER_ROOT', $conf['rootpath']);
define('INCLUDE_ROOT', SERVER_ROOT.FS_DIV.'lib');
define('CLASSES_ROOT', INCLUDE_ROOT.FS_DIV.'classes');


//** Server
$conf['app_title'] = RMNETDOV_APP_TITLE;
$conf['app_version'] = RMNETDOV_APP_VERSION;
$conf['app_link'] = 'https://www.howtoforge.com/forums/showthread.php?t=26988';
$conf['modules_available'] = 'admin,mail,sites,monitor,client,dns,help';
$conf['server_id'] = '1';


//** Interface
$conf['interface_modules_enabled'] = 'dashboard,mail,sites,dns,tools';

//** Demo mode
/* Demo način je možnost za omejitev določenih dejanj v vmesniku, npr
* sprememba gesla uporabnikov s sys_userid < 3 itd
* lahko zažene vmesnik RM-Net - DOV CP kot spletno predstavitev. Ne gre
* vpliva na strežniški del. Demo način mora biti vedno nastavljen na false
* pri vsaki običajni namestitvi
*/
$conf['demo_mode'] = false;


//** Logging
$conf['log_file'] = $conf['rmnetdov_log_dir'].'/rmnetdov.log';
$conf['log_priority'] = 0; // 0 = Debug, 1 = Warning, 2 = Error


//** Themes
$conf['theme'] = 'default';
$conf['html_content_encoding'] = 'utf-8'; // example: utf-8, iso-8859-1, ...
$conf['logo'] = 'themes/default/images/header_logo.png';

//** Templates
$conf['templates'] = '/usr/local/rmnetdov/server/conf';

//** Default Language
$conf['language'] = 'en';
$conf['debug_language'] = false;
$conf['language_file_import_enabled'] = true; // Bool value: true / false

//** Default Country
$conf['country'] = 'DE';


//** Misc.
$conf['interface_logout_url'] = ''; // example: http://www.domain.tld/


//** Auto Load Modules
$conf['start_db'] = true;
$conf['start_session'] = true;


//** Constants
define('LOGLEVEL_DEBUG', 0);
define('LOGLEVEL_WARN', 1);
define('LOGLEVEL_ERROR', 2);

//** Admin IP whitelist file
$conf['admin_ip_whitelist_file'] = '/usr/local/rmnetdov/security/admin_ip.whitelist';

?>
