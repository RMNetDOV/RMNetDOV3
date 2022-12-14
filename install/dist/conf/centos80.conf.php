<?php

//***  Fedora 9 default settings

//* Main
$conf['language'] = 'sl';
$conf['distname'] = 'centos80';
$conf['hostname'] = 'server1.domain.tld'; // Full hostname
$conf['rmnetdov_install_dir'] = '/usr/local/rmnetdov';
$conf['rmnetdov_config_dir'] = '/usr/local/rmnetdov';
$conf['rmnetdov_log_priority'] = 2;  // 0 = Debug, 1 = Warning, 2 = Error
$conf['rmnetdov_log_dir'] = '/var/log/rmnetdov';
$conf['server_id'] = 1;
$conf['init_scripts'] = '/etc/init.d';
$conf['runlevel'] = '/etc';
$conf['shells'] = '/etc/shells';
$conf['pam'] = '/etc/pam.d';

//* Services provided by this server, this selection will be overridden by the expert mode
$conf['services']['mail'] = true;
$conf['services']['web'] = true;
$conf['services']['dns'] = true;
$conf['services']['file'] = true;
$conf['services']['db'] = true;
$conf['services']['vserver'] = true;

//* MySQL
$conf['mysql']['installed'] = false; // will be detected automatically during installation
$conf['mysql']['init_script'] = 'mariadb';
$conf['mysql']['host'] = 'localhost';
$conf['mysql']['ip'] = '127.0.0.1';
$conf['mysql']['port'] = '3306';
$conf['mysql']['database'] = 'dbrmnetdov';
$conf['mysql']['admin_user'] = 'root';
$conf['mysql']['admin_password'] = '';
$conf['mysql']['charset'] = 'utf8';
$conf['mysql']['rmnetdov_user'] = 'rmnetdov';
$conf['mysql']['rmnetdov_password'] = md5(random_bytes(20));
$conf['mysql']['master_slave_setup'] = 'n';
$conf['mysql']['master_host'] = '';
$conf['mysql']['master_database'] = 'dbrmnetdov';
$conf['mysql']['master_admin_user'] = 'root';
$conf['mysql']['master_admin_password'] = '';
$conf['mysql']['master_rmnetdov_user'] = '';
$conf['mysql']['master_rmnetdov_password'] = md5(random_bytes(20));

//* Apache
$conf['apache']['installed'] = false; // will be detected automatically during installation
$conf['apache']['user'] = 'apache';
$conf['apache']['group'] = 'apache';
$conf['apache']['init_script'] = 'httpd';
$conf['apache']['version'] = '2.2';
$conf['apache']['vhost_conf_dir'] = '/etc/httpd/conf/sites-available';
$conf['apache']['vhost_conf_enabled_dir'] = '/etc/httpd/conf/sites-enabled';
$conf['apache']['vhost_port'] = '8090';
$conf['apache']['php_ini_path_apache'] = '/etc/php.ini';
$conf['apache']['php_ini_path_cgi'] = '/etc/php.ini';

//* Website base settings
$conf['web']['website_basedir'] = '/var/www';
$conf['web']['website_path'] = '/var/www/clients/client[client_id]/web[website_id]';
$conf['web']['website_symlinks'] = '/var/www/[website_domain]/:/var/www/clients/client[client_id]/[website_domain]/';

//* Apps base settings
$conf['web']['apps_vhost_ip'] = '_default_';
$conf['web']['apps_vhost_port'] = '8081';
$conf['web']['apps_vhost_servername'] = '';
$conf['web']['apps_vhost_user'] = 'ispapps';
$conf['web']['apps_vhost_group'] = 'ispapps';

//* AWStats settings
$conf['awstats']['pl'] = '/usr/share/awstats/wwwroot/cgi-bin/awstats.pl';

//* Fastcgi
$conf['fastcgi']['fastcgi_phpini_path'] = '/etc/';
$conf['fastcgi']['fastcgi_starter_path'] = '/var/www/php-fcgi-scripts/[system_user]/';
$conf['fastcgi']['fastcgi_bin'] = '/usr/bin/php-cgi';

//* Postfix
$conf['postfix']['installed'] = false; // will be detected automatically during installation
$conf['postfix']['config_dir'] = '/etc/postfix';
$conf['postfix']['init_script'] = 'postfix';
$conf['postfix']['user'] = 'postfix';
$conf['postfix']['group'] = 'postfix';
$conf['postfix']['vmail_userid'] = '5000';
$conf['postfix']['vmail_username'] = 'vmail';
$conf['postfix']['vmail_groupid'] = '5000';
$conf['postfix']['vmail_groupname'] = 'vmail';
$conf['postfix']['vmail_mailbox_base'] = '/var/vmail';

//* Mailman
$conf['mailman']['installed'] = false; // will be detected automatically during installation
$conf['mailman']['config_dir'] = '/etc/mailman';
$conf['mailman']['init_script'] = 'mailman';

//* Getmail
$conf['getmail']['installed'] = false; // will be detected automatically during installation
$conf['getmail']['config_dir'] = '/etc/getmail';
$conf['getmail']['program'] = '/usr/bin/getmail';

