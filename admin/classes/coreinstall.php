<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include $d_root.'admin/classes/upload.php';

include_once $d_root.'includes/safe_glob.php';

global $PACKAGE_TYPES;

$PACKAGE_TYPES = array('components' => 'component', 'modules' => 'module', 'drabots' => 'drabot',
					'patches' => 'patch', 'languages' => 'language', 'templates' => 'template');

// from Gladius DB - gladius_datetime_enc()
function package_datetime_enc($data) {
	if (!preg_match('/(\\d\\d\\d\\d)-(\\d\\d)-(\\d\\d)(\\s+(\\d\\d):(\\d\\d)(:(\\d\\d))?)?\\s*$/A',
					$data, $m))
		return null;
	if (isset($m[5]))
		$hour = (int)$m[5];
	else $hour = 0;
	if (isset($m[6]))
		$minute = (int)$m[6];
	else $minute = 0;
	if (isset($m[8]))
		$second = (int)$m[8];
	else $second = 0;
	return gmmktime($hour, $minute, $second, (int)@$m[2], (int)@$m[3], (int)@$m[1]);
}

class CoreInstall {
	var $xml = false; //object
	var $type = false;
	var $installer_file;
	var $install_dir = false;
	var $install_to = false;
	var $subsites = null;
	var $error = false;
	var $error_msg = _FORM_NC;	//TODO: it is not effective
	var $temp_folder = '';
	var $version = '';
	var $fs;
	
	function CoreInstall() {
		global $d_root;
		include_once $d_root.'admin/classes/fs.php';

		$this->fs = new FS(); 
	}

	function findInstallFile() {
		// Try to find the package XML file
		$files = safe_glob($this->install_dir.'*.xml');
		if (!count($files))
			return false;
		foreach($files as $file) {
			$pkg =& $this->isPackageFile(substr($file, strlen($this->install_dir)));
                        if ($pkg->getName() != 'dkinstall')
                                continue;
			if (isset($pkg)) {
				$this->xml =& $pkg;
				return true;
			}
		}
		return false;
	}

	function &isPackageFile($p_file) {
		$xmlDoc = new AnyXML();
		if (!@$xmlDoc->fromString(file_get_contents($this->install_dir.$p_file)))
			$xmlDoc = null;
		else
			$this->installer_file = $p_file;
		$p =& $xmlDoc;
		return $p;
	}
	
	function isValid() {
		if ($this->xml->getName() != 'dkinstall')
			return false;
		$this->version = $this->xml->attributes('version');
		return true;
	}

	function queries(&$obj, $path) {
	    global $conn;
	    $query_element = $obj->getElementByPath($path);
	    if (isset($query_element)) {
	        $queries = $query_element->getAllChildren();
			if (!count($queries))
				return;
	        foreach($queries as $query) {
	            if (!@$conn->Execute($query->getValue()))
					echo '<p>'.$conn->ErrorMsg().'</p>';
	        }
	    }
	}
	
	function Cleanup() {
		// provide cleanup of temporary folders
		if ($this->temp_folder!=='') {
			$this->fs->deldir($this->temp_folder);
			$this->temp_folder = '';
		}
	}

	function from_dir($dir) {
		if ($dir[strlen($dir)-1]!='/')
			$dir.='/';
		$this->install_dir = $dir;
		return $dir;
	}
	
	var $_csc = array();
	
	function _subsite_exec($subpath, $sql) {
		if (!isset($this->_csc[$subpath])) {
			global $d_root, $d,  $d_private;
			$S = new CMSConfig($d_root.$subpath.$d_private);
			$S->name = substr($subpath, 0, -1);
			$s_db = $S->getVar('d_db');
			$this->_csc[$subpath] = new DbFork($s_db);
			$newconn =& $this->_csc[$subpath];
			$newconn->SubInitialize($S->name,
						$S->getVar('d_uid'),
						$S->getVar('d_dbhost'),
						$S->getVar('d_dbusername'), $S->getVar('d_dbpassword'),
						$S->getVar('d_dbname'), $S->getVar('d_prefix'));
		} else
			$newconn =& $this->_csc[$subpath];
		$newconn->Execute($sql);
	}
	
	function _subsites_sync(&$sql) {
		global $conn;
		if (!isset($this->subsites))
			$this->subsites = $conn->SelectColumn('#__subsites', 'subpath');
		if (!count($this->subsites))
			return;
		global $d_root;
		foreach ($this->subsites as $subpath) {
			$this->_subsite_exec($subpath, $sql);
		}
	}

}
?>
