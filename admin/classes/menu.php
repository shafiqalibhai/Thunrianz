<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Menu generator class
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# archetype for admin backend menu generation
#

class MenuNode {
	
	var $title;
	var $data; // (0 - icon, 1- link, 2- desc, 3 - target)
	var $children = array();
	var $is_splitter = false;
	var $_cls_name;	// the name of the node class, set in the head node
	
	function MenuNode($title, &$data) {
		$this->data =& $data;
		$this->title = $title;
		$this->_clsname = get_class($this);
	}
	
	function add_split() {
		$child =& $this->iadd('');
		$child->is_splitter = true;		
	}

	// this is the template function, copy/paste it in the cloned classes
	function &iadd($title, $icon=null,$link='', $desc='', $target = null) {
		$data = func_get_args();
		array_shift($data);
		$data = array_pad($data, 3, '');
		$cls = $this->_clsname;
		$child = new $cls($title, $data);
		$this->children[] =& $child;
		return $child;
	}

}

?>