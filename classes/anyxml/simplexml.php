<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## AnyXML class
# @author legolas558
# @license GPL
# @copyright (c) 2007 legolas558
# @website http://sourceforge.net/projects/anyxml
# 
# This class provides a common abstraction layer for
# all possible combinations of XML parsers
#
# SimpleXML binding
#

class AnyXML extends AnyXML_base {

	function AnyXML($assoc = true) {
	}
	
	function fromString($raw_xml) {
		try {
			$this->{'@xml'} = new SimpleXMLElement($raw_xml);
		}
		catch (Exception $e) { return false; }
		return true;
	}

	function &_makeAllObj(&$arr) {
		$all = array();
		foreach($arr as $obj) {
			$all[] = $this->_makeObj($obj);
		}
		return $all;
	}
	
	function &_makeObj(&$obj) {
		$nc = new AnyXML();
		$nc->{'@xml'} = $obj;
		return $nc;
	}
	
	function &getElementByPath($path) {
		$xml =& $this->{'@xml'};
		$path = trim($path, '/');
		if (!strpos($path, ':')) {
			$obj = $xml->xpath($path);
			if ($obj===false) {
				$n = null;
				return $n;
			}
			if (is_array($obj)) {
				$c=count($obj);
				if ($c==1)
					return $this->_makeObj($obj[0]);
				if ($c==0) {
					$n = null;
					return $n;
				}
			}
			return $this->_makeAllObj($obj);
		}
		// alternative way - to be tested for speed
		$parts	= explode('/', $path);
		$tmp =& $this;
		$found = false;
		foreach ($parts as $node) {
			$found = false;
			if (isset($tmp->$node)) {
				$tmp =& $tmp->$node;
				$found = true;
			} else
				break;
		}

		if ($found) {
			$ref =& $tmp;
		} else {
			$n = null;
			$ref =& $n;
		}
		return $ref;
	}
	
	function &getAllChildren() {
		if (isset($this->{'@children'}))
			return $this->{'@children'};
		$xml =& $this->{'@xml'};
		$childs = array();
		foreach ($xml as $name => $child) {
			$nc = new AnyXML();
			$nc->{'@xml'} =& $child;
			$childs[] =& $nc;
			unset($nc);
			unset($child);
		}
		$d = trim($xml);
		if (strlen($d)) {
			if (count($childs)) {
				if ($d!=(string)$childs[0])
					$childs[] = $d;
			} else
				$childs[] = $d;
		}
		
		$this->{'@children'} =& $childs;
		return $childs;
	}
	
	function attributes($attrib = null) {
		if (!isset($this->{'@attributes'})) {
			$attrs = array();
			foreach($this->{'@xml'}->attributes() as $name => $attr) {
				$attrs[$name] = (string)$attr;
			}
			$this->{'@attributes'} = $attrs;
		}
		return parent::attributes($attrib);
	}
	
	function getName() {
		return $this->{'@xml'}->getName();
	}

	function getValue() {
		return (string)$this->{'@xml'};
	}


}

?>