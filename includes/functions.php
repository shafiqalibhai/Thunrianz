<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Global Lanius CMS functions
#
# file-related functions should be separated in a different class (FS)
# functions requiring an user context should be moved to user_functions.php

// definitions for d__env_flags
define('_LCMS_NO_putenv', 1);
define('_LCMS_NO_ini_get', 2);

if (!defined('ENT_IGNORE'))
	define('ENT_IGNORE', 4);

global $d__bad_env;
$d__bad_env = 'get_magic_quotes_gpc';
$d__bad_env = function_exists($d__bad_env) ? $d__bad_env():false;

global $d_pic_extensions;
$d_pic_extensions = array('png','jpg','jpeg','gif');

if (!defined('DATE_RSS'))
	//the 'r' format was broken in older versions of PHP4 (or maybe still broken)
	define('DATE_RSS', "D, d M Y H:i:s O");
	
define('_EMAIL_REGEX', "[\\w\\.\\-]+@(\\w+[\\w\\-]*\\.){1,}\\w{1,4}");

// ctype_digit, ctype_alpha, ctype_alnum, file_get_contents, html_entity_decode functions emulation
// removed because always available since PHP v4.3.0
// you might get them (and paste here) from SVN path branches/blake/includes/php430compat.php

function lcms_ctype_digit($s) {
	return ctype_digit((string)$s);
}

function lcms_ctype_xdigit($s) {
	return ctype_xdigit((string)$s);
}

function lcms_ctype_alpha($s) {
	return ctype_alpha((string)$s);
}

if (!function_exists('file_put_contents')) {	// PHP5
	function file_put_contents($path, $content) {
		$f = @fopen($path, 'wb');
		if ($f===false)
			return false;
		$amt = fwrite($f, $content);
		fclose($f);
		return $amt;
	}
}

if (!function_exists('stripos')) {	//PHP5
	function stripos($haystack, $needle, $offset = 0) {
		return strpos(strtolower($haystack), strtolower($needle), $offset);
	}
}

function safe_readfile($fname) {
	if (@readfile($fname) === false)
		echo file_get_contents($fname);
}

function is_windows() {	// DEPRECATED, use the global instead
	return $GLOBALS['d__windows'];
}

function is_url(&$s) {
	return (preg_match('/(([A-Za-z\\+]+\\:\\/\\/)|(mailto:))./A', $s) > 0);
}

## update the count field of a table
function change_val($table, $id, $field, $delta = 1) {
	global $conn;
	$row = $conn->SelectRow('#__'.$table, $field, " WHERE id=$id");
	$val = current($row) + $delta;
	$conn->Update('#__'.$table, $field.'='.$val, ' WHERE id='.$id);
	return $val;
}

## retrieve the local domain from the user-supplied website URL
function get_domain($ext=false) {
	global $d_website;
	$domain = parse_url($d_website);
	$domain = $domain['host'];
	if (!$ext) {
		$p = strpos($domain, '.');
		if ($p!==false) // intranet domains may not have extension
			return substr($domain, 0, $p);
	}
	return $domain;
}

function is_email( $email ){
	if (preg_match( '/'._EMAIL_REGEX.'/', $email ))
		return true;
	return false;
}

function file_ext($file)
{
	$p = strrpos($file, '.');
	if ($p===false)return '';
	return strtolower(substr($file,$p+1));
}

define('_RRD_NO_DIRECTORIES', 0x001);
define('_RRD_RECURSE', 0x002);
define('_RRD_SORTED', 0x008);

function _rrd_sort($a, $b) {
	$al = $a[strlen($a)-1];
	$bl = $b[strlen($b)-1];
	if ($al=='/') {
		if ($bl!='/')
			return -1;
		return strnatcmp($a, $b);	
	} else {
		if ($bl=='/')
			return 1;
		return strnatcmp($a, $b);
	}
}

function lcms_opendir($dir, $exit_on_fail = true) {
	global $d__last_error;
	$d__last_error = null;
	$dh = @opendir($dir);
	if ($dh === false) {
		if ($exit_on_fail) {
			global $d_root;
			include_once $d_root.'includes/servererror.php';
			if (!isset($d__last_error)) $msg = ''; else $msg = $d__last_error;
			service_msg("Open directory", sprintf("Failed to open directory %s", fix_root_path($dir)), $msg, 'stop');
			exit();
		}
		// fallback to return false
	}
	return $dh;
}

