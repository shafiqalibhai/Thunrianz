<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function templates_table() {
global $d_root,$d_template;
$gui=new ScriptedUI();
$gui->add("form","adminform","","admin.php?com_option=templates");
$gui->add("com_header",_TEMPLATES_HEAD);

$table_head = array ( array('title'=>'radio' , 'val'=>'id' , 'len'=>'1%') ,
					  array('title'=>_NAME,'val'=>'name','len'=>'40%','info'=>_INFO,'ilink'=>'admin.php?com_option=templates&task=info&cid[]=ivar1','ivar1'=>'id') ,
					  array('title'=>_PUBLISHED,'val'=>'published','len'=>'10%','align'=>'center'),
					  array('title'=>_OWNER,'val'=>'author','len'=>'15%','mlink'=>'authorEmail') ,
					  array('title'=>_VER,'val'=>'version','len'=>'10%','align'=>'center') ,
					  array('title'=>_DATE,'val'=>'date','len'=>'8%','align'=>'center') ,
					  array('title'=>_WEBSITE,'val'=>'authorUrl','len'=>'20%','hlink'=>'authorUrl')
					 );
$table_req = array ( 'name'=>'name','author'=>'author','version'=>'version','date'=>'creationDate','authorEmail'=>'authorEmail','authorUrl'=>'authorUrl');
$table_data = read_dir_xml($d_root."templates/","template.xml",$table_req, 'templates/template');
$table_data = insert_published($table_data,"published",$d_template);
$gui->add("data_table_arr","maintable",$table_head,$table_data);
$gui->add("end_form");
$gui->generate();
}
function writable_string($filename) {
	return sprintf(_EDIT_STATUS, $filename, (is__writable($filename) ? _TEMPLATES_WRITE:_TEMPLATES_NWRITE) );
}

function edit_template($template) {
global $d_root;

$html_file=$d_root."templates/".$template."/index.php";
$css_file=$d_root."templates/".$template."/template.style.css";

$gui=new ScriptedUI();
$gui->add("form","adminform","","admin.php?com_option=templates");
$gui->add("hidden","template","",$template);
$gui->add("com_header",_TEMPLATES_EDIT);
$gui->add("tab_link","dtab");
$gui->add("tab_head");
$gui->add("tab",_TEMPLATES_EDIT_HTML,writable_string($html_file),"dtab");
//$gui->add("hidden","html_file","",$html_file);
$gui->add("textarea_big","template_html_data",_TEMPLATES_EDIT_HTML,xhtml_safe(file_get_contents($html_file)));
$gui->add("tab_end");
$gui->add("tab",_TEMPLATES_EDIT_CSS,writable_string($css_file),"dtab");
//$gui->add("hidden","css_file","",$css_file);
$gui->add("textarea_big","template_css_data",_TEMPLATES_EDIT_CSS,xhtml_safe(file_get_contents($css_file)));
$gui->add("tab_end");
//$gui->add("hidden","css_file","",$css_file);
//$gui->add("tab_end");
$gui->add("tab_tail");
$gui->add("end_form");
$gui->add("tab_sel","dtab","","1");

$gui->generate();
}

function info_template($template) {
	global $d_root, $d_subpath;

	$xml_file=$d_root."templates/".$template."/template.xml";
	$preview_path=$d_subpath."templates/".$template."/template_thumbnail.png";
	$table_req = array ( 'name'=>'name','author'=>'author','version'=>'version','date'=>'creationDate','authorEmail'=>'authorEmail','authorUrl'=>'authorUrl',"desc"=>"Description");
	$info=read_file_xml($xml_file, $table_req, 'templates/template');
	if (!$info) {
		global $pathway;
		$pathway->add(_TEMPLATES_INFO);
		echo _TEMPLATES_INFO_ERROR;
		return;
	}
	
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=templates");
	$gui->add("com_header",_TEMPLATES_INFO);

	$gui->add("tab_head");
	$gui->add("tab_simple","",_TEMPLATES_INFO);
	$gui->add("html","","","<tr><td width = 20% ><img src=\"$preview_path\"></td><td valign=\"top\">");
	$gui->add("table","","",'', null,"cellpadding='5' cellspacing='2' ");
	$gui->add("text","",_NAME,$info['name']);
	$auth = $info['author'];
	if (strlen($info['authorEmail']))
		$auth .= ' (<a href="mailto:'.xhtml_safe($info['authorEmail']).'">'.$info['authorEmail'].'</a>)';
	$gui->add("text","",_AUTHOR, $auth);
	$gui->add("text","",_VER,$info['version']);
	$gui->add("text","",_CDATE,$info['date']);
	$url = $info['authorUrl'];
	if (strlen($url)) {
		$url = '<a target="_blank" href="'.xhtml_safe($url).'">'.$url.'</a>';
		$gui->add("text","",_WEBSITE,$url);
	}
	if (strlen($info['desc']))
		$gui->add("text","",_DESC,$info['desc']);
	$gui->add("end_table");
	$gui->add("html","","","</td></tr>");
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

?>