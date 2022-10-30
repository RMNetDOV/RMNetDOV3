<?php

class openvz_tools {
	/**
	 * Checks, if the server ist a OpenVZ - Host
	 */


	public function isOpenVzHost() {
		/*
		 * if there is a "/proc/user_beanconters" we have OpenVz "in use"
		*/
		if (file_exists('/proc/user_beancounters')) {
			/*
			 * if "vzctl" exists, it is a host
			*/
			system('which vzctl', $retval);
			if($retval === 0) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Checks, if the server ist a OpenVZ - VE
	 */
	public function isOpenVzVe() {
		/*
		 * if there is a "/proc/user_beanconters" we have OpenVz "in use"
		*/
		if (file_exists('/proc/user_beancounters')) {
			/*
			 * if "vzctl" does not exists, it is a VE
			*/
			system('which vzctl', $retval);
			if($retval === 0) {
				return false;
			}
			else {
				return true;
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Return information about all created VE's at this Host
	 */
	public function getOpenVzVeInfo() {
		/*
		 * If it is not a OpenVz - Host, we have NO VE's
		*/
		if (!$this->isOpenVzHost()) {
			return array();
		}
		/*
		 * if it is a OpenVz-Host, we first have to get the VE - List and then parse it to a array
		*/
		$output = shell_exec('vzlist -a');

		/* transfer this output-string into a array */
		$outputArray = explode("\n", $output);

		/* the first list of the output is not needed */
		array_shift($outputArray);

		/* now process all items of the rest */
		$res = array();
		foreach ($outputArray as $item) {
			/*
			 * eliminate all doubled spaces and spaces at the beginning and end
			 */
			while (strpos($item, '  ') !== false) {
				$item = str_replace('  ', ' ', $item);
			}
			$item = trim($item);

			/*
			 * Now get every token and insert it to the array
			 */
			if ($item != '') {
				$tmp = explode(' ', $item);
				$tmpRes['veid']     = $tmp[0];
				$tmpRes['nproc']    = $tmp[1];
				$tmpRes['status']   = $tmp[2];
				$tmpRes['ip_addr']  = $tmp[3];
				$tmpRes['hostname'] = $tmp[4];
				$res[] = $tmpRes;
			}
		}

		/* ready */
		return $res;
	}

	/**
	 * Return information about the user_beancounters of this VE
	 */
	public function getOpenVzVeBeanCounter() {
		/*
		 * If it is not a OpenVz - VE, we need no beancounter, because we use the beancounter
		 * "inside" of each VE
		*/
		if (!$this->isOpenVzVe()) {
			return "";
		}
		/*
		 * if it is a OpenVz-VE, we get the output to a string
		*/
		$res = file_get_contents('/proc/user_beancounters');

		/* ready */
		return $res;
	}

}

?>
