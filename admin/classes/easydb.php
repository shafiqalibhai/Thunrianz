<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## EasyDB class
# @author Lanius CMS Team
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# A class to simplify some recurring database functions
#

class EasyDB {

	var $section = false;
	
	var $rev_order = false;
	
	// automatically create a section if one with the same name does not already exist
	// 'sections' here are NOT rows of #__sections but parent categories
	// always returns the section (=parent category) id
	function auto_section($com, $prefix, $id = 0, $com_cat = '') {
		$section = in_num($prefix.'_section', $_POST, 0);
		$new_section = in_sql($prefix.'_new_section', $_POST, '');
		$old_section = in_sql($prefix.'_old_section', $_POST, 0);
		if (!strlen($new_section)) {
			// if there is not a new section, check only for a section change
			if ($old_section != $section) {
				change_val('categories', $old_section, 'count', -1);
				// optimize should remove empty sections
				$this->_sections_optimize($com);
			}
			return $section;
		}
		// there is a new section, subtract from the old section (if there was one
		if ($old_section)
			change_val('categories', $old_section, 'count', -1);
		// see if a section already exists
		global $conn;
		$row = $conn->GetRow('SELECT id FROM #__categories WHERE section=\'com_'.$com.'\' AND name=\''.$new_section.'\'');
		if (count($row)) {
			// the section exists, increment its count field
			change_val('categories',$row['id'],"count",1);
			//$this->_sections_optimize($com);	// unnecessary
			return $row['id'];
		}
		// the section does not exist, let's get an ordering for it
		$ordering = $this->neworder('categories', 'name=\'com_'.$com.'\'');
		// the section will inherit access from the category itself (if existant)
		if ($id) {
			$row = $conn->SelectRow('#__'.$com_cat.'categories', 'access', ' WHERE id='.$id);
			$access = $row['access'];
		} else { $id=0; $access=0; }
		// actually insert this new section into the database
		$conn->Insert('#__categories', '(section, name, ordering, access, count)', '\'com_'.$com.'\', \''.$new_section.'\', '.$ordering.', '.$access.', 1');
		//$this->_sections_optimize($com);	// unnecessary
		return $conn->Insert_ID();
	}
	
	function _sections_optimize($com) {
//		global $conn;
//		$conn->Execute('DELETE FROM #__categories WHERE count=0 AND section=\'com_'.$com.'\'');
	}
	
	function sections($com, $selected = null) {
		global $conn;
		$sections = $conn->GetArray('SELECT id,name FROM #__categories WHERE section=\'com_'.$com.'\'');
		$r = array( array('value' => '0', 'name' => _NONE));
		$set_sel = false;
		foreach($sections as $section) {
			$el = array('value' => $section['id'], 'name' => $section['name']);
			if (!$set_sel && ($selected == $section['id'])) {
				$el['selected'] = true;
				$set_sel = true;
			}
			$r[] = $el;
		}
		if (!$set_sel)
			$r[0]['selected'] = true;
		return $r;
	}

	function data_table($cid, $task, $table, $redir = '', $extra = '', $section = false) {
		if ($section !== false)
			$this->section = true;
		$this->{'_'.$task}($table, $cid, $extra);
		if (!empty($redir))
			CMSResponse::Redir($redir);
	}
	
	// automatically handles category operations
	function auto_category($table,$id,$count) {
		if (!$this->section)
			return;
		global $conn;
		$row = $conn->SelectRow('#__'.$table, 'catid', " WHERE id=$id");
		//TODO: use alternative syntax for tables which do not have a catid
		change_val("categories",$row['catid'],"count",$count);
	}
	
	function is_published($table,$id,$extra='') {
		global $conn;
		$row=$conn->SelectRow('#__'.$table, 'published', ' WHERE id='.$id.' '.$extra);
		return $row['published'];
	}
	
