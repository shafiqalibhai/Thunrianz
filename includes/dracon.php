<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Secure initialization functions
# @author legolas558
#
# these functions provide a strong input validation layer to Lanius CMS
# functions are supplied in different flavours to ease coding

	define('__RAW', 0);				// no changes will be performed to input
	define('__SQL', 1);				// en/decoded to be safe for the current database
	//TODO: replace all __XHTML with __HTMLAREA!!! deprecate __XHTML
	define('__XHTML', 2);			// html en/decoded
	define('__PATH', 4);				// input will be safe for path usage
	define('__NUM', 16);			// positive integer number or null
	define('__UNUSED', 32);			// previously assigned to RFC2045 validation
	define('__ARR', 64);			// Array type (to be recursively scanned)
	define('__ARR0', 128);		// Same as above, but only the first element will be considered
	/*NOTE: __ARR0 can be considered transitional (it is preferred not to use it)
		it is currently used in the backend and will one day be deprecated */
	define('__NOHTML', 256);		// strip HTML tags
	define('__SIGNED', 512);		// signed number (can have a minus sign in front of it)
	define('__CHECKBOX', 1024);		// form checkbox value
	define('__HTMLAREA', 2048);		// a special case of __XHTML
//	define('__HTMLENC', 2048);	// HTML encoding fix - used to fix javascript-encoded HTML

	/**
	 * trims a string length to a specified length.
	 *
	 * @param string $data the string to trim
	 * @param null|int $max maximum length for the string
	 */
	function croplen(&$data, $max) {
		if ($max) {
			if (strlen($data)>$max)
				$data = substr($data, 0, $max);
		}
	}

	/**
	 * encode a string for SQL statements inclusion
	 * note: global slashes quoting (gp magic quotes) is not
	 * inherent to this function
	 * @return The \var $data variable with proper quoting
	 * done through the adoDB qstr() function
	 */
	function sql_encode($data, $max=null) {
    	global $conn;
		croplen($data, $max);
	    $r = $conn->Quote($data, false);
		return $r;
	}

	// private function called for removal of PHPSESSID (or any other session name) poisoning
	function _sid_safe(&$data) {
		global $d__sid_cleanup;
		if (!isset($d__sid_cleanup)) {
			if (ini_get('session.use_only_cookies') || !ini_get('session.use_trans_sid'))
				$d__sid_cleanup = false;
			else
				$d__sid_cleanup = true;
		}
		if ($d__sid_cleanup) {
			$sid = session_id();
			if (strlen($sid))
				$data = preg_replace('/(\\?|&amp;|&)'.preg_quote(session_name()).'='.preg_quote($sid).'/', '', $data);
		}
	}

	function xhtml_filter($data, $max=null) {
		global $d_root;
		_sid_safe($data);
		require_once $d_root.'includes/xhtml.php';
		croplen($data, $max);
		return htmLawed($data);
	}
	
	// workaround for PHP bug #43896
	$d__utf8_unsafe = true;
	if (@htmlspecialchars('A'.chr(160), ENT_COMPAT | ENT_IGNORE, 'UTF-8') == '') {
		// UTF-8 support broken, convert to default encoding
		//TODO: double re-encoding
		function safe_htmlspecialchars($s) {
			return htmlspecialchars($s, ENT_COMPAT);
		}
	} else {
		function safe_htmlspecialchars($s) {
			return htmlspecialchars($s, ENT_COMPAT | ENT_IGNORE, 'UTF-8');
		}
	}
	$d__utf8_unsafe = false;

	// $data must be properly encoded!
	function xhtml_safe($data, $max=null) {
		croplen($data, $max);
		return dbg_htmlspecialchars($data);
	}

	function dbg_htmlspecialchars($s, $strict = true) {
		$s = @safe_htmlspecialchars($s);
		if ($s === FALSE) {
			global $d;
			if ($strict && isset($d))
				$d->log(1,
					sprintf("Cannot apply htmlspecialchars() to \"%s\"",
					str_replace(array('<', '>'), '', (strlen($s>255) ? substr($s, 0, 255).'...' : $s))));
			// fallback to iso-8859-1
			return htmlspecialchars($s, ENT_COMPAT);
		}
		return $s;
	}

