<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Install class
# @author legolas558
#
# This file has been refactored from previous code.
# Not yet finished

include $d_root.'admin/classes/coreinstall.php';

class Install extends CoreInstall {

	function Install($type = false) {
		$this->type = $type;
		parent::CoreInstall();
	}

	// extract an archive and delete it
	function _extract_arch($src_file, $dest_dir = null, $can_delete = true) {
		global $d_root;
		if (!isset($dest_dir))
			$dest_dir = $this->temp_folder = $src_file.'_ext/';
		$ext = file_ext($src_file);
		switch ($ext) {
			case 'gz':
			case 'tar':
			case 'tgz':
				require_once $d_root.'admin/classes/pcl/pcltar.lib.php';
			PclTarExtract($src_file, $dest_dir);
				$pcl_success = (PclErrorCode()==1);
				if (!$pcl_success)
					$this->error_msg = 'TAR/GZ: '._INSTALL_INVALID_ARC." ".PclErrorString();
				break;
			default:	// fallback for URLs
				//TODO: disable this case
			case 'zip':
				require_once $d_root.'admin/classes/pcl/pclzip.lib.php';
				$zipfile = new PclZip($src_file);
				$zipfile->extract($dest_dir);
				$pcl_success = (PclErrorCode()==1);
				if (!$pcl_success) {
//					echo PclErrorString();
					if ($ext!='zip') {
						// also try with TAR/GZ
						require_once $d_root.'admin/classes/pcl/pcltar.lib.php';
							PclTarExtract($src_file, $dest_dir);
						$pcl_success = (PclErrorCode()==1);
					}
					if (!$pcl_success)
						$this->error_msg = strtoupper($ext).': '._INSTALL_INVALID_ARC." ".PclErrorString();
				}
				break;
			/*
				$pcl_success = false;
				$this->error_msg = 'Invalid extension!';
				break;
			*/
		}
		if ($can_delete)
		// remove the original archive (stored in sys temp dir)
			$this->fs->unlink($src_file);
//		if (!$pcl_success)
//			return false;
		
		// directory will not exist in case of extraction failure
		if (!$this->fs->dir_exists($dest_dir))
			return false;
//		die( $this->error_msg );

		$dest_dir = $this->from_dir($dest_dir);
		// Try to find the correct install directory in case that the package has only one subdirectory
		// Save the install dir for later cleanup
		$filesindir = read_dir($dest_dir);
		if (count($filesindir) == 1) {
			if (is_dir($dest_dir.$filesindir[0]))
				$this->from_dir($dest_dir.$filesindir[0]);
			else { // if it is not a directory, then it might be a zip file - check it
				if (file_ext($dest_dir.$filesindir[0])=='zip') {
					if (!$this->_extract_arch($dest_dir.$filesindir[0], $dest_dir))
						return false;
				}
			}
		}
	
		return true;
	}
	
	var $url = null;
	
	function GetPackageFile($flags, $container = null) {
		if ($flags & _UPL_URL) {
			$package_url = in_raw('package_url', $_POST);
			if (is_url($package_url)) {
				if (!isset($container)) {
					global $d_temp;
					$tmp_file = $d_temp.md5($package_url);
					// always re-download
					//L: deletion might not be necessary since rewrital is supposed to happen
					if (file_exists($tmp_file))
						$this->fs->remove($tmp_file);
					// attempt to use the attachment filename extension
					$remote_fname = get_attachment_filename($package_url);
					if (strlen($remote_fname))
						$tmp_file.='.'.file_ext($remote_fname);
				} else {
					$remote_fname = get_attachment_filename($package_url);
					if (!strlen($remote_fname))
						$remote_fname = md5($package_url);
					$tmp_file = $container.$remote_fname;
				}
				// download the file
				if (!get_url($package_url, $tmp_file)) {
					$this->error_msg = sprintf(_INSTALL_INVALID_URL, $package_url);
					return false;
				}
				$this->url = $package_url;
				return $tmp_file;
			}
		}
		if ($flags & _UPL_FILE) {
			$upload = in_upload('package_file', $container, isset($container) ? 0 : _DKUPL_RANDOM_PREFIX,
						array('zip','tar','gz','tgz'), !isset($container));
			if (is_array($upload))
				return $upload[0];
			$this->error_msg = $upload;
			return false;
		}
		return false;
	}
	
