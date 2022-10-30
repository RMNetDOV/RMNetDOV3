ALTER TABLE `mail_user`
	CHANGE `uid` `uid` int(11) NOT NULL DEFAULT '5000',
	CHANGE `gid` `gid` int(11) NOT NULL DEFAULT '5000';

ALTER TABLE `mail_user`
	ADD COLUMN `sender_cc` varchar(255) NOT NULL DEFAULT '' AFTER `cc`;

ALTER TABLE `client_template` ADD `default_mailserver` INT(11) NOT NULL DEFAULT 1;
ALTER TABLE `client_template` ADD `default_webserver` INT(11) NOT NULL DEFAULT 1;
ALTER TABLE `client_template` ADD `default_dnsserver` INT(11) NOT NULL DEFAULT 1;
ALTER TABLE `client_template` ADD `default_slave_dnsserver` INT(11) NOT NULL DEFAULT 1;
ALTER TABLE `client_template` ADD `default_dbserver` INT(11) NOT NULL DEFAULT 1;
ALTER TABLE `client_template`
  ADD COLUMN `default_xmppserver` int(11) unsigned NOT NULL DEFAULT '1',
  ADD COLUMN `xmpp_servers` blob,
  ADD COLUMN `limit_xmpp_domain` int(11) NOT NULL DEFAULT '-1',
  ADD COLUMN `limit_xmpp_user` int(11) NOT NULL DEFAULT '-1',
  ADD COLUMN `limit_xmpp_muc` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_anon` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_vjud` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_proxy` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_status` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_pastebin` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_httparchive` ENUM( 'n', 'y' ) NOT NULL default 'n';

ALTER TABLE  `client` ADD  `contact_firstname` VARCHAR( 64 ) NOT NULL DEFAULT '' AFTER  `gender`;

UPDATE `dns_template` SET `fields` = 'DOMAIN,IP,NS1,NS2,EMAIL,DKIM,DNSSEC' WHERE `dns_template`.`template_id` =1;
UPDATE `dns_template` SET `template` = '[ZONE]
origin={DOMAIN}.
ns={NS1}.
mbox={EMAIL}.
refresh=7200
retry=540
expire=604800
minimum=3600
ttl=3600

[DNS_RECORDS]
A|{DOMAIN}.|{IP}|0|3600
A|www|{IP}|0|3600
A|mail|{IP}|0|3600
NS|{DOMAIN}.|{NS1}.|0|3600
NS|{DOMAIN}.|{NS2}.|0|3600
MX|{DOMAIN}.|mail.{DOMAIN}.|10|3600
TXT|{DOMAIN}.|v=spf1 mx a ~all|0|3600' WHERE `dns_template`.`template_id` = 1;

ALTER TABLE `sys_user` ADD `lost_password_function` TINYINT(1) NOT NULL DEFAULT '1' ;
ALTER TABLE `mail_backup` CHANGE `filesize` `filesize` VARCHAR(20) NOT NULL DEFAULT '';
ALTER TABLE `web_backup` CHANGE `filesize` `filesize` VARCHAR(20) NOT NULL DEFAULT '';

ALTER TABLE `sys_datalog` ADD INDEX `dbtable` (`dbtable` (25), `dbidx` (25)), ADD INDEX (`action`);
ALTER TABLE `mail_user` ADD `greylisting` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n' AFTER `postfix`;
ALTER TABLE `mail_user` ADD `maildir_format` varchar(255) NOT NULL default 'maildir' AFTER `maildir`;
ALTER TABLE `mail_forwarding` ADD `greylisting` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n' AFTER `active`;

ALTER TABLE `openvz_ip` CHANGE `ip_address` `ip_address` VARCHAR(39) DEFAULT NULL;

-- XMPP Support

ALTER TABLE `server` ADD COLUMN `xmpp_server` tinyint(1) NOT NULL default '0' AFTER `firewall_server`;

