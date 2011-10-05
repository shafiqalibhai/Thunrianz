<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Admin functions @gid 4
#
# functions exposed for backend users (once only manager and admin)
# some of these should be downgraded to user level and/or made more secure

function version_compat($ver) {
	$dkver = explode(' ',cms_version(false));
	$dkver = current($dkver);
	return (strnatcmp($dkver, $ver) >= 0);
}

function get_internal_version() {
	global $conn;
	$row = $conn->SelectRow('#__packages', 'version', ' WHERE type=\'core\' AND (name=\'Lanius CMS\' OR name=\'Drake CMS\')');
	if (empty($row))
		return null;
	return $row['version'];
}

// for legacy purposes
function array_assoc($a) {
	$na = array();
	foreach($a as $k=>$v) {
		$na[] = array('name' => $v, 'value' => $k);
	}
	return $na;
}

function remote_update($package_id) {
	$core_ver = current(explode(' ', $GLOBALS['d_version']));
	$p = strrpos($package_id, '-');
	$p_id = substr($package_id, 0, $p);
	$p_ver = substr($package_id, $p+1);
	return $GLOBALS['d__server'].'index2.php?option=nest&no_html=1&task=retrieve&core_ver='.
			$core_ver.'&p_ver='.rawurlencode($p_ver).'&p_id='.rawurlencode($p_id);
}

function read_dir_xml($dir,$xml_file,$req, $path='') {
	$info=array();
	$info_file = '';
	$dh=lcms_opendir($dir);
	while ( $dir_name = readdir( $dh ))	{
		if( $dir_name[0]=='.') continue;	//L: skip hidden folders and . ..
		if ( is_dir( $dir.$dir_name ) ) {
			// look for any XML file
			if($xml_file==='') {
				global $d_root;
				include_once $d_root.'includes/safe_glob.php';
				$xmls = safe_glob($dir.$dir_name.'/*.xml');
				if (count($xmls))
					$info_file = $xmls[0];
			} else
				$info_file=$dir.$dir_name.'/'.$xml_file;

		$tinfo='';
		$xml = new AnyXML();

		if (!$xml->fromString((string)@file_get_contents($info_file)))
			continue;
			
		if (strlen($path))
			$xml = $xml->getElementByPath($path);
		if (!isset($xml))
			continue;

		$tinfo['id']=$dir_name;
		foreach($req as $var=>$val ){
			$obj = $xml->getElementByPath($val);
			if (!isset($obj))
				continue;
			$tinfo[$var]=$obj->getValue();
		}
		$info[]=$tinfo;
		}

	}
	closedir( $dh );
	return $info;
}

function read_file_xml($info_file,$req, $path = '') {
	if(!is_file($info_file))
		return false;

	$xml = new AnyXML();
	if (!$xml->fromString(file_get_contents($info_file)))
		return false;
	
	if (strlen($path)) {
		$xml = $xml->getElementByPath($path);
		if (!isset($xml))
			return false;
	}
	
	//$tinfo['id']=$dir_name;	//L: remove this commented line?
	$tinfo=array();
	
	foreach($req as $var=>$val ) {
		$tinfo[$var] = $xml->getValueByPath($val);
	}
	
	return $tinfo;
}

/* Various interface arrays*/
function category_array($sez='',$select=0, $extra = '') {
	global $conn, $access_sql;
	$rsa=$conn->SelectArray('#__categories', 'id,name', " WHERE section = '$sez' $access_sql $extra");
	if (!isset($rsa[0])) return array();
	$cat_arr=array();
	if($select!=-1)$cat_arr[]=array("name"=>_SELECTCAT,"value"=>"");
	foreach($rsa as $row)$cat_arr[]=array("name"=>$row['name'],"value"=>$row['id']);
	return select($cat_arr,$select);
}

function pos_array($default='left') {
	$pos_array=array(array("name"=>_MODULES_POS_LEFT,"value"=>"left"),array("name"=>_MODULES_POS_RIGHT,"value"=>"right"));
	return select($pos_array,$default);
}

function access_array($default=0) {
	// make a copy not to waste the public global one
	$al = $GLOBALS['access_level'];
	return select($al, $default);
}

function insert_published($data,$key,$id) {
	for($i=0;$i<count($data);$i++)
	{
		if($data[$i]['id']==$id)
			$data[$i][$key]=1;
		else
			$data[$i][$key]=0;
	}
	return $data;
}

global $d__users;
$d__users = null;

// used by various components
function username_by_id($id) {
	if ($id==0)
		return _ANONYMOUS;
	global $d__users;
	if (!isset($d__users)) {
		global $conn;
		$rsa = $conn->SelectArray('#__users', 'id,username');
		foreach($rsa as $row) {
			$d__users[(int)$row['id']] = $row['username'];
		}
	}
	if (!isset($d__users[$id]))
		// this can happen in case of removed users
		return _NA.' ('.$id.')';
	return $d__users[$id];
}

