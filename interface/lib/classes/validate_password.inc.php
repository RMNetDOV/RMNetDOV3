<?php

class validate_password {
	
	private function _get_password_strength($password) {
		$length = strlen($password);
		
		$points = 0;
		if ($length < 5) {
			return 1;
		}

		$different = 0;
		if (preg_match('/[abcdefghijklnmopqrstuvwxyz]/', $password)) {
			$different += 1;
		}

		if (preg_match('/[ABCDEFGHIJKLNMOPQRSTUVWXYZ]/', $password)) {
			$points += 1;
			$different += 1;
		}

		if (preg_match('/[0123456789]/', $password)) {
			$points += 1;
			$different += 1;
		}

		if (preg_match('/[`~!@#$%^&*()_+|\\=\-\[\]}{\';:\/?.>,<" ]/', $password)) {
			$points += 1;
			$different += 1;
		}
		

		if ($points == 0 || $different < 3) {
			if ($length >= 5 && $length <= 6) {
				return 1;
			} else if ($length >= 7 && $length <= 8) {
				return 2;
			} else {
				return 3;
			}
		} else if ($points == 1) {
			if ($length >= 5 && $length <= 6) {
				return 2;
			} else if ($length >= 7 && $length <=10) {
				return 3;
			} else {
				return 4;
			}
		} else if ($points == 2) {
			if ($length >= 5 && $length <= 8) {
				return 3;
			} else if ($length >= 9 && $length <= 10) {
				return 4;
			} else {
				return 5;
			}
		} else if ($points == 3) {
			if ($length >= 5 && $length <= 6) {
				return 3;
			} else if ($length >= 7 && $length <= 8) {
				return 4;
			} else {
				return 5;
			}
		} else if ($points >= 4) {
			if ($length >= 5 && $length <= 6) {
				return 4;
			} else {
				return 5;
			}
		}
		
	}
	
	/* Validator function */
	function password_check($field_name, $field_value, $validator) {
		global $app;
		
		if($field_value == '') return false;
		
		$app->uses('ini_parser,getconf');
		$server_config_array = $app->getconf->get_global_config();
		
		$min_password_length = $app->auth->get_min_password_length();
		$min_password_strength = $app->auth->get_min_password_strength();
		
		if($min_password_strength > 0) {
			$lng_text = $app->lng('weak_password_txt');
			$lng_text = str_replace(array('{chars}', '{strength}'), array($min_password_length, $app->lng('strength_' . $min_password_strength)), $lng_text);
		} else {
			$lng_text = $app->lng('weak_password_length_txt');
			$lng_text = str_replace('{chars}', $min_password_length, $lng_text);
		}
		if(!$lng_text) $lng_text = 'weak_password_txt'; // always return a string, even if language is missing - otherwise validator is NOT MATCHING!

		if(strlen($field_value) < $min_password_length) return $lng_text;
		if($this->_get_password_strength($field_value) < $min_password_strength) return $lng_text;
		
		return false;
	}
}
