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
# expat binding
#

class AnyXML extends AnyXML_base {

	function AnyXML($assoc = false) {
	
		$this->{'@children'} = array();
	}
	
	function fromString($raw_xml) {
		$this->_parser = xml_parser_create('');

		xml_set_object($this->_parser, $this);
		xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, 0);

		xml_set_element_handler($this->_parser, '_startElementInit', '_endElement');
		xml_set_character_data_handler($this->_parser, '_characterData');
			
		$this->{'@slevel'} = array();
		
		//TODO: correct implementation of $assoc
		$assoc = false;
		$this->_parse_children($raw_xml, $assoc);
		
		return true;
	}

	
	function _handleError($code, $line, $col) {
		trigger_error('AnyXML parsing error at '.$line.':'.$col.'. Error '.$code.': '.xml_error_string($code));
	}

	function _parse_children($data, $assoc) {
		if (!xml_parse($this->_parser, $data)) {
			$this->_handleError(
				xml_get_error_code($this->_parser),
				xml_get_current_line_number($this->_parser),
				xml_get_current_column_number($this->_parser)
			);
			return false;
		}
		
		unset($this->{'@slevel'});
		
		xml_parser_free($this->_parser);
		unset($this->_parser);
	}

	function _startElementInit($parser, $name, $attrs = array()) {
		$this->_create($name, $attrs);
		$this->{'@slevel'}[] = array(&$this);

		xml_set_element_handler($parser, '_startElement', '_endElement');
	}
	
	function _startElement($parser, $name, $attrs = array())
	{
		$assoc = false;

		$level = count($this->{'@slevel'});

		$child = new AnyXML();
		$child->_create($name, $attrs);
		if (isset($this->{'@slevel'}[$level])) {
			array_pop($this->{'@slevel'}[$level]);
			$this->{'@slevel'}[$level][] =& $child;
		} else
			$this->{'@slevel'}[$level] = array(&$child);
		
		if ($assoc)
			//TODO: arrays!
			$par->$name =& $child;

		$par_arr =& $this->_stackLocation(-1);
		$sl =& $par_arr[count($par_arr)-1];
		
		$sl->{'@children'}[] =& $child;
		unset($child);
	}

	function _endElement($parser, $name) {
		$level = count($this->{'@slevel'})-1;
		array_pop($this->{'@slevel'}[$level]);
		if (!count($this->{'@slevel'}[$level]))
			unset($this->{'@slevel'}[$level]);
	}
	
	function &_stackLocation($delta = 0) {
		return $this->{'@slevel'}[count($this->{'@slevel'})-1 + $delta];
	}

	function _characterData($parser, $data)
	{
		$data = trim($data);
		if (!strlen($data))
			return;
		$par_arr =& $this->_stackLocation();
		$sl =& $par_arr[count($par_arr)-1];
		$sl->{'@children'}[] = $data;
	}

	function _create($name, $attrs) {
		$this->{'@name'} = $name;

		if (count($attrs)) {
			$a='@attributes';
			$this->$a = $attrs;
		}
	}

}

?>