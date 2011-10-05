<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Javascript menu generator class
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# creates the javascript array for all menu items
#

class JSMenuNode extends MenuNode {

	function _recurse() {
		// output a splitting bar
		if ($this->is_splitter) {
			echo "_cmSplit"; return;
		}
		// add the icon
		if (isset($this->data[0]))
			$icon='\'<img align="absmiddle" src="'.$this->data[0].'" alt="" />&nbsp;\'';
		else $icon = 'null';
		// add the target (_blank etc.)
		if (isset($this->data[3]))
			$target = "'".$this->data[3]."'";
		else $target = 'null';
		// output the array
		echo "[$icon,'".js_enc($this->title)."','".$this->data[1]."',$target,'".js_enc($this->data[2])."'";
		// recurse into all children
			$this->_recurse_children();
		echo "]";
	}
	
	function _recurse_children() {
		if (!count($this->children)) return;
		echo ", ";
		$last = array_pop($this->children);
		foreach($this->children as $child) {
			$child->_recurse();
			echo ",\n";
		}
		$last->_recurse();
	}

}

// for the javascript menu, this is the head node
class	JSMenu extends JSMenuNode {

	// sets the child node class
	function JSMenu($cls) {
//		$this->title = '';
		$this->_clsname = $cls;
	}

	function generate() {
		ob_start();
			echo "[";
			$this->_recurse_children();
			echo "];";
		return ob_get_clean();
	}

}

?>