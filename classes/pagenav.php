<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## PageNav class
# @author legolas558
#
# page navigation tool
# this class help selecting the correct slice of rows
# and generating the correct page navigator hyperlinks

class PageNav {

	var $_show_count;
	var $_compact;
	var $_page;
	var $_total_items;
	var $_pointer = 0;
	var $_item_index;
	var $_defaulting;
	var $_rsa = array();

	function PageNav($show_count, $compact = false) {
		$this->_show_count = $show_count;
		$this->_compact = $compact;
		$this->_page = in_num('page', $_GET, null);
		$this->_defaulting = !isset($this->_page);
		if ($this->_defaulting)
			$this->_page = 1;
		$this->_total_items = in_num('total_items', $_GET, null);
	}

	// main function used to limit SQL queries - this is the standard way to use the PageNav class
	function Slice($table, $args, $extra1, $extra2) {
		global $conn;
		if (!isset($this->_total_items))
			$this->_total_items = $conn->Count('SELECT COUNT(*) FROM '.$table.' '.$extra1);
		$rs=$conn->SelectLimit('SELECT '.$args.' FROM '.$table.' '.$extra1.' '.$extra2,
							$this->_show_count,
							($this->_page-1)*$this->_show_count);
		return $rs->GetArray();
	}
	
	function Page() {
		return $this->_page;
	}
	
	function Total() {
		return $this->_total_items;
	}
	
	function TotalPages() {
		if (!isset($this->_total_items))
			return null;
		return 1 + (int)ceil($this->_total_items/$this->_show_count);
	}
	
	function SetTotal($total) {
		$this->_total_items = $total;
	}
	
	var $_counting = false;
	
	// initialize query counting
	function QueryCount() {
		// set counting mode if there are no previous items
		if (!isset($this->_total_items)) {
			$this->_counting = true;
			$this->_total_items = 0;
		}
	}
	
	// this is the 4th way to use the PageNav class
	// it only allows to individuate the matching range and makes no actual storage
	// returns 0 - first offset not yet reached, 1 - in range, 2 - end of range
	function QueryAddFlag() {
		$offset = ($this->_page-1)*$this->_show_count;
		if ($this->_counting)
			++$this->_total_items;
//		echo '<hr />pointer is '.$this->_pointer.'<br />';
//		echo 'total is '.$this->_total_items.'<br />';
		if ($this->_pointer >= $offset + $this->_show_count) {
			// we are not counting, the slice is complete
			if (!$this->_counting)
				return 2;
//			echo 'counting...<br />';
			// skip the row, but keep counting
//			++$this->_total_items;
			return 0;
		}
		// the page is in range
		if (++$this->_pointer > $offset) {
//			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;in range!<br />';
			return 1;
		}
		return 0;		
	}
	
	// this is the secondary way to use the PageNav class - you feed in rows until the requested window
	// of rows is filled -  returns true if other rows can be feeded in
	function QueryAdd($row) {
		$offset = ($this->_page-1)*$this->_show_count;
		if (++$this->_pointer >= $offset)
			$this->_rsa[] = $row;
		return ($this->_pointer < $offset + $this->_show_count);
	}
	
	// see above method - returns the collected range of rows
	function QueryArray() {
		$this->_total_items = count($this->_rsa);
		return $this->_rsa;
	}
	
	function SetItemIndex($i) {
		$this->_item_index = $i;
	}

	// this is the 3rd way to use the PageNav class - provide a full recordset and select a range
	function &ArraySlice(&$rsa) {
		$this->_rsa =& $rsa;
		if (!isset($this->_total_items))
			$this->_total_items = count($this->_rsa);
		if (isset($this->_item_index) && $this->_defaulting) {	// override page with index of item if no page was specified
			$sel = null;
			foreach($this->_rsa as $i => $val) {
				if ($val==$this->_item_index) {
					$sel = $i;
					break;
				}
			}
			if (isset($sel))
				$this->_page = (int)ceil(($sel+1)/$this->_show_count);
		}
		$rsasl = array_slice($this->_rsa, ($this->_page-1)*$this->_show_count, $this->_show_count);
		return $rsasl;
	}

	// method used to generate the hyperlinks of the navigation bar
	function NavBar($page_url, $class='pagenav') {
		// add the request .php page
		$page_url = CMSRequest::ScriptName().'?'.$page_url;
		// calculate the number of pages to be displayed
		$num_pages = (int)ceil($this->_total_items/$this->_show_count);
		// if there is only one page, the navigation bar is not needed
		if ($num_pages<2)
			return '';
		// setup PREV link
		if ($this->_page>1)
			$previous="<a href=\"$page_url&amp;page=".($this->_page-1).'&amp;total_items='.$this->_total_items.
				'"  class="'.$class.'">'._ITEM_PREVIOUS.'</a>';
			else
				$previous="<span class='$class'>"._ITEM_PREVIOUS.'</span>';
		// setup NEXT link
		if($this->_page<$num_pages)
			$next='<a href="'.$page_url.'&amp;page='.($this->_page+1).'&amp;total_items='.$this->_total_items.
			'"  class="'.$class.'">'._ITEM_NEXT.'</a>';
		else
			$next="<span class='$class'>"._ITEM_NEXT.'</span>';

		// return a smaller compact navigation bar
		if($this->_compact)
			return "$previous  ( ".sprintf(_PN_PAGE, $this->_page, $num_pages)." ) $next";
		// setup FIRST link
		if ($num_pages > 2) {
			 if($this->_page>1)
				$first="<a href='$page_url' class='$class'>"._PREV_ARROW.' '._PN_START."</a>";
			else
				 $first="<span class='$class'>"._PREV_ARROW.' '._PN_START.'</span>';
		 } else
			$first = '';
		// setup LAST link
		if ($num_pages > 2) {
			if($this->_page<$num_pages)$last="<a href=\"$page_url&amp;page=".$num_pages.'&amp;total_items='.$this->_total_items.'"  class="'.$class.'">'._PN_END.' '._NEXT_ARROW."</a>";
			else
				$last="<span class='$class'>"._PN_END.' '._NEXT_ARROW.'</span>';
		} else
			$last = '';
		
		// create the hyperlinks of the navigation bar
		$page_links='';
		for($pn=1;$pn<$this->_page;$pn++) {
			$page_links.=" <a href=\"$page_url&amp;page=".$pn.'&amp;total_items='.$this->_total_items.
			'"  class="'.$class.'">'.$pn.'</a> ';
		}
		$page_links.="<span class=\"$class\">".$this->_page."</span>";
		for($pn=$this->_page+1;$pn<=$num_pages;$pn++ ) {
			$page_links.=" <a href=\"$page_url&amp;page=".$pn.'&amp;total_items='.$this->_total_items.'"  class="'.$class.'">'.$pn.'</a> ';
		}
		return "$first $previous $page_links $next $last";
	}

} // class PageNav

?>