function raw_read_dir($dir, $ext = false, $exclude_paths = false, $flags = 0, $cb = null, $custom_data_for_cb = null) {
	$dh = lcms_opendir($dir);
	$data = array();
	while (false !== ($fname = readdir($dh))) {
		if ($fname[0]=='.') continue;
		if (is_dir($dir.$fname)) {
			if ($flags & _RRD_NO_DIRECTORIES)
				continue;
			$fname = $dir.$fname.'/';
			if ($exclude_paths && in_array($fname, $exclude_paths))
				continue;
			if ($flags & _RRD_RECURSE) {
				$fname = raw_read_dir($fname, $ext, $exclude_paths, $flags, $cb, $custom_data_for_cb);
				if (!count($fname))
					continue;
			}
			// flag for callback
			$is_d = true;
		} else {
			if ($ext) {
				if (!in_array(file_ext($fname), $ext))
					continue;
			}
			$fname = $dir.$fname;
			$is_d = false;
		}
		if (isset($cb)) {
			if (!$cb($fname, $is_d, $custom_data_for_cb))
				continue;
		}
		$data[] = $fname;
	}
	if ($flags & _RRD_SORTED)
		usort($data, '_rrd_sort');
	return $data;
}

// the below function is deprecated, please use the above instead
//MUST use a trailing slash!!!
function read_dir($dir,$type='both',$extra=false,$allowed_ext=false) {
	$info=array();
	$dh=lcms_opendir($dir);
	while (false !== ($d_name = readdir($dh))) {
		if( $d_name[0]=='.' ) continue;	// skip all folders whose name starts with a dot
		if ( is_dir( $dir.$d_name ) && ($type!='file') ) {
			if($extra) {
				$tinfo['id']=substr($d_name,strrchr($d_name,'/'));
				$tinfo['path']=$dir.$d_name."/";
				$tinfo['size']=_NA;
//				if (defined('_VALID_ADMIN'))
					$tinfo['perms']=getPerms(fileperms($tinfo['path']));
				$tinfo['created']=filectime($dir.$d_name);
				$tinfo['modified']=filemtime($dir.$d_name);
				$info[]=$tinfo;
			} else
				$info[] = $d_name;
		}
		
		if ( is_file( $dir.$d_name ) && ($type!='dir')  ){
			if (!$allowed_ext or (in_array(file_ext($d_name), $allowed_ext))) {
				if($extra) {
					$tinfo['id']=substr($d_name,strrchr($d_name,'/'));
					$tinfo['path']=$dir.$d_name;
					$tinfo['size']=convert_bytes(filesize($dir.$d_name));
//					if (defined('_VALID_ADMIN'))
						$tinfo['perms']=getPerms(fileperms($tinfo['path']));
					$tinfo['created']=filectime($dir.$d_name);
					$tinfo['modified']=filemtime($dir.$d_name);
					$info[]=$tinfo;
				} else
					$info[]=$d_name;
			}
		}
	}
	return $info;
}

// create a select list with elements linked to a directory's files
function select_array($directory='',$default=null,$selected=null,$type='file',$allowed_ext=false, $dead_select = true) {
	global $d_root;
	// if we have a default item, initialize to it
	if (isset($default)) {
		$img_arr=array(array("name"=>$default,"value"=>""));
		// eventually set selection to first item
		if ($selected === '') {
			$img_arr[0]['selected'] = true;
			$has_sel = true;
		} else
			$has_sel = false;
	} else { // normal empty result initialization
		$img_arr = array();
		$has_sel = false;
	}
	$file_arr=read_dir($d_root.$directory,$type,false,$allowed_ext);
	
	foreach($file_arr as $var) {
		$a = array("name"=>$var,"value"=>$var);
		if (!$has_sel && ($var == $selected)) {
			$a['selected'] = true;
			$has_sel = true;
		}
		$img_arr[]=$a;
	}
	if ($dead_select && !$has_sel && isset($selected))
		$img_arr[] = array('name'=>$selected, 'value'=>$selected,'selected'=>true);
	return $img_arr;
}

function select(&$data,$value) {
	$l = count($data);
	if (is_array($value)) {
		for($i=0;$i<$l;$i++) {
			// mark all the selected values - no break here
//			if (!is_array($data))				trigger_error('Cannot use string offset as an array');
			if (in_array($data[$i]['value'], $value))
				$data[$i]["selected"]=true;
		}
	} else {
		if (!isset($value))
			return $data;
		$sel = false;
		for($i=0;$i<$l;$i++) {
			if($data[$i]['value']==$value) {
				$data[$i]["selected"]=true;
				$sel = true;
				break;
			}
		}
	}
	return $data;
}

