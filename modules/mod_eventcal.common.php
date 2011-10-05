<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Event calendar module
# @author legolas558
#
# common include file
#

// must be manually declared when not available from PHP
if (!function_exists('cal_days_in_month')) {
 /*
 * cal_days_in_month($month, $year)
 * Returns the number of days in a given month and year, taking into account leap years.
 *
 * $month: numeric month (integers 1-12)
 * $year: numeric year (any integer)
 *
 * Prec: $month is an integer between 1 and 12, inclusive, and $year is an integer.
 * Post: none
 */

	// corrected by ben at sparkyb dot net
	function cal_days_in_month($calendar, $month, $year) {
	  // calculate number of days in a month
	  return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
	}

}

function u_substr($s, $start, $cnt = null) {
	$l = strlen($s);
	if (!isset($cnt))
		$cnt = $l-$start;
	if ($cnt<$l && !$start)
		return $s;
//	else $cnt = max(strlen($s)-$start, $cnt);
	if ($start!=0)
		$p = '.{'.$start.'}';
	else $p = '';
	if (!preg_match('/'.$p.'(.{0,'.$cnt.'})/Aus', $s, $m)) return FALSE;
	return trim($m[1]);
}

function dowi($d) {
	return xhtml_safe(u_substr(lc_strftime('%a', 60*60*24*(4+$d), 'ucfirst'), 0, 4));
}

function dow($d) {
	return lc_strftime('%a', 60*60*24*(4+$d), 'ucfirst');
}

function ec_time($month, $day, $year, $is_dst = null) {
	// will not use lc_mktime here
	return gmmktime(12,0,0,$month,$day,$year);
}

?>