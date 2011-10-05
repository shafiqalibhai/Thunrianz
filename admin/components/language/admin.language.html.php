<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Admin language component
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# html generation functions
#

include com_path('dklang');

function language_table() {
	global $d_root;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=language");
	global $d_subpath;
	$gui->add('text', '',
		'<a href="'.create_context_help_url('Administration/System/Languages_manager').'" target="_blank"><img src="'.
		$d_subpath.'admin/templates/default/images/i18n.png" border="0" />&nbsp;'.
		_LANGUAGE_TRANSLATE_INFO.'</a>');
	$gui->add('spacer');
	$gui->add("com_header",_AMENU_MYSITE_LMANAGE);

	$table_head = array ( array('title'=>'radio' , 'val'=>'id' , 'len'=>'1%') ,
						  array('title'=>_LANG,'val'=>'name','len'=>'60%','info'=>_INFO,'ilink'=>'admin.php?com_option=language&task=info&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_PUBLISHED,'val'=>'published','len'=>'10%') ,
						  array('title'=>_LANGUAGE_CC,'val'=>'id','len'=>'10%') ,
						  array('title'=>_OWNER,'val'=>'author','len'=>'10%','mlink'=>'authorEmail') ,
						  array('title'=>_VER,'val'=>'version','len'=>'9%','align'=>'center') ,
						  array('title'=>_DATE,'val'=>'date','len'=>'10%','align'=>'center') ,
						 );
	$table_req = array ( 'name'=>'name','author'=>'author','version'=>'version','date'=>'creationDate','authorEmail'=>'authorEmail');
	$td1 = read_dir_xml($d_root."lang/",'language.xml',$table_req, 'languages/language');
	$table_data = array();
	global $d_dlangs;
	foreach($td1 as $row) {
		$row['published'] = (strpos($d_dlangs, $row['id'])===false ? 1 : 0);
		$table_data[] = $row;
	} $td1 = null;
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();
}

include_once $d_root.'includes/safe_glob.php';

## creates the language editor form, also used for translation
function edit_language($lang) {
	global $d_root;

	$files = parse_lang_xml($lang, $lang_name);
	if (!$files)
		return;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=language");
	$gui->add('spacer');
	$gui->add("com_header",_LANG_EDIT_HEAD.' "'.$lang_name.'"');

	$dtab = in_cookie('dtab', 1);
	if (!lcms_ctype_digit($dtab))
		$dtab = 1;
	$dtab = (int)$dtab;
	
	$gui->add("tab_combo_link", "dtab", '', $dtab);
	$gui->add("tab_head");
	
	global $d;
//	$d->add_raw_js('var resource_flags = [];');
	
	// parse English definitions side by side with language's definitions
	for ($id=0;$id<count($files);$id++) {
		dklang_parse_definitions($gui, $lang, $files[$id], $id);
	}	
	
	$d->add_raw_js('
	var files_changed = ['.str_repeat('false, ', count($files)-2).' false];
	
	function resource_changed(file_index, def) {
		files_changed[file_index] = true;
		var fpanel = document.getElementById("fp"+file_index+def);
		fpanel.innerHTML = "'.js_enc("Updated").'";
		var pre=document.getElementById("pr"+file_index+def);
		pre.style.color = "black";
	}
	
	function language_submit() {
		document.body.style.cursor = "wait";
		var frm = document.getElementById("adminform");
		var m = null;
		var re = /^l[rf]\[(\d+)\]/;
		for (var fi=0;fi<frm.elements.length;fi++) {
			subobj = frm.elements[fi];
			if (!subobj.name) continue;
			m = subobj.name.match(re);
			if (!m) continue;
			if (files_changed[m[1]]) continue;
			subobj.disabled=true;
		}
		document.body.style.cursor = "";
		return true;
	}');

//	$rwlang = sprintf(_LANGUAGE_EDIT_STATUS,$lang_file,(is__writable($lang_file) ? _WRITE:_NWRITE));

	$gui->add("tab_tail");
	$gui->add("hidden","tab_num");
	$gui->add("hidden","lang",'',$lang);
	$gui->add("tab_combo_sel","dtab",'',$dtab);

	$gui->add("end_form");

	$gui->generate();
}

function info_language($lang) {
	global $d_root;

	$xml_file = $d_root.'lang/'.$lang.'/language.xml';
	$table_req = array ( 'name'=>'name','author'=>'author','version'=>'version','date'=>'creationDate','authorEmail'=>'authorEmail');
	if(!($info=read_file_xml($xml_file,$table_req,'languages/language'))) {
		echo _LANG_INFO_ERROR;
		return;
	}
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=language");
	$gui->add('spacer');
	$gui->add("com_header",_LANG_INFO_HEAD);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_LANG_INFO_HEAD,"");
	$gui->add("text","",_LANG,$info['name']);
	$gui->add("text","",_AUTHOR,$info['author']." (<a href=\"mailto:".$info['authorEmail']."\">".$info['authorEmail']."</a>)");
	$gui->add("text","",_VER,$info['version']);
	$gui->add("text","",_CDATE,$info['date']);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function new_language($lang) {
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=language");
	$gui->add('spacer');
	$gui->add("com_header",_LANGUAGE_CREATE);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_LANG_INFO_HEAD,"");
	$gui->add("text","",sprintf(_LANGUAGE_INFO_CLONE, '<strong>'.raw_strtoupper($lang).'</strong>'),"");
	$gui->add('spacer');

	global $d_root;
	require_once $d_root.'includes/i18n/languages.php';
	$cc_drop=select($cc, $lang);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("select","lang_cc",_LANGUAGE_CC,$cc_drop,$v);
	$gui->add("text","",_LANGUAGE_INFO_CC."<a target=\"_blank\" href=\"http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html\">ISO 3166</a>");
	$gui->add('spacer');
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","lang_name",_NAME,'',$v);
	$gui->add("textfield","lang_version",_LANGUAGE_VERSION,'0.1',$v);
	$gui->add("textfield","lang_author",_AUTHOR,'',$v);
	$gui->add("textfield","lang_email",_EMAIL);
	
	$gui->add("hidden","lang_base",'',$lang);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

global $_ACC;

function _acc_cb($s) {
	global $_ACC;
	$_ACC .= $s.'<br />';
}

function atomic_form() {
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=language");
	$gui->add('spacer');
	global $_ACC;
	$gui->add("text","",'', $_ACC);
	$_ACC = null;
	$gui->add("end_form");
	$gui->generate();
}

?>