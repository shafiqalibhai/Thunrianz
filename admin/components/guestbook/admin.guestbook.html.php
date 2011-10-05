<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function guestbook_table() {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=guestbook");
	$gui->add('spacer');
	$gui->add("com_header",_GUESTBOOK_A_TITLE);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_TITLE,'val'=>'title','len'=>'50%','ilink'=>'admin.php?com_option=guestbook&task=edit&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_NAME,'val'=>'name','len'=>'15%','align'=>'center'),
						  array('title'=>_EMAIL,'val'=>'email','len'=>'15%','align'=>'center'),
						  array('title'=>_GUESTBOOK_IP,'val'=>'ip','len'=>'20%','align'=>'center'),
						 ); 
						 
	$table_data=$conn->SelectArray('#__guestbook', 'id,title,name,email,ip,date'," ORDER BY date DESC");$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();					
}

function edit_guestbook($id) {
	global $conn;

	$rsar=$conn->SelectRow('#__guestbook', '*', ' WHERE id='.$id);					   

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=guestbook");
	$gui->add('spacer');
	$gui->add("com_header",_GUESTBOOK_EDIT);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_GUESTBOOK_EDIT);
	$gui->add("hidden","guestbook_id","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$gui->add("textfield","guestbook_name",_NAME,$rsar['name'], $v);
	$gui->add("textfield","guestbook_email",_EMAIL,$rsar['email'], $v);
	$gui->add("textfield","guestbook_url",_URL,$rsar['url']);
	$gui->add("textfield","guestbook_country",_GUESTBOOK_A_COUNTRY,$rsar['country']);
	$gui->add("textfield","guestbook_title",_TITLE,$rsar['title'], $v);
	$gui->add("textarea","guestbook_message",_GUESTBOOK_A_MESSAGE,$rsar['message'], $v);
	$gui->add("textarea","guestbook_reply",_GUESTBOOK_A_REPLY,$rsar['reply']);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

?>