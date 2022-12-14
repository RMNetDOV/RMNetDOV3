<?php

if(!defined('INSTALLER_RUN')) die('Patch update file access violation.');

/*
	Example installer patch update class. the classname must match
	the php and the sql patch update filename. The php patches are
	only executed when a corresponding sql patch exists.
*/

class upd_0086 extends installer_patch_update {

	public function onAfterSQL() {
		global $inst;
		
		// delete all the files that were deleted on previous updates
		$delete = array(
			'interface/web/js/mail_domain_dkim.js',
			'interface/web/mail/mail_domain_dkim_create.php'
		);
		
		$curpath = dirname(dirname(realpath(dirname(__FILE__))));
		
		$c = 0;
		$del_all = false;
		foreach($delete as $file) {
			if(strpos($file, '..') !== false) continue; // security!
			
			if($del_all == false) {
				$answer = $inst->simple_query('Delete obsolete file ' . $file . '?', array('y', 'n', 'a', 'all', 'none'), 'y');
				if($answer == 'n') continue;
				elseif($answer == 'a' || $answer == 'all') $del_all = true;
				elseif($answer == 'none') break;
			}
			if(@is_file('/usr/local/rmnetdov/' . $file) && !@is_file($curpath . '/' . $file)) {
				// be sure this is not a file contained in installation!
				@unlink('/usr/local/rmnetdov/' . $file);
				ilog('Deleted obsolete file /usr/local/rmnetdov/' . $file);
				$c++;
			}
		}
		ilog($c . 'obsolete files deleted.');
	}
}

?>
