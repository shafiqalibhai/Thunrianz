<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
/*
Copyright  July 2005
Author: Limbo Freak
website: http://www.limbofreak.com
email:  freak@limbofreak.com
Licence: GNU/GPL License
*/

/* items interface */
function comment_table() {
	global $conn,$d;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=comment");
	$gui->add("com_header",_COMMENT_HEADER);

	$table_head = array (
		     array('title'=>'#' , 'val'=>'id' , 'len'=>'2%','align'=>'center') ,
		     array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'2%','align'=>'center') ,
		     array('title'=>_COMMENT_TITLE,'val'=>'title','len'=>'50%','ilink'=>'admin.php?com_option=comment&task=edit&id=ivar1','ivar1'=>'id') ,
		     array('title'=>_COMMENT_POSTED_BY,'val'=>'name','len'=>'20%','align'=>'center'),
		     array('title'=>_COMMENT_DATE_POSTED,'val'=>'date','len'=>'30%','align'=>'center'),
		     array('title'=>_PUBLISHED,'val'=>'published','len'=>'10%','align'=>'center'),
						 );
	$table_data=$conn->SelectArray('#__content_comment' ,'*', ' ORDER BY date DESC');
	if ($table_data){
		foreach ($table_data as $row){$date_array[]=
			array("name"=> lc_strftime(_DATE_FORMAT_LC.' %H:%M',$row['date']),
		"value"=> $row['date'] );}
		$replace = array( "date"=>$date_array);
		$table_data = gui_array_replace($table_data,$replace);
	}
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();
}

function edit_comment($id) {
	global $conn;
	$rsar=$conn->SelectRow('#__content_comment', '*', ' WHERE id='.$id);

	$c_interface=new ScriptedUI();
	$c_interface->add("form","adminform","","admin.php?com_option=comment");
	$c_interface->add('spacer');
	$c_interface->add("com_header",_COMMENT_HEADER_EDIT);
	$c_interface->add("tab_head");
	$c_interface->add("tab_simple",_COMMENT_CONTENT,_COMMENT_CONTENT_DESC);
	$c_interface->add("hidden","id","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$c_interface->add("textfield","_name",_COMMENT_NAME,$rsar['name'], $v);
	$c_interface->add("textarea","_comment",_COMMENT_COMMENT,$rsar['comment'], $v);
	$c_interface->add("tab_end");
	$c_interface->add("tab_tail");
	$c_interface->add("end_form");
	$c_interface->add('spacer');
	$c_interface->generate();
}

?>