ALTER TABLE `client`
  ADD COLUMN `default_xmppserver` int(11) unsigned NOT NULL DEFAULT '1',
  ADD COLUMN `xmpp_servers` blob,
  ADD COLUMN `limit_xmpp_domain` int(11) NOT NULL DEFAULT '-1',
  ADD COLUMN `limit_xmpp_user` int(11) NOT NULL DEFAULT '-1',
  ADD COLUMN `limit_xmpp_muc` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_anon` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_auth_options` varchar(255) NOT NULL DEFAULT 'plain,hashed,isp',
  ADD COLUMN `limit_xmpp_vjud` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_proxy` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_status` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_pastebin` ENUM( 'n', 'y' ) NOT NULL default 'n',
  ADD COLUMN `limit_xmpp_httparchive` ENUM( 'n', 'y' ) NOT NULL default 'n';


CREATE TABLE `xmpp_domain` (
  `domain_id` int(11) unsigned NOT NULL auto_increment,
  `sys_userid` int(11) unsigned NOT NULL default '0',
  `sys_groupid` int(11) unsigned NOT NULL default '0',
  `sys_perm_user` varchar(5) NOT NULL default '',
  `sys_perm_group` varchar(5) NOT NULL default '',
  `sys_perm_other` varchar(5) NOT NULL default '',
  `server_id` int(11) unsigned NOT NULL default '0',
  `domain` varchar(255) NOT NULL default '',

  `management_method` ENUM( 'normal', 'maildomain' ) NOT NULL default 'normal',
  `public_registration` ENUM( 'n', 'y' ) NOT NULL default 'n',
  `registration_url` varchar(255) NOT NULL DEFAULT '',
  `registration_message` varchar(255) NOT NULL DEFAULT '',
  `domain_admins` text,

  `use_pubsub` enum('n','y') NOT NULL DEFAULT 'n',
  `use_proxy` enum('n','y') NOT NULL DEFAULT 'n',
  `use_anon_host` enum('n','y') NOT NULL DEFAULT 'n',

  `use_vjud` enum('n','y') NOT NULL DEFAULT 'n',
  `vjud_opt_mode` enum('in', 'out') NOT NULL DEFAULT 'in',

  `use_muc_host` enum('n','y') NOT NULL DEFAULT 'n',
  `muc_name` varchar(30) NOT NULL DEFAULT '',
  `muc_restrict_room_creation` enum('n', 'y', 'm') NOT NULL DEFAULT 'm',
  `muc_admins` text,
  `use_pastebin` enum('n','y') NOT NULL DEFAULT 'n',
  `pastebin_expire_after` int(3) NOT NULL DEFAULT 48,
  `pastebin_trigger` varchar(10) NOT NULL DEFAULT '!paste',
  `use_http_archive` enum('n','y') NOT NULL DEFAULT 'n',
  `http_archive_show_join` enum('n', 'y') NOT NULL DEFAULT 'n',
  `http_archive_show_status` enum('n', 'y') NOT NULL DEFAULT 'n',
  `use_status_host` enum('n','y') NOT NULL DEFAULT 'n',

  `ssl_state` varchar(255) NULL,
  `ssl_locality` varchar(255) NULL,
  `ssl_organisation` varchar(255) NULL,
  `ssl_organisation_unit` varchar(255) NULL,
  `ssl_country` varchar(255) NULL,
  `ssl_email` varchar(255) NULL,
  `ssl_request` mediumtext NULL,
  `ssl_cert` mediumtext NULL,
  `ssl_bundle` mediumtext NULL,
  `ssl_key` mediumtext NULL,
  `ssl_action` varchar(16) NULL,

  `active` enum('n','y') NOT NULL DEFAULT 'n',
  PRIMARY KEY  (`domain_id`),
  KEY `server_id` (`server_id`,`domain`),
  KEY `domain_active` (`domain`,`active`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table  `xmpp_user`
--

CREATE TABLE `xmpp_user` (
  `xmppuser_id` int(11) unsigned NOT NULL auto_increment,
  `sys_userid` int(11) unsigned NOT NULL default '0',
  `sys_groupid` int(11) unsigned NOT NULL default '0',
  `sys_perm_user` varchar(5) NOT NULL default '',
  `sys_perm_group` varchar(5) NOT NULL default '',
  `sys_perm_other` varchar(5) NOT NULL default '',
  `server_id` int(11) unsigned NOT NULL default '0',
  `jid` varchar(255) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `active` enum('n','y') NOT NULL DEFAULT 'n',
  PRIMARY KEY  (`xmppuser_id`),
  KEY `server_id` (`server_id`,`jid`),
  KEY `jid_active` (`jid`,`active`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

ALTER TABLE `sys_ini` ADD `default_logo` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , ADD `custom_logo` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
UPDATE `sys_ini` SET `default_logo` = 'data:image/png;base64,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' WHERE `sys_ini`.`sysini_id` = 1;

ALTER TABLE `directive_snippets` ADD `required_php_snippets` VARCHAR(255) NOT NULL DEFAULT '' AFTER `customer_viewable`;
ALTER TABLE `dns_rr` CHANGE `ttl` `ttl` INT(11) UNSIGNED NOT NULL DEFAULT '3600';
ALTER TABLE `dns_soa` CHANGE `minimum` `minimum` INT(11) UNSIGNED NOT NULL DEFAULT '3600', CHANGE `ttl` `ttl` INT(11) UNSIGNED NOT NULL DEFAULT '3600';
ALTER TABLE `client` CHANGE `web_php_options` `web_php_options` VARCHAR(255) NOT NULL DEFAULT 'no,fast-cgi,cgi,mod,suphp,php-fpm,hhvm';
ALTER TABLE `web_domain` ADD COLUMN `enable_pagespeed` ENUM('y','n') NOT NULL DEFAULT 'n' AFTER `directive_snippets_id`;

ALTER TABLE openvz_template ADD COLUMN `features` varchar(255) DEFAULT NULL AFTER `capability`;
ALTER TABLE openvz_vm ADD COLUMN `features` TEXT DEFAULT NULL AFTER `capability`;
ALTER TABLE openvz_template ADD COLUMN `iptables` varchar(255) DEFAULT NULL AFTER `features`;
ALTER TABLE openvz_vm ADD COLUMN `iptables` TEXT DEFAULT NULL AFTER `features`;

CREATE TABLE `server_ip_map` (
  `server_ip_map_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sys_userid` int(11) unsigned NOT NULL DEFAULT '0',
  `sys_groupid` int(11) unsigned NOT NULL DEFAULT '0',
  `sys_perm_user` varchar(5) DEFAULT NULL,
  `sys_perm_group` varchar(5) DEFAULT NULL,
  `sys_perm_other` varchar(5) DEFAULT NULL,
  `server_id` int(11) unsigned NOT NULL DEFAULT '0',
  `source_ip` varchar(15) DEFAULT NULL,
  `destination_ip` varchar(35) DEFAULT '',
  `active` enum('n','y') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`server_ip_map_id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `web_domain` ADD COLUMN `rewrite_to_https` ENUM('y','n') NOT NULL DEFAULT 'n' AFTER `seo_redirect`;

