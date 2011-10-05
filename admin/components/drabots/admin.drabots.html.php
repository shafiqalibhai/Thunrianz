<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function drabots_manage_table() {
	global $conn;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=drabots");
	$gui->add("com_header",_DRABOTS_HEAD);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'2%','align'=>'center') ,
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>_NAME,'val'=>'name','len'=>'60%','ilink'=>'admin.php?com_option=drabots&task=edit&cid[]=ivar1','ivar1'=>'id'),
						  array('title'=>_ID , 'val'=>'element' , 'len'=>'10%') ,
						  array('title'=>_TYPE, 'val'=>'type' , 'len'=>'10%','align'=>'center') ,
						  array('title'=>_ACCESS , 'val'=>'access' , 'len'=>'10%','align'=>'center') ,
	  					  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center')
						);
	$replace = array("type"=>array(array("value"=>"content","name"=>_CONTENT),
					   				   array("value"=>"search","name"=>_SEARCH),
									   )
						);

	$table_data=$conn->SelectArray('#__drabots', 'id,name,element,type,access,ordering', $gui->Ordering());
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
//	$gui->add('massops');
	$gui->add("end_form");
	$gui->generate();
}

function drabot_edit($id) {
	global $conn,$d_root,$easydb;

	$rsar=$conn->SelectRow('#__drabots', 'id,name,element,showon,access,params', ' WHERE id = '.$id);

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=drabots");
	$gui->add("com_header",_DRABOTS_EDIT_HEAD." &gt; ".$rsar['name']);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_DRABOTS_EDIT_HEAD,"");
	$gui->add("hidden","drabot_id","",$rsar['id']);
	$rsam=$conn->GetArray("SELECT id,name,ordering FROM #__sections ORDER BY ordering ASC");
	$parent[]=array("name"=>"All","value"=>"0");
	foreach($rsam as $rowm)
		$parent[]=array("name"=>$rowm['name'],"value"=>$rowm['id']);
	if ($rsar['showon']=="")
		$parent=select($parent,0);
	else {
		$show_arr=explode("_",$rsar['showon'],strlen($rsar['showon']));
		$sel_link=false;
		foreach($show_arr as $enable) {
			if($enable=="") continue;
			else $sel_link[]=$enable;
		}
		$parent=select($parent,$sel_link);
	}
	$gui->add("listm","drabot_showon[]",_DRABOTS_EDIT_ACTIVE,$parent, null, ' size="6"');

	$gui->add("access","drabot_access",_ACCESS,$rsar['access']);

	$gui->add("tab_end");

	$gui->add("html","","","<br /><br />");

	$xml = $d_root."drabots/".$rsar['element'].".xml";

	global $my;
	$path = bot_lang($my->lang, $rsar['element']);
	if (file_exists($path)) {
		include $path;
	}

	//FIXME: hotfix for
	if (($rsar['element'] == 'forum_profile') && ($my->lang != 'en'))
		define('_FPROF_DEFAULT_INSTANCE', "Default forum component instance for related links");
	else if (is_file($xml))
		$gui->addxmlparams($xml,$rsar['params'], false, _DRABOTS_PARAMS, 'drabots/drabot');

	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

?>