if (ini_get('allow_url_fopen')) {

	function get_url($url,$file=null) {
		$rd = @file_get_contents($url);
		if ($rd===false) return false;
		if (isset($file)) {
			global $d_root;
			include_once $d_root.'admin/classes/fs.php';
			$fs = new FS();
			return $fs->put_contents($file, $rd);
		} else
			return $rd;
	}

} else {

	function get_url($url,$file=null) {
		global $d_root;
		require_once $d_root.'classes/sst.php';
		$downloader = new classDownloader;
		return $downloader->Download($url,$file);
	}

}

function get_attachment_filename($url) {
	global $d_root;
	require_once $d_root.'classes/sst.php';
	$downloader = new classDownloader();
	if (!$downloader->DownloadHeaders($url))
		return '';
	return $downloader->AttachmentFilename();
}

// builds a matrix to choose the correct identifiers
global $format_date_matrix;

//L: returns an unix timestamp from a JS calendar formatted date
function format_date($fmt_date) {
	global $format_date_matrix;
	// initialize the format date matrix
	if (!isset($format_date_matrix)) {
		preg_match_all('/%(.)/', _DATE_FORMAT_EXTENDED, $format_date_matrix);
		$format_date_matrix = array_flip($format_date_matrix[1]);
	}
	$data = array();
	sscanf($fmt_date,preg_replace('/%./', '%d', _DATE_FORMAT_EXTENDED),$data[0],$data[1],$data[2],$data[3],$data[4],$data[5]);
	// fill empty values
	foreach($data as $k => $v) {
		if (!isset($v)) {
			if ($k>2)
				$data[$k] = 0;
			else
				$data[$k] = 1;
		}
	}
	return lc_mktime($data[$format_date_matrix['H']],$data[$format_date_matrix['M']],$data[$format_date_matrix['S']],$data[$format_date_matrix['m']],$data[$format_date_matrix['d']],$data[$format_date_matrix['Y']]);
}

function search_query($fields, $q, $op, &$common_words, &$stricken_words) {
	if ($op == 'exact') {
		$q = sql_encode(str_replace('%', '%%', trim($q)));
		$common_words = false;
	} else {
		$q = sql_encode(preg_replace('/\\s+/', ' ', trim($q)));
		$q = str_replace('%', '%%', $q);
		$words = search_get_keywords($q, $common_words, $op, $stricken_words);
	}
	$full_sql = '';
	if ($op != 'exact')
		$word = array_shift($words);
	foreach($fields as $field) {
		if ($op != 'exact')
			reset($words);
		switch ($op) {
			case 'any':
				$sql = "($field LIKE '%".$word."%')";
				foreach($words as $w) {
					$sql .= " OR ($field LIKE '%$w%')";
				}
				if (count($words))
					$sql = "($sql)";
				break;
			case 'all':
				$sql = "($field LIKE '%".$word."%')";
				foreach($words as $w) {
					$sql .= " AND ($field LIKE '%$w%')";
				}
				if (count($words))
					$sql = "($sql)";
				break;
			case 'exact':
				$sql = "($field LIKE '%$q%')";
				break;
		}
		if (strlen($full_sql))
			$full_sql.=' OR '.$sql;
		else
			$full_sql = $sql;
	}
	return $full_sql;
}

function _sort_words($a, $b) {
	return (strlen($b) < strlen($a));
}

// in case of "any" searches, removes the smaller duplicate keywords for a stricter search criterium
function _optimize_search_words($words, $op, &$stricken_words) {
	if ($op=='all')
		return $words;

	$refk = array_flip($words);

	usort($words, '_sort_words');
	
	$c=count($words)-1;
	$optimized = array();
	foreach($words as $i => $word) {
		for($l=$c;$l>$i;--$l) {
			if (strpos($words[$l], $word)!==false) {
				$stricken_words[] = $word;
				continue 2;
			}
		}
		$optimized[$refk[$word]] = $word;
	}
	ksort($optimized);

	return array_values($optimized);
}

function search_get_keywords($q, &$common_words, $op, &$stricken_words) {
	if ($op=='any')
		$q = strtolower($q);
	$words = explode(' ', $q);
	//TODO: skip common words of the default language
	//TODO: return the removed common words in $common_words
	$stricken_words = array();
	$words = _optimize_search_words($words, $op, $stricken_words);
	$common_words = false;
	return $words;
}