	function get_installer($flags, $tmp_file = null) {
		$can_delete = !isset($tmp_file);
		if ($can_delete)
			$tmp_file = $this->GetPackageFile($flags);
		if ($tmp_file===false) {
			if (strlen($this->error_msg))
				return false;
			// fallback if no error message was present
		} else
			return $this->_extract_arch($tmp_file, null, $can_delete);
		if ($flags & _UPL_DIR) {
			$package_dir = in_raw('package_dir', $_POST);
			// from local path, last chance
			if (!strlen($package_dir)) {
				$this->error_msg = _INSTALL_NO_SOURCE;
				return false;
			}
		}
		
		// $package_dir MUST NOT be absolute
		$this->from_dir($package_dir);
		return true;
	}

	function _do_install($sing, $plur, &$msg) {
		$collection = $this->xml->getElementByPath($plur);
		if (isset($collection)) {
			$collection = $collection->getAllChildren();
			if (!count($collection))
				return true;
			$mth = 'install_'.$sing;
			foreach ($collection as $child) {
				$msg.=$this->$mth($child);
				if ($this->error)
					return false;
			}
		} else
		return true;
	}
	
	function go($flags = _UPL_INSTALL) {
		// extract the archive contents
		if (!$this->get_installer($flags))
			return $this->error_msg;
		$r = $this->package_validate();
		if (is_string($r)) {
			$this->Cleanup();
			return $r;
		}
		// by reference
		$msg = '';
		
		if ($this->type===false) {
			$objects = array('admintemplate', 'template', 'language', 'module', 'drabot', 'component', 'patch');
//			$this->_do_install('class', 'classes', $msg);
			$this->_do_install('patch', 'patches', $msg);
/*		} else if ($this->type=='class') {
			$this->_do_install('class', 'classes', $msg);
			$this->Cleanup();
			return $msg; */
		} else if ($this->type=='patch') {
			$this->_do_install('patch', 'patches', $msg);
			$this->Cleanup();
			return $msg;
		} else
			$objects = array($this->type);
		foreach ($objects as $obj) {
			if (!$this->_do_install($obj, $obj.'s', $msg))
				break;
		}

		$this->Cleanup();
		if ($msg === '') return _INSTALL_NOTHING;
		return $msg;
	}
	
	function package_validate() {
		// check if failed at locating the proper package descriptor
		if (!$this->findInstallFile())
			return _INSTALL_NO_INSTALLER;
		// (5) check that the container tag is dkinstall
		if (!$this->isValid())
			return _INSTALL_INVALID;
		if (!version_compat($this->version))
			return sprintf(_INSTALL_INVALID_VERSION, $this->version);
		
		// (6) locate the addon in this package
		$children = $this->xml->getAllChildren();
		if (count($children) < 1)
			return "This package does not contain addons";
		$elem = $children[0]; $elem = $elem->getName();
		global $PACKAGE_TYPES;
		if (!isset($PACKAGE_TYPES[$elem]))
				return "Unknown elements family: ".$elem;
		$s_elem = $PACKAGE_TYPES[$elem];
		$obj = $this->xml->getElementByPath($elem);
		$children = $obj->getAllChildren();
		if (count($children) < 1)
			return "This package does not contain 1 children addon";
		$elem = $children[0]; $elem = $elem->getName();
		if ($elem !== $s_elem)
			return "The child addon does not match ($elem != $s_elem)";
		// (7) check that <files /> do exist
		$files = $obj->getElementByPath($elem.'/files');
		if (isset($files)) {
			$files = $files->getAllChildren();
			$not_existing = '';
			foreach($files as $file) {
				$file = $file->getValue();
				if (!file_exists($this->install_dir.$file))
					$not_existing .= $file."\n";
			}
			if (strlen($not_existing))
				return "Following files do not exist in package but are indexed in <files /> section:\n".$not_existing;
		}
		// (8) extract some informations from the XML package
		$p_un = $obj->getElementByPath($elem.'/id');
		$p_un = $p_un->getValue();
		if (unix_name($p_un) !== $p_un)
			return "Invalid UNIX name in <id />";
		$p_version = $obj->getElementByPath($elem.'/version');
		$p_version = $p_version->getValue();
		
		// check that the package does not already exist
		global $conn;
		$row = $conn->SelectRow('#__packages', 'id', ' WHERE name=\''.
				sql_encode($p_un).'\' AND version=\''.sql_encode($p_version).'\' AND type=\''.sql_encode($this->type).'\'');
		if (!empty($row))
			return "The same package (name,type,version) already exists";
		$p_name = $obj->getElementByPath($elem.'/name');
		$p_name = $p_name->getValue();
		
		$req_version = $obj->getElementByPath($elem.'/requirements/core/version');
		if (!isset($req_version))
			return "Package does not define a <requirements/core/version>";
		$req_version = $req_version->getValue();
		if (!preg_match('/^\\d+\\.\\d+\\.\\d+$/', $req_version))
			return "Package does not define a valid <requirements/core/version> (x.y.z)";
		$p_author = $obj->getElementByPath($elem.'/author');
		$p_author = $p_author->getValue();
		$cdate = $obj->getElementByPath($elem.'/creationDate');
		if (!isset($cdate))
			return "Package does not define a <creationDate/>";
		$cdate = $cdate->getValue();
		$cdate = package_datetime_enc($cdate);
		if (!isset($cdate))
			return "Package does not define a valid <creationDate/>\nFormat string is one of:\nYYYY-MM-DD\nYYYY-MM-DD hh:mm\nYYYY-MM-DD hh:mm:ss";
		$p_desc = $obj->getElementByPath($elem.'/description');
		if (isset($p_desc))
			$p_desc = $p_desc->getValue();
		else $p_desc = '';
		$p_license = $obj->getElementByPath($elem.'/license');
		if (!isset($p_license))
			return "No <license /> defined";
		$p_license = $p_license->getValue();
//		return array($elem, $p_name, $p_un, $p_version, $p_license, $p_desc,	$req_version,$cdate,$p_author);
		return true;
	}

