<?php

/*
  Some common helper functions which can be reused throughout the project.
*/

/**
 * Includes all the menu files from the menu dir.
 * @param string $menu_dir Path to the menu dir
 * @return void
 */
function include_menu_dir_files($menu_dir)
{
	global $app, $module;

	if (is_dir($menu_dir)) {
		if ($dh = opendir($menu_dir)) {
			//** Go through all files in the menu dir
			while (($file = readdir($dh)) !== false) {
				if ($file != '.' && $file != '..' && substr($file, -9, 9) == '.menu.php' && $file != 'dns_resync.menu.php') {
					include_once $menu_dir.'/'.$file;
				}
			}
		}
	}
}