//TODO: might need optimization/deprecation
function gui_array_replace($data,$rep_array, $cbs = null, $full_cbs = null) {
	$c=count($data);
	for($i=0;$i<$c;$i++) {
		// general-purpose array replacement
		foreach($rep_array as $var=>$val) {
//			if (isset($data[$i][$var])) {
				foreach($val as $kvar=>$kval) {
					if($kval['value']==$data[$i][$var]) {
						$data[$i]["o".$var]=$data[$i][$var];
						$data[$i][$var]=$kval['name'];
						break;
					}
				}
//			}
		}
		// callbacks used to transform the value
		if (isset($cbs)) {
			foreach($cbs as $var => $cb) {
//				if (isset($data[$i][$var]))
					$data[$i][$var] = $cb($data[$i][$var]);
			}
		}
		// a special breed of callbacks which operate on the whole row
		if (isset($full_cbs)) {
			foreach($full_cbs as $var => $cb) {
//				if (isset($data[$i][$var]))
					$cb($data[$i]);
			}
		}
	}
	return $data;
}

// permission info available only in admin mode
function getPerms( $in_Perms ) {
   $sP= '';
	   // owner
   $sP .= (($in_Perms & 0x0100) ? 'r' : '&minus;') .
           (($in_Perms & 0x0080) ? 'w' : '&minus;') .
           (($in_Perms & 0x0040) ? (($in_Perms & 0x0800) ? 's' : 'x' ) :
                                   (($in_Perms & 0x0800) ? 'S' : '&minus;'));

   // group
   $sP .= (($in_Perms & 0x0020) ? 'r' : '&minus;') .
           (($in_Perms & 0x0010) ? 'w' : '&minus;') .
           (($in_Perms & 0x0008) ? (($in_Perms & 0x0400) ? 's' : 'x' ) :
                                   (($in_Perms & 0x0400) ? 'S' : '&minus;'));

   // world
   $sP .= (($in_Perms & 0x0004) ? 'r' : '&minus;') .
           (($in_Perms & 0x0002) ? 'w' : '&minus;') .
           (($in_Perms & 0x0001) ? (($in_Perms & 0x0200) ? 't' : 'x' ) :
                                   (($in_Perms & 0x0200) ? 'T' : '&minus;'));
   return $sP;
}
  
function _sql__recode($m) {
	$s = substr($m[0], 1, strlen($m[0])-2);
	if (!strlen($s)) return "''";
	return "'".sql_encode(str_replace("''", "'", $s))."'";
}

function _sql__recode_utf8($m) {
	$s = substr($m[0], 1, strlen($m[0])-2);
	if (!strlen($s)) return "''";
	return "'".sql_encode(utf8_encode(str_replace("''", "'", $s)))."'";
}

function _sql__utf8($m) {
	$s = substr($m[0], 1, strlen($m[0])-2);
	if (!strlen($s)) return "''";
	return "'".utf8_encode($s)."'";
}

function _sql_recode(&$query, $fn) {
	// converts the Lanius CMS DB strings in the proper format
	return preg_replace_callback('/'.__QUOTED_S.'/', $fn, $query);
}

// from legolas558's Gladius DB
function split_sql(&$sql, $recode=false, $utf8=false) {
	// remove the single-line and multi-line comments (may be harmful for inner data?)
//	$sql = preg_replace('/\\/\\*.*?\\*\\//s', '', $sql);
	/*	some history of PHP here. See http://bugs.php.net/bug.php?id=41050
		Before the integration of PCRE 7.0 into PHP (e.g. PHP < 4.4.6 & PHP <= 5.2.0)
		the following regular expression worked with no harm for nobody:
		/(insert|drop|create|select|delete|update)([^;']*(('[^']*')+)?)*(;|$)/i
		when PCRE 7.0 was inserted, it stopped working (causing a segmentation fault)
		The current version (using a '?' on the last regexp) seems to work correctly now
		on any PHP/PCRE.
	*/
	//WARNING: this is a memory-intensive operation
//	var_dump(ini_get('memory_limit'));die;
	if (preg_match_all("/(?i:select|update|insert|delete|create|drop|alter)\\s+([^;']*('[^']*')*)*?(;|$)/",$sql, $m)) {
		if ($GLOBALS['d_db']=='gladius')
			$recode = false;
		if (!$recode && !$utf8)
			return $m[0];
		$n=array();
		$fn='_sql__';
		if ($recode) {
			$fn.='recode';
			if ($utf8)
				$fn.='_utf8';
		} else
			$fn.='utf8';
		foreach($m[0] as $s) {
			$n[] = _sql_recode($s, $fn);
		}
		return $n;
	} else
		return array();
}

// should be used only when $fname is a file
// NOTE: is_writable() cannot be used here because it would not be consistent with ACLs
//see http://bugs.php.net/bug.php?id=27609
//see http://bugs.php.net/bug.php?id=30931
function is__writable_file($fname) {
	// check tmp file for read/write capabilities
	$rm = file_exists($fname);
	$f = @fopen($fname, 'a');
	if ($f===false)
		return false;
	fclose($f);
	if (!$rm)
		unlink($fname);
	return true;
}

