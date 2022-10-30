<?php

/* random_bytes can be dropped when php 5.6 support is dropped */
if (! function_exists('random_bytes')) {
	function random_bytes($length) {
		return openssl_random_pseudo_bytes($length);
	}
}

/* random_int can be dropped when php 5.6 support is dropped */
if (! function_exists('random_int')) {
	function random_int($min=null, $max=null) {
		if (null === $min) {
			$min = PHP_INT_MIN;
		}

		if (null === $max) {
			$min = PHP_INT_MAX;
		}

		if (!is_int($min) || !is_int($max)) {
			trigger_error('random_int: $min and $max must be integer values', E_USER_NOTICE);
			$min = (int)$min;
			$max = (int)$max;
		}

		if ($min > $max) {
			trigger_error('random_int: $max can\'t be lesser than $min', E_USER_WARNING);
			return null;
		}

		$range = $counter = $max - $min;
		$bits = 1;

		while ($counter >>= 1) {
			++$bits;
		}

		$bytes = (int)max(ceil($bits/8), 1);
		$bitmask = pow(2, $bits) - 1;

		if ($bitmask >= PHP_INT_MAX) {
			$bitmask = PHP_INT_MAX;
		}

		do {
			$result = hexdec(bin2hex(random_bytes($bytes))) & $bitmask;
		} while ($result > $range);

		return $result + $min;
	}
}