	// apply ordering of specified fields
	function _order($table, $cid, $sort_type, $extra='') {
		global $conn;
		$id = current($cid);
		if($sort_type==SORT_DESC) {
			$sort="DESC";
			$sortsym="<";
		} else { // if $sort_type=SORT_DESC
			$sort="ASC";
			$sortsym=">";
		}
		if (strlen($extra))
			$extra='AND '.$extra;
		$row = $conn->SelectRow('#__'.$table, 'ordering', " WHERE id=$id $extra");
		$order=$row['ordering'];
		$row = $conn->SelectRow('#__'.$table, 'id,ordering', " WHERE ordering $sortsym $order $extra ORDER BY ordering $sort");
		if (count($row)) {
			$cid1_o=$order;
			$cid2=$row['id'];
			$cid2_o=$row['ordering'];
			$conn->Update('#__'.$table, "ordering = $cid2_o", " WHERE id=$id");
			$conn->Update('#__'.$table, "ordering = $cid1_o", " WHERE id=$cid2");
		}
	}

	function _orderup($table, $cid, $extra='') {
		$this->_order($table, $cid, SORT_DESC, $extra);
	}

	function _orderdown($table, $cid, $extra='') {
		$this->_order($table, $cid, SORT_ASC, $extra);
	}

	function neworder($table,$extra='') {
		global $conn;
		if(strlen($extra)) $extra = 'WHERE '.$extra;
		$rev_order = in_num('insert_top', $_POST, $this->rev_order);
		if ($rev_order) {
			$ORD = 'ASC';
			$ord_delta = -1;
		} else {
			$ORD = 'DESC';
			$ord_delta = 1;
		}
		$rsa=$conn->SelectArray('#__'.$table, 'id,ordering', " $extra ORDER BY ordering $ORD");
		$last = count($rsa);
		// the starting ordering ID is 1
		if (!$last) return 1;
		// check whether reordering is needed or not
		$reorder_required=false;
		for($i=1;$i<$last;$i++) {
			if($rsa[$i]['ordering']==$rsa[$i-1]['ordering']) {
				$reorder_required=true;
				break;
			}
		}
		// prevent negative ordering values
		if (!$reorder_required) {
			$ordering = $rsa[0]['ordering'];
			if ($rev_order && ($ordering<1))
				$reorder_required = true;
			else
				$ordering += $ord_delta;
		}
		// reorder with normalization
		if ($reorder_required) {
			$new_order=$last;
			for($i=0;$i<$last;$i++) {
				$conn->Update('#__'.$table, "ordering=$new_order", ' WHERE id='.$rsa[$i]['id']);
				$new_order -= $ord_delta;
			}
			$ordering = $last + $ord_delta;
		}
		//DEBUG
		if ($ordering<0)
			trigger_error('negative ordering: '.$ordering);
		return $ordering;
	}

	function _reorder($table, $cid, $extra) {
		global $conn;
		foreach($cid as $id) {
			$ooid = in_num('ooid'.$id, $_REQUEST);
			$oid = in_num('oid'.$id, $_REQUEST);
			if (!isset($oid) || !isset($ooid))
				continue;
			if($ooid!==$oid)
				$conn->Update('#__'.$table, 'ordering='.$oid, ' WHERE id='.$id);
		}
		$this->neworder($table, $extra);
	}
	
	function _published_change($table, $cid, $pub) {
		global $conn;
		foreach($cid as $id){
			if($this->is_published($table,$id)!=$pub) {
				$conn->Update('#__'.$table, "published=$pub", " WHERE id=$id");
			    $this->auto_category($table,$id, ($pub ? 1 : -1));
		 	}
		}
	}

	function _unpublish($table, $cid) {
		$this->_published_change($table, $cid, 0);
	}

	function _publish($table, $cid) {
		$this->_published_change($table, $cid, 1);
	}
	
	function _archive($table, $cid) {
		global $conn;
		foreach($cid as $id) {
			if ($this->is_published($table, $id))
			   $this->auto_category($table,$id,-1);
			$conn->Update('#__'.$table, 'published=4', " WHERE id=$id");
		}
	}

	function _unarchive($table, $cid) {
		global $conn;
		foreach($cid as $id){
			$conn->Update('#__'.$table, 'published=0', " WHERE id=$id");
		}
	}

	function _delete($table,$cid, $extra='') {
		global $conn;
		if (!is_array($cid)) return;
		if($extra!=='')$extra='AND '.$extra;
		foreach($cid as $var){
			//L: will probably cause an error for tables which no more have the published field
			if($this->is_published($table,$var,$extra)) {
			   $this->auto_category($table,$var,-1);
			}
			$conn->Delete('#__'.$table, " WHERE id = $var $extra");
		}
	}

