<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
// see minixml-license.txt for information about this class

require_once(MINIXML_CLASSDIR . "/treecomp.inc.php");
require_once(MINIXML_CLASSDIR . "/node.inc.php");

class MiniXMLElement extends MiniXMLTreeComponent {
	
	
	var $xname;
	var $xattributes;
	var $xnumChildren;
	var $xnumElementChildren;
	var $xchildren;

	var $xavoidLoops = MINIXML_AVOIDLOOPS;
	function MiniXMLElement ($name=NULL)
	{
		$this->MiniXMLTreeComponent();
		$this->xname = NULL;
		$this->xattributes = array();
		$this->xchildren = array();
		$this->xnumChildren = 0;
		$this->xnumElementChildren = 0;
		if ($name)
		{
			$this->name($name);
		} else {
			return _MiniXMLError("MiniXMLElement Constructor: must pass a name to constructor");
		}
	} /* end method MiniXMLElement */
	
	
	function name ($setTo=NULL)
	{
		if (! is_null($setTo))
		{
			if (! is_string($setTo))
			{
				return _MiniXMLError("MiniXMLElement::name() Must pass a STRING to method to set name");
			}
			
			$this->xname = $setTo;
		}
		
		return $this->xname;
		
	} /* end method name */
	
	
	
	function attribute ($name, $primValue=NULL, $altValue=NULL)
	{
		$value = (is_null($primValue) ? $altValue : $primValue );


		if (MINIXML_UPPERCASEATTRIBUTES > 0)
		{
			$name = strtoupper($name);
		} elseif (MINIXML_LOWERCASEATTRIBUTES > 0)
		{
			$name = strtolower($name);
		}
		
		if (! is_null($value))
		{
			
			$this->xattributes[$name] = $value;
		}
		
		if (! is_null($this->xattributes[$name]))
		{
			return $this->xattributes[$name];
		} else {
			return NULL;
		}
	
	} /* end method attribute */


	function text ($setToPrimary = NULL, $setToAlternate=NULL)
	{
		$setTo = ($setToPrimary ? $setToPrimary : $setToAlternate);
		
		if (! is_null($setTo))
		{
			$this->createNode($setTo);
		}
		
		$retString = '';
		
		/* Extract text from all child nodes */
		for($i=0; $i< $this->xnumChildren; $i++)
		{
			if ($this->isNode($this->xchildren[$i]))
			{
				$nodeTxt = $this->xchildren[$i]->getValue();
				if (! is_null($nodeTxt))
				{
					$retString .= "$nodeTxt ";
					
				} /* end if text returned */
				
			} /* end if this is a MiniXMLNode */
			
		} /* end loop over all children */
		
		return $retString;
		
	}  /* end method text */
	
	
	
	function numeric ($setToPrimary = NULL, $setToAlternate=NULL)
	{
		$setTo = (is_null($setToPrimary) ? $setToAlternate : $setToPrimary);
		
		if (! is_null($setTo))
		{
			$this->createNode($setTo);
		}
		
	} /* end method numeric */
	
	
	function & comment ($contents)
	{
		$newEl = new MiniXMLElementComment();
		
		$appendedComment =& $this->appendChild($newEl);
		$appendedComment->text($contents);
		
		return $appendedComment;
		
	} /* end method comment */
		
	
	
	
		
		
	
