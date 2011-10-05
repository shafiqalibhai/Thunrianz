<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## FS class
# @author legolas558
#
# centralized filesystem operations

include_once $GLOBALS['d_root'].'lang/'.$GLOBALS['my']->lang.'/admin/classes/fs.php';

if (!strlen($GLOBALS['d_setmode']))
	define('_FS_WRITE_MODE', octdec('0777'));
else
	define('_FS_WRITE_MODE', octdec($GLOBALS['d_setmode']));

class FS {

var $_batch_mode;
var $_critical_mode;

	function FS($failback = false, $batch = false) {
		// atomical batch sequences not yet supported
		$this->_batch_mode = $batch;
		$this->SetFailback($failback);
	}
	
	function SetFailback($failback) {
		// choose to fail (with server error) or to simply report back a false return value
		$this->_critical_mode = !$failback;
	}
	
	var $_checked_files = array();

	// opens a file for writeability and returns FALSE on failure
	function write_open($filename) {
		if (file_exists($filename)) {
			if (!isset($this->_checked_files[$filename])) {
				$this->assertownership($filename);
				$this->assertmode($filename);
				$this->_checked_files[$filename] = true;
			}
		}
		$f = @fopen($filename, 'wb');
		if ($f===false) {
			if ($this->_critical_mode)
				$this->_failure(_FS_WRITE_ACCESS_DENIED, $GLOBALS['d__last_error'], ' %s', $filename);
			return false;
		}
		return $f;
	}
	
	// closes the file and sets the proper permissions
	function write_close($handle, $filename) {
		if (!isset($this->_checked_files[$filename])) {
			$this->assertownership($filename);
			$this->assertmode($filename);
			$this->_checked_files[$filename] = true;
		}
		return fclose($handle);
	}

	// opens a file in append mode and returns FALSE on failure
	function append_open($filename) {
		if (file_exists($filename)) {
			if (!isset($this->_checked_files[$filename])) {
				$this->assertownership($filename);
				$this->assertmode($filename);
				$this->_checked_files[$filename] = true;
			}
		}
		$f = @fopen($filename, 'a');
		if ($f===false) {
			if ($this->_critical_mode)
				$this->_failure(_FS_WRITE_ACCESS_DENIED, $GLOBALS['d__last_error'], ' %s', $filename);
			return false;
		}
		return $f;
	}

	function put_contents($filename, $content) {
		if (file_exists($filename)) {
			if (!isset($this->_checked_files[$filename])) {
				$this->assertownership($filename);
				$this->assertmode($filename);
				$this->_checked_files[$filename] = true;
			}
		}
		if (@file_put_contents($filename, $content)===false) {
			if ($this->_critical_mode)
				$this->_failure(_FS_WRITE_FAILURE, $GLOBALS['d__last_error'], ' %s', $filename);
			return false;
		}
		if (!isset($this->_checked_files[$filename])) {
			$this->assertownership($filename);
			$this->assertmode($filename);
			$this->_checked_files[$filename] = true;
		}
		return true;
	}
	
	function _failure() {
		$args = func_get_args();
		$msg = array_shift($args);
		$err = array_shift($args);
		$format_str = array_shift($args);
		
		global $my;
		if ($my->is_admin()) {
			if (count($args)) {
				$r_arg = array($format_str);
				foreach($args as $arg) {
					$r_arg[] = '<small>'.fix_root_path($arg).'</small>';
				}
				$msg.=call_user_func_array('sprintf', $r_arg);
			}
		}
		global $d,$d_root;
		include $d_root.'includes/servererror.php';
//		ob_end_clean();
//		ob_start();	// ???
		$explanation = '<hr />';
		if ($my->is_admin()) {
			$explanation.=_FS_FAILURE_EXPLANATION;
			$d->log(5, 'Filesystem: '.strip_tags($msg));
		} else {
			$explanation.=_FS_CONTACT_ADMIN;
			$d->log(5, 'Filesystem: '.$msg);
		}
		if (isset($err))
			$msg.="\nPHP error: <small><em>".strip_tags($err).'</em></small>';
		service_msg(_FS_OPERATION_FAILED, $msg, $explanation, 'stop');
		exit;
	}
	
	// assert the existance of a path (with trailing slash)
	// if $external is false, $path is relative to $d_root
	function assertpath($path, $ofs = 0, $external = false) {
//		echo 'Asserting '.substr($path, $ofs).'<br>';
		if (!$external) $r = $GLOBALS['d_root']; else $r='';		
		$p = strpos($path, '/', $ofs);
		// there is no slash
		if ($p===false) {
			if ($ofs==0)
				return $this->assertdir($r.$path);
			return $this->assertdir($r.substr($path,0,$ofs));
		}
		// first character is slash, check rest of path
		if ($p == 0)
			return $this->assertpath($path, 1, $external);
		return ($this->assertdir($r.substr($path, 0, $p)) && $this->assertpath($path, $p+1, $external));
	}
	
