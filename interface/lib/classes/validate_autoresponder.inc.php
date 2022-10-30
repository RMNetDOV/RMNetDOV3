<?php

include_once 'validate_datetime.inc.php';

class validate_autoresponder extends validate_datetime
{
	function end_date($field_name, $field_value, $validator)
	{
		global $app;

		$start_date = $app->tform_actions->dataRecord['autoresponder_start_date'];
		
		// Parse date
		$datetimeformat = (isset($app->remoting_lib) ? $app->remoting_lib->datetimeformat : $app->tform->datetimeformat);
		$start_date_array = date_parse_from_format($datetimeformat, $start_date);
		$end_date_array = date_parse_from_format($datetimeformat, $field_value);
		
		//calculate timestamps
		$start_date_tstamp = mktime($start_date_array['hour'], $start_date_array['minute'], $start_date_array['second'], $start_date_array['month'], $start_date_array['day'], $start_date_array['year']);
		$end_date_tstamp = mktime($end_date_array['hour'], $end_date_array['minute'], $end_date_array['second'], $end_date_array['month'], $end_date_array['day'], $end_date_array['year']);
		
		// If both are set, end date has to be > start date
		if($start_date && $field_value && $end_date_tstamp <= $start_date_tstamp) {
			return $app->tform->lng($validator['errmsg']).'<br />';
		}
	}

}
