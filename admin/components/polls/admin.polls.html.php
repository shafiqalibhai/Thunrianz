<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function polls_table() {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=polls");
	$gui->add("com_header",_POLLS_MANAGE);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>_NAME,'val'=>'name','len'=>'60%','ilink'=>'admin.php?com_option=polls&task=edit&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_ACCESS,'val'=>'access','len'=>'10%','align'=>'center'),
						  array('title'=>_POLLS_A_ID,'val'=>'id','len'=>'10%','align'=>'center'),
						  array('title'=>_POLLS_A_OPTIONS , 'val'=>'count' , 'len'=>'10%','align'=>'center') ,
						  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center')
						 );
	$table_data=$conn->SelectArray('#__categories', 'id,name,section,access,count,ordering', " WHERE section='com_polls'".$gui->Ordering());
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
//	$gui->add("massops");
	$gui->add("end_form");
	$gui->generate();
}

function edit_polls($id = null) {
	global $conn;

	if(isset($id)) {
		$rsar=$conn->SelectRow('#__categories', 'id,access,name', " WHERE id = ".$id);
		$rsa1=$conn->SelectColumn('#__polls_data', 'polloption', " WHERE pollid = ".$id);
		$c_head = _POLLS_EDIT_HEAD;
	}else {
		$rsar=array("id"=>"","name"=>"", 'access' => '9');
		$c_head = _POLLS_NEW_HEAD;
	}
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=polls");
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head,"");
	$gui->add("hidden","pollid","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","poll_title",_POLLS_A_QUESTION,$rsar['name'],$v);
	$gui->add("access","poll_access",_ACCESS,$rsar['access']);
	$gui->add('spacer');
	$gui->add('text','',_POLLS_VOPTIONS);
	$gui->add('spacer');
	for($i=1;$i<11;$i++) {
		$req=false;
		if($i==1 || $i==2)$req=true;
		if (isset($rsa1[$i-1]) && strlen($rsa1[$i-1]))
			$str=$rsa1[$i-1];
		else $str = '';
		$gui->add("textfield","poll_option$i",_POLLS_A_OPTION." $i",$str,($req ? $v : null));
	}
	if (!isset($id))
		$gui->add('insert_where');

	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

?>