	function & entity ($name,$value)
	{
		
		$newElement = new MiniXMLElementEntity($name, $value);
		$appendedEl =& $this->appendChild($newElement);
		
		return $appendedEl;
	}
	
	
	function & cdata ($contents)
	{
		$newElement = new MiniXMLElementCData($contents);
		$appendedChild =& $this->appendChild($newElement);
		
		return $appendedChild;
	}
		
		
	function getValue ($seperator=' ')
	{
		$retStr = '';
		$valArray = array();
		for($i=0; $i < $this->xnumChildren; $i++)
		{
			$value = $this->xchildren[$i]->getValue();
			if (! is_null($value))
			{
				array_push($valArray, $value);
			}
		}
		if (count($valArray))
		{
			$retStr = implode($seperator, $valArray);
		}
		return $retStr;
		
	} /* end method getValue */
	
	
	function getElement ($name)
	{
		
		if (MINIXML_DEBUG > 0)
		{
			$elname = $this->name();
			_MiniXMLLog("MiniXMLElement::getElement() called for $name on $elname.");
		}
		if (is_null($name))
		{
			return _MiniXMLError("MiniXMLElement::getElement() Must Pass Element name.");
		}
		
		
		
		if (! $this->xnumChildren )
		{
			return NULL;
		}
		
		/* Try each child (immediate children take priority) */
		for ($i = 0; $i < $this->xnumChildren; $i++)
		{
			$childname = $this->xchildren[$i]->name();
			if ($childname)
			{
				if (MINIXML_CASESENSITIVE > 0)
				{
					/* case sensitive matches only */
					if (strcmp($name, $childname) == 0)
					{
						return $this->xchildren[$i];
					}
				} else {
					/* case INsensitive matching */
					if (strcasecmp($name, $childname) == 0)
					{
						return $this->xchildren[$i];
					}
				} /* end if case sensitive */
			} /* end if child has a name */
			
		} /* end loop over all my children */
		
		for ($i = 0; $i < $this->xnumChildren; $i++)
		{
			$theelement = $this->xchildren[$i]->getElement($name);
			if ($theelement)
			{
				if (MINIXML_DEBUG > 0)
				{
					_MiniXMLLog("MiniXMLElement::getElement() returning element $theelement");
				}
				return $theelement;
			}
		}
		
		/* Not found */
		return NULL;
		
		
	}  /* end method getElement */
	
	
	function getElementByPath($path)
	{
		$names = split ("/", $path);
		
		$element = $this;
		foreach ($names as $elementName)
		{
			if ($element && $elementName) /* Make sure we didn't hit a dead end and that we have a name*/
			{
				/* Ask this element to get the next child in path */
				$element = $element->getElement($elementName);
			}
		}
		
		return $element;
		
	} /* end method getElementByPath */
	
	
	function numChildren ($named=NULL)
	{
		if (is_null($named))
		{
			return $this->xnumElementChildren;
		}
		
		/* We require only children named '$named' */
		$allkids =& $this->getAllChildren($named);
		
		return count($allkids);
		
		
	}

	
	function &getAllChildren ($name=NULL)
	{
		$retArray = array();
		$count = 0;
		
		if (is_null($name))
		{
			/* Return all element children */
			for($i=0; $i < $this->xnumChildren; $i++)
			{
				if (method_exists($this->xchildren[$i], 'MiniXMLElement'))
				{
					$retArray[$count++] =& $this->xchildren[$i];
				}
			}
		} else {
			/* Return only element children with name $name */

			for($i=0; $i < $this->xnumChildren; $i++)
			{
				if (method_exists($this->xchildren[$i], 'MiniXMLElement'))
				{
					if (MINIXML_CASESENSITIVE > 0)
					{
						if ($this->xchildren[$i]->name() == $name)
						{
							$retArray[$count++] =& $this->xchildren[$i];
						}
					} else {
						if (strcasecmp($this->xchildren[$i]->name(), $name) == 0)
						{
							$retArray[$count++] =& $this->xchildren[$i];
						}
					} /* end if case sensitive */
					
				} /* end if child is a MiniXMLElement object */
				
			} /* end loop over all children */
			
		} /* end if specific name was requested */
			
		return $retArray;
		
	} /* end method getAllChildren */
	
		
		
