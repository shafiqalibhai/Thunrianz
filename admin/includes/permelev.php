<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Permission elevator
# @author legolas558
#
# permission elevation functions for Lanius CMS

function find_wf($root) {
	$dh=opendir($root);
	while (false !== ($dir = readdir($dh))) {
		if (!is_dir($dir)) continue;
		if ($dir[0]=='.') continue;
		if (is__writable($root.$dir.'/'))
			return $dir.'/';
	}
	return false;
}

/*
function mvdir($source, $dest) {

   $dh = opendir($source);
   while (($fname = readdir($dh)) !== false) {
       if ($fname[0] == '.') continue;
          
		$path_a = $source.$fname;
		$path_b = $dest.$fname;

		if (is_dir($path_a)) {
			if (!@mkdir($path_b.'/'))
				return false;
			if (!mvdir($path_a.'/', $path_b.'/'))
				return false;
			rmdir($path_a);
		} else {
			if (!@copy($path_a, $path_b))
				return false;
			if (!@chmod($path_b))	// ?
				return false;
			if (!unlink($path_a))
				return false;              
           }
   }
  
   return true;
  
}
*/

function perm_elev($path, $elevator) {
	/*
	if ($path{strlen($path)-1}=='/') {
		$tmpfolder = random_string(6).'-tmp';
		if (!mkdir($elevator.$tmpfolder))
			return false;
		if (!mvdir($path, $elevator.$tmpfolder))
			return false;
		if (!rename($elevator.$tmpfolder, $path))	// the magic is here
			return false;
		return true;
	}
	*/

	include $d_root.'admin/classes/fs.php';
	$fs = new FS(true);

	$tmpfile = random_string(6).'.tmp';
	if (!$fs->copy($path, $elevator.$tmpfile))
		return false;
	if (!$fs->unlink($path))
		return false;

	if (!$fs->move($elevator.$tmpfile, $path))	// the magic is here
		return false;
}

?>