<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## UTF-8 utility functions
# @author legolas558
# Released under GNU GPL License
#
# this file will be included when the system encoding is not utf-8
# and will assert that output through lc_enc() will be utf-8 compliant
#

	// try to set one of the _LOCALE locales
	$d__locale = setlocale (LC_ALL, $lids);
	// always get the current locale
	if ($d__locale===false)
		$d__locale = setlocale(LC_CTYPE, "0");
		
	$parts = explode('.', $d__locale);
	// check if we have a non iso-8859-1 encoding
	global $d_encoding;
	if (isset($parts[1]) && (strpos($parts[1], '8859-1')===false)) {
		$d_encoding = strtoupper($parts[1]);
		// check if this is a windows numeric codepage
		if (lcms_ctype_digit($d_encoding))
			$d_encoding = 'CP'.$d_encoding;
		// check if iconv/libiconv is available
		$has_fn = false;
		if (function_exists('iconv')) {
			function lc_enc($s) {
				return iconv($GLOBALS['d_encoding'], 'UTF-8', $s);
			}
			$has_fn = true;
		} else if (function_exists('libiconv')) {
			function lc_enc($s) {
				return libiconv($GLOBALS['d_encoding'], 'UTF-8', $s);
			}
			$has_fn = true;
			// check if the jap multibyte functions are available (requires at least PHP 4.4.3)
		} else if (function_exists('mb_convert_encoding') && function_exists('mb_check_encoding')) {
			if (@mb_check_encoding('test', $GLOBALS['d_encoding'])) {
				function lc_enc($s) {
					return mb_convert_encoding($s, 'UTF-8', $GLOBALS['d_encoding']);
				}
				$has_fn = true;
			}
		}
		if (!$has_fn) {	// if no PHP library is available, use the pure PHP encoder
			include $d_root.'classes/utf8/utf8encoder.php';
			
			// create one instance of the UTF8Encoder class
			global $utf8enc;
			$utf8enc = new UTF8Encoder();
			// attempt to load the charset
			if (!$utf8enc->LoadCharset($d_encoding))
				trigger_error('Cannot find codepage conversion table for '.$d_encoding);
			
			function lc_enc($s) {
				global $utf8enc;
				return $utf8enc->Encode($s);
			}
		}
		
//		die(lc_strftime('%A').' ');

	} else {	// we have an iso-8859-1 encoding, use the embedded conversion function
		function lc_enc($s) {
			return utf8_encode($s);
		}
		$d_encoding = 'ISO-8859-1';
	}

?>