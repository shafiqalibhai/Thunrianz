<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
/* (C) 2006 by legolas558
	TarBackup skeleton class v 0.2.2
	Licensed under GPL
	http://sf.net/projects/phptarbackup

 * This program is free software and open source software; you can redistribute
 * it and/or modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA  or visit
 * http://www.gnu.org/licenses/gpl.html
 */

if (!defined('__TBK_CHUNK')) {
	define('__TBK_CHUNK', 5 * 1024 * 1024);
}

class TarBackupSkel {
var $progress;
var $average;
var $errstr;

var $relative_paths = false;
var $logcb = null;

function TarBackupSkel(&$var) {
	$this->progress =& $var;
	$this->errstr = '';
	$this->average = 0.0;
}

function GetTarballName() {
	return $this->progress->tarname;
}

function ErrorMsg() {
	return $this->errstr;
}

function Percent() {
	return sprintf('%.2f', ($this->progress->done*100/$this->progress->total));
}

function Finished() {
	return ($this->progress->done == $this->progress->total);
}

function _addFile($fn, $fs) {
	if (strlen($fn)>99) {
		$this->errstr = "File name \"$fn\" is too long (max 99 characters)";
		if (isset($this->logcb))
			$this->logcb($this->errstr);
		return false;
	}
	$this->progress->fname[] = $fn;
	$this->progress->fsize[] = $fs;
	$this->progress->total += $fs;
	return true;
}

function _init($root, $p_tarname) {
	$this->progress = new stdClass();
	$this->progress->root = $root;
	$this->progress->tarname = $p_tarname;
	$this->progress->done = 0;
	$this->progress->total = 0;
	$this->progress->spent = 0.0;
	$this->progress->fname = array();
	$this->progress->fsize = array();
}

function _pclexec() {
	$args = func_get_args();
	$funcname = $args[0];
	$max_bytes = $args[1];

	$lista = array();
	$done = 0;
	$c = count($this->progress->fname);
	for ($i=0;$i<$c;$i++) {
		$done += $this->progress->fsize[$i];
		$lista[] = ($this->relative_paths ? $this->progress->root : '').$this->progress->fname[$i];
		if ($done>$max_bytes) {
			$c = $i+1;
			break;
		}
	}
	
	if (count($lista)==0)
		return true;

	array_splice($args, 0, 2);
	array_unshift($args, $lista);
	array_unshift($args, $this->progress->tarname);

	$er = error_reporting(-1 ^ E_NOTICE);
		$r = call_user_func_array($funcname, $args);
	error_reporting($er);

	if (PclErrorCode() < 0) { // some error happened
		$this->errstr = PclErrorString($r);
		return false;
	}

	$this->progress->done += $done;
	array_splice($this->progress->fname, 0, $c);
	array_splice($this->progress->fsize, 0, $c);

	return true;
}

function _sync_average($start) {
	$spent = mt_float()-$start;
	$this->average = $this->progress->done / ($this->progress->spent+$spent);
//	$this->_debug();
	return $spent;
}

function _debug() {
	echo 'Average speed: '.sprintf('%.2F', $this->average/1024).' kb/s<br />';
	echo 'Total data processed: '.sprintf('%.2F', $this->progress->done/1024).' kb<br />';
	echo 'Total data left: '.sprintf('%.2F', ($this->progress->total-$this->progress->done)/1024).' kb<br />';
	echo 'Total time spent: '.sprintf('%.2F', $this->progress->done/$this->average).' s<br />';
	echo 'PclErrorString: '.PclErrorString(PclErrorCode()).'<hr>';
	flush();
}

// main looping function
// will return TRUE if no error happened and the time was correctly spent executing $method
// will return FALSE if an error happened in _pclexec
function _loop($next_step_cb, $max_time, $method) {

	if ($max_time <= 0) {
		// try to run for about 4 minutes
		@set_time_limit(250);
		$default = (int)@ini_get('max_execution_time');
		if ($default == 0)
			$default = 30;
		$max_time += $default;
	}

	$start = mt_float();

	// Perform an average checking cycle if necessary
	$do_avr = ($this->progress->spent == 0);
	if ($do_avr) {

		$r = $this->$method(__TBK_CHUNK);

		$spent = $this->_sync_average($start);
//		$this->average/=$this->progress->spent;
		if (!$r || ($max_time-$spent<1)) {
			$this->progress->spent += $spent;
			$this->_finalize($next_step_cb);
			return $r;
		}
	}

	if (!$do_avr || $this->$method(__TBK_CHUNK/5)) {
		do {
			$spent = $this->_sync_average($start);
			$left = $max_time-$spent;
			$chunk = (int)($left*$this->average);
			if ($chunk > __TBK_CHUNK)
				$chunk = __TBK_CHUNK/2;
			else {
				$this->progress->spent += $this->_sync_average($start);
				$this->_finalize($next_step_cb);
				return true;
			}
		} while ($this->$method($chunk));
	}
	$this->progress->spent += $this->_sync_average($start);
	$this->_finalize($next_step_cb);
	return true;
}


function _finalize($next_step_cb) {
	$next_step_cb($this);
//	if ($this->Finished()) // eventually free the associated session variable
}

}

?>