// this function supports also directory paths
function is__writable($path) {
	if ($path[strlen($path)-1]=='/') // recursively return a temporary file path
	    return is__writable_file($path.uniqid(mt_rand()).'.tmp');
	else if (is_dir($path))
		return is__writable_file($path.'/'.uniqid(mt_rand()).'.tmp');
	return is__writable_file($path);
}

if (isset($GLOBALS['d_atemplate'])) {
	// allow non-default templates to override pictures
	if ($GLOBALS['d_atemplate']=='default') {
		function admin_template_pic($pic) {
			return $GLOBALS['d_subpath'].'admin/templates/default/images/'.$pic;
		}
	} else {
		function admin_template_pic($pic) {
			global $d_root;
			$custom = 'admin/templates/'.$GLOBALS['d_atemplate'].'/images/'.$pic;
			if (file_exists($d_root.$custom))
				return $custom;
			return $GLOBALS['d_subpath'].'admin/templates/default/images/'.$pic;
		}
	}
}

// try to set a certain amount of seconds as timeout and return the actual value (that might or might not be changed)
function shift_timeout($amt) {
	global $d__utf8_unsafe;
	$d__utf8_unsafe = true;
	@set_time_limit($amt);
	$spent = mt_float() - $GLOBALS['page_start_time'];
	$rv = (int)(@ini_get('max_execution_time') - $spent);
	$d__utf8_unsafe = false;
	return $rv;
}

define('_DKDB_SIGNATURE', '/* @DKDB %s @GID %s <?php if (@$my->gid<'.'%s)die; ?> */'."\n");
//NOTE: the databases with 'Beta' are of an unsupported breed
define('_DKDB_SIGNATURE_RX', '/\\/\\* @DK(PDL|DB) (\\d+\\.\\d+\\.?\\d?\\d?)(-SVN)? './*(?:Beta ).*/
		'?@GID\\s+(\\d+) \\<\\?php if \\(@\\$my-\\>gid\\<\\s*(.\\d+)\\)die; \\?\\> \\*\\//i');

## opens a Lanius CMS database file
#
# returns array($min_gid, $min_version, $table_prefix)
#
function open_dkdb($fname) {
	$ext = file_ext($fname);
	if ($ext == 'gz') {
		$f = @gzopen($fname, 'rb');
		if ($f===false) return null;
		$s = @gzread($f, strlen(_DKDB_SIGNATURE)+600);
		gzclose($f);
	} else {
		$f = @fopen($fname, 'rb');
		if ($f===false) return null;
		$s = @fread($f, strlen(_DKDB_SIGNATURE)+600);
		fclose($f);
	}
	return open_dkdb_raw($s);
}

function open_dkdb_raw(&$s) {
	$hdr = array(null,null);
/*	header('Content-Type: text/plain');
	echo current(explode("\n", $s))."\n";
	echo _DKDB_SIGNATURE_RX;	*/
	if (preg_match(_DKDB_SIGNATURE_RX, $s, $m)) {
		$gid = (int)$m[4];
		if ($gid !== (int)$m[5]) {
			$p=strpos($s, '?> */');
			fseek($f, $p+5);
			return $f;
		}
		$ver = $m[2];
		$hdr = array($gid, $ver);
		// the SVN specification was present only since version 0.5.2
		// theorically could use >= after release
		if (strnatcmp($ver, '0.5.2')>=0)
			$svn = (strlen($m[3]) > 0);
		else
			$svn = null;
	} else {
		// very very old databases (Drake CMS)
		if (preg_match('/\\/\\* @DKDB Drake CMS v([^\\s]+).*?\\*\\//', $s, $m)) {
			$hdr = array(5, $m[1]);
			if (preg_match('/gid<\\s*(\\d+)/', $m[0], $m))
				$hdr[0] = (int)$m[1];
		}
		$svn = null;
	}
	// finally adds the DKDB header prefix
	$hdr[] = dkdb_read_prefix($s);
	$hdr[] = $svn;
	return $hdr;
}

function dkdb_h($gid) {
	settype($gid, 'string');
	if (strlen($gid)==1) $gid = ' '.$gid;
	$ver = cms_version();
	$svn = (strpos($ver, 'SVN') !== FALSE);
	$a = explode(' ', $ver);
	$ver = current($a);
	if ($svn)
		$ver.='-SVN';
	return sprintf(_DKDB_SIGNATURE, $ver, (int)$gid, $gid);
}

function dkdb_prefix(&$rand_prefix) {
	if (!isset($rand_prefix))
		$rand_prefix = '#'.random_string(5).'_';
	return "/* DKDB PREFIX = $rand_prefix */\n\n";
}

function dkdb_read_prefix(&$sql) {
	if (!preg_match('/\\n\\/\\* DKDB PREFIX = (.*?) \\*\\//', substr($sql, 0, min(strlen($sql), 600)), $m))
		return '#__';
	return $m[1];
}

?>