	function dir_exists($path) {
		return (/*file_exists($path) || */ is_dir($path));
	}

	// assert that $path is a directory, eventually creating it
	function assertdir($path, $mode = _FS_WRITE_MODE) {
		if (!$this->dir_exists($path))
			return $this->mkdir($path, $mode);
		return true;
	}
	
	function assertmode($path) {
		global $d_setmode;
		if (!strlen($d_setmode))
			return;
		$perms = @fileperms($path);
		if ($perms !== false) {
			$cmode='0'.decoct($perms & 0x0fff);
			if ($cmode !== $d_setmode)
				// direct call
				return @chmod($path, octdec($d_setmode));
		}
		return true;
	}

	function mkdir($path, $mode = _FS_WRITE_MODE) {
		if (!@mkdir($path, $mode)) {
			if ($this->_critical_mode)
				$this->_failure(_FS_MKDIR_FAILURE, $GLOBALS['d__last_error'], ' %s', $path);
			return false;
		}
		$this->assertownership($path);
		$this->assertmode($path);
		return true;
	}
	
	// warning: $source must exist before calling!
	function copy($source, $dest) {
		if (!@copy($source, $dest)) {
			// A bug of PHP on Windows...
			if (@filesize($source)===0)
				return true;
			if ($this->_critical_mode)
				$this->_failure(_FS_COPY_FAILURE, $GLOBALS['d__last_error'], _FS_SRC_TO_DST, $source, $dest);
			return false;
		}
		return true;
	}
	
	function _raw_move($source, $dest) {
		// first try a copy/delete approach
		if (!@copy($source, $dest)) {
			if ($this->_critical_mode)
				$this->_failure(_FS_MOVE_FAILURE, $GLOBALS['d__last_error'],
							_FS_SRC_TO_DST, $source, $dest);
			return false;
		}
		return $this->remove($source);
	}
	
	function move($source, $dest) {
		if (!@rename($source, $dest))
			// first try a copy/delete approach
			return $this->_raw_move($source, $dest);
		return true;
	}
	
	function assert_remove($path) {
		if (!file_exists($path))
			return true;
		return $this->remove($path);
	}
	
	function remove($path) {
		if (!isset($this->_checked_files[$path])) {
			$this->assertownership($path);
			$this->assertmode($path);
			$this->_checked_files[$path] = true;
		}
		if (!@unlink($path)) {
/*			if (is_windows()) {	// try to remove the readonly flag
				@chmod($path, $GLOBALS['d_setmode']);
				if (@unlink($path))
					return true;
			} */
			if ($this->_critical_mode)
				$this->_failure(_FS_REMOVE_FAILURE, $GLOBALS['d__last_error'], ' %s', $path);
			return false;
		}
		unset($this->_checked_files[$path]);
		return true;
	}
	
	function rmdir($path) {
		if (!isset($this->_checked_files[$path])) {
			$this->assertownership($path);
			$this->assertmode($path);
			$this->_checked_files[$path] = true;
		}
		if (!@rmdir($path)) {
			if ($this->_critical_mode)
				$this->_failure(_FS_RMDIR_FAILURE.
				( count(read_dir($path)) ? ' '._FS_FILES_WITHIN_FOLDER : ''),
				$GLOBALS['d__last_error'], 
				' %s', $path);
			return false;
		}
		unset($this->_checked_files[$path]);
		return true;
	}

	// copies all files and folders from $src to $to (recursively)
	function xcopy($src, $to, $assert_paths = true) {
		$done = $this->fcopy($src, $to);
		$folders = read_dir($src, 'dir');
		foreach ($folders as $dir) {
			if ($assert_paths)
				$this->assertdir($to.$dir);
			$done += $this->xcopy($src.$dir.'/', $to.$dir.'/', $assert_paths);
		}
		return $done;
	}

	// as ::xcopy(), but moves the files
	function xmove($src, $to, $assert_paths = true) {
		$done = $this->fmove($src, $to);
		$folders = read_dir($src, 'dir');
		foreach ($folders as $dir) {
			if ($assert_paths)
				$this->assertdir($to.$dir);
			$done += $this->xmove($src.$dir.'/', $to.$dir.'/', $assert_paths);
		}
		return $done;
	}

	
	// copies only folders and files in subfolders (does not copy normal files in the $src folder)
	function tcopy($src, $to) {
		$done = 0;
		$folders = read_dir($src, 'dir');
		foreach ($folders as $dir) {
			$this->assertdir($to.$dir);
			$done += $this->fcopy($src.$dir.'/', $to.$dir.'/');
		}
		return $done;
	}
	
