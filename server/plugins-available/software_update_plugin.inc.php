<?php

class software_update_plugin {

	var $plugin_name = 'software_update_plugin';
	var $class_name  = 'software_update_plugin';

	//* This function is called during rmnetdov installation to determine
	//  if a symlink shall be created for this plugin.
	public function onInstall() {
		global $conf;

		return true;
	}

	/*
	 	This function is called when the plugin is loaded
	*/

	public function onLoad() {
		global $app;
		//* Register for actions
		$app->plugins->registerAction('os_update', $this->plugin_name, 'os_update');
	}

	//* Operating system update
	public function os_update($action_name, $data) {
		global $app;

		//** Debian and compatible Linux distributions
		if(file_exists('/etc/debian_version')) {
			exec("apt-get update");
			exec("apt-get upgrade -y");
			$app->log('Izvedena posodobitev Debian / Ubuntu', LOGLEVEL_DEBUG);
		}

		//** Redhat, CentOS, Fedora
		if(file_exists('/etc/redhat-release')) {
			exec("which dnf &> /dev/null && dnf -y update || yum -y update");
		}

		//** Gentoo Linux
		if(file_exists('/etc/gentoo-release')) {
			exec("glsa-check -f --nocolor affected");
			$app->log('Izvedena posodobitev Gentoo', LOGLEVEL_DEBUG);
		}

		return 'ok';
	}

} // end class

?>