	// copy files from an xml tree and eventually create the needed folders
	function copyxmlfiles($files, $src, $dest, $assert_paths = true) {
		if (!count($files)) return 0;
		$filescopied=0;
		// if we are in a temporary path, move the files, otherwise copy them (install from directory for example)
		if ($this->temp_folder!=='')
			$func = 'move';
		else $func = 'copy';
		$xfunc = 'x'.$func;
		foreach($files as $file) {
			$fname = $file->getValue();
			if (substr($fname, -1)=='/') {
				// assert check is done here because some paths may contain additional folders
				if ($assert_paths) {
					if (!$this->fs->assertdir($dest.$fname))
						return 0;
				}
				// this is a plain directory copy/move
				$filescopied += $this->fs->$xfunc($src.$fname, $dest.$fname, $assert_paths);
				continue;
			} else {
				// assert check is done here because some paths may contain additional folders
				if ($assert_paths) {
					if (!$this->fs->assertdir(dirname($dest.$fname).'/'))
						return 0;
				}
			}
			$filescopied += $this->fs->$func($src.$fname, $dest.$fname, $assert_paths);
		}
		return $filescopied;
    }
	
	function copyxml($dest_dir = '') {
		if (!strlen($dest_dir))
			$dest_dir = $this->install_to;
		else $this->fs->assertdir($dest_dir);
		return $this->fs->copy($this->install_dir.$this->installer_file, $dest_dir.$this->installer_file);
	}
	
	function _install_files(&$obj) {
		$files = $obj->getElementByPath('files');
		return ($this->copyxmlfiles($files->getAllChildren(),
					$this->install_dir, $this->install_to) +
					$this->copyxml());
	}

	//L: might not be supported at all in version 1.0
/*	function install_class(&$obj) {
		return 'NOT YET AVAILABLE';
		
		$msg = _INSTALL_FAILURE;
		// (1) requirements pre-check
		if (!$this->_requirements_ok($obj, $msg))
			return $msg;

		$this->install_to = $GLOBALS['d_root'];
		if ($this->_install_files($obj) > 0)
			return _INSTALL_SUCCESS;
		return $msg;
	} */
	
	function install_language(&$obj) {
		global $d_root;
		$lang_dir = $d_root.'lang/';
		$e = $obj->getElementByPath('id');
		$d_languageName = strtolower($e->getValue());
		$this->fs->mkdir($lang_dir.$d_languageName);
		$this->install_to = $lang_dir.$d_languageName.'/';
		
		if ($this->_install_files($obj) > 0)
			return _INSTALL_SUCCESS;
		return _INSTALL_FAILURE;
	}
	
	function _install_template(&$obj, $dir) {
		global $d_root;
		$e = $obj->getElementByPath('id');
		$d_templateName = $e->getValue();
		$this->install_to = $d_root.$dir.'templates/'.$d_templateName.'/';
		$this->fs->mkdir($this->install_to);
		if ($this->_install_files($obj) > 0)
			return _INSTALL_SUCCESS;
		return _INSTALL_FAILURE;
	}
    