	function delete_np($table,$cid,$extra='') {
		global $conn;
		if(strlen($extra))$extra='AND '.$extra;
		$r = array();
		foreach($cid as $id) {
			$conn->Delete('#__'.$table, " WHERE id=$id $extra");
			if ($conn->Affected_Rows() > 0)
				$r[] = $id;
		}
		return $r;
	}

//TODO: deprecate this function, or at least the 2nd part of it
function check_category($table,$id,$new_cat,$old_cat) {
	//TODO: the check should be performed before calling the function
	if ($new_cat != $old_cat ) {
		if ($this->is_published($table,$id)) {
			change_val("categories",$new_cat,"count",1);
			change_val("categories",$old_cat,"count",-1);
		}
	}
	global $conn;
	$c = $conn->SelectCount('#__'.$table, '*', 
		' WHERE catid='.$new_cat.' AND published=1');
	// unless we can freely use COUNT(*) on all DBMSes...
	$conn->Update('#__categories', 'count='.$c, ' WHERE id='.$new_cat);
}

function position_list($table,$title,$where="") {
	global $conn,$cid;
	$rsa=$conn->SelectArray('#__'.$table, $title.',ordering,position', " $where ORDER BY ordering ASC");
	$pos_array[]=array("name"=>"First","value"=>'first');
	if(count($rsa)) {
		foreach($rsa as $row){$pos_array[]=array("name"=>" ".$row['ordering']." : ".$row[$title]." (".$row['position'].")","value"=>$row['ordering']); }
	}
	$pos_array[]=array("name"=>"Last","value"=>'last');
	return $pos_array;
}
	
	function Insert($table, $prefix, $fields, $flags = null, $max = null, $ordering = null, $ordering_q = '', $static = array()) {
		// set the flags array if unset
		if (!isset($flags))
			$flags = array_fill(0, count($fields), 0);
		else {
			if (count($fields)!=count($flags))
				trigger_error('You should supply a flag for each field and viceversa');
		}
		if (!isset($max))
			$max = array_fill(0, count($fields), null);
		$upd = '';
		$fld = '';
		$val = '';
		foreach($fields as $field) {
			$m = current($max);
			$f = current($flags);
			$v = in($prefix.'_'.$field, $f | __SQL, $_POST, $m);
			if (!isset($v))
				return false;
			if (current($flags) & __NUM)
				$val .= $v.', ';
			else
				$val .= "'".$v."', ";
			$fld .= $field.', ';
			next($flags);
			next($max);
		}
		foreach ($static as $field => $v) {
			$fld .= $field.', ';
			$val .= $v.', ';
		}
		$fld = substr($fld, 0, -2);
		$val = substr($val, 0, -2);
		
		if (isset($ordering)) {
			$fld .= ', '.$ordering;
			$val .= ', '.$this->neworder($table, $ordering_q);
		}
		
		global $conn;
		$conn->Insert('#__'.$table, '('.$fld.')', $val);
		return true;
	}

	function Update($table, $prefix, $fields, $flags = null, $max = null, $id_field = 'id',
				$condition = '', $static = array()) {
		// set the flags array if unset
		if (!isset($flags))
			$flags = array_fill(0, count($fields), 0);
		else {
			if (count($fields)!=count($flags))
				trigger_error('You should supply a flag for each field and viceversa');
		}
		if (!isset($max))
			$max = array_fill(0, count($fields), null);
		$upd = '';
		// get the ID field
		$v = in_num($prefix.'_'.$id_field, $_POST);
		if (!isset($v))
			return false;
		// prepare the WHERE condition
		if (strlen($condition)) $condition = ' AND '.$condition;
		$condition = ' WHERE '.$id_field.'='.$v.$condition;
		foreach($fields as $field) {
			$m = current($max);
			$f = current($flags);
			$v = in($prefix.'_'.$field, $f | __SQL, $_POST, $m);
			if (!isset($v))
				return false;
			$upd .= $field.' = ';
			if (($f & __NUM) || ($f & __CHECKBOX))
				$upd .= $v.', ';
			else
				$upd .= "'".$v."', ";
			next($flags);
			next($max);
		}
		// remove redundant comma
		$upd = substr($upd, 0, -2);
		global $conn;
		$conn->Update('#__'.$table, $upd, $condition);
		return true;
	}
	
