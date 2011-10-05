<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Filelog logger
# @author legolas558
#

$_DRABOTS->registerFunction( 'OnLog', 'filelog_logger' );
$_DRABOTS->registerFunction( 'OnAdminMenuLogItem', 'filelog_logmenuitem' );
$_DRABOTS->registerFunction( 'OnAdminMenuLogShow', 'filelog_logmenushow' );
$_DRABOTS->registerFunction( 'OnAdminMenuLogPages', 'filelog_logpages' );
$_DRABOTS->registerFunction( 'OnAdminMenuLogClear', 'filelog_logmenuclear' );
$_DRABOTS->registerFunction( 'OnAdminMenuLogCanClear', 'filelog_logmenucanclear' );

define('_FILELOG_HEADER', '<'."?php if(!defined('_VALID')){header('Status: 404 Not Found');die;} ?".">\n");

function filelog_logger($priority, $message) {
	global $_DRABOTS;
	$params = $_DRABOTS->GetBotParameters('logger', 'filelog');
	// prevent PHP tags injection
	$message = str_replace(array('<'.'?', '?'.'>'), array('< ?', '? >'), $message);
	global $d_root,$d, $d_private;
	// just to be sure the class exists
	include_once $d_root.'classes/http.php';
	if (strlen($GLOBALS['d_subpath']))
		$root = $d_root.CMSRequest::ScriptDirectory().'/';
	else
		$root = $d_root;
	
	$logfile = _filelog_rotate_logs();
	
	$f = @fopen($logfile, 'a+');
	if ($f===false) {
		echo '<big style="font-weight:bold; color:red;">Could not open log file for append, please report this error to the administrator<br /></big>';
		return false;
	} else {
		fwrite($f, 'At '.CMSCore::LogTimestamp().' with priority '.$priority.': '.$message."\n");
		//fflush($f);
		fclose($f);
	}
	return true;
}

function _filelog_rotate_logs() {
	global $d_root;
	$bname = _filelog_log_prefix();
	$fname = $bname.'1.php';
	if (file_exists($fname)) {
		global $_DRABOTS;
		$params = $_DRABOTS->GetBotParameters('logger', 'filelog');
		// check if overlapping maximum size
		if (filesize($fname) >= $params->get('split_size', 10240)) {
			$old_logs = array();
			include_once $d_root.'includes/safe_glob.php';
			// get paths of old log filenames
			foreach(safe_glob($bname.'*.gz.php') as $logfile) {
				if (preg_match('/log\\.(\\d+)\\.gz\\.php$/', $logfile, $m))
					$old_logs[(int)$m[1]] = $logfile;
			}
			// sort them
			krsort($old_logs);
			// shift all gzipped log files
			foreach($old_logs as $n => $fname) {
				rename($fname, $bname.($n + 1).'.gz.php');
			}
			// compress the current one
			file_put_contents($bname.'2.gz.php', _FILELOG_HEADER.
						gzcompress(substr(file_get_contents($bname.'1.php'), strlen(_FILELOG_HEADER)), 9));
		} else // keep appending
			return $bname.'1.php';
	}
	// reset the logfile (creating it)
	file_put_contents($bname.'1.php', _FILELOG_HEADER);
	return $bname.'1.php';
}

function filelog_logmenuitem() {
	return true;
}

function _filelog_log_prefix() {
	global $_DRABOTS, $d_private, $d_root;
	$params = $_DRABOTS->GetBotParameters('logger', 'filelog');
	return $d_root.$params->get('log_path', $d_private).'log.';
}

function filelog_logpages() {
	global $d_root;
	$bname = _filelog_log_prefix();
	include_once $d_root.'includes/safe_glob.php';
	// get paths of all log filenames and delete them
	$total = 1;
	foreach(safe_glob($bname.'*.gz.php') as $logfile) {
		if (preg_match('/log\\.\\d+\\.gz\\.php$/', $logfile))
			++$total;
	}
	return $total;
}

function filelog_logmenushow($page = 1) {
	$fname = _filelog_log_prefix().$page;
	if ($page > 1)
		$fname .= '.gz.php';
	else $fname .= '.php';
	if (!file_exists($fname))
		echo '&nbsp;';
	else {
		if ($page == 1)
			echo substr(file_get_contents($fname), strlen(_FILELOG_HEADER));
		else
			echo gzuncompress(substr(file_get_contents($fname), strlen(_FILELOG_HEADER)));
	}
}

function filelog_logmenuclear() {
	global $d_root, $d_private;
	$bname = _filelog_log_prefix();
	include_once $d_root.'includes/safe_glob.php';
	// get paths of all log filenames and delete them
	foreach(safe_glob($bname.'*.php') as $logfile) {
		if (preg_match('/log\\.\\d+\\.gz\\.php$/', $logfile))
			unlink($logfile);
	}
	// reset current logfile
	file_put_contents($bname.'1.php', _FILELOG_HEADER);
}

function filelog_logmenucanclear() {
	return is__writable_file(_filelog_log_prefix().'1.php');
}

?>