function search_ext($hay,$needle,$op, &$common_words, &$stricken_words) {
	$hay = html_to_text($hay);	// Why? RFC!
	if ($op!='exact') {
		$needle = trim(preg_replace('/\\s+/', ' ', $needle));
		$search_arr=search_get_keywords($needle, $common_words, $op, $stricken_words);
	}
	switch($op) {
		case "any":
			foreach($search_arr as $keyword) {
				if(stripos($hay,$keyword)!==false)
					return true;
			}
			break;
		case "all":
			foreach($search_arr as $keyword) {
				if(stripos($hay,$keyword)===false)
					return false;
			}
			return true;
		case "exact":
			if (strpos($hay,$needle)!==false)
				return true;
			break;
	}
	return false;
}

function usr_com_path($file,$com=null) {
	return $GLOBALS['d_root'].usr_rel_com_path($file,$com);
}

// ADMIN  paths
if(defined( '_VALID_ADMIN' )) {

	## returns the frontend relative component path, $com.$file is the filename
	/*static */function usr_rel_com_path($file,$com=null) {
		if (!isset($com))	$com = $GLOBALS['com_option'];
		return "components/$com/".$com.'.'.$file;
	}

	## returns the relative component path, admin.$com.$file is the filename
	function rel_com_path($file, $com=null) {
		if (!isset($com)) $com = $GLOBALS['com_option'];
		return "admin/components/$com/admin.$com.$file";
	}

	## returns the absolute component path, admin.$com.$file.php is the filename
	/*static */ function com_path($file,$com=null) {
		return $GLOBALS['d_root'].rel_com_path($file,$com).'.php';
	}

	## returns the relative language path, lang/$lang/admin/components/$com.php is the filename
	function com_lang($lang, $com=null) {
		global $d_root;
		if (!isset($com)) $com = $GLOBALS['com_option'];
		$path='components/'.$com.'.php';
		return $d_root.'lang/'.$lang.'/admin/'.$path;
	}
	
} else {
// USER paths

	/*static */function usr_rel_com_path($file,$com=null) {
		if (!isset($com))	$com = $GLOBALS['option'];
		return "components/$com/".$com.'.'.$file;
	}

	function rel_com_path($file, $com=null) {
		return usr_rel_com_path($file,$com);
	}

	/*static */ function com_path($file,$com=null) {
		return usr_com_path($file,$com).'.php';
	}

	function com_lang($lang, $com=null) {
		global $d_root;
		if (!isset($com)) $com = $GLOBALS['option'];
		$path='components/'.$com.'.php';
		return $d_root.'lang/'.$lang.'/'.$path;
	}
	
}

function mod_lang($lang, $mod) {
	global $d_root;
	return $d_root.'lang/'.$lang.'/modules/'.$mod.'.php';;
}

function bot_lang($lang, $bot) {
	global $d_root;
	return $d_root.'lang/'.$lang.'/drabots/'.$bot.'.php';;
}

//L: returns the template image (if available) or the default one
function template_pic($file, $alt='') {
	global $d_root,$d_template,$d_subpath;
	$path='templates/'.$d_template.'/images/';
	if (is_file($d_root.$path.$file)) $path = $d_subpath.$path;
	else $path = $d_subpath.'media/common/';
	return '<img src="'.$path.$file.'" border="0" alt="'.$alt.'" />';
}

//L: enhanced
function random_string($len) {
	static $salt = "abchefghjkmnpqrstuvwxyz0123456789";
	mt_srand((double)microtime()*1000000);
	$i = 0;
	$rand='';
	for ($i=0;$i<$len;$i++)
		$rand .= $salt[mt_rand(0, 32)];
	return $rand;
}

function return_bytes($val) {

	$val = trim($val);
	$i = 0; $l = strlen($val);
	while ($i<$l && (!lcms_ctype_alpha($val{$i}))) {
		$i++;
	}
	$pf = strtolower(trim(substr($val,$i)));
	$val=(float)substr($val,0,$i);
		
	if (isset($pf[0])) {
		switch($pf[0]) {
	       case 'g':
	           $val *= 1024;
	       case 'm':
	           $val *= 1024;
	       case 'k':
	           $val *= 1024;
	   }
   }
   return $val;
}

function convert_bytes($val) {
	$val=(float)$val;
	if ($val < 1024)
		return $val;
	$k = $val / 1024;
	if ($k >= 1024) {
		$m = $k / 1024;
		if ($m >= 1024)
			$n = number_format($m/1024,2).' GB';
		else
			$n = number_format($m,2).' MB';
	} else
		$n = number_format($k,2).' KB';
	return str_replace('.00', '', $n);
}