	function MassOp($table, $back_url = '', $extra = '', $field = 'access') {
		global $conn, $d;
		if (strlen($extra))
			$extra.=' AND';
		// take the page index
		$table_page = in_num('table_page', $_POST, 1);
		if ($table_page != 1)
			$back_url .= '&table_page='.$table_page;
		// take the elements
		$cid = in('cid', __ARR|__NUM, $_POST);
		if (!isset($cid))
			CMSResponse::Redir($back_url, _FORM_NC);
		// check if access was changed
		$ac = in_num('mo_access', $_POST);
		if (isset($ac)) {
			foreach ($cid as $id) {
				$conn->Update('#__'.$table, $field.'='.$ac, ' WHERE '.$extra.' id='.$id);
			}
		}
		// check if frontpage was changed
		$fp = in_num('mo_frontpage', $_POST);
		if (isset($fp)) {
			//(1) get the frontpage ids
			$rsa = $conn->SelectArray('#__content', 'id,frontpage', ' WHERE '.each_id($cid));
			$todo = array();
			//(2) insert/remove from #__content_frontpage and add to $todo array
			foreach($rsa as $row) {
				// if there has been a change
				if ($row['frontpage'] != $fp) {
					if ($fp) {
						// frontpage -> YES
						$conn->Insert('#__content_frontpage', '(id)', $row['id']);
					} else {
						// frontpage -> NO
						$conn->Delete('#__content_frontpage', ' WHERE id='.$row['id']);
					}
					$todo[] = $row['id'];
				}
			}
			//(3) mass update the content items
			if (count($todo))
				$conn->Update('#__content', 'frontpage='.($fp ? 1 : '0'), ' WHERE '.each_id($todo));
		}
		// check if ownership was changed
		$owner = in_num('mo_owner', $_POST, '');
		global $my;
		if (strlen($owner) && $my->is_admin()) {
			$owner_name = username_by_id($owner);
			foreach ($cid as $id) {
				$row = $conn->SelectRow('#__'.$table, 'userid', ' WHERE '.$extra.' id='.$id);
				if (empty($row) || ($row['userid']==$owner))
					continue;
				$conn->Update('#__'.$table, 'userid='.$owner, ' WHERE '.$extra.' id='.$id);
//				"User %s (#%d) changed ownership of element #%d in table %s from %d (%s) to %d (%s)"
				$d->log(4, sprintf(_MOP_OWNERSHIP_CHANGE, $my->username, $my->id, $id,
					'#__'.$table, username_by_id($row['userid']), $row['userid'],
					$owner_name, $owner));
			}
		}
		// check if invert order was ticked
		$invert = in_checkbox('mo_invert_order', $_POST);
		if ($invert) {
			$c=count($cid);
			if ($c>=2) {
				// remove the central item if odd
				if ($c % 2) {
					--$c;
					array_splice($cid, $c+1, 1);
				}
				for($i=0;$i<$c;++$i) {
					// ordering of inverse position item
					$ro = in_num('ooid'.$cid[$c-$i-1], $_POST);
					$conn->Update('#__'.$table, 'ordering='.$ro, ' WHERE '.$extra.' id='.$cid[$i]);
				}
			}
		}
		// check if re-order dropdown list was changed
		$reorder = in_raw('mo_reorder', $_POST);
		if (isset($reorder) && strlen($reorder)) {
			$neworder = array();
			foreach($conn->SelectArray('#__'.$table, 'id,ordering', ' WHERE '.each_id($cid).' ORDER BY '.$reorder) as $row) {
				$oldorder[] = (int)$row['ordering'];
				$neworder[] = (int)$row['id'];
			}
			sort($oldorder);
			foreach($neworder as $id) {
				$ordering = current($oldorder); next($oldorder);
				$conn->Update('#__'.$table, 'ordering='.$ordering, ' WHERE id='.$id);
			}
		}
		CMSResponse::Redir($back_url);
	}

	
}
?>