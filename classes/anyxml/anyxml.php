<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## AnyXML class
# @author legolas558
# @license GPL
# @version 0.1
# @copyright (c) 2007 legolas558
# @website http://sourceforge.net/projects/anyxml
# 
# This class provides a common abstraction layer for
# all possible combinations of XML parsers
#
# main class
#

class AnyXML_base {

	## look for parallel brother nodes (expat and MiniXML)
	function &_getBrothers(&$children, &$bro, $i,$c) {
		$name = $bro->{'@name'};
		for(;$i<$c;$i++) {
			$child =& $children[$i];
			if (!is_object($child))
				continue;
			if ($child->{'@name'} == $name) {
				if (!is_array($bro)) {
					$ne =& $bro;
					unset($bro);
					$bro = array(&$ne, &$child);
					unset($ne);
				} else
					$bro[] = &$child;
			}
		}
		
		return $bro;	
	}

	function &getElementByPath($path)
	{
		$tmp	=& $this;
		$found = null;
		$parts	= explode('/', trim($path, '/'));
		
		$left = count($parts);
		foreach ($parts as $node) {
			$children =& $tmp->{'@children'};
			$c=count($children);
			for($i=0;$i<$c;++$i) {
				$child =& $children[$i];
				if (!is_object($child))
					continue;
				if ($child->{'@name'} == $node) {
					--$left;
					if (!$left) {
						$found =& $this->_getBrothers($children, $child, $i+1, $c);
						break 2;
					}
					$tmp =& $child;
					unset($child);
				}
			}
		}
		
		return $found;
	}

	## retrieve value of a specific node
	function getValueByPath($path) {
		$ele =& $this->getElementByPath($path);
		if (!isset($ele))
			return null;
		if (is_object($ele))
			return $ele->getValue();
		$value = null;
		foreach($ele as $elem) {
			if (is_object($elem)) {
				$val = $elem->getValue();
				if (isset($val))
					$value .= $val;
			} else
				$value .= $elem;
		}
		return $value;
	}

	## overriden by SimpleXML binding
	function &getAllChildren() {
		return $this->{'@children'};
	}
	
	## overriden by SimpleXML binding
	function getName() {
		return $this->{'@name'};
	}
	
	function _getValue() {
		$val = null;
		foreach($this->getAllChildren() as $child) {
			if (is_object($child)) {
				$add = $child->getValue();
				if (isset($add))
					$val.=$add;
			}
			$val .= $child;
		}
		return $val;
	}
	
	function getValue() {
		if (!isset($this->{'@value'}))
			$this->{'@value'} = $this->_getValue();
		return $this->{'@value'};
	}

	function attributes($attribute = null) {
		if(!isset($attribute))
			return isset($this->{'@attributes'}) ? $this->{'@attributes'} : array();
			
		return isset($this->{'@attributes'}[$attribute]) ? $this->{'@attributes'}[$attribute] : null;
	}

	## map a callback function to all children
	function map($callback, $args=array()) {
		$callback($this, $args);
		$children =& $this->getAllChildren();
		if ($n = count($children)) {
			for($i=0;$i<$n;$i++) {
				$children[$i]->map($callback, $args);
			}
		}
	}

	## default renderer for PHP5
	function __toString() {
		return (string)$this->getValue();
	}
	
}

// PHP5 >=5.1.3 SimpleXML
if (class_exists('SimpleXML')) {
	include $d_root.'classes/anyxml/simplexml.php';
	return;
}

//PHP4 with expat extension
if( function_exists('xml_parser_create')) {
	include $d_root.'classes/anyxml/expat_xml.php';
	return;
}

//PHP4 with...nothing! This is the poor man's solution, it works great but is slower than the previous
include $d_root.'classes/anyxml/minixml/minixml.inc.php';

include $d_root.'classes/anyxml/minixml.php';

?>