	function &insertChild (&$child, $idx=0)
	{
		
		
		
		if (! $this->_validateChild($child))
		{
			return;
		}
		
		/* Set the parent for the child element to this element if 
		** avoidLoops or MINIXML_AUTOSETPARENT is set
		*/
		if ($this->xavoidLoops || (MINIXML_AUTOSETPARENT > 0) )
		{
			if ($this->xparent == $child)
			{
				
				$cname = $child->name();
				return _MiniXMLError("MiniXMLElement::insertChild() Tryng to append parent $cname as child of " 
							. $this->xname );
			}
			$child->parent($this);
		}
		
		
		$nextIdx = $this->xnumChildren;
		$lastIdx = $nextIdx - 1;
		if ($idx > $lastIdx)
		{
		
			if ($idx > $nextIdx)
			{
				$idx = $lastIdx + 1;
			}
			$this->xchildren[$idx] = $child;
			$this->xnumChildren++;
			if ($this->isElement($child))
			{
				$this->xnumElementChildren++;
			}
			
		} elseif ($idx >= 0)
		{
			
			$removed = array_splice($this->xchildren, $idx);
			array_push($this->xchildren, $child);
			$numRemoved = count($removed);
			
			for($i=0; $i<$numRemoved; $i++)
			{
			
				array_push($this->xchildren, $removed[$i]);
			}
			$this->xnumChildren++;
			if ($this->isElement($child))
			{
				$this->xnumElementChildren++;
			}
			
			
		} else {
			$revIdx = (-1 * $idx) % $this->xnumChildren;
			$newIdx = $this->xnumChildren - $revIdx;
			
			if ($newIdx < 0)
			{
				return _MiniXMLError("Element::insertChild() Ended up with a negative index? ($newIdx)");
			}
			
			return $this->insertChild($child, $newIdx);
		}
			
		return $child;
	}
		

	function &appendChild (&$child)
	{
		
		if (! $this->_validateChild($child))
		{
			_MiniXMLLog("MiniXMLElement::appendChild() Could not validate child, aborting append");
			return NULL;
		}
		
		/* Set the parent for the child element to this element if 
		** avoidLoops or MINIXML_AUTOSETPARENT is set
		*/
		if ($this->xavoidLoops || (MINIXML_AUTOSETPARENT > 0) )
		{
			if ($this->xparent == $child)
			{
				
				$cname = $child->name();
				return _MiniXMLError("MiniXMLElement::appendChild() Tryng to append parent $cname as child of " 
							. $this->xname );
			}
			$child->parent($this);
		}
		
		
		$this->xnumElementChildren++; /* Note that we're addind a MiniXMLElement child */
		
		/* Add the child to the list */
		$idx = $this->xnumChildren++;
		$this->xchildren[$idx] =& $child;
		
		return $this->xchildren[$idx];
		
	} /* end method appendChild */
	
	
	function &prependChild ($child)
	{
		
		
		if (! $this->_validateChild($child))
		{
			_MiniXMLLog("MiniXMLElement::prependChild - Could not validate child, aborting.");
			return NULL;
		}
		
		/* Set the parent for the child element to this element if 
		** avoidLoops or MINIXML_AUTOSETPARENT is set
		*/
		if ($this->xavoidLoops || (MINIXML_AUTOSETPARENT > 0) )
		{
			if ($this->xparent == $child)
			{
				
				$cname = $child->name();
				return _MiniXMLError("MiniXMLElement::prependChild() Tryng to append parent $cname as child of " 
							. $this->xname );
			}
			$child->parent($this);
		}
		
		
		$this->xnumElementChildren++; /* Note that we're adding a MiniXMLElement child */
		
		/* Add the child to the list */
		$idx = $this->xnumChildren++;
		array_unshift($this->xchildren, $child);
		return $this->xchildren[0];
		
	} /* end method prependChild */
	
