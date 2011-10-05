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
# MiniXML binding
#

class AnyXML extends AnyXML_base {

	function AnyXML($assoc=false) {
	}
	
	function fromString($raw_xml) {
		$xml = new MiniXMLDoc();
		$xml->fromString($raw_xml);

		$doc =& $xml->getRoot();
		if ($doc->xnumElementChildren!=1 || $doc->xnumChildren!=1)
			trigger_error('ANYXML unhandled exception #001');
			
		$xml = $doc->xchildren[0]; unset($doc);
		
		$this->_parse_children($xml,$assoc);
		
		return true;
	}
	
	function _parse_children(&$xml,$assoc) {
		$this->{'@name'} = $xml->xname;
		if (count($xml->xattributes))
			$this->{'@attributes'} = $xml->xattributes;
		
		$nchilds = array();
		foreach ($xml->xchildren as $child) {
			if (get_class($child)=='MiniXMLNode') {
				$ne = $child->getValue();
				$nchilds[] = $ne;
			} else {
				// create subelement
				$ne = new AnyXML();
				$ne->_parse_children($child, $assoc);
				if ($assoc) {
					//TODO: create associative members
				}
				$nchilds[] =& $ne;
				unset($ne);
			}
		}
		
		$this->{'@children'} =& $nchilds;
	}
	
	function &getAllChildren() {
		return $this->{'@children'};
	}
	
}

?>