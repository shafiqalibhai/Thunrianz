<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
// see minixml-license.txt for information about this class

class MiniXMLTreeComponent {
	
	var $xparent;
	
	/*  MiniXMLTreeComponent
	** Constructor.  Creates a new MiniXMLTreeComponent object.
	**
	*/
	function MiniXMLTreeComponent ()
	{
//		$this->xparent = NULL;
	} /* end MiniXMLTreeComponent constructor */
	
	
	/* Get set function for the element name
	*/
	function name ($setTo=NULL)
	{
		return NULL;
	}
	
	/* Function to fetch an element */
	function getElement ($name)
	{
		return NULL;
	}
	
	/* Function that returns the value of this 
	component and its children */
	function getValue ()
	{
		return NULL;
	}
	
	function &parent (&$setParent)
	{	
		if (! is_null($setParent))
		{
			/* Parents can only be MiniXMLElement objects */
			if (! method_exists($setParent, 'MiniXMLTreeComponent'))
			{
				return _MiniXMLError("MiniXMLTreeComponent::parent(): Must pass an instance derived from "
							. "MiniXMLTreeComponent to set.");
			}
			$this->xparent = $setParent;
		}
		
		return $this->xparent;
		
		
	}
	
	/* Return a stringified version of the XML representing
	this component and all sub-components */
	function toString ($depth=0)
	{
		return NULL;
	}

	/* dump
	** Debugging aid, dump returns a nicely formatted dump of the current structure of the
	** MiniXMLTreeComponent-derived object.
	*/
	function dump ()
	{
		return var_dump($this);
	}
	
	/* helper class that everybody loves */
	function _spaceStr ($numSpaces)
	{
		$retStr = '';
		if ($numSpaces < 0)
		{
			return $retStr;
		}
			
		for($i = 0; $i < $numSpaces; $i++)
		{
			$retStr .= ' ';
		}
		
		return $retStr;
	}
	
	/* Destructor to keep things clean -- patch by Ilya */
	function __destruct()
	{
		if (MINIXML_AUTOSETPARENT)
			$this->xparent = null;
	}
	
} /* end class definition */


?>