	function _validateChild (&$child)
	{
	
		if (is_null($child))
		{
			return  _MiniXMLError("MiniXMLElement::_validateChild() need to pass a non-NULL MiniXMLElement child.");
		}
		
		if (! method_exists($child, 'MiniXMLElement'))
		{
			return _MiniXMLError("MiniXMLElement::_validateChild() must pass a MiniXMLElement object to _validateChild.");
		}
		
		/* Make sure element is named */
		$cname = $child->name();
		if (is_null($cname))
		{
			_MiniXMLLog("MiniXMLElement::_validateChild() children must be named");
			return 0;
		}
		
		
		/* Check for loops */
		if ($child == $this)
		{
			_MiniXMLLog("MiniXMLElement::_validateChild() Trying to append self as own child!");
			return 0;
		} elseif ( $this->xavoidLoops && $child->parent())
		{
			_MiniXMLLog("MiniXMLElement::_validateChild() Trying to append a child ($cname) that already has a parent set "
						. "while avoidLoops is on - aborting");
			return 0;
		}
		
		return 1;
	}
	function & createChild ($name, $value=NULL)
	{
		if (! $name)
		{
			return _MiniXMLError("MiniXMLElement::createChild() Must pass a NAME to createChild.");
		}
		
		if (! is_string($name))
		{
			return _MiniXMLError("MiniXMLElement::createChild() Name of child must be a STRING");
		}
		
		$child = new MiniXMLElement($name);
		
		$appendedChild =& $this->appendChild($child);
		
		if (! $appendedChild )
		{
			_MiniXMLLog("MiniXMLElement::createChild() '$name' child NOT appended.");
			return NULL;
		}

		if (! is_null($value))
		{
			if (is_numeric($value))
			{
				$appendedChild->numeric($value);
			} elseif (is_string($value))
			{
				$appendedChild->text($value);
			}
		}
		
		$appendedChild->avoidLoops($this->xavoidLoops);
		
		return $appendedChild;
		
	} /* end method createChild */
	
	
		
	function &removeChild (&$child)
	{
		if (! $this->xnumChildren)
		{
			if (MINIXML_DEBUG > 0)
			{
				_MiniXMLLog("Element::removeChild() called for element without any children.") ;
			}
			return NULL;
		}
		
		$foundChild = NULL;
		$idx = 0;
		while ($idx < $this->xnumChildren && ! $foundChild)
		{
			if ($this->xchildren[$idx] == $child)
			{
				$foundChild =& $this->xchildren[$idx];
			} else {
				$idx++;
			}
		}
		
		if (! $foundChild)
		{
			if (MINIXML_DEBUG > 0)
			{
				_MiniXMLLog("Element::removeChild() No matching child found.") ;
			}
			return NULL;
		}
		
		array_splice($this->xchildren, $idx, 1);
		
		$this->xnumChildren--;
		if ($this->isElement($foundChild))
		{
			$this->xnumElementChildren--;
		}
		
		unset ($foundChild->xparent) ;
		return $foundChild;
	}
	
	
	function &removeAllChildren ()
	{
		$emptyArray = array();
		
		if (! $this->xnumChildren)
		{
			return $emptyArray;
		}
		
		$retList =& $this->xchildren;
		
		$idx = 0;
		while ($idx < $this->xnumChildren)
		{
			unset ($retList[$idx++]->xparent);
		}
		
		$this->xchildren = array();
		$this->xnumElementChildren = 0;
		$this->xnumChildren = 0;
		
		
		return $retList;
	}
	
		
	function & remove ()
	{
		$parent =& $this->parent();
		
		if (!$parent)
		{
			_MiniXMLLog("XML::Mini::Element::remove() called for element with no parent set.  Aborting.");
			return NULL;
		}
		
		$removed =& $parent->removeChild($this);
		
		return $removed;
	}
		
	
	
	function &parent (&$setParent)
	{
		if (! is_null($setParent))
		{
			/* Parents can only be MiniXMLElement objects */
			if (! $this->isElement($setParent))
			{
				return _MiniXMLError("MiniXMLElement::parent(): Must pass an instance of MiniXMLElement to set.");
			}
			$this->xparent = $setParent;
		}
		
		return $this->xparent;
		
	} /* end method parent */
	
	
	function avoidLoops ($setTo = NULL)
	{
		if (! is_null($setTo))
		{
			$this->xavoidLoops = $setTo;
		}
		
		return $this->xavoidLoops;
	}
	
	function toString ($depth=0)
	{
		if ($depth == MINIXML_NOWHITESPACES)
		{
			return $this->toStringNoWhiteSpaces();
		} else {
			return $this->toStringWithWhiteSpaces($depth);
		}
	}
	
