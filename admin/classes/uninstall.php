<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include $d_root.'admin/classes/coreinstall.php';

class UnInstall extends CoreInstall {
	var $uninstall_string = null;

	function UnInstall($type, $fname = null) {
		parent::CoreInstall();
		$this->type = $type;
		$this->uninstall_string = $fname;
	}

	function name($fname) {
		$this->uninstall_string = $fname;
	}

	function go(){
		$mth = 'uninstall_'.$this->type;
		return $this->$mth();
	}
	
	function uninstall_module() {
		return 'NOT YET AVAILABLE';
		global $d_root, $conn;
		if (!$this->fs->unlink($d_root . "modules/" . $this->uninstall_string . ".php"))
			return _UNINSTALL_FAILED;
		$dir=read_dir($d_root . "modules/",'file');
		foreach($dir as $file){
			if(strstr($file,$this->uninstall_string)) {
				$this->fs->unlink($d_root.'modules/'.$file);
			}
		}
		$sql = 'DELETE FROM #__modules WHERE module=\''.$this->uninstall_string.'\'';
		$conn->Execute($sql);
		$this->_subsites_sync($sql);
		return _UNINSTALL_SUCCESS;
    }

	function uninstall_drabot() {
		return 'NOT YET AVAILABLE';
		global $d_root, $conn;
		if (!$this->fs->unlink($d_root . "drabots/" . $this->uninstall_string . ".php"))
				return _UNINSTALL_FAILED;
			//check for other similar files ( delete them 2 )
			$files = safe_glob($d_root.'drabots/'.$this->uninstall_string.'*.*');
			foreach($files as $file) {
				if (!$this->fs->unlink($file))
					return _UNINSTALL_FAILED;
			}
		    $sql = 'DELETE FROM #__drabots WHERE element=\''.$this->uninstall_string.'\'';
			$conn->Execute($sql);
			$this->_subsites_sync($sql);
			if (!$this->fs->unlink($d_root . "drabots/" . $this->uninstall_string . ".xml"))
				return _UNINSTALL_FAILED;
			return _UNINSTALL_SUCCESS;
	}

	function uninstall_template() {
		global $d_root;
		if ($this->fs->deldir($d_root . "templates/" . $this->uninstall_string . "/")) return _UNINSTALL_SUCCESS;
		else return _UNINSTALL_FAILED;
	}
	
	function uninstall_language() {
		global $d_root;
		if ($this->fs->deldir($d_root . "lang/" . $this->uninstall_string . "/"))return _UNINSTALL_SUCCESS;
		else return _UNINSTALL_FAILED;
	}

	function uninstall_component() {
		return 'NOT YET AVAILABLE';
		global $d_root, $conn, $d_private;
		// individuate the XML descriptor
		$uninstall_dir = $d_root.'components/'.$this->uninstall_string.'/';
		$this->from_dir($uninstall_dir);
		if (!$this->findInstallFile())
			return _INSTALL_INVALID;
		$uninstallfile_element = $this->xml->getElementByPath('uninstallfile');
		
		if (isset($uninstallfile_element)) {
			// remove the file for the custom uninstall file before putting in the new one
			//FIXME: should use temporary directory instead
			if (is_file($d_root . $d_private . $uninstallfile_element->getValue())) {
				$this->fs->unlink($d_root . $d_private . $uninstallfile_element->getValue());
			}
			copy($uninstall_dir . $uninstallfile_element->getValue(), $d_root . $d_private . $uninstallfile_element->getValue());
		}
		$this->queries($this->xml, 'uninstall/queries');
		$this->fs->deldir($uninstall_dir);
		$row = $conn->SelectRow('#__components', 'admin_menu_link', ' WHERE option_link=\'com_'.
				$this->uninstall_string.'\'');
		if (strlen($row['admin_menu_link']))
			$this->fs->deldir($d_root . "admin/components/" . $this->uninstall_string . "/");

		$module_element = $this->xml->getElementByPath('modules');
	
	if (isset($module_element)) {
            $modules = $module_element->getAllChildren();
            foreach($modules as $module) {
				$muninstall=new UnInstall("module");
				$muninstall->name($module->getValue());
				$muninstall->go();
            }
   	}

	$drabot_element = $this->xml->getElementByPath('drabots');
        if (isset($drabot_element)) {
			$drabots = $drabot_element->getAllChildren();
            foreach($drabots as $drabot) {
				$muninstall=new UnInstall("drabot");
				$muninstall->name($drabot->getValue());
				$muninstall->go();
            }
   	    }

	$sql = "DELETE FROM #__components WHERE option_link='com_" . $this->uninstall_string . "'";
        $conn->Execute($sql);
	$this->_subsites_sync($sql);

	$sql = "DELETE FROM #__packages WHERE type='component' AND name='".$this->uninstall_string . "'";
        $conn->Execute($sql);
	$this->_subsites_sync($sql);

	//FIXME!! bloody hackish!!
        if (isset($uninstallfile_element)) {
		return '&uninstall_file=' . $d_root . $d_private . $uninstallfile_element->getValue();
        }
        return _UNINSTALL_SUCCESS;
    }
}

?>