	// moves only files from the specified directory
	function fmove($src, $to) {
		$done = 0;
		$files = read_dir($src, 'file');
		foreach ($files as $file) {
			if ($this->move($src.$file, $to.$file)!==FALSE)
				++$done;
		}
		return $done;
	}

	// copies only files from the specified directory
	function fcopy($src, $to) {
		$done = 0;
		$files = read_dir($src, 'file');
		foreach ($files as $file) {
			if ($this->copy($src.$file, $to.$file)!==FALSE)
				++$done;
		}
		return $done;
	}

	// recursively delete a directory
	// must use trailing slash
	function purge($path) {
		if (!is_dir($path)) {
			if ($this->_critical_mode)
				$this->_failure(_FS_DIR_NOT_FOUND, null, ' %s', $path);
			return false;
		}
		$handle = lcms_opendir($path, $this->_critical_mode);
		$noerror = true;
		while (false !== ($file = readdir($handle))) {
			// skip self-references
			if (($file == '.') || ($file == '..'))
				continue;
			if (is_dir($path.$file)) {
				$this->purge ($path.$file.'/');
				if(!$this->rmdir($path.$file))
					$noerror = false;
			} else if(!$this->unlink($path.$file))
				$noerror = false;
		}
		closedir($handle);
		return $noerror;
	}

	// recursively delete a directory and finally remove it too
	// must use trailing slash
	function deldir($dir) {
		if ($this->purge($dir))
			return $this->rmdir($dir);
		else
			return false;
	}
	
	function chmod($filename, $mode = _FS_WRITE_MODE) {
		if (!@chmod($filename, $mode)) {
			if ($this->_critical_mode)
				$this->_failure(_FS_CHMOD_FAILURE, $GLOBALS['d__last_error'], ' %s', $filename);
			return false;
		}
		return true;
	}

	// path can be file or directory, trailing slash is optional
	function assertownership($path) {
		global $d_setowner, $d_setgroup;
		if (!strlen($d_setowner) && !strlen($d_setgroup))
			return;
		$uid = @fileowner($path);
		$gid = @filegroup($path);
		if ($uid===FALSE || $gid===FALSE)
			return;
		if (strlen($d_setowner)) {
			if ($uid != $d_setowner)
				// direct call OK
				@chown($path, $uid);
		}
		if (strlen($d_setgroup)) {
			if ($gid != $d_setgroup)
				@chgrp($path, $gid);
		}
		if (is_dir($path)) {
			if (substr($path, 0, -1) !== '/')
				$path .= '/';
			$d = lcms_opendir ($path, $this->_critical_mode) ;
			while(($file = readdir($d)) !== false) {
				if ($file != "." && $file != "..")
					$this->assertownership($path.$file);
			}
		}
	}
	
	function chgrp($filename, $gid) {
		if (!@chgrp($filename, $gid)) {
			if ($this->_critical_mode)
				$this->_failure(_FS_CHGRP_FAILURE, $GLOBALS['d__last_error'], ' %s', $filename);
			return false;
		}
		return true;
	}

	function chmod_all($path, $mode) {
		$handle = lcms_opendir($path, $this->_critical_mode);
		$noerror = $this->chmod($path, $mode);
		if (!$noerror) return false;
		while (false !== ($file = readdir($handle))) {
			// skip self-references
			if (($file == '.') || ($file == '..'))
				continue;
			if (is_dir($path.$file)) {
				$noerror = $this->chmod_all ($path.$file."/", $mode);
				if (!$noerror) break;
			} else {
				$noerror = $this->chmod($path.$file, $mode);
				if (!$noerror) break;
			}
		}
		closedir($handle);
		return $noerror;
	}
	
	function move_uploaded_file($uploaded_file, $destination) {
		if (!@move_uploaded_file($uploaded_file, $destination)) {
			// attempt to create the file since move operation failed miserably
			$ct = @file_get_contents($uploaded_file);
			if ($ct === false) {
				if ($this->_critical_mode)
					$this->_failure(_FS_CANNOT_READ_UPLOAD,
								$GLOBALS['d__last_error'], ' %s', $uploaded_file);
				return false;
			}
			return $this->put_contents($destination, $ct);
		}
		// file was moved, let's check the permissions/ownership
		$this->assertownership($destination);
		$this->assertmode($destination);
		$this->_checked_files[$destination] = true;
	}
	
	// deprecated methods
	function is_dir($d) { return $this->dir_exists($d); }
	function rename($src, $dest) { return $this->move($src, $dest); }
	function unlink($file) { return $this->remove($file); }
	function delete($file) { return $this->remove($file); }
}

// DO NOT ADD trailing newline
?>