ALTER TABLE openvz_ip ADD COLUMN `additional` VARCHAR(255) NOT NULL DEFAULT 'n';

ALTER TABLE openvz_template ADD COLUMN `custom` text;

ALTER TABLE openvz_vm
  ADD COLUMN `bootorder` INT(11) NOT NULL DEFAULT '1' AFTER `start_boot`,
  ADD COLUMN `custom` text;

ALTER TABLE `web_domain` ADD `ssl_letsencrypt` enum('n','y') NOT NULL DEFAULT 'n' AFTER `ssl`;

ALTER TABLE `openvz_template` CHANGE `vmguarpages` `vmguarpages` varchar(255) DEFAULT '65536:unlimited';
ALTER TABLE `openvz_template` CHANGE `privvmpages` `privvmpages` varchar(255) DEFAULT '131072:139264';

CREATE TABLE `ftp_traffic` (
	`hostname` varchar(255) NOT NULL,
	`traffic_date` date NOT NULL,
	`in_bytes` bigint(32) unsigned NOT NULL,
	`out_bytes` bigint(32) unsigned NOT NULL, 
	UNIQUE KEY (`hostname`,`traffic_date`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `mail_forwarding` ADD COLUMN `allow_send_as` ENUM('n','y') NOT NULL DEFAULT 'n' AFTER `active`;
UPDATE `mail_forwarding` SET `allow_send_as` = 'y' WHERE `type` = 'alias';

ALTER TABLE `dns_rr` CHANGE COLUMN `type` `type` ENUM('A','AAAA','ALIAS','CNAME','DS','HINFO','LOC','MX','NAPTR','NS','PTR','RP','SRV','TXT','TLSA','DNSKEY') NULL DEFAULT NULL AFTER `name`;

ALTER TABLE `dns_soa`
	ADD COLUMN `dnssec_initialized` ENUM('Y','N') NOT NULL DEFAULT 'N',
	ADD COLUMN `dnssec_wanted` ENUM('Y','N') NOT NULL DEFAULT 'N',
	ADD COLUMN `dnssec_last_signed` BIGINT NOT NULL DEFAULT '0',
	ADD COLUMN `dnssec_info` TEXT NULL;

ALTER TABLE `client` ADD COLUMN `limit_ssl_letsencrypt` enum('n','y') NOT NULL DEFAULT 'n' AFTER `limit_ssl`;
ALTER TABLE `client_template` ADD COLUMN `limit_ssl_letsencrypt` enum('n','y') NOT NULL DEFAULT 'n' AFTER `limit_ssl`;
ALTER TABLE `client` ADD COLUMN `limit_directive_snippets` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n' AFTER `limit_backup`;
ALTER TABLE `client_template` ADD COLUMN `limit_directive_snippets` ENUM( 'n', 'y' ) NOT NULL DEFAULT 'n' AFTER `limit_backup`;

ALTER TABLE `sys_user`
	ADD COLUMN `lost_password_hash` VARCHAR(50) NOT NULL DEFAULT '',
	ADD COLUMN `lost_password_reqtime` DATETIME NULL default NULL;

ALTER TABLE `web_database` ADD COLUMN `quota_exceeded` enum('n','y') NOT NULL DEFAULT 'n' AFTER `database_quota`;

ALTER TABLE `client` ADD COLUMN `limit_database_user` int(11) NOT NULL DEFAULT '-1' after limit_database;
ALTER TABLE `client_template` ADD COLUMN `limit_database_user` int(11) NOT NULL DEFAULT '-1' after limit_database;
ALTER TABLE `client` CHANGE `customer_no_template` `customer_no_template` VARCHAR(255) NULL DEFAULT 'R[CLIENTID]C[CUSTOMER_NO]';
	
ALTER TABLE `client` CHANGE `added_date` `added_date` DATE NULL DEFAULT NULL;
ALTER TABLE `ftp_user` CHANGE `expires` `expires` DATETIME NULL DEFAULT NULL;
ALTER TABLE `mail_user` CHANGE `autoresponder_start_date` `autoresponder_start_date` DATETIME NULL DEFAULT NULL;
ALTER TABLE `mail_user` CHANGE `autoresponder_end_date` `autoresponder_end_date` DATETIME NULL DEFAULT NULL;
ALTER TABLE `openvz_traffic` CHANGE `traffic_date` `traffic_date` DATE NULL DEFAULT NULL;
ALTER TABLE `openvz_vm` CHANGE `active_until_date` `active_until_date` DATE NULL DEFAULT NULL;
ALTER TABLE `sys_session` CHANGE `date_created` `date_created` DATETIME NULL DEFAULT NULL;
ALTER TABLE `sys_session` CHANGE `last_updated` `last_updated` DATETIME NULL DEFAULT NULL;
ALTER TABLE `web_domain` CHANGE `added_date` `added_date` DATE NULL DEFAULT NULL;
ALTER TABLE `web_traffic` CHANGE `traffic_date` `traffic_date` DATE NULL DEFAULT NULL;

UPDATE `client` SET `added_date` = NULL WHERE `added_date` = '0000-00-00';
UPDATE `ftp_user` SET `expires` = NULL WHERE `expires` = '0000-00-00 00:00:00';
UPDATE `mail_user` SET `autoresponder_start_date` = NULL WHERE `autoresponder_start_date` = '0000-00-00 00:00:00';
UPDATE `mail_user` SET `autoresponder_end_date` = NULL WHERE `autoresponder_end_date` = '0000-00-00 00:00:00';
UPDATE `openvz_traffic` SET `traffic_date` = NULL WHERE `traffic_date` = '0000-00-00';
UPDATE `openvz_vm` SET `active_until_date` = NULL WHERE `active_until_date` = '0000-00-00';
UPDATE `sys_session` SET `date_created` = NULL WHERE `date_created` = '0000-00-00 00:00:00';
UPDATE `sys_session` SET `last_updated` = NULL WHERE `last_updated` = '0000-00-00 00:00:00';
UPDATE `web_domain` SET `added_date` = NULL WHERE `added_date` = '0000-00-00';
UPDATE `web_traffic` SET `traffic_date` = NULL WHERE `traffic_date` = '0000-00-00';
ALTER TABLE `web_domain` ADD `http_port` INT NOT NULL DEFAULT '80' , ADD `https_port` INT NOT NULL DEFAULT '443' ;
