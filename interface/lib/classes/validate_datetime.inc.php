<?php

class validate_datetime
{
	/**
	 * Check if the parsed datetime selectors are not set
	 * to it's default value (zero).
	 *
	 * @param array $field_value
	 * @return bool
	 */


	function _datetime_selected($field_value)
	{
		if (is_array($field_value) && count($field_value) >= 5)
		{
			$result = array_filter($field_value, create_function('$dt_unit', 'return ($dt_unit > 0);'));
			return count($result) !== 0;
		}

		return false;
	}





	/**
	 * Check wordbook for the existence of an
	 * error message. If not found will return
	 * the parsed error message with line break.
	 *
	 * @param $errmsg
	 * @return string
	 */
	function _get_error($errmsg)
	{
		global $app;

		$errmsg = (isset($app->tform->wordbook[$errmsg])) ? $app->tform->wordbook[$errmsg] : $errmsg;
		$errmsg .= '<br />' . PHP_EOL;

		return $errmsg;
	}





	/**
	 * Helper function - filter the contents of the
	 * selectors and return the resulting unix timestamp.
	 *
	 * @param $field_value
	 * @return int Unix timestamp
	 */
	function _get_timestamp_value($field_value)
	{
		if(!is_array($field_value)) return 0;
		$second = 0;
		$filtered_values = array_map(create_function('$item', 'return (int)$item;'), $field_value);
		extract($filtered_values, EXTR_OVERWRITE);

		return mktime($hour, $minute, $second, $month, $day, $year);
	}





	/**
	 * The minimum requirement to submit a datetime field
	 * is to set the day, month and year values. Check that
	 * these values are not zero (default).
	 *
	 * @param string $field_name
	 * @param array $field_value
	 * @param array $validator
	 * @return string|void Error message if found
	 */
	function not_empty($field_name, $field_value, $validator)
	{
		if(!is_array($field_value)) return $this->_get_error($validator['errmsg']);
		extract($field_value);
		if ( !($day > 0 && $month > 0 && $year > 0) ) {
			return $this->_get_error($validator['errmsg']);
		}
	}





	/**
	 * Check that the selected datetime is in the future.
	 *
	 * @param string $field_name
	 * @param array $field_value
	 * @param array $validator
	 * @return string|void Error message if found
	 */
	function is_future($field_name, $field_value, $validator)
	{
		$validator['compare'] = mktime(date('H'), (date('i')-30), 0, date('m'), date('d'), date('Y')); // Turn back the clock 30 minutes for slow posters.
		return $this->is_greater($field_name, $field_value, $validator);
	}





	/**
	 * Compare the selected datetime to a timestamp
	 * parsed via the validator array (key: compare).
	 *
	 * @param string $field_name
	 * @param array $field_value
	 * @param array $validator
	 * @return string|void Error message if found
	 */
	function is_greater($field_name, $field_value, $validator)
	{
		if ( !isset($validator['compare']) || !is_numeric($validator['compare']) || (date('d/m/Y', $validator['compare']) == '01/01/1970') ) {
			return $this->_get_error('Could not find a unix timestamp to compare datetime with.');
		}

		$dt_stamp = $this->_get_timestamp_value($field_value);
		if ($dt_stamp < $validator['compare']) {
			return $this->_get_error($validator['errmsg']);
		}
	}


}