	function install_admintemplate(&$obj) {
		return $this->_install_template($obj, 'admin/');
	}

	function install_template(&$obj) {
		return $this->_install_template($obj, '');
	}

	function install_module(&$obj) {
		global $d_root, $conn, $easydb;

		$msg = _INSTALL_FAILURE;
		// (1) requirements pre-check
		if (!$this->_requirements_ok($obj, $msg))
			return $msg;

		$e = $obj->getElementByPath('id');
		$mod_id = $e->getValue();
		if ((strpos($mod_id, 'mod_')!==0) || (strlen($mod_id)<5)) {
			$this->error = true;
			return _INSTALL_INVALID;
		}
		$e = $obj->getElementByPath('name');
		$moduleName = $e->getValue();
		$e = $obj->getElementByPath('position');
		if (!isset($e))
			$pos = 'left';
		else $pos = sql_encode($e->getValue());
		$this->install_to = $d_root.'modules/';
		if($conn->SelectRow('#__modules', '*', " WHERE module='$mod_id'")) {
			$this->error = true;
			return _INSTALL_FAILED_EXIST;
		}
		
		$order = $easydb->neworder("modules");
		$sql = "INSERT INTO #__modules (title,ordering,position,module,iscore) VALUES ('".
				sql_encode($moduleName). "',$order,'$pos','" . sql_encode($mod_id) . "',1)";
		$conn->Execute($sql);
		$this->_subsites_sync($sql);
		if ($this->_install_files($obj) == 0) {
			$this->error = true;
			return _INSTALL_FAILURE;
		}
		
		$this->_internationalize($obj);
		
		return _INSTALL_SUCCESS;
	}
	
	// copy the i18n files to the proper folders
	function _internationalize(&$obj) {
		global $d_root;
		// copy the internationalization files
		$e = $obj->getElementByPath('i18n');
		if (isset($e)) {
			$langs = $e->getAllChildren();
			foreach ($langs as $lang) {
				$dest = $d_root.'lang/'.$lang->getName().'/';
				// copy the i18n files even if the language is not installed
				if (!is_dir($dest)) continue;
				$files = $lang->getElementByPath('files');
				if (!isset($files)) continue;
				
				$this->copyxmlfiles($files->getAllChildren(),
									$this->install_dir.'lang/'.$lang->getName().'/',
									$dest);
			}
		}
	}

	function install_drabot(&$obj) {
		$msg = _INSTALL_FAILURE;
		// (1) requirements pre-check
		if (!$this->_requirements_ok($obj, $msg))
			return $msg;

		global $d_root, $conn, $easydb;
		$e = $obj->getElementByPath('name');
		$drabotName =  $e->getValue();
		$this->install_to = $d_root . "drabots/";
		$e = $obj->getElementByPath("group");
			$bot_type = $e->getValue();
		$b_name = $obj->getElementByPath('files/filename');
		$e = $obj->getElementByPath('id');
		$bot_id = $e->getValue();
		if /*((strpos($bot_id, 'dra')!==0) || */(strlen($bot_id)<4)/*)*/ {
			$this->error = true;
			return _INSTALL_INVALID;
		}
		if($conn->SelectRow('#__drabots', '*', ' WHERE element=\''.sql_encode($bot_id)."'")) {
			$this->error = true;
			return _INSTALL_FAILED_EXIST;
		}
		// queries
		$order = $easydb->neworder("drabots");
		$sql = "INSERT INTO #__drabots (name,type,element,ordering,iscore) VALUES ('".
			sql_encode($drabotName)."','".sql_encode($bot_type)."','".sql_encode($bot_id)."',$order,1)";
		$conn->Execute($sql);
		$this->_subsites_sync($sql);

		if ($this->_install_files($obj) > 0) {
			$this->_internationalize($obj);
			return _INSTALL_SUCCESS;
		}
		$this->error = true;
		return _INSTALL_FAILURE;
	}

