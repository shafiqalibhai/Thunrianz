<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function show_e_message($msg) {
	$gui=new ScriptedUI();
	$gui->add("com_header",_USERS_HEAD);
	$gui->add('text', '', '', $msg);
	$gui->generate();
}

function users_table() {
	global $conn,$access_level;

	$gui=new ScriptedUI();
	$gui->add("form","adminform",'','admin.php?com_option=user');
	$gui->add("com_header",_USERS_HEAD);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
							  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
							  array('title'=>_NAME,'val'=>'name','len'=>'40%','ilink'=>'admin.php?com_option=user&task=edit&cid[]=ivar1','ivar1'=>'id') ,
							  array('title'=>_USERS_UID,'val'=>'username','len'=>'10%','align'=>'center'),
							  array('title'=>_USER_ACTIVE,'val'=>'published','len'=>'10%','align'=>'center'),
							  array('title'=>_EMAIL,'val'=>'email','len'=>'10%','mlink'=>'email','align'=>'center'),
							  array('title'=>_LANGUAGE,'val'=>'lang','len'=>'10%','align'=>'center'),
							  array('title'=>_USERS_GRP,'val'=>'gid','len'=>'10%','align'=>'center')
							);
	$table_data=$conn->SelectArray('#__users', 'id,name,username,email,published,gid,lang');
	$replace = array( "gid" => $access_level, 'published' => array(array('name' => _NO, 'value' => 0), array('name'=> _YES, 'value' => 1) ));
	$table_data = gui_array_replace($table_data,$replace, array('name' => 'xhtml_safe', 'username' => 'xhtml_safe', 'email' => 'xhtml_safe'));
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();
}

function &form_available_languages($selected, $is_editing = false) {
	global $d;
	$tmp = $d->GetActiveLangs();
	$langs = array( array('value' => '', 'name' => '-- Auto --'));
	if (!count($tmp)) return $langs;
	
	global $d_root;
	$some_selected = false;
	foreach($tmp as $lid) {
		$xml = new AnyXML();
		$xml->fromString(file_get_contents($d_root.'lang/'.$lid.'/language.xml'));
		$e = $xml->getElementByPath('languages/language/name');
//		if (!isset($e))		continue;
		$dir = array('value' => $lid, 'name'=> $e->getValue());
		if ($lid == $selected) {
			$some_selected = true;
			$dir['selected'] = true;
		}
		$langs[] = $dir;
	}
	if ($is_editing && !$some_selected)
		$langs[0]['selected'] = true;
	return $langs;
}


function edit_users($id = null) {
	global $conn;

	if (isset($id)) {
		$rsar=$conn->SelectRow('#__users', '*', ' WHERE id='.$id);
		global $my;
		if ($my->gid<$rsar['gid']) {
			global $d;
			CMSResponse::Redir('admin.php?com_option=user', _UNAUTHORIZED_ACCESS);
			return;
		}
		$c_head = _USERS_EDIT_HEAD;
	}else {
		$rsar=array("id"=>"","name"=>"","username"=>"","email"=>"","gid"=>'1',"lang"=>"",  'timezone' => _CMS_DEFAULT_TIMEZONE);
		$c_head = _USERS_NEW_HEAD;
	}

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=user");
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head);
	$gui->add("hidden","user_id","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","user_name",_NAME,xhtml_safe($rsar['name']),$v);
	$gui->add("textfield","user_user",_USER_NAME, xhtml_safe($rsar['username']),$v);
	$gui->add("textfield","user_email",_EMAIL,xhtml_safe($rsar['email']),$v);
	$gui->add("select","user_lang",_LANGUAGE,form_available_languages($rsar['lang'], isset($id)));
	if (substr(phpversion(), 0, 1)!='4') {
		global $d_root;
		include $d_root.'includes/i18n/timezones.php';
	}
	$gui->add("select","user_tz", _USER_TIMEZONE, timezones_array($rsar['timezone']));

	$al = $GLOBALS['access_level'];
	$group_arr = select($al,$rsar['gid']);
	unset($group_arr[0]);
	array_pop($group_arr);
	global $my;
	for($gid = $my->gid+1;$gid<6;++$gid) unset($group_arr[$gid]);
	$gui->add("select","user_gid",_USERS_GRP,$group_arr);
	$gui->add('spacer');
	if (isset($id)) {
		$v = null;
		$gui->add('text', '', _USER_PASSWORD_DESC);
	}
	$gui->add("password","user_password",_PASSWORD, '',$v,' onkeypress="updateQualityMeter(this)"');
	global $d;

	$gui->add("text",'','', $d->place_pwd_pb('user_password', 'the_password', _CMS_PASSWORD_QUALITY) );
	$gui->add("password","user_password1",_USER_PASSWORD_CONFIRM,"",$v);
	// show the extended options only if we are editing a previously existing user
	if ($rsar['id']) {
		global $_DRABOTS;
		list($html, $r) = $_DRABOTS->trigger_ob('OnEditUserProfile', array(&$rsar));
		if (strlen($html)) {
			$gui->add('spacer');
			$gui->add('text', '', '', $html);
		}
	}
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");

	global $_DRABOTS;
	$results = $_DRABOTS->trigger('OnEditUserProfileHasFileUpload', array(&$rsar));
	$complex = false;
	foreach($results as $r) {
		$complex |= $r;
	}
	if ($complex) $gui->form_type = 'file';
	
	$gui->generate();
}

?>