/*
	function _enc_xhtml_byte($m) {
		$m = $m[0];
		$l=strlen($m);
		$s='';
		for($i=0;$i<$l;++$i) {
			$s.='&#x'.dechex(ord($m[$i])).';';
		}
		return $s;
	}
	
	function xhtml_safe_encoding($data, $max = null) {
		croplen($data, $max);
		// \x1-a
		$data = preg_replace_callback('/["&<>\xc0-\xff]+/', '_enc_xhtml_byte', $data);
		return $data;
	} */
	
	function num_get($data, $def=null) {
		if (!lcms_ctype_digit($data))
			return $def;
		else
			return (int)$data;
	}
	
	function signed_get($data, $def=null) {
		if (!preg_match('/\\s*-?\\s*\\d+\\s*$/', $data))
			return $def;
		return (int)$data;
	}

	function path_safe($data) {
		if (!strlen($data)) return '';
		$data = str_replace(array('/','\\'), '_', $data);
		if (($data[0]=='.') || ($data[0]==chr(0))) $data[0]='_';
		return $data;
	}
	
	## returns true if the path is normalized
	function valid_path($s) {
		if (	preg_match( '/[^a-zA-Z0-9_\\-\\.\\/]/', $s) ||
			preg_match('/^(\\.|\\.\\.|\\/)/', $s))
			return false;
		return true;
	}

	/**
	 * prepare input for sql statements
	 * @return The variable input prepared by sql_encode for sql
	 * usage, not including quoting. Quoting is inherent to the SQL statement
	 */
	function in_sql($name, $arr = null, $def = null, $max = null) {
		if (!isset($arr))
			$arr =& $_REQUEST;
		if (!isset($arr[$name])) return $def;
		$data = $arr[$name];
		//s2: this should now be handled by the adodb driver
		//L: global quoting settings are not inherent to sql_encode()
		if (is_array($data)) return 'Array';
		raw_fix($data);
		return sql_encode($data, $max);
	}

	function in_html($name, $arr = null,$def = null, $max = null) {
		$data = in_raw($name, $arr, $def, $max);
		return xhtml_filter($data, $max);
	}

	function in_num($name, $arr = null,$def = null) {
		if (!isset($arr))
			$arr =& $_REQUEST;
		if (!isset($arr[$name]))	return $def;
		$data = $arr[$name];
		// raw_fix($data) is not needed here
		return num_get($data, $def);
	}
	
	function in_signed($name, $arr = null, $def = null) {
		if (!isset($arr))
			$arr =& $_REQUEST;
		if (!isset($arr[$name]))	return $def;
		$data = $arr[$name];
		// raw_fix($data) is not needed here
		return signed_get($data, $def);
	}
	
	// if the checkbox was not defined, return 0 (which is the correct default value)
	function in_checkbox($name, $arr = null, $def = 0) {
		if (!isset($arr))
			$arr =& $_REQUEST;
		if (!isset($arr[$name]))	return $def;
		return checkbox_get($arr[$name], $def);
	}
	
	function checkbox_get($data, $def=0) {
		if (!$data)	return 0;	// will evaluate '0', 'false' and ''
		// raw_fix($data) is not needed here
		if ($data!='off')
			return 1;
		return 0;
	}

	function in_raw($name, $arr = null, $def = null, $max = null) {
		if (!isset($arr))
			$arr =& $_REQUEST;
		if (!isset($arr[$name])) return $def;
		$data = $arr[$name];
		if (is_array($data)) return 'Array';
		raw_fix($data);
		croplen($data, $max);
		return $data;
	}

	function spam_filter($s) {
		$c = substr_count($s, 'http://');
		$c = max($c, substr_count($s, 'https://'));
		if ($c > 5)
			return preg_replace('/[^:]{3,}:\\/\\/[^\\s]*/', '<http://nospam.laniuscms.org/>', $s);
		return $s;
	}

	// experimental spam-filtering input function
	// might be useful to fix user content snippets broadcast via emails
	function in_nospam($name, $arr = null, $def = null, $max = null) {
		if (!isset($arr))
			$arr =& $_REQUEST;
		if (!isset($arr[$name])) return $def;
		$data = $arr[$name];
		if (is_array($data)) return 'Array';
		raw_fix($data);
		croplen($data, $max);
		return spam_filter($data);
	}

	function in_path($name, $arr = null, $def = null, $max = null) {
		if (!isset($arr))
			$arr =& $_REQUEST;
		if (!isset($arr[$name])) return $def;
		$data = $arr[$name];
		if (is_array($data)) return 'Array';
		raw_fix($data);
		$data = path_safe($data);
		croplen($data, $max);
		return $data;
	}
	
	// inputs array of arrays - always takes care of keys and applies the flags
	function in_arr2($name, $flags = 0, $arr = null, $def = null, $max = null) {
		if (!isset($arr))
			$arr =& $_REQUEST;
		if (!isset($arr[$name])) return $def;
		$data = $arr[$name];
		$ak = array_keys($data);
		foreach($ak as $akey)
			array_validation($data[$akey], $flags , $def, $max);
		return $data;
	}

	// inputs an array of data - elements cannot be arrays
	function in_arr($name, $flags = 0, $arr = null, $def = null, $max = null) {
		if (!isset($arr))
			$arr =& $_REQUEST;
		if (!isset($arr[$name])) return $def;
		$data = $arr[$name];
		// raw_fix($data) is not needed here since it is executed by array_validation()
		if (!is_array($data))
			return $def;
		array_validation($data, $flags, $def, $max);
		return $data;
	}
	
	function array_validation(&$arr, $flags = 0, $def = null, $max = null) {
		if (!$flags) {	// optimization for raw input
			if ($GLOBALS['d__bad_env'])
				$arr = array_map('stripslashes', $arr);
			return;
		}
		//TODO: could optimize in_validation() usage
		$ak = array_keys($arr);
		if (isset($def)) {
			foreach($ak as $akey) {
				in_validation($arr[$akey], $flags , current($def), $max);
				next($def);
			}
		} else {
			foreach($ak as $akey)
				in_validation($arr[$akey], $flags , null, $max);
		}
	}

	if ($GLOBALS['d__bad_env']) {
		function raw_fix(&$data) {
			$data = stripslashes($data);
		}
	} else {
		function raw_fix(&$data) {}
	}

	function in_validation(&$data, $flags, $def, $max) {
		if (is_array($data)) {	// disallow nested arrays
			$data = $def;
			return;
		}
		raw_fix($data);
		if (!$flags) {
			croplen($data, $max);
			return;	// be careful with optimizations
		}
		if ($flags & __NUM) {
			if ($flags & __SIGNED)
				$data = signed_get($data, $def);
			else
				$data = num_get($data, $def);
		}

		if ($flags & __HTMLAREA)
			$data = area_filter($data, $max);
		else if ($flags & __XHTML)
			$data = xhtml_filter($data, $max);
		if ($flags & __NOHTML)
			$data = xhtml_safe($data, $max);
		if ($flags & __SQL)
			$data = sql_encode($data, $max);
		if ($flags & __PATH)
			$data = path_safe($data);
		if ($flags & __CHECKBOX)
			$data = checkbox_get($data, $def);
		//L: skipped because of previous optimization
//		croplen($data, $max);
	}

	// prepare a variable for input from the (wild) external world
	function in($name, $flags = 0, &$arr, $def = null, $max = null) {
		if (!isset($arr))
			$arr =& $_REQUEST;
		if (!isset($arr[$name])) return $def;
		$data = $arr[$name];
		// raw_fix($data) is not needed here since it is executed by in_validation()
		if ($flags & __ARR0) {
			if (!isset($data[0]))
				return $def;
			in_validation($data[0], $flags ^ __ARR0 , $def, $max);
			return $data[0];
		}
		if ($flags & __ARR) {
			if (!is_array($data))
				return $def;

			$c=count($data);
			$data = array_values($data);
			for($i=0;$i<$c;$i++)	// referencing pointers to array items (through '&') is allowed only on PHP5+
				in_validation($data[$i], $flags ^ __ARR , $def, $max);
			return $data;
		}

		in_validation($data, $flags, $def, $max);
		return $data;
	}
	
	function _filter_blank($s) {
		if (strlen($s)>20)
			return $s;
		// filter out some blank contents
		if (preg_match('/^\\s*(<br\\s*\\/?\\s*>)|(<p>(?:&(nbsp|#160);)<\\/p>)\\s*$/', $s))
			return '';
		return $s;
	}

	function _fix_urls($s) {
		if (strlen($s))
			return preg_replace_callback('/<\\w+\\s+[^>]+>/s', '_url_fix_nested', $s);
		return $s;
	}

	function _URL_replace_rel($m) {
		// d_website already contains the subsite path
		global $d_website;
		if (strpos($m[2], $d_website)===0)
			return $m[1].'="'.substr($m[2], strlen($d_website)).'"';
		return $m[0];
	}

	function __fix_space_urls($m) {
		return $m[1].'="'.str_replace(' ', '%20', $m[2]);
	}
	
	function _url_fix_nested($m) {
		$data = preg_replace_callback('/(href|src)="([^"><]+)/i', '__fix_space_urls', $m[0]);
		return preg_replace_callback('/(href|src)="?([^"\\s>]+)"?/i', '_URL_replace_rel', $data);
	}
	
	function area_filter($data, $max) {
		$data = _fix_urls(_filter_blank($data));
		croplen($data, $max);
		return $data;
	}

	function in_area($name, &$arr, $def = null, $max = null) {
		return area_filter(in($name, __XHTML, $arr, $def, $max), $max);
	}
	
	function in_cookie($name, $def = null, $max = null) {
		return in($GLOBALS['d_rand'].'-'.$name, __RAW, $_COOKIE, $def, $max);
	}
	
	function in_session($name, $flags = __RAW, $def = null, $max = null) {
		return in($GLOBALS['d_uid'].'-'.$name, $flags, $GLOBALS['_SESSION'], $def, $max);
	}

	function out_session($name, $value) {
		$_SESSION[$GLOBALS['d_uid'].'-'.$name] = $value;
	}
	
	function unset_session($name) {
		unset($_SESSION[$GLOBALS['d_uid'].'-'.$name]);
	}
	
	function _userid_enc($h, $encode = true) {
		// create the destination map
		global $d_rand;
		$b = $d = '309bc1a2648efd57';
		$l = strlen($d_rand);
		// walk on odd letters
		for ($i=0;$i<$l;$i+=2) {
			$o = $i % 16;
			$p = ord($d_rand[$i]) % 16;
			$t = $d[$p]; $d[$p] = $d[$o]; $d[$o] = $t;
		}
		// walk complement on even letters
		for ($i=1;$i<$l;$i+=2) {
			$o = $i % 16;
			$p = 15 - (ord($d_rand[$i]) % 16);
			$t = $d[$p]; $d[$p] = $d[$o]; $d[$o] = $t;
		}
		if ($encode)
			return strtr($h, $b, $d);
		else
			return strtr($h, $d, $b);
	}
	
	function encode_userid($n) {
		return _userid_enc(dechex($n), true);
	}

	function decode_userid($n) {
		return hexdec(_userid_enc($n, false));
	}

	function in_userid($name, &$arr, $def = null) {
		$data = in_raw($name, $arr);
		if (!isset($data) || !lcms_ctype_xdigit($data))
			return $def;
		return decode_userid($data);
	}
	
	//NOTE: arrays should be used instead of this prefix approach
	function in_prefix($prefix, $flags, &$arr, $def = null) {
		if (!isset($arr))
			$arr =& $_REQUEST;
		$keys = array_keys($arr);
		if (!isset($keys[0])) return $def;
		$data = array();
		foreach($keys as $key) {
			if (strpos($key, $prefix)===0) {
				$dk = substr($key, strlen($prefix));			
				$data[$dk] = in($key, $flags, $arr,
					isset($def[$dk]) ? $def[$dk] : null);
			}
		}
		if (!count($data)) return $def;
		return $data;
	}

// moved here from com_fb for filemanager
function is_safe_path($path) {
	// if path is empty, it's ok
	if (!strlen($path)) return true;
	// if path tries to climb up the directory level, it's bad
	if (strpos($path, '..')!==false) return false;
	// if path contains invalid slashes sequences, it's bad
	if (strpos($path, '//')!==false) return false;
	if (strpos($path, './')!==false) return false;
	return true;
}

?>