//* Courier
$conf['courier']['installed'] = false; // will be detected automatically during installation
$conf['courier']['config_dir'] = '/etc/authlib';
$conf['courier']['courier-authdaemon'] = 'courier-authlib';
$conf['courier']['courier-imap'] = 'courier-imap';
$conf['courier']['courier-imap-ssl'] = '';
$conf['courier']['courier-pop'] = '';
$conf['courier']['courier-pop-ssl'] = '';

//* Dovecot
$conf['dovecot']['installed'] = false; // will be detected automatically during installation
$conf['dovecot']['config_dir'] = '/etc/dovecot';
$conf['dovecot']['init_script'] = 'dovecot';

//* SASL
$conf['saslauthd']['installed'] = false; // will be detected automatically during installation
$conf['saslauthd']['config'] = '/etc/sysconfig/saslauthd';
$conf['saslauthd']['init_script'] = 'saslauthd';

//* Amavisd
$conf['amavis']['installed'] = false; // will be detected automatically during installation
$conf['amavis']['config_dir'] = '/etc/amavisd';
$conf['amavis']['init_script'] = 'amavisd';

//* Rspamd
$conf['rspamd']['installed'] = false; // will be detected automatically during installation
$conf['rspamd']['config_dir'] = '/etc/rspamd';
$conf['rspamd']['init_script'] = 'rspamd';

//* ClamAV
$conf['clamav']['installed'] = false; // will be detected automatically during installation
$conf['clamav']['init_script'] = 'clamd@amavisd';

//* Pureftpd
$conf['pureftpd']['installed'] = false; // will be detected automatically during installation
$conf['pureftpd']['config_dir'] = '/etc/pure-ftpd';
$conf['pureftpd']['init_script'] = 'pure-ftpd';

//* MyDNS
$conf['mydns']['installed'] = false; // will be detected automatically during installation
$conf['mydns']['config_dir'] = '/etc';
$conf['mydns']['init_script'] = 'mydns';

//* PowerDNS
$conf['powerdns']['installed'] = false; // will be detected automatically during installation
$conf['powerdns']['database'] = 'powerdns';
$conf["powerdns"]["config_dir"] = '/etc/powerdns/pdns.d';
$conf['powerdns']['init_script'] = 'pdns';

//* BIND DNS Server
$conf['bind']['installed'] = false; // will be detected automatically during installation
$conf['bind']['bind_user'] = 'named';
$conf['bind']['bind_group'] = 'named';
$conf['bind']['bind_zonefiles_dir'] = '/var/named';
$conf['bind']['named_conf_path'] = '/etc/named.conf';
$conf['bind']['named_conf_local_path'] = '/etc/named.conf.local';
$conf['bind']['init_script'] = 'named';

//* Jailkit
$conf['jailkit']['installed'] = false; // will be detected automatically during installation
$conf['jailkit']['config_dir'] = '/etc/jailkit';
$conf['jailkit']['jk_init'] = 'jk_init.ini';
$conf['jailkit']['jk_chrootsh'] = 'jk_chrootsh.ini';
$conf['jailkit']['jailkit_chroot_app_programs'] = '/usr/bin/groups /usr/bin/id /usr/bin/dircolors /bin/basename /usr/bin/dirname /usr/bin/nano';
$conf['jailkit']['jailkit_chroot_cron_programs'] = '/usr/bin/php /usr/bin/perl /usr/share/perl /usr/share/php';

//* Squid
$conf['squid']['installed'] = false; // will be detected automatically during installation
$conf['squid']['config_dir'] = '/etc/squid';
$conf['squid']['init_script'] = 'squid';

//* Nginx
$conf['nginx']['installed'] = false; // will be detected automatically during installation
$conf['nginx']['user'] = 'nginx';
$conf['nginx']['group'] = 'nginx';
$conf['nginx']['config_dir'] = '/etc/nginx';
$conf['nginx']['vhost_conf_dir'] = '/etc/nginx/sites-available';
$conf['nginx']['vhost_conf_enabled_dir'] = '/etc/nginx/sites-enabled';
$conf['nginx']['init_script'] = 'nginx';
$conf['nginx']['vhost_port'] = '8090';
$conf['nginx']['cgi_socket'] = '/var/run/fcgiwrap.socket';
$conf['nginx']['php_fpm_init_script'] = 'php-fpm';
$conf['nginx']['php_fpm_ini_path'] = '/etc/php.ini';
$conf['nginx']['php_fpm_pool_dir'] = '/etc/php-fpm.d';
$conf['nginx']['php_fpm_start_port'] = 9010;
$conf['nginx']['php_fpm_socket_dir'] = '/var/lib/php5-fpm';

//* vlogger
$conf['vlogger']['config_dir'] = '/etc';

//* cron
$conf['cron']['init_script'] = 'crond';
$conf['cron']['crontab_dir'] = '/etc/cron.d';
$conf['cron']['wget'] = '/usr/bin/wget';

//* OpenVZ
$conf['openvz']['installed'] = false;

// AppArmor
$conf['apparmor']['installed'] = false;

?>
