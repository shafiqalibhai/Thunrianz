<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
// see minixml-license.txt for information about this class
// htmlentities references have been replaced with xhtml_safe references

require_once(MINIXML_CLASSDIR . "/treecomp.inc.php");


class MiniXMLNode extends MiniXMLTreeComponent {
	
	
//	var $xtext;
//	var $xnumeric;

	/* MiniXMLNode [CONTENTS]
	** Constructor.  Creates a new MiniXMLNode object.
	**
	*/
	function MiniXMLNode ($value=NULL, $escapeEntities=NULL)
	{
		$this->MiniXMLTreeComponent();
//		$this->xtext = NULL;
//		$this->xnumeric = NULL;
		
		/* If we were passed a value, save it as the 
		** appropriate type
		*/
		if (isset($value))
		{
			if (is_numeric($value))
			{
				if (MINIXML_DEBUG > 0)
				{
					_MiniXMLLog("Setting numeric value of node to '$value'");
				}
			
				$this->xnumeric = $value;
			} else {
				if (MINIXML_IGNOREWHITESPACES > 0)
				{
					$value = trim($value);
					$value = rtrim($value);
				}
				
				if (isset($escapeEntities))
				{
					if ($escapeEntities)
					{
						$value = xhtml_safe($value);
					}
				} elseif (MINIXML_AUTOESCAPE_ENTITIES > 0) {
					$value = xhtml_safe($value);
				} 
				
				if (MINIXML_DEBUG > 0)
				{
					_MiniXMLLog("Setting text value of node to '$value'");
				}
				
				$this->xtext = $value;
			
				
			} /* end if value numeric */
			
		} /* end if value passed */
			
	} /* end MiniXMLNode constructor */
	
	/* getValue
	** 
	** Returns the text or numeric value of this Node.
	*/
	function getValue ()
	{
		$retStr = NULL;
		if (isset($this->xtext) )
		{
			$retStr = $this->xtext;
		} elseif (isset($this->xnumeric))
		{
			$retStr = "$this->xnumeric";
		}
		
		if (MINIXML_DEBUG > 0)
		{
			_MiniXMLLog("MiniXMLNode::getValue returning '$retStr'");
		}
		
		return $retStr;
	}
	
	
	function text ($setToPrimary = NULL, $setToAlternate=NULL)
	{
		$setTo = ($setToPrimary ? $setToPrimary : $setToAlternate);
		
		if (! is_null($setTo))
		{
			if (isset($this->xnumeric) ) 
			{
				return _MiniXMLError("MiniXMLNode::text() Can't set text for element with numeric set.");
				
			} elseif (! is_string($setTo) && ! is_numeric($setTo) ) {
			
				return _MiniXMLError("MiniXMLNode::text() Must pass a STRING value to set text for element ('$setTo').");
			}
			
			if (MINIXML_IGNOREWHITESPACES > 0)
			{
				$setTo = trim($setTo);
				$setTo = rtrim($setTo);
			}
			
			
			if (MINIXML_AUTOESCAPE_ENTITIES > 0)
			{
				$setTo = xhtml_safe($setTo);
			} 
			
			
			if (MINIXML_DEBUG > 0)
			{
				_MiniXMLLog("Setting text value of node to '$setTo'");
			}
			
			$this->xtext = $setTo;
			
		}
		
		return $this->xtext;
	}
	function numeric ($setToPrim = NULL, $setToAlt = NULL)
	{
		$setTo = is_null($setToPrim) ? $setToAlt : $setToPrim;
		
		if (! is_null($setTo))
		{
			if (isset($this->xtext)) {
			
				return _MiniXMLError("MiniXMLElement::numeric() Can't set numeric for element with text.");
			
			} elseif (! is_numeric($setTo))
			{
				return _MiniXMLError("MiniXMLElement::numeric() Must pass a NUMERIC value to set numeric for element.");
			}
			
			if (MINIXML_DEBUG > 0)
			{
				_MiniXMLLog("Setting numeric value of node to '$setTo'");
			}
			$this->xnumeric = $setTo;
		}
		
		return $this->xnumeric;
	}
	
	
	
	
	function toString ($depth=0)
	{
		if ($depth == MINIXML_NOWHITESPACES)
		{
			return $this->toStringNoWhiteSpaces();
		}
		
		if (MINIXML_DEBUG > 0)
		{
			_MiniXMLLog("MiniXMLNode::toString() call with depth $depth");
		}
		
		$spaces = $this->_spaceStr($depth);
		$retStr = $spaces;
		
		if (isset($this->xtext) )
		{
			/* a text element */
			$retStr .= $this->xtext;
		} elseif (isset($this->xnumeric)) {
			/* a numeric element */
			$retStr .=  $this->xnumeric;
		} 
		
		/* indent all parts of the string correctly */
		$retStr = preg_replace("/\n\s*/sm", "\n$spaces", $retStr);
		
		return $retStr;
	}
	
	
	function toStringWithWhiteSpaces ($depth=0)
	{
		return $this->toString($depth);
	}
	
	function toStringNoWhiteSpaces ()
	{
	
		if (MINIXML_DEBUG > 0)
		{
			_MiniXMLLog("MiniXMLNode::toStringNoWhiteSpaces() call with depth $depth");
		}
		
		if (isset($this->xtext) )
		{
			/* a text element */
			$retStr = $this->xtext;
		} elseif (isset($this->xnumeric)) {
			/* a numeric element */
			$retStr =  $this->xnumeric;
		}
		
		return $retStr;
	}
	
	
} /* end class definition */



?>