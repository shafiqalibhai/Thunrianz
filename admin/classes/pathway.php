<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

class AdminPathway extends PathwayAncestor {

	function AdminPathway() {
		$this->_base_url = 'admin.php';
		$this->_home = $this->_base_url;
	}
	
	function _auto_option_url() {
		$s = $this->_base_url.'?com_option='.
			rawurlencode($GLOBALS['com_option']);
		if (isset($GLOBALS['task']))
			$s .= '&task='.rawurlencode($GLOBALS['task']);
		return $s;
	}
	
	function Undefined() {
		CMSResponse::Unavailable('No pathway defined');
		return false;
	}

}

?>