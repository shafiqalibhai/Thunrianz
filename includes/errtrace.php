<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## PHP error tracer
# @author legolas558
#
# gives a debug backtrace for caught errors

// will remove the paths if found also in common strings
function fix_string($s, &$len, $cropit = true) {
	global $d_root, $d__windows;
	$rl = strlen($d_root);
	if ($d__windows)
		$copy_s = str_replace('\\', '/', $s);
	else
		$copy_s = $s;
	$len = $orig_len = strlen($s);
	$p = 0;
	$rounds = 0;
	// always remove the full path, derty
	while (false !== ($p=strpos($copy_s, $d_root, $p))) {
		$s = substr_replace($s, '::/', $p-$rounds*($rl-3), $rl);
		$p += $rl;
		$len -= $rl - 3;
		++$rounds;
	}
	if ($len != $orig_len)
		$len = '+'.(string)$len;
	if ($cropit) {
		if (strlen($s) > 255)
			$s = substr($s, 0, 255).'...';
	}
	if ($d__windows)
		return str_replace('\\', '/', $s);
	return $s;
}

function fix_root_path($path) {
	global $d__windows;
	if ($d__windows)
		$copy_s = str_replace('\\', '/', $path);
	else
		$copy_s = $path;
	if (strpos($copy_s, $GLOBALS['d_root'])===0) {
		$rp = '::/';
		global $my;
		// cannot use ::is_admin() here because some emulated User class instances can be active
		if (isset($my) && $my->gid==5)
			$rp = '<a href="javascript:alert(\'Root%20path%20is%20'.js_enc($GLOBALS['d_root']).'\')">'.$rp.'</a>';
		$path = $rp.substr($copy_s, strlen($GLOBALS['d_root']));
	}
	return $path;
}

global $d__last_error;
$d__last_error = null;