	function toStringWithWhiteSpaces ($depth=0)
	{
		$attribString = '';
		$elementName = $this->xname;
		$spaces = $this->_spaceStr($depth) ;
		
		$retString = "$spaces<$elementName";
		
		
		foreach ($this->xattributes as $attrname => $attrvalue)
		{
			$attribString .= "$attrname=\"$attrvalue\" ";
		}
		
		
		if ($attribString)
		{
			$attribString = rtrim($attribString);
			$retString .= " $attribString";
		}
		
		if (! $this->xnumChildren)
		{
			/* No kids -> no sub-elements, no text, nothing - consider a <unary/> element */
			$retString .= " />\n";
			
			return $retString;
		} 
		
		
		
		/* If we've gotten this far, the element has
		** kids or text - consider a <binary>otherstuff</binary> element 
		*/
		
		$onlyTxtChild = 0;
		if ($this->xnumChildren == 1 && ! $this->xnumElementChildren)
		{
			$onlyTxtChild = 1;
		}
		
		
		
		if ($onlyTxtChild)
		{
			$nextDepth = 0;
			$retString .= "> ";
		} else {
			$nextDepth = $depth+1;
			$retString .= ">\n";
		}
		
		
		
		for ($i=0; $i < $this->xnumChildren ; $i++)
		{
			if (method_exists($this->xchildren[$i], 'toStringWithWhiteSpaces') )
			{
			
				$newStr = $this->xchildren[$i]->toStringWithWhiteSpaces($nextDepth);
				
					
				if (! is_null($newStr))
				{
					if (! ( preg_match("/\n\$/", $newStr) || $onlyTxtChild) )
					{
						$newStr .= "\n";
					}
				
					$retString .= $newStr;
				}
				
			} else {
				_MiniXMLLog("Invalid child found in $elementName ". $this->xchildren[$i]->name() );
				
			} /* end if has a toString method */
			
		} /* end loop over all children */
		
		/* add the indented closing tag */
		if ($onlyTxtChild)
		{
			$retString .= " </$elementName>\n";
		} else {
			$retString .= "$spaces</$elementName>\n";
		}
		return $retString;
		
	} /* end method toString */
	
	
	
	
	function toStringNoWhiteSpaces ()
	{
		$retString = '';
		$attribString = '';
		$elementName = $this->xname;
		
		foreach ($this->xattributes as $attrname => $attrvalue)
		{
			$attribString .= "$attrname=\"$attrvalue\" ";
		}
		
		$retString = "<$elementName";
		
		
		if ($attribString)
		{
			$attribString = rtrim($attribString);
			$retString .= " $attribString";
		}
		
		if (! $this->xnumChildren)
		{
			/* No kids -> no sub-elements, no text, nothing - consider a <unary/> element */
			
			$retString .= " />";
			return $retString;
		}
		
		
		/* If we've gotten this far, the element has
		** kids or text - consider a <binary>otherstuff</binary> element 
		*/
		$retString .= ">";
		
		/* Loop over all kids, getting associated strings */
		for ($i=0; $i < $this->xnumChildren ; $i++)
		{
			if (method_exists($this->xchildren[$i], 'toStringNoWhiteSpaces') )
			{
				$newStr = $this->xchildren[$i]->toStringNoWhiteSpaces();
					
				if (! is_null($newStr))
				{
					$retString .= $newStr;
				}
				
			} else {
				_MiniXMLLog("Invalid child found in $elementName");
				
			} /* end if has a toString method */
			
		} /* end loop over all children */
		
		/* add the indented closing tag */
		$retString .= "</$elementName>";
		
		return $retString;
		
	} /* end method toStringNoWhiteSpaces */
	
	
	/* toStructure
	**
	** Converts an element to a structure - either an array or a simple string.
	** 
	** This method is used by MiniXML documents to perform their toArray() magic.
	*/
	function & toStructure ()
	{
	
		$retHash = array();
		$contents = "";
		$numAdded = 0;
		
		
		
		for($i=0; $i< $this->xnumChildren; $i++)
		{
			if ($this->isElement($this->xchildren[$i]))
			{
				$name = $this->xchildren[$i]->name();
				
				if (array_key_exists($name, $retHash))
				
				{
					if (! (is_array($retHash[$name]) && array_key_exists('_num', $retHash[$name])) )
					{
						$retHash[$name] = array($retHash[$name],
									 $this->xchildren[$i]->toStructure());
									 
						$retHash[$name]['_num'] = 2;
					} else {
						array_push($retHash[$name], $this->xchildren[$i]->toStructure() );
						
						$retHash[$name]['_num']++;
					}
				} else {
					$retHash[$name] = $this->xchildren[$i]->toStructure();
				}
			
				$numAdded++;
			} else {
				$contents .= $this->xchildren[$i]->getValue();
			}
			
		
		}
		
		
		foreach ($this->xattributes as $attrname => $attrvalue)
		{
			#array_push($retHash, array($attrname => $attrvalue));
			$retHash["_attributes"][$attrname] = $attrvalue;
			$numAdded++;
		}
		
		
		if ($numAdded)
		{
			if (! empty($contents))
			{
				$retHash['_content'] = $contents;
			}
			
			return $retHash;
		} else {
			return $contents;
		}
		
	} // end toStructure() method
	
	
	
	
	
