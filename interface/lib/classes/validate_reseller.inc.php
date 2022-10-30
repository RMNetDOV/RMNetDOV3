<?php

class validate_reseller {

	/*
        Validator function to check if a given cron command is in correct form (url only).
    */
	function limit_client($field_name, $field_value, $validator) {
		global $app;

		if($field_value <= 0 && $field_value != -1) {
			return $app->tform->lng('limit_client_error_positive_or_unlimited');
		} else {
			return '';
		}
	}


}
