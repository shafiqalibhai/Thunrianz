<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function items_table() {
	global $conn,$sec_id;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=event");
	$gui->add("com_header",_EVENT_MANAGE);

	$table_head = array( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>_EVENT_TITLE,'val'=>'title','len'=>'70%','ilink'=>'admin.php?com_option=event&task=edit&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_OWNER,'val'=>'userid','len'=>'10%','align'=>'center'),						  array('title'=>_EVENT_SDATE,'val'=>'sdate','date'=>'1','len'=>'20%','ilink'=>'admin.php?com_option=events&task=edit&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_ACCESS,'val'=>'access','len'=>'10%','align'=>'center'),						  array('title'=>_EVENT_SDATE,'val'=>'sdate','date'=>'1','len'=>'20%','ilink'=>'admin.php?com_option=events&task=edit&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_PUBLISHED,'val'=>'published','len'=>'10%','align'=>'center'),
						  );
	$table_data=$conn->SelectArray('#__event', 'id,title,sdate,published,access,userid',
		' ORDER BY sdate DESC');
	$table_data = gui_array_replace($table_data, array(), array('userid' => 'username_by_id'));
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();
}

function edit_items($cid) {
	global $conn,$time,$d;

	if (isset($cid)) {
		$rsar=$conn->SelectRow('#__event', '*',' WHERE id='.$cid);
		$c_head = _EVENT_EDIT_HEAD;
	} else {
		$rsar=array("id"=>"","title"=>"","description"=>"","sdate"=>$time,"edate"=>$time + (24 * 60 * 60),"venue"=>"","street"=>"","city"=>"","state"=>"","country"=>"", 'published' => '0', 'access' => '0');
		$c_head = _EVENT_NEW_HEAD;
	}

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=event");
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head);
	$gui->add("hidden","event_id","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","event_title",_TITLE,$rsar['title'],$v);
	$gui->add("date","event_sdate",_EVENT_SDATE,$rsar['sdate']);
	$gui->add("date","event_edate",_EVENT_EDATE,$rsar['edate']);
	$gui->add("textarea","event_description",_DESC,$rsar['description']);
	if (!isset($cid))
		$gui->add('insert_where');
	$gui->add("boolean","event_published",_PUBLISHED,$rsar['published']);
	$gui->add("access","event_access",_ACCESS,$rsar['access']);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

?>