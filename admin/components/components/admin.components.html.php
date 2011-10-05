<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function components_table() {
	global $conn;

	$gui=new ScriptedUI(true);
	$gui->add("form","adminform","","admin.php?com_option=components");
	$gui->add('spacer');
	$gui->add("com_header",_COMPONENTS_HEAD);

	$table_head = array ( array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_NAME,'val'=>'name','len'=>'40%'),
						  array('title'=>_COMPONENT_ID , 'val'=>'option_link' , 'len'=>'40%') , 
						  array('title'=>_MANAGEMENT_GROUP, 'val'=>'admin_access' , 'len'=>'20%') , 
						  array('title'=>_TYPE , 'val'=>'iscore' , 'len'=>'20%') , 
					); 
	$replace = array(  "iscore"=>array(array("value"=>"0","name"=>_GENERAL),
	        		   				   array("value"=>"1","name"=>_CORE),
									   ),
					'admin_access' => $GLOBALS['access_level']
				);
		 
	$table_data=$conn->SelectArray('#__components',
				'id,name,parent,option_link,admin_access,iscore', ' WHERE parent=0 ORDER BY iscore');  
	$table_data = gui_array_replace($table_data,$replace);
	$gui->add("data_table_arr","maintable",$table_head,$table_data);

	$gui->add('hidden', 'option','', 'manage');
	//FIXME!
//	$gui->add('massops', 1);

	$gui->add("end_form");
	$gui->generate();					
}

?>