	function install_component(&$obj) {
		$msg = _INSTALL_FAILURE;
		// (1) requirements pre-check
		if (!$this->_requirements_ok($obj, $msg))
			return $msg;
		global $d_root, $conn, $easydb;
		$e = $obj->getElementByPath('id');
		$com_name = $e->getValue();
		if ((strpos($com_name, 'com_')!==0) || (strlen($com_name)<5)) {
			$this->error = true;
			return _INSTALL_INVALID;
		}
		$com_name = substr($com_name, 4);
		$this->componentDir = $d_root . "components/" .$com_name.'/';
		$this->componentAdminDir = $d_root . "admin/components/" .$com_name.'/';

        // userfiles
		$files = $obj->getElementByPath('files');
		$front_end=$this->copyxmlfiles($files->getAllChildren(), $this->install_dir, $this->componentDir);

	    // admin files
		$files = $obj->getElementByPath('administration/files');
		if (isset($files))
			$files = $files->getAllChildren();
		else $files = array();
		$back_end = $this->copyxmlfiles($files, $this->install_dir, $this->componentAdminDir);

		// copy the install/uninstall files (if present) into the admin backend
		$installfile = $obj->getElementByPath('installfile');
		if (isset($installfile)) {
				$installfile = $installfile->getValue();
				$this->fs->assertdir($this->componentAdminDir);
		    $this->fs->copy($this->install_dir . $installfile, $this->componentAdminDir.$installfile);
		}
		$e = $obj->getElementByPath('uninstallfile');
		if (isset($e)) {
			$this->fs->assertdir($this->componentAdminDir);
			$this->fs->copy($this->install_dir . $e->getValue(), $this->componentAdminDir.$e->getValue());
		}

		// queries
		$this->queries($obj, 'install/queries');
		// menus
		$front_link="";
		if($front_end)$front_link='option='.$com_name;
		$back_link="";
		if($back_end)$back_link='com_option='.$com_name;
		
		$adminmenu_element = $obj->getElementByPath('administration/access');
		if (isset($adminmenu_element))
			$com_admin_access = (int) $adminmenu_element->getValue();
		else
			$com_admin_access = 5;
		$adminmenu_element = $obj->getElementByPath('administration/menu');
			
		if (isset($adminmenu_element)) {
			$adminsubmenu_element = $obj->getElementByPath('administration/submenu');
			$com_admin_menuname = $adminmenu_element->getValue();
		} else {
	//		if ($back_end) {// default menu name is the component's name
				$e = $obj->getElementByPath('name');
				$com_admin_menuname = $e->getValue();
	//		} else	$com_admin_menuname = '';
		}
			
		if (isset($adminsubmenu_element)) {
			//TODO: fixme
			// explicit SQL for subsites insertion
			$sql = "INSERT INTO #__components (name,link,admin_menu_link,admin_menu_alt,option_link,admin_access) VALUES ('$com_admin_menuname','$front_link','$back_link','$com_admin_menuname','com_$com_name',$com_admin_access)";
			$conn->Execute($sql);
			$com_admin_menu_id = $conn->Insert_ID();
			// synchronize subsites
			$this->_subsites_sync($sql);
			$com_admin_submenus = $adminsubmenu_element->getAllChildren();

			$submenuordering = 0;
			foreach($com_admin_submenus as $admin_submenu) {
				if ($admin_submenu->attribute("option"))
					$com_admin_menu_link = "com_option=$com_name&option=".
						$admin_submenu->attribute("option");
				else if ($admin_submenu->attribute("link"))
					$com_admin_menu_link = $admin_submenu->attribute("link");
				else
					$com_admin_menu_link = "com_option=$com_name";
				$sql = "INSERT INTO #__components (name,parent,admin_menu_link,admin_menu_alt,option_link)
					VALUES ('" . $admin_submenu->getValue() . "',$com_admin_menu_id,'$com_admin_menu_link','" . $admin_submenu->getValue() . "','com_$com_name')";
				$conn->Execute($sql);
				$this->_subsites_sync($sql);
			}
		} else {
			$sql = "INSERT INTO #__components (name,link,admin_menu_link,admin_menu_alt,option_link,admin_access)
					VALUES ('$com_admin_menuname','$front_link','$back_link','$com_admin_menuname','com_$com_name',$com_admin_access)";
		}
			
		$conn->Execute($sql);
		$this->_subsites_sync($sql);
		// copy the XML descriptor (with parameter declarations) into the frontend component dir
		$this->copyxml($this->componentDir);
		
		$ver = $obj->getElementByPath('version');
		$ver = $ver->getValue();
		
		$sql = 'INSERT INTO #__packages (type, name, version) VALUES(\'component\', \''.$com_name.'\', \''.$ver.'\')';
		$conn->Execute($sql);
		$this->_subsites_sync($sql);

		if (isset($installfile)) {
				$success = include($this->install_dir.$installfile);
				if (!$success)
					return _INSTALL_FAILURE;
		}
		
		$this->_internationalize($obj);
		
		$aobj = $obj->getElementByPath('administration');
		if (isset($aobj))
			$this->_internationalize($aobj);
	
		return _INSTALL_SUCCESS;
	}
	