	function isElement (&$testme)
	{
		if (is_null($testme))
		{
			return 0;
		}
		
		return method_exists($testme, 'MiniXMLElement');
	}
	function isNode (&$testme)
	{
		if (is_null($testme))
		{
			return 0;
		}
		
		return method_exists($testme, 'MiniXMLNode');
	}
	function & createNode (&$value, $escapeEntities=NULL)
	{
		
		$newNode = new MiniXMLNode($value, $escapeEntities);
		
		$appendedNode =& $this->appendNode($newNode);
		
		return $appendedNode;
	}
		
	
	function &appendNode (&$node)
	{
		if (is_null($node))
		{
			return  _MiniXMLError("MiniXMLElement::appendNode() need to pass a non-NULL MiniXMLNode.");
		}
		
		
		if (! method_exists($node, 'MiniXMLNode'))
		{
			return _MiniXMLError("MiniXMLElement::appendNode() must pass a MiniXMLNode object to appendNode.");
		}
		
		if (MINIXML_AUTOSETPARENT)
		{
			if ($this->xparent == $node)
			{
				return _MiniXMLError("MiniXMLElement::appendnode() Tryng to append parent $cname as node of " 
							. $this->xname );
			}
			$node->parent($this);
		}
		
		
		$idx = $this->xnumChildren++;
		$this->xchildren[$idx] = $node;
		
		return $this->xchildren[$idx];
		
		
	}
	
	function __destruct()
	{
		if (MINIXML_AUTOSETPARENT) {
		for ($i = 0; $i < count($this->xchildren); ++$i)
			$this->xchildren[$i]->xparent = null;
		}
	}
	
	
} /* end MiniXMLElement class definition */







class MiniXMLElementComment extends MiniXMLElement {

	function MiniXMLElementComment ($name=NULL)
	{
		$this->MiniXMLElement('!--');
	}
	
	
	function toString ($depth=0)
	{
		if ($depth == MINIXML_NOWHITESPACES)
		{
			return $this->toStringNoWhiteSpaces();
		} else {
			return $this->toStringWithWhiteSpaces($depth);
		}
	}
	
		
	function toStringWithWhiteSpaces ($depth=0)
	{

		$spaces = $this->_spaceStr($depth) ;
		
		$retString = "$spaces<!-- \n";
		
		if (! $this->xnumChildren)
		{
			/* No kids, no text - consider a <unary/> element */
			$retString .= " -->\n";
			
			return $retString;
		}
		
		/* If we get here, the element does have children... get their contents */
		
		$nextDepth = $depth+1;
		
		for ($i=0; $i < $this->xnumChildren ; $i++)
		{
			$retString .= $this->xchildren[$i]->toStringWithWhiteSpaces($nextDepth);
		}
		
		$retString .= "\n$spaces -->\n";
		
		
		return $retString;
	}
	
	
	function toStringNoWhiteSpaces ()
	{
		$retString = '';
		
		$retString = "<!-- ";
		
		if (! $this->xnumChildren)
		{
			/* No kids, no text - consider a <unary/> element */
			$retString .= " -->";
			return $retString;
		}
		
		
		/* If we get here, the element does have children... get their contents */
		for ($i=0; $i < $this->xnumChildren ; $i++)
		{
			$retString .= $this->xchildren[$i]->toStringNoWhiteSpaces();
		}
		
		$retString .= " -->";
		
		
		return $retString;
	}
		
	
}