function str_decode($s) {
	return str_replace(array('\\n', '\\"', '\\r', '\\t', '\\$', '\\\\'), array("\n", '"', "\r", "\t", '$', '\\'), $s);
}

function str_encode($s) {
	return str_replace(array('\\', "\n", '"', "\r", "\t", '$'), array('\\\\', '\\n', '\\"', '\\r', '\\t', '\\$'), $s);
}

if (substr(phpversion(), 0, 1)=='4') {
	global $d__HTT_table;
	function safe_html_entity_decode($text) {
		global $d__HTT_table;
		if (!isset($d__HTT_table)) {
			$ttbl = get_html_translation_table(HTML_ENTITIES);
			foreach($ttbl as $k => $v)
				$d__HTT_table[$v] = utf8_encode($k);
		}
		return strtr($text, $d__HTT_table);
	}
} else {
	function safe_html_entity_decode($text) {
		return html_entity_decode($text, ENT_COMPAT | ENT_IGNORE, "UTF-8");
	}
}

// provides a suffix for URLs containing title keywords
function content_sef($title) {
	global $d_seo;
	if (!$d_seo)
		return '';
	$title = safe_html_entity_decode($title, ENT_COMPAT | ENT_IGNORE);
	$title = preg_replace('/[^\\s\\w\\d]+/', ' ', $title);
	$title = preg_replace('/\\s+/', '-', trim($title));
	return '&amp;-'.rawurlencode(substr($title, 0, $d_seo));
}

// provides a list of keywords from an XHTML content
// on-entry text should not be XHMTL!
function content_keywords($content) {
	$kl = preg_replace('/[^\\s\\w\\d]/', ' ', html_to_text($content));
	$kl = preg_replace('/\\s[^\\s]{1,3}\\s/U', ' ', ' '.$kl.' ');
	$kl = preg_replace('/\\s+/', ',', trim($kl));
	global $d__max_keywords;
	if (isset($kl[$d__max_keywords]))
		$kl = substr($kl, 0, $d__max_keywords);
	return $kl;
}

function content_description($desc) {
	global $d__max_description;
	if (isset($desc[$d__max_description]))
		$desc = substr($desc,0,$d__max_description);
	return trim(preg_replace('/\\s+/', ' ', $desc));
}

function cms_version($full = false) {
	if ($full)
		return $GLOBALS['d_version'].' r'.$GLOBALS['d__revision'];
	return $GLOBALS['d_version'];
}

//L: from my Gladius DB
define('__QUOTED_S', "('[^']*')+");

function _url_mangler($m) {
	$url = $m[1];
	if (!is_url($url))
		$url = $GLOBALS['d_website'].$url;
	return $m[2].' ['.$url.']';
}

function html_to_text($html) {
	return preg_replace('/\\s+$/m', '', strip_tags(str_replace(array('<br />','<br/>','<br>'), "\n", str_replace('>', '> ', str_replace('</p>', "\n", preg_replace_callback('/<a.*?href="(.*?)".*?>([^<]+)<\\/a>/', '_url_mangler', safe_html_entity_decode($html)))))));
}

function text_to_html($text) {
	return str_replace(array("\n", "\r\n", "\r"), '<br />', $text);
}

function js_enc($s) { return js_encode($s); } //deprecated

function js_encode($s) {
	return str_replace("\n", "\\n", addslashes($s));
}

global $d__cookiepath, $d__cookiedomain;

function d_hascookie($name) {
	return isset($_COOKIE[$GLOBALS['d_rand'].'-'.$name]);
}

function d_unsetcookie($name) {
	if (d_hascookie($name))
		return d_setcookie($name, false, -365*12*30*24*60*60);
}

function d_setcookie($name, $value, $lifetime = false) {
	global $d__cookiepath, $d__cookiedomain, $d__https;
	if (!isset($d__cookiepath)) {
		// create the cookie validity path
		global $d_website;
		$a = parse_url($d_website);
		$d__cookiedomain = $a['host'];
		if (strpos($d__cookiedomain, '.')===false)
			$d_cookiepath = $d__cookiedomain = false;
		else
			$d__cookiepath = $a['path'];
		$d__https = ($a['scheme']=='https') ? 1 : 0;
	}
	return setcookie($GLOBALS['d_rand'].'-'.$name, $value,
					($lifetime ? time()+$lifetime : false),
					$d__cookiepath, $d__cookiedomain,
					$d__https);
}

