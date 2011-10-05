<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function edit_massmail() {
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=massmail");
	$gui->add("com_header",_MASSMAIL_HEAD);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_MASSMAIL_HEAD,"");
	$gui->add("text","",_MASSMAIL_HELP);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","massmail_subject",_MASSMAIL_SUBJECT,'',$v);
	$al = $GLOBALS['access_level'];
	$gids = select($al,1);
	array_shift($gids);
	array_pop($gids);
	$gui->add("select","massmail_gid",_MASSMAIL_EMAIL,$gids);
	$gui->add("boolean","massmail_inclusive",_MASSMAIL_INCLUSIVE, 1);
	global $d_force_text_email;
	if ($d_force_text_email)
		$gui->add("textarea","massmail_message",_MASSMAIL_NEWS);
	else {
		$gui->add("boolean","massmail_text",_MASSMAIL_TEXT, 0);
		$gui->add("htmlarea","massmail_message",_MASSMAIL_NEWS);
	}
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

?>