class MiniXMLElementCData extends MiniXMLElement {

		
	
	
	function MiniXMLElementCData ($contents)
	{
		
		$this->MiniXMLElement('CDATA');
		if (! is_null($contents))
		{
			$this->createNode($contents, 0) ;
		}
	}
	

	function toStringNoWhiteSpaces ()
	{
		return $this->toString(MINIXML_NOWHITESPACES);
	}
	
	function toStringWithWhiteSpaces ($depth=0)
	{
		return $this->toString($depth);
	}
	
	function toString ($depth=0)
	{
		$spaces = '';
		if ($depth != MINIXML_NOWHITESPACES)
		{
			$spaces = $this->_spaceStr($depth);
		}
		
		$retString = "$spaces<![CDATA[ ";
		
		if (! $this->xnumChildren)
		{
			$retString .= "]]>\n";
			return $retString;
		}
		
		for ( $i=0; $i < $this->xnumChildren; $i++)
		{
			$retString .= $this->xchildren[$i]->getValue();
			
		}
		
		$retString .= " ]]>\n";
		
		return $retString;
	}
	


}
class MiniXMLElementDocType extends MiniXMLElement {

	var $dtattr;
	
	function MiniXMLElementDocType ($attr)
	{
		$this->MiniXMLElement('DOCTYPE');
		$this->dtattr = $attr;
	}
	function toString ($depth=0)
	{
		if ($depth == MINIXML_NOWHITESPACES)
		{
			return $this->toStringNoWhiteSpaces();
		} else {
			return $this->toStringWithWhiteSpaces($depth);
		}
	}
	
		
	function toStringWithWhiteSpaces ($depth=0)
	{

		$spaces = $this->_spaceStr($depth);
		
		$retString = "$spaces<!DOCTYPE " . $this->dtattr . " [\n";
		
		if (! $this->xnumChildren)
		{
			$retString .= "]>\n";
			return $retString;
		}
		
		$nextDepth = $depth + 1;
		
		for ( $i=0; $i < $this->xnumChildren; $i++)
		{
			
			$retString .= $this->xchildren[$i]->toStringWithWhiteSpaces($nextDepth);
			
		}
		
		$retString .= "\n$spaces]>\n";
		
		return $retString;
	}


	function toStringNoWhiteSpaces ()
	{
	
		$retString = "<!DOCTYPE " . $this->dtattr . " [ ";
		
		if (! $this->xnumChildren)
		{
			$retString .= "]>\n";
			return $retString;
		}
		
		for ( $i=0; $i < $this->xnumChildren; $i++)
		{
			
			$retString .= $this->xchildren[$i]->toStringNoWhiteSpaces();
			
		}
		
		$retString .= " ]>\n";
		
		return $retString;
	}


}


class MiniXMLElementEntity extends MiniXMLElement {


	
	function MiniXMLElementEntity  ($name, $value=NULL)
	{
		
		$this->MiniXMLElement($name);
		
		if (! is_null ($value))
		{
			$this->createNode($value, 0);
		}
		
	}
	
	function toString ($depth = 0)
	{
		
		$spaces = '';
		if ($depth != MINIXML_NOWHITESPACES)
		{
			$spaces = $this->_spaceStr($depth);
		} 
		
		$retString = "$spaces<!ENTITY " . $this->name();
		
		if (! $this->xnumChildren)
		{
			$retString .= ">\n";
			return $retString;
		}
		
		 $nextDepth = ($depth == MINIXML_NOWHITESPACES) ? MINIXML_NOWHITESPACES
										: $depth + 1;
		$retString .= '"';
		for ( $i=0; $i < $this->xnumChildren; $i++)
		{
			
			$retString .= $this->xchildren[$i]->toString(MINIXML_NOWHITESPACES);
			
		}
		$retString .= '"';
		$retString .= " >\n";
		
		return $retString;
	}
	
	
	function toStringNoWhiteSpaces ()
	{
		return $this->toString(MINIXML_NOWHITESPACES);
	}
	
	function toStringWithWhiteSpaces ($depth=0)
	{
		return $this->toString($depth);
	}


}


?>