// adjust count of category elements removing those not accessible by access group id
function adjust_count($c, $catid, $table, $extra = '') {
	global $my;
	// the user is registered or guest, normal count is OK for him
	if ($my->gid<2)
		return $c;
	// fix the extra SQL query
	if (strlen($extra))
		$extra='WHERE '.$extra.' AND ';
	else $extra = 'WHERE ';
	global $conn;
	$dc = $conn->SelectCount($table, '*', ' '.$extra.'access>'.$my->gid);
	// return (count - unaccessable_count)
	return $c-$dc;
}

function module_instance($id, $com_name) {
	return component_instance($id, $com_name);
}

function get_default_instance($component) {
	// get the first component ID in database
	global $conn;
	$row = $conn->SelectRow('#__components', 'id', ' WHERE option_link=\'com_'.$component.'\'');
//	if (empty($row))		return null;
	return $row['id'];
}

function component_instance($id, $component) {
	if (!$id)
		$id = get_default_instance($component);
	return '&amp;Itemid='.$id;
}

function str_replace_once($search, $replace, $subject) {
	if (($p = strpos($subject, $search)) !== false)
		return substr($subject, 0, $p).$replace.substr($subject, $p + strlen($search));
	else
		return $subject;
}

// this is an UI function
function file_input_field($name, $max_bytes = null, $size = 45, $maxlength = null) {
	$html = '<input type="hidden" name="MAX_FILE_SIZE" value="';
	global $d_max_upload_size;
	if (isset($max_bytes))
		$html .= min(return_bytes($d_max_upload_size),$max_bytes);
	else $html .= return_bytes($d_max_upload_size);
	$html .= '" />';
	$html .= "\n";
	$html .= '<input id="'.$name.'" name="'.$name.'" type="file" class="dk_inputbox" value="" size="'.$size.'" ';
	if (isset($maxlength))
		$html .= ' maxlength="'.$maxlength.'"';
	$html .= ' />';
	return $html;
}

// Returns true if $string is valid UTF-8 and false otherwise.
function is_utf8($string) {
   
    // From http://w3.org/International/questions/qa-forms-utf-8.html
    return preg_match('%^(?:
          [\x09\x0A\x0D\x20-\x7E]            # ASCII
        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
        |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
    )*$%xs', $string);
   
} // function is_utf8

function each_id($ids, $field = 'id') {
	$sql = $field.'='.$ids[0];
	$c=count($ids);
	for($i=1;$i<$c;$i++)
		$sql.=' OR '.$field.'='.$ids[$i];
	return $sql;
}

// create a correct URL for help page
function create_context_help_url($title = '', $remote = false) {
	global $my;
	global $d_docs_server;
	// create the static page name
	$has_t = (strlen($title) != 0);
	if ($has_t) {
		// replace common weird characters
		$a = array(' ', ':', '/', '(', ')');
		$b = array('_', '-', '-', '', '');
		$page = strtolower(str_replace($a, $b, $title)).'.htm';
	} else
		$page = 'category-manual_index';
	//if there is a custom server defined, redirect to there
	if (strlen($d_docs_server)) {
		// server is wiki, give complete URL
		if (file_ext($d_docs_server) == 'php')
			return $d_docs_server.($has_t ? '?title='.$title : '');
		return $d_docs_server.'lang/'.$my->lang.'/'.$page;
	}

	if (!$remote) {
		global $d_root, $d_subpath;
		// if the page does exist
		if (is_file($d_root.'lang/'.$my->lang.'/docs/'.$page)) {
			// documentation in the correct language
			return $d_subpath.'lang/'.$my->lang.'/docs/'.$page;
		}
	}
	global $d__server;
	// finally redirect to proxy on Lanius CMS server
	return $d__server.'docs.php?title='.$title;
}

## extract content summary (mod_latest_news, mod_popular)
function content_summary($xhtml, $limit = 100) {
	$s = html_to_text($xhtml);
	if (strlen($s) > $limit) {
		$marker = '-'.random_string(8).'-';
		$s = wordwrap( substr($s, 0, (int)($limit * 1.5)), $limit, $marker);
		$p = strpos($s, $marker);
		$s = substr($s, 0, $p).' ...';
	}
	$d__utf8_unsafe = true;
	$s = xhtml_safe($s);
	$d__utf8_unsafe = false;
	return $s;
}

function unix_name($str) {
	return strtolower(preg_replace('/[^A-Za-z0-9_\\-]+/', '_', trim($str)));
}

?>