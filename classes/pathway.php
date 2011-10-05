<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Pathway class
# @author legolas558
#
# generation of the navigation pathway

/*
   Generation scheme of the pathway:
   
   (1) home page link (arrow or home page icon)
   (2) children items
   (3) the last of children items becomes the current page pathway caption
   
*/

class PathwayAncestor {

	// children
	var $urls = array();
	var $captions = array();
	
	// private
	var $_home;
	
	function Count() {
		return count($this->urls);
	}
	
	function Current() {
		$url = end($this->urls);
		reset($this->urls);
		return $url;
	}
	
	function add($caption, $url=null) {
		if (!isset($url)) {
			$url = @CMSRequest::Querystring();
			if (!strlen($url))
				$url = CMSResponse::FrontendOptionUrl();
		}
		$this->urls[] = $url;
		$this->captions[] = $caption;
	}
	
	function add_head($caption, $url=null) {
		if (!isset($url))
			$url = CMSResponse::FrontendOptionUrl();
		array_unshift($this->captions, $caption);
		array_unshift($this->urls, $url);
	}
	
	// $url has already the script name in it
	function _make_hyperlink($caption, $url) {
		return '<a href="'.$url.content_sef($caption).'" class="pathway">'.$caption."</a>";
	}

	// generate the HTML for the pathway
	function Generate() {
		global $conn;
		
		// (1) set the first menu item
		$path = '<a title="'._PATHWAY_HOME_LINK.'" href="'.$GLOBALS['d_website'].''.$this->_home.
			'" class="pathway">'.template_pic('home.png', _PATHWAY_HOME_LINK)."</a> ";
		$img =  template_pic('arrow.png', '&gt;');
		
		$img = " $img ";
		
		// (2) parse all the children items
		$c = count($this->captions);
		if (!$c) {
			if (!$this->Undefined()) {
				exit();
				return;
			}
			$c = count($this->captions);
			//DEBUG
			if (!$c) {
				$this->add('No pathway defined');
				$c = 1;
			}
		}
		global $d;
		for($i=0;$i<$c-1;++$i) {
			$url = $this->urls[$i];
			//DEBUG
			if (strpos($url, '&amp;'))
				trigger_error('Pathway url is already encoded');
			$url = xhtml_safe($url);
			$path.= $this->_make_hyperlink($this->captions[$i], CMSRequest::ScriptName().'?'.$url).$img;
			$d->add_title($this->captions[$i]);
		}
		
		// (3) current item
		$caption = $this->captions[$c-1];
		$path .= $caption;
		
		$path .= ' <a title="'._PATHWAY_PERMA_LINK.'" href="'.xhtml_safe(CMSRequest::ScriptName().'?'.$this->urls[$c-1]).'">'.template_pic('box.png', _PATHWAY_PERMA_LINK).'</a> ';
		
		// modify the global title using the current item's caption
		$d->add_title($caption);

		// return the generated path (1 + 2 + 3 + 4)
		return $path;
	}

}

class Pathway extends PathwayAncestor {

	function Pathway() {
		$this->_home = '';
	}
	
	function Undefined() {
		CMSResponse::Unavailable('No pathway defined');
		return false;
	}

}

?>