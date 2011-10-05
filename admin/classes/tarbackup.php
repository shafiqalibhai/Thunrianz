<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
/* (C) 2006 by legolas558
	TarBackup class v0.2
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

include_once $d_root.'admin/classes/pcl/pcltar.lib.php';

include $d_root.'admin/classes/tarbackupskel.php';

class TarBackup extends TarBackupSkel {

function GetTarballName() {
	return $this->progress->tarname;
}

function AddFile($fn) {
	return $this->_addFile($fn, filesize($this->progress->root.$fn));
}

# How update works

# the list of files present into the Tar archive is retrieved
# if filename is of a directory and is not in the exclude list, it is added to the main list of updateable files
# files in an excluded directory are deleted (and the dellist is kept)
# otherwise if filename does not belong to an excluded path, add it

function BeginUpdate($root, $p_tarname, $exclude_list = array()) {
	$er = error_reporting(-1 ^ E_NOTICE);
	$list = PclTarList($p_tarname);

	$this->_init($root, $p_tarname);

	$rv = true;
	// apply the exclude list
	$this->dellist = array();
	foreach ($list as $fn) {
		// in case of a directory
		if (substr($fn['filename'], -1)=='/') {
			if (!in_array($fn['filename'], $exclude_list)) {
				if (is_dir($this->progress->root.$fn['filename']))
					$this->_recurseDir($fn['filename'], $exclude_list);
				else
					// remove no more existing directory
					$this->dellist[] = $fn['filename'];
			}
		} else {
			if (!path_matches($exclude_list, $fn['filename'])) {
				if (is_file($this->progress->root.$fn['filename']))
					$rv |= $this->_addFile($fn['filename'], $fn['size']);
				else
					// remove no more existing file
					$this->dellist[] = $fn['filename'];
			} else
				$this->dellist[] = $fn['filename'];
		}
	}
	if (!empty($this->dellist)) {
		$er = error_reporting(-1 ^ E_NOTICE);
			PclTarDelete($this->progress->tarname, $this->dellist);
		error_reporting($er);
	}
	return $rv;
}

// mandatory: directory $dir must have a trailing slash
function _mydirname($dir) {
	return substr($dir,0,strlen($dir)-1);
}

function BeginCreation($root, $p_tarname = null, $exclude_list = array()) {

	if ($p_tarname == null)
		$p_tarname = $this->_mydirname($root).'.tgz';

	$this->_init($root, $p_tarname);

	$this->_recurseDir('', $exclude_list);	// start recursion from $root

	PclTarCreate($p_tarname);
}

	function _recurseDir($folder, &$exclude_list) {
		$dh = lcms_opendir($this->progress->root.$folder);
		$fc = 0;
		while (false !== ($file = readdir($dh))) {
			if ($file[0]=='.') continue;
			if (is_dir($this->progress->root.$folder.$file)) {
				if (in_array($folder.$file.'/', $exclude_list))
					continue;
				$fc += $this->_recurseDir($folder.$file.'/', $exclude_list);
			} else {
				$fc++;
				$this->AddFile($folder.$file);
			}
		}
		closedir($dh);
		if ($fc==0)	{ // add empty folders
			$this->progress->fname[] = $folder;
			$this->progress->fsize[] = 0;
			$fc++;
		}
		return $fc;
	}

function UpdateStep($max_bytes = __TBK_CHUNK) {
	return $this->_pclexec('PclTarUpdate', $max_bytes, '','', $this->progress->root);
}

function AddStep($max_bytes = __TBK_CHUNK) {
	return $this->_pclexec('PclTarAddList', $max_bytes,	'',	$this->progress->root);
}

function CreateBackup($next_step_cb, $max_time = 0) {
	return $this->_loop($next_step_cb, $max_time, 'AddStep');
}

function PerformUpdate($next_step_cb, $max_time = 0) {
	return $this->_loop($next_step_cb, $max_time, 'UpdateStep');
}

}

function array_walk_cond($arr, $funcname, $match) {
	$c=count($arr);
	for($i=0;$i<$c;$i++) {
		if ($funcname($arr[$i], $match))
			return true;
	}
	return false;
}

function _match_ab($a, $b) {
	return (strpos($a, $b)===0);
}

function _match_ba($b, $a) {
	return (strpos($a, $b)===0);
}

function path_matches($paths, $filename) {
	return array_walk_cond($paths, '_match_ab', $filename);
}

function rev_path_matches($paths, $filename) {
	return array_walk_cond($paths, '_match_ba', $filename);
}


?>