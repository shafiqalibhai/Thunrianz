<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

// (PHP5) set the default timezone
if (function_exists('date_default_timezone_set')) {
	
	date_default_timezone_set($my->GetTimezone());
	
	// provides correct UTF-8 encoding
	// allows a callback $func to be called on the raw string before UTF-8 encoding (strtoupper for example)
	function lc_strftime($format, $t = null, $func = null) {
		if (!isset($t))
			$t = $GLOBALS['time'];
		if (isset($func))
			return lc_enc($func(strftime($format, $t)));
		return lc_enc(strftime($format, $t));
	}

	function lc_date($format, $t = null, $func = null) {
		if (!isset($t))
			$t = $GLOBALS['time'];
		if (isset($func))
			return lc_enc($func(date($format, $t)));
		return lc_enc(date($format, $t));
	}
	
	function lc_mktime($hour, $minute, $second, $month, $day, $year, $is_dst = -1) {
		return mktime($hour,  $minute, $second, $month, $day, $year, $is_dst);
	}
	
} else { // this is PHP4
	// we use timezone offsets from an array and try to adjust for DST settings using
	// a putenv() approach, that will not work when safe_mode is enabled
	include $d_root.'includes/i18n/timezones_php4.php';
	
	global $d__dst_ok, $d__user_offset;
	$tz = $my->GetTimeZone();
	// DEBUG code, should be removed
	if (!isset($timezones[$tz])) {
		$d->log(6, 'PHP4 timezone for '.$tz.' is not set!');
		$d__user_offset = 0;
		$d__dst_ok = false;
	} else {
		// set the user offset to the timezone offset
		$d__user_offset = $timezones[$tz];
		global $d__utf8_unsafe;
		$d__utf8_unsafe = true;
		if (isset($timezones_dst[$tz])) {
			// if this timezone has DST settings (NOTE: be sure that putenv('TZ=',...) was not called before)
			if (isset($_ENV['TZ']))
				// save the value of that variable
				$env_tz = (string)$_ENV['TZ'];
			else $env_tz = '';

			// try to set the environment TZ variable
			if (!($d_env_flags & _LCMS_NO_putenv) && (@putenv('TZ=' . $tz))) {
				// we register a function that will restore the previous TZ on shutdown
				function _restore_tz($env_tz) {
					putenv('TZ='.$env_tz);
				}
				register_shutdown_function('_restore_tz', $env_tz);
				// we set a flag to say that strftime() and date() can be used
				$d__dst_ok = true;
			} else $d__dst_ok = false;
			// no DST correction needed
			$d__utf8_unsafe = false;
		} else $d__dst_dok = false;
	}
	
	function lc_strftime($format, $t = null, $func = null) {
		if (!isset($t))
			$t = $GLOBALS['time'];
		global $d__user_offset, $d__dst_ok;
		// add the timezone offset
		$t += $d__user_offset;
		if ($d__dst_ok)
			// apply DST fix if necessary
			$t += date('I', $t)*60*60;
		if (isset($func))
			return lc_enc($func(gmstrftime($format, $t)));
		return lc_enc(gmstrftime($format, $t));
	}

	function lc_date($format, $t = null, $func = null) {
		if (!isset($t))
			$t = $GLOBALS['time'];
		global $d__user_offset, $d__dst_ok;
		// add the timezone offset
		$t += $d__user_offset;
		if ($d__dst_ok)
			// apply DST fix if necessary
			$t += date('I', $t)*60*60;
		if (isset($func))
			return lc_enc($func(gmdate($format, $t)));
		return lc_enc(gmdate($format, $t));
	}
	
	function lc_mktime($hour, $minute, $second, $month, $day, $year, $is_dst = -1) {
		$ts = gmmktime($hour,  $minute, $second, $month, $day, $year, $is_dst);
		if ($ts==-1) {
			trigger_error('Invalid parameters specified for gmmktime()');
			return 0;
		}
		global $d__user_offset, $d__dst_ok;
		// add the timezone offset
		$ts -= $d__user_offset;
		if ($d__dst_ok)
			// apply DST fix if necessary
			$ts -= date('I', $ts)*60*60;
		return $ts;
	}

}

?>