function cms_error_handler ($errno, $errstr, $orig_errfile, $orig_errline) {
	$errlevel = error_reporting();
	
	// some E_STRICT errors which are not logged
	if (strpos($errstr, 'Non-static method')===0)		return;
	if (strpos($errstr, 'var: Deprecated')===0)		return;
	if (strpos($errstr, 'Strict Standards')===0)		return;
	if (strpos($errstr, 'Invalid multibyte sequence in argument')===0) {
		if ($GLOBALS['d__utf8_unsafe'])	return;
		//TODO: add more info to the error message
//		$errstr = 'UTF-8 corruption';
	}
	// does not log fopen() access errors because fopen() can be used to check writability
	if (!($errlevel & $errno) && (strpos($errstr, 'fopen(')===0))
		return;
		
	$ignored = null;
	// conceal paths in the error message
	$errstr = fix_string($errstr, $ignored, false);
		
//DEBUG
//	if ($errno!=2048)
//	{
	// log this PHP error if the CMS instance is present
		global $d;
		if (isset($d))
			$d->log(6, (isset($orig_errfile)?$orig_errfile:'unknown').'('.(isset($orig_errline)?$orig_errline:'0').'): E'.$errno.' '.$errstr);
		$GLOBALS['d__last_error'] = $errstr;
//	}
	
	// if we are not monitoring these errors, let's ignore them
	if (($errno & $errlevel) != $errno)
		return;
		
/*	if (($errno == 8) && ($errlevel<2048))
		return;	// we ignore this type of notices since revision 658	*/
		
	// EXPERIMENTAL
	restore_error_handler();

	global $d_root;
	require_once $d_root.'includes/errtrace_funcs.php';

	global $d_subpath, $d_error_report, $d__type;
	$textonly = false;
	// $d_type would have been used, if consistent
	if (isset($d__type))
		$textonly = ($d__type != 'xhtml');
	
	// Attempt to clear the output
	//ob_clean();	// DO NOT ENABLE

	// show the header
	if (!$textonly) { ?>
		</select>
		</table>
		<div id="errorinfo" style="text-align: left; background-image: url(<?php echo $d_subpath; ?>media/common/crack.png);  background-position: right top; background-repeat: no-repeat; background-color:white; border: 3px solid;">
		<h1 style="text-align:center">Lanius CMS unhandled error 
		<?php
//	echo '<img src="'.$d_subpath.'media/common/cut.png"><hr>';
	} else {
		echo "\n*********************************";
		echo "\n**  Lanius CMS unhandled error  **";
		echo "\n*********************************\n\n";
	}

	// analyze the debug backtrace
	$hashlet = '';
	$errline = $orig_errline;
	$errfile = $orig_errfile;
	$lines = array();
	
	$has_backtrace = !ini_get('safe_mode') && function_exists('debug_backtrace');
	
	if ($has_backtrace) {
		$dbg = debug_backtrace();
		$c = count($dbg);
		$ignore = null;
		for ($i=$c-1;$i>=1;$i--) {
			// when inner function cannot be recognized, skip the line
			// it's useful for correct hashing (PHP 5.0.4 generates different hashes otherwise)
			if ($dbg[$i]['function']=='unknown')
				continue;
			$path = fix_string((string)@$dbg[$i]['file'], $ignore, false);
			if (
//L: for Gladius DB in-house testing
//				($GLOBALS['d_db']!='gladius') &&
				// do not debug DbFork class (for more compact dumps)
				(strpos($path, 'adodb_lite')!==false)
				)
				{
				// give a look to the following record
				if (!isset($dbg[$i+1]['line']))
					$errline = 'unknown';
				else
					$errline = $dbg[$i+1]['line'];
				$errfile = '';
				$errstr = 'E'.$errno.': '.$errstr;
				$hashlet .= "$path($errline):E$errno\n";
				break;
			}
			if (!isset($dbg[$i]['line']))
				$line = 'unknown';
			else
				$line = $dbg[$i]['line'];
			$lines[] = $path.'('.$line.'): '.
						$dbg[$i]['function'].'('.list_args(@$dbg[$i]['args'], $textonly).')';
			$hashlet .= "$path($line):".$dbg[$i]['function']."\n";
		}
	}
	$last = fix_root_path($errfile);
	if ($errfile !== '') {
		$hashlet .= fix_string($errfile, $ignored, false)."($errline):E$errno\n";
		$last .= "($errline): E$errno";
	}
	if ($errfile !== '') $last .= ' ';
	$last .= $errstr;

	// reduce the md5 hash to 8 characters (Lanius CMS short error hash format)
	if (strlen($hashlet)>0 && $has_backtrace) {
		$ehash = _cms_error_hash($hashlet);
		$hashlet = null;
	} else // since there was no debug backtrace, the error hash cannot be generated
		$ehash = '';
	// in case of full error dump add previous page and current page relative urls
	if ($d_error_report>=1) {
		if (!$textonly) {
			// display the error hash
			echo $ehash; ?></h1>
		<script language="javascript" type="text/javascript">
		function lcms_togglevis(obj) {
			if (obj.style.display == 'none')
				obj.style.display = 'block';
			else
				obj.style.display = 'none';
		}
		</script>
		&nbsp;&nbsp;<a href="javascript:lcms_togglevis(document.getElementById('lcms_errblk'))">Show/Hide error dump</a>
		<span id="lcms_errblk" style="display: <?php
			// display dump if this is an SVN revision
			if (strpos($GLOBALS['d_version'], 'SVN')!==false)
				echo 'block'; else echo 'none';
		?>"><small><small>
			<h3>=Error hash=</h3>
			&nbsp;<?php echo $ehash; ?>
			<h3>=Previous page=</h3>
	<?php } else
			echo "=Error hash=\n$ehash\n=Previous page=\n";
		include_once $d_root.'classes/http.php';
		$ref = @CMSRequest::Referer();
		echo_url($ref, $textonly);
		include_once $d_root.'classes/http.php';
		$qs = @CMSRequest::Querystring();
		$cur_url = @CMSRequest::ScriptName().(strlen($qs) ? '?'.$qs : '');
		if (!$textonly) { ?>
			<h3>=Current page=</h3>
			<?php echo $cur_url; ?>
			<h3>=Version information=</h3><?php
		} else
			echo "\n=Current page=\n$cur_url\n=Version information=\n";
		//TODO: remove this one
		ob_start();
		version_info(); ?>
		<h3>=Debug backtrace=</h3>
			<pre><?php
		echo implode("\n", $lines);
	}	$lines = null;
	
	$lang_def_error = (preg_match('/^Use of undefined constant _[A-Z0-9_]+/', $errstr)>0);
	
	if ($d_error_report>=1) {
		global $d__server;
		if (!isset($d__server))
			$d__server = 'http://www.laniuscms.org/';
		?><div style="font-size:16px; background-color: black; color:cyan"><?php
		echo $last.'</div></pre></small></small></span>';
		$error = strip_tags(ob_get_contents());	// copy the error message
		if ($textonly) {
			ob_end_clean();
			echo $error;
		} else
			ob_end_flush();
		if (!$textonly) {
		echo '<hr>';
//		echo '<img src="'.$d_subpath.'media/common/cut.png">';
		if ($lang_def_error)
			echo '<p>This is possibly an <strong>Internationalization</strong> error, be sure that you are using the English language files or that your language files are up to date before proceeding.</p>';
		?>
		<p align="center">
		<big><big><?php if (false) {?>
		<a href="<?php echo $d__server; ?>services/error.php?ehash=<?php echo $ehash; ?>" target="_blank" title="Investigate about this error">Click here to check if the error has already been reported and fixed</a>
		<br />
		<big><strong>OR</strong></big><br />
<?php	} // disabled URL
	} else {
			echo "\n\nVisit the following URL to check if this error has already been reported:\n".
			"${d__server}services/error.php?ehash=$ehash\n\n".
			"We are sorry for the inconvenience; thanks for your collaboration\n".
			"Developers will review your submission and fix the bug before the next release.\n";
			if ($lang_def_error)
				echo "This is possibly an Internationalization error, be sure that you are using the English language files or that your language files are up to date before proceeding.\n";
		}
		if (!$textonly && $has_backtrace)
			advanced_report($dbg, $lines, $ehash, $last, $error, $ref, $cur_url);
		if (!$textonly) { ?>
		</big>
		<hr />
		<p><strong>We are sorry for the inconvenience; thanks for your collaboration.</strong></p>
		<p>Developers will review your submission and fix the bug before the next release.</p>
		</big>
		</p>
		</div>
<?php
		}
		exit();
	} else {
		global $d__server;
		//TODO: use $valid_lang
		$buggie = '<a href="'.$d__server.'services/error.php?ehash='.$ehash.'" target="_blank" title="Investigate about this error"><img src="'.$d_subpath.'media/common/buggie.png" border="0" alt="ERROR" width="32" height="42" /></a>';
		if ($textonly) echo strip_tags($buggie); else echo $buggie;
	}
	return false;
}

?>