	function _recurse_path($entry, $l) {
		// if an array was passed, recurse into each entry
		if (is_array($entry)) {
			foreach($entry as $ss) {
				if (!$this->_recurse_path($ss, $l))
					return false;
			}
			// if all recursions were successful, return true
			return true;
		}
		// if this is a directory
		if (substr($entry, -1)=='/')
			// get the recursive tree of everything under it
			return $this->_recurse_path(raw_read_dir($entry, false, false, _RRD_RECURSE), $l);
		// check if this file has a valid path
		global $d_root;
		if (!$this->fs->assertpath(substr($entry, $l), true))
			return false;
		return true;
	}
	
	function _requirements_ok(&$obj, &$msg) {
		$requirements = $obj->getElementByPath('requirements');
		if (!$requirements) {
			$msg = 'No requirements element found';
			return false;
		}
		$core_req = $requirements->getElementByPath('core');
		if ($core_req) {
			//TODO: implement 'comparation' attribute
			$req_version = $core_req->getElementByPath('version');
			if ($req_version) {
				if (!version_compat($req_version->getValue())) {
					$msg = _INSTALL_VERSION_NCOMPAT;
					return false;
				}
			}
			$req_revision = $core_req->getElementByPath('revision');
			if (isset($req_revision)) {
				if ($req_revision->getValue() > $GLOBALS['d__revision']) {
					$msg = _INSTALL_VERSION_TOO_LOW;
					return false;
				}
			}
		}
		// no requirements to statisfy or requirements satistfied
		return true;		
	}

	function install_patch(&$obj) {
		global $d_root, $conn;
		// get the custom patch script element
		$installfile_element = $obj->getElementByPath('installfile');

		$msg = _INSTALL_FAILURE;

		// (1) requirements pre-check
		if (!$this->_requirements_ok($obj, $msg))
			return $msg;
		
		// (2) file permissions pre-check on destinations of binaries
		// during this step the whole directory structure will be created
		$binaries = $obj->getElementByPath('binaries');
		if (isset($binaries)) {
			$binaries = $binaries->getAllChildren();
			foreach($binaries as $entry) {
				$entry = $entry->getValue();
				if (!$this->_recurse_path($this->install_dir.$entry, strlen($this->install_dir)))
					return _INSTALL_FAILURE_PATHS;
			}
		}
		
		$files = $obj->getElementByPath('files');
		if (isset($files)) {
			$files = $files->getAllChildren();
			if (count($files))
				return 'Patches are not supported';
		}

		// (4) before making actual changes the install script is executed
		// note that the install script must still rely on the previous CMS' functions
		// at max it can use some of the new binaries under the install_dir
		if (isset($installfile_element)) {
			$install_dir = $this->install_dir;
			$msg = "";
			//L: install scripts must return true (or a true expression) on success
			$success = include($install_dir.$installfile_element->getValue());
			
			if (!$success)
				return _INSTALL_FAILURE.$msg;
			$msg = _INSTALL_SUCCESS.$msg;
		}

		// (6) the new files are entirely copied
		// note that file overwriting is not the safe way to perform patches!
		// binaries should be used only for new files
		// directories will be created when necessary
		if (isset($binaries)) {
			if ($this->copyxmlfiles($binaries, $this->install_dir, $d_root, false) > 0)
				$msg = _INSTALL_SUCCESS;
		}
		return $msg;
	}

	//TODO: change name of this function!
	function install_interface($form, $title, $web_path) {
		global $pathway;
		$pathway->add($title);
		
		$gui = new ScriptedUI();
		$gui->add("form", "adminform", "", $form);
		$gui->add("spacer");
		$gui->add("com_header", $title);

		$gui->add('text','',_INSTALL_FILE_DESC);
		$gui->add('spacer');
		
		if (strlen($web_path)>8)
			$dtab=2;
		else $dtab=1;
	
		$s = _SUPPORTED_FORMATS.': ZIP/TAR/GZ/TGZ';
		$ext = array('zip', 'tar', 'gz', 'tgz');
		$gui->dtabs_interface(_UPL_INSTALL,array(&$s, &$s, _INSTALL_DIR_DESC),
							$ext, 'file', $web_path,'',$dtab);

		$gui->add("end_form");
		$gui->generate();
	}

}

?>