<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function frontpage_table() {
	global $conn;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=frontpage");
	$gui->add("com_header",_FRONTPAGE_HEAD);
	$gui->add("text",'', _FRONTPAGE_HEAD_DESC);
	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>_TITLE,'val'=>'title','len'=>'70%') ,
						  array('title'=>_PUBLISHED,'val'=>'published','len'=>'10%','align'=>'center'),
						  array('title'=>_ACCESS,'val'=>'access','len'=>'10%','align'=>'center') ,
						  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center')
						 );
//	$gui->order = -1;
	$rs = $conn->Select('#__content_frontpage', 'id,ordering', $gui->Ordering());
	
	$table_data = array();
	while ($rsa = $rs->GetArray(1)) {
		$rsca=$conn->GetArray('SELECT title,published,access FROM #__content WHERE id ='.$rsa[0]['id']);
/*		if (empty($rsca)) {
			$err = 'Frontpage item '.$rsa[0]['id'].' not found in #__content table, additional information:<pre>'.print_r($rsa, true).'<hr>';
			$r = $conn->GetArray('SELECT id FROM #__content');
			$err.=print_r($r).'</pre>';
			trigger_error($err);
		}*/
		$table_data[] = array_merge($rsa[0], $rsca[0]);
	}

$gui->add("data_table_arr","maintable",$table_head,$table_data);
//$gui->add('massops');
$gui->add("end_form");
$gui->generate();
}

?>
