<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function default_group() {
	global $d_root;
	$gid = @filegroup($d_root.'install');
	if ($gid === FALSE)
		return _CONFIG_CHECK_GROUP;
	return sprintf(_CONFIG_DEFAULT_GROUP, (string)$gid);
}

function default_owner() {
	global $d_root;
	$uid = @fileowner($d_root.'install');
	if ($uid === FALSE)
		return _CONFIG_CHECK_OWNER;
	return sprintf(_CONFIG_DEFAULT_OWNER, (string)$uid);
}

function show_config($writable) {
	global $d_root, $d_private;
	include $d_root.$d_private.'config.php';
	
	if (!$writable)
		$extra = ' disabled="disabled"';
	else
		$extra = '';

	$gui=new ScriptedUI();
	$gui->add("form","adminform");
	$gui->add("com_header",_CONFIG_HEAD);
	if (!$writable) {
		$gui->add('text', '', fix_root_path($d_root.$d_private)._CONFIG_NOT_WRITABLE);
		$gui->add('spacer');
	}
	$gui->add("tab_link","dtab");
	
	$gui->add("tab_head");
	$gui->add("tab",_CONFIG_GENERAL,_CONFIG_GENERAL_EXP,"dtab");

	$gui->add("boolean","ngzip",_CONFIG_SERVER_GZIP,$d_gzip,null, $extra);
	
	$gui->add("textfield","nmax_upload_size",
	_CONFIG_MAX_FILE_UPLOAD,
	$d_max_upload_size?$d_max_upload_size:convert_bytes(return_bytes(@ini_get('upload_max_filesize'))),null,$extra);
//	$gui->add("textfield","nmax_upload_quota",_CONFIG_MAX_UPLOAD_QUOTA,$d_max_upload_quota,null,$extra);

	$reporting_options = array(
		array('name'=>_CONFIG_SIMPLE, 'value'=>'0'),
		array('name'=>_CONFIG_DEBUG, 'value'=>'1'),
//		array('name'=>_CONFIG_AUTO_SUBMIT, 'value'=>'2')
		);
		
	$reporting_options = select($reporting_options, $GLOBALS['d_error_report']);
	$gui->add("select","nerror_report",_CONFIG_SERVER_ERROR, $reporting_options,null,$extra);
	
	$gui->add("textfield","ndocs_server",_CONFIG_SERVER_DOCUMENTATION,$d_docs_server,null,$extra);
	$gui->add("boolean","nstats",_CONFIG_SITE_STATS, $d_stats,null,$extra);

	$sv = new ScriptedUI_Validation();
	$sv->max = 255;
	$sv->min = 0;
	$gui->add("textfield","nseo",_CONFIG_SERVER_SEO,$d_seo,$sv,$extra);
	
	$idv = new ScriptedUI_Validation();
	$idv->required = $idv->not_empty = false;
	$idv->digits = true;
	$idv->min = 0;
	$idv->max = 12;
	
	$gui->add("textfield",'nsetmode',_CONFIG_WRITE_MODE,$d_setmode, $idv, $extra);
	$gui->add("textfield",'nsetowner',sprintf(_CONFIG_SET_OWNER, default_owner()),$d_setowner, $idv, $extra);
	$gui->add("textfield",'nsetgroup',sprintf(_CONFIG_SET_GROUP, default_group()),$d_setgroup, $idv, $extra);
	$auth_options = array(
		array('name' => _CONFIG_HTTP_AUTH_NONE, 'value' => '0'),
		array('name' => _CONFIG_HTTP_AUTH_BASIC, 'value' => '1')
	);
	// prevent Digest auth to be enabled for PHP4
	if (strnatcmp(phpversion(), '5.1')<0) {
		if ($d_http_auth==2)
			$d_http_auth = 0;
	} else
		$auth_options[] = array('name' => _CONFIG_HTTP_AUTH_DIGEST, 'value' => '2');
	$auth_options = select($auth_options, $d_http_auth);
	
	$gui->add("select","nhttp_auth",_CONFIG_HTTP_AUTH,$auth_options,null,$extra);

/*	if (!function_exists('ldap_connect')) {
		$d_ldap_auth='0';
		$ldap_extra=' disabled="disabled"';
	} else $ldap_extra = $extra;
	$gui->add("boolean","nldap_auth",_CONFIG_LDAP_AUTH, $d_ldap_auth,null, $ldap_extra); */
	
	$gui->add("boolean","nclear_pw",_CONFIG_CLEAR_PW, $d_clear_pw,null, $extra);
	
	$gui->add("boolean","nresource_deny",_CONFIG_RESOURCE_DENY,$d_resource_deny,null, $extra);
	
	$gui->add('spacer');

	$gui->add("boolean","ncache",_CONFIG_PAGECACHE_ENABLE_CACHE,$d_cache,null, $extra);
	$gui->add("boolean","ncache_debug",_CONFIG_PAGECACHE_CACHE_ENABLE_DEBUG,$d_cache_debug,null, $extra);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","ntemp",_CONFIG_TEMP_PATH,$d_temp,$v,$extra);
	$gui->add("boolean","nsqldebug",_CONFIG_SQLDEBUG_ENABLE,$d_sqldebug, null, $extra);	
	$gui->add("tab_end");
	
	$gui->add("tab",_CONFIG_SITE,_CONFIG_SITE_EXP,"dtab");
	$gui->add("textfield","nwebsite",_CONFIG_SITE_URL,$d_website,$v,$extra);
	$gui->add("textfield","ntitle",_CONFIG_SITE_TITLE,$d_title,$v,$extra);
	$gui->add("boolean","nonline",_CONFIG_SITE_ONLINE, $d_online,null,$extra);
	$gui->add("text","",_CONFIG_SEARCH_INTRO);
	$gui->add("textfield","nkeywords",_CONFIG_SEARCH_KEYWORDS,$d_keywords,null,$extra);
	$gui->add("textarea","ndesc",_CONFIG_SEARCH_DESC,$d_desc,null,$extra);
	$gui->add("textarea","noffline_msg",_CONFIG_SITE_OFFMESSAGE,$d_offline_msg);
	$gui->add("tab_end");
	
	$gui->add("tab",_CONFIG_LOCALE,_CONFIG_LOCALE_EXP,"dtab");

	include_once $d_root.'includes/langsel.php';

	$lang_sel=select_language('ndeflang', $d_deflang, '', '', false);
	$gui->add("text",'',_CONFIG_LOCALE_FALLBACK_LANGUAGE_DESC);
	$gui->add("text",'',_CONFIG_LOCALE_FALLBACK_LANGUAGE, $lang_sel);
	
	$gui->add("tab_end");

	$gui->add("tab",_CONFIG_DB,_CONFIG_DB_EXP,"dtab");
	
	$gui->add("text",'',_CONFIG_DB_DESC.'<hr />');

	ob_start();
	$has_db = true;
	$_SESSION['installing'] = true;	// will allow test db button
	global $my;
//	$gui->add('html', '<tr><td>');
	include $d_root.'lang/'.$my->lang.'/admin/includes/dbsettings.php';
	include $d_root.'admin/includes/dbsettings.php';
	$gui->add('html','','',ob_get_clean());
//	$gui->add('html', '</td></tr>');

	$gui->add("tab_end");
	
	$gui->add("tab",_CONFIG_BACKEND,_CONFIG_BACKEND_EXP,"dtab");

	$dv = new ScriptedUI_Validation();
	$dv->min_value=1;
	$dv->required = $dv->not_empty = true;
	$gui->add("textfield","nshow_count",_CONFIG_LOCALE_SHOWCOUNT,$d_show_count,$dv,$extra);
	$gui->add("boolean","nview_filter",_CONFIG_VIEW_FILTER,$d_view_filter,null, $extra);

	$gui->add("tab_end");

	$gui->add("tab",_EMAIL,_CONFIG_EMAIL_EXP,"dtab");
	$event_opts = array(
		array('name' => _NONE, 'value' => 0),
		array('name' => _CONFIG_EMAIL_NOTIFY_ADMINS_ONLY, 'value' => 1),
		array('name' => _CONFIG_EMAIL_NOTIFY_MANAGERS_ONLY, 'value' => 2),
		array('name' => _CONFIG_EMAIL_NOTIFY_ADMINS_MANAGERS, 'value' => 3)
	);
	$event_opts = select($event_opts, $d_event);
	$gui->add("select","nevent",_CONFIG_SERVER_EVENT,$event_opts,null,$extra);
	$gui->add("boolean","nemail_split",_CONFIG_EMAIL_SPLIT,$d_email_split,null,$extra);
	$hc_opts = array(
		array('name' => _NONE, 'value' => 0),
		array('name' => _CONFIG_EMAIL_HASHCASH_SINGLE, 'value' => 1),
		array('name' => _CONFIG_EMAIL_HASHCASH_MULTI, 'value' => 2)
	);
	$gui->add("select","nemail_hashcash", _CONFIG_EMAIL_HASHCASH, select($hc_opts, $d_email_hashcash), null,$extra);
	
	$gui->add("textfield","nemail_from",_CONFIG_EMAIL_FROM,$d_email_from,$v,$extra);
	$gui->add("textfield","nemail_name",_CONFIG_EMAIL_FROM_NAME,$d_email_name,$v,$extra);
	
	// test button for emailing system
	global $d;
	$d->add_raw_js('function send_test_email() {
		var f2=document.mc;
		f2.submit();
	}');
	$gui->add('spacer');
	$gui->add("boolean","nforce_text_email",_CONFIG_FORCE_TEXT_EMAIL,$d_force_text_email,null,$extra);
	$gui->add("boolean","nemail_text",_CONFIG_EMAIL_TEXT,(!$d_force_text_email && $d_email_text),null, /*$extra*/ ' disabled="disabled"');
	$gui->add('spacer');
	$gui->add("text", '', '<input type="button" value="'._CONFIG_TEST_EMAIL.'" onclick="send_test_email()" />');
//	$gui->add("tab_end");

	$gui->add("tab_end");
	
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->add("tab_selc","dtab","","1");

	$gui->generate();
	//	<input type="hidden" value="" name="mailer">
	?><form id="mc" name="mc" method="get" action="admin2.php" target="_blank">
	<input type="hidden" value="1" name="no_html"/>
	<input type="hidden" value="config" name="com_option"/>
	<input type="hidden" name="task" value="test_email"/>
	</form><?php
	dbtest_form();
}

?>