<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## CSS menu generator class
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# creates the CSS HTML fragment for the CSS menu
#

class CSSMenuNode extends MenuNode {

	function _recurse() {
		// output a splitting bar
		if ($this->is_splitter) {
			echo '<hr />'; return;
		}
		// add the icon
		if (isset($this->data[0]))
			$icon='<img border="0" align="absmiddle" src="'.$this->data[0].'" />&nbsp;';
		else $icon = '';
		// add the target (_blank etc.)
		if (isset($this->data[3]))
			$target = 'target="'.$this->data[3].'" ';
		else $target = '';
		if (strlen($this->data[2]))
			$desc = 'title="'.rawurlencode($this->data[2]).'" ';
		else $desc = '';
		// output the list item
		echo '<li>';
		if (strlen($this->data[1]))
			echo '<a '.$desc.'href="'.$this->data[1].'" '.$target.'>';
			echo $icon.$this->title;
		if (strlen($this->data[1]))
			echo '</a>';
			$this->_recurse_children();
		echo "</li>";
	}
	
	function _recurse_children($extra = '') {
		if (!count($this->children)) return;
		echo "<ul$extra>\n";
		$last = array_pop($this->children);
		foreach($this->children as $child) {
			$child->_recurse();
			echo "\n";
		}
		$last->_recurse();
		echo "</ul>\n";
	}

}

// for the javascript menu, this is the head node
class	CSSMenu extends CSSMenuNode {

	// sets the child node class
	function CSSMenu($cls) {
//		$this->title = '';
		$this->_clsname = $cls;
	}

	function generate() {
		ob_start();
//		echo '<div id="dka_menu">';
			$this->_recurse_children(' class="nav"');
//		echo '</div>';
		return ob_get_clean();
	}

}

?>