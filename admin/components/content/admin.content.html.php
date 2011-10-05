<?php if (!defined('_VALID')) {header('Status: 404 Not Found');die;}

function sections_table() {
	global $conn;

	$gui = new ScriptedUI();
	$gui->add("form", "adminform");
	$gui->add('spacer');
	$gui->add("com_header", _CONTENT_SECTION_HEAD);
	$table_head = array (array('title' => '#' , 'val' => 'id' , 'len' => '1%', 'align' => 'center') ,
	    array('title' => 'checkbox' , 'val' => 'id' , 'len' => '1%', 'align' => 'center') ,
	    array('title' => _NAME, 'val' => 'name', 'len' => '50%', 'bval' => 'title', 'ilink' => 'admin.php?com_option=content&task=edit&cid[]=ivar1', 'ivar1' => 'id') ,
	    array('title' => _PUBLISHED, 'val' => 'published', 'len' => '10%', 'align' => 'center'),
	    array('title' => _RECORDS , 'val' => 'count' , 'len' => '10%', 'align' => 'center') ,
	    array('title' => _ACCESS, 'val' => 'access', 'len' => '10%', 'align' => 'center') ,
	    array('title' => _ORDERING, 'val' => 'ordering', 'len' => '10%', 'align' => 'center')
	    );
	global $access_acl;
	$table_data = $conn->SelectArray('#__sections', 'id,title,name,published,access,count,ordering', ' WHERE '.$access_acl.' '.$gui->Ordering());
	$gui->add("data_table_arr", "maintable", $table_head, $table_data);
//	$gui->add("massops");
	$gui->add("end_form");
	$gui->generate();
}

function edit_section($id) {
	global $conn;

	if (isset($id)) {
		$rsar = $conn->SelectRow('#__sections', 'id,title,name,image,image_position,description,access', " WHERE id=".$id);
		$c_head = _CONTENT_SECTION_EDIT_HEAD;
	} else {
		$rsar = array("id" => '', "title" => '', "name" => '', "image" => '', "image_position" => "left", "description" => '', "access" => "0");
		$c_head = _CONTENT_SECTION_NEW_HEAD;
	}
	$gui = new ScriptedUI();
	$gui->add("form", "adminform", '', "admin.php?com_option=content");
	$gui->add('spacer');
	$gui->add("com_header", $c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple", '', $c_head, '');
	$gui->add("hidden", "section_id", '', $rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield", "section_title", _TITLE, $rsar['title'], $v);
	$gui->add("textfield", "section_name", _NAME, $rsar['name'],$v);
	$img_arr = select_array('media/icons/', _SELECTIMAGE, $rsar['image'], 'file', $GLOBALS['d_pic_extensions']);
	$gui->add("list_image", "section_image", _SELECTIMAGE, $img_arr, null, 'media/icons/');
	$gui->add('file', 'section_image_upload');
	$pos = pos_array($rsar['image_position']);
	$gui->add("select", "section_image_position", _IMAGEPOS, $pos);
	$gui->add("access","section_access",_ACCESS,$rsar['access']);
	$gui->add("textarea", "section_description", _DESC, $rsar['description']);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function categories_table($sec_id) {
	global $conn;
	$gui = new ScriptedUI();
	$gui->add("form", "adminform", '', "admin.php?com_option=content&option=categories&sec_id=$sec_id");
	$row = $conn->SelectRow('#__sections', 'title', " WHERE id = $sec_id");
	$gui->add("com_header", sprintf(_CONTENT_MANAGE_CATEGORIES, $row['title']));
	$table_head = array (array('title' => '#' , 'val' => 'id' , 'len' => '1%', 'align' => 'center') ,
	    array('title' => 'checkbox' , 'val' => 'id' , 'len' => '1%', 'align' => 'center') ,
	    array('title' => _NAME, 'val' => 'name', 'len' => '60%', 'ilink' => 'admin.php?com_option=content&option=categories&task=edit&sec_id=ivar1&cid[]=ivar2', 'ivar1' => 'section', 'ivar2' => 'id',
	    'explore' => 'admin.php?com_option=content&option=items&sec_id=ivar1&cid[]=ivar2') ,
	    array('title' => _RECORDS , 'val' => 'count' , 'len' => '10%', 'align' => 'center') ,
	    array('title' => _ACCESS, 'val' => 'access', 'len' => '10%', 'align' => 'center') ,
	    array('title' => _EDITGROUP, 'val' => 'editgroup', 'len' => '10%', 'align' => 'center') ,
	    array('title' => _ORDERING, 'val' => 'ordering', 'len' => '10%', 'align' => 'center')
	    );
	global $edit_sql;
	$table_data = $conn->SelectArray('#__categories',
'id,name,section,ordering,access,editgroup,count',
						" WHERE section=$sec_id $edit_sql ".$gui->Ordering());
	$gui->add("data_table_arr", "maintable", $table_head, $table_data);
//	$gui->add('massops');
	$gui->add("end_form");
	$gui->generate();
}

function edit_categories($sec_id, $cid = null) {
	global $conn, $d_root;
	$row = $conn->SelectRow('#__sections', 'title', " WHERE id = $sec_id");
	$sec_title = $row['title'];
	if (isset($cid)) {
		$rsar = $conn->SelectRow('#__categories', 'id,name,image,image_position,description,access,editgroup', ' WHERE id='.$cid);
		$c_head = _CONTENT_CAT_EDIT_HEAD;
	} else {
		$rsar = array("id" => '', "name" => '', "description" => '', "access" => "0", "image" => '', "image_position" => "left", 'editgroup' => '3');
		$c_head = _CONTENT_CAT_NEW_HEAD;
	}
	$gui = new ScriptedUI();
	$gui->add("form", "adminform", '', 'admin.php?com_option=content&option=categories&sec_id='.$sec_id);
	$gui->add("com_header", "$sec_title &gt; $c_head");
	$gui->add("tab_head");
	$gui->add("tab_simple", '', $c_head, '');
	$gui->add("hidden", "section_id", '', $rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield", "section_name", _NAME, $rsar['name'],$v);
	$img_arr = select_array('media/icons/', _SELECTIMAGE, $rsar['image'], 'file',$GLOBALS['d_pic_extensions']);
	$gui->add("list_image", "section_image", _SELECTIMAGE, $img_arr, null, 'media/icons/');
	$gui->add('file', 'section_image_upload');
	$pos = pos_array($rsar['image_position']);
	$gui->add("select", "section_image_position", _IMAGEPOS, $pos);
	$gui->add("access","section_access",_ACCESS,$rsar['access']);
	$gui->add("access","section_editgroup",_EDITGROUP,$rsar['editgroup']);
	$gui->add("textarea", "section_description", _DESC, $rsar['description']);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

/* archive system interface */
function archive_items_table() {
	global $conn, $sec_id;
	$row = $conn->SelectRow('#__sections', 'title', ' WHERE id='.$sec_id);
	$sec_title = $row['title'];
	$gui = new ScriptedUI();
	$gui->add("form", "adminform", '', "admin.php?com_option=content&option=items&sec_id=$sec_id");
	$gui->add("com_header", $sec_title.' &gt;'._CONTENT_ARCHIVE_HEAD);
	$table_head = array (array('title' => '#' , 'val' => 'id' , 'len' => '1%', 'align' => 'center') ,
		array('title' => 'checkbox' , 'val' => 'id' , 'len' => '1%', 'align' => 'center') ,
		array('title' => _TITLE, 'val' => 'title', 'len' => '70%', 'ilink' => 'admin.php?com_option=content&option=items&task=edit&sec_id=' . $sec_id . '&cid[]=ivar1', 'ivar1' => 'id') ,
		array('title' => _CAT, 'val' => 'catid', 'len' => '30%', 'align' => 'center'),
	    );
	global $access_sql;
	$table_data = $conn->SelectArray('#__content', 'id,title,sectionid,catid,created,published,access,hits',
								" WHERE sectionid=$sec_id AND published=4 $access_sql ".
								$gui->Ordering());
	$cat_arr = category_array($sec_id, "-1");
	$replace = array("catid" => $cat_arr);
	$table_data = gui_array_replace($table_data, $replace);
	$gui->add("data_table_arr", "maintable", $table_head, $table_data);
	$gui->add("end_form");
	$gui->generate();
}

/* items interface */
function items_table($sec_id = null, $cid = null) {
	global $conn, $time;
	if (isset($cid)) {
		$crow = $conn->SelectRow('#__categories', 'section', ' WHERE id='.$cid);
		if (!$crow) {
			CMSResponse::NotFound();
			return;
		}
		$sec_id = $crow['section'];
		if (!lcms_ctype_digit($sec_id)) {
			CMSResponse::BadRequest();
			return;
		}
		$row = $conn->SelectRow('#__sections', 'title', ' WHERE id='.$sec_id);
		$where = ' WHERE catid='.$cid.' AND published<>4';
	} else {
		// if no section provided, choose the first section - caused by com_start
		if (!isset($sec_id)) {
			$row = $conn->SelectRow('#__sections', 'id,title');
			if (!$row) {
				global $d;
				CMSResponse::NotFound();
				return;
			}
			$sec_id = $row['id'];
		} else {
			$row = $conn->SelectRow('#__sections', 'title', ' WHERE id='.$sec_id);
			if (!$row) {
				global $d;
				CMSResponse::NotFound();
				return;
			}
		}
		$where = ' WHERE sectionid='.$sec_id.' AND published<>4';
	}
	$sec_title = $row['title'];
	$gui = new ScriptedUI();
	$gui->has_frontpage = true;
	$gui->add("form", "adminform", '', "admin.php?com_option=content&option=items&sec_id=$sec_id".
		(isset($cid) ? '&cid[]='.$cid : ''));
	$gui->add("com_header", $sec_title.' &gt; '._CONTENT_ITEM_HEAD);
	$table_head = array (array('title' => '#' , 'val' => 'id' , 'len' => '1%', 'align' => 'center') ,
	    array('title' => 'checkbox' , 'val' => 'id' , 'len' => '1%', 'align' => 'center') ,
	    array('title' => _TITLE, 'val' => 'title', 'len' => '50%', 'ilink' => 'admin.php?com_option=content&option=items&task=edit&sec_id=' . $sec_id . (isset($cid) ? '&catid='.$cid : '').
	    '&cid[]=ivar1', 'ivar1' => 'id') ,
	    array('title' => _PUBLISHED, 'val' => 'published', 'len' => '10%', 'align' => 'center'),
	    array('title' => _CAT, 'val' => 'catid', 'len' => '10%', 'align' => 'center'),
	    array('title' => _ACCESS, 'val' => 'access', 'len' => '10%', 'align' => 'center') ,
	    array('title' => _OWNER, 'val' => 'userid', 'len' => '10%', 'align' => 'center') ,
	    array('title' => _FRONTPAGE, 'val' => 'frontpage', 'len' => '10%', 'align' => 'center') ,
	    array('title' => _ORDERING, 'val' => 'ordering', 'len' => '10%', 'align' => 'center')
	    );
//	$gui->order = -1;
	$table_data = $conn->SelectArray('#__content', 'id,title,catid,published,frontpage,ordering,access,userid,hits', $where.$gui->Ordering());
	$cat_arr = category_array($sec_id, "-1");
	$replace = array("catid" => $cat_arr);
	$table_data = gui_array_replace($table_data, $replace, array('userid' => 'username_by_id'));
	$gui->add("data_table_arr", "maintable", $table_head, $table_data);
	
	if (isset($cid))
		$gui->add('hidden', 'catid', '', $cid);

//	$gui->add('massops');

	$gui->add("end_form");
	$gui->generate();
}

function edit_items($sec_id, $cid = null, $catid = null) {
	global $conn, $time, $my;

	$row = $conn->SelectRow('#__sections', 'title', " WHERE id=".$sec_id);
	$sec_title = $row['title'];

	if (isset($cid)) {
		$rsar = $conn->SelectRow('#__content', '*', ' WHERE id='.$cid);
		if (empty($rsar)) {
			CMSResponse::BadRequest();
			return;
		}
		$c_head = _CONTENT_ITEM_EDIT_HEAD;
	} else {
		$rsar = array("id" => '', "title" => '', "title_alias" => '', "catid" => '', "created" => $time, "modified" => $time, "name" => '', "description" => '', "introtext" => '', "bodytext" => '', "access" => "0", "mask" => 0, "created_by_alias" => $my->name, "frontpage" => '', "metakey" => '', "metadesc" => '', 'published' => '0');
		$c_head = _CONTENT_ITEM_NEW_HEAD;
	}
	$gui = new ScriptedUI();
	$gui->add("form", "adminform", '', "admin.php?com_option=content&option=items&sec_id=$sec_id");
	$gui->add('spacer');
	$gui->add("com_header", "$sec_title &gt; $c_head");
	$gui->add("tab_link", "dtab");
	$gui->add("tab_head");
	$gui->add("tab", _CONTENT, $c_head, "dtab");
	if (isset($catid))
		$gui->add("hidden", "catid", '', $catid);
	$gui->add("hidden", "content_id", '', $rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield", "content_title", _TITLE, $rsar['title'], $v);
	$gui->add("textfield", "content_title_alias", _CONTENT_ITEM_CONTENT_TALIAS, $rsar['title_alias']);
	$cat_drop = category_array($sec_id, $rsar['catid']);
	$gui->add("select", "content_catid", _SELECTCAT, $cat_drop, $v);
	$gui->add("hidden", "content_ocatid", '', $rsar['catid']);
	global $_DRABOTS;
	$_DRABOTS->loadCoreBotGroup('editor');
	$_DRABOTS->trigger('OnContentEdit', array(&$rsar['introtext']));
	$_DRABOTS->trigger('OnContentEdit', array(&$rsar['bodytext']));
	$gui->add("htmlarea", "content_introtext", _CONTENT_ITEM_CONTENT_INTRO, $rsar['introtext'], $v);
	$gui->add("htmlarea", "content_bodytext", _CONTENT_ITEM_CONTENT_COMPLETE, $rsar['bodytext']);
	$gui->add("tab_end");
	$gui->add("tab", _CONTENT_ITEM_PUBLISH, _CONTENT_ITEM_PUBLISH_EXP, "dtab");
	if ($rsar['published']<=2) {
		if ($rsar['published'] == 2)
			$published = '0';
		else
			$published = $rsar['published'];
		$gui->add("boolean", "content_published", _PUBLISHED, $published);
	} else  {
		$gui->add('text', '', '', _CONTENT_ARCHIVED_NO_PUB);
		$gui->add('hidden', 'content_published', '', (string)$rsar['published']);
	}
	$gui->add("boolean", "content_frontpage", _CONTENT_ITEM_PUBLISH_FRONTPAGE, (string)$rsar['frontpage']);
	$gui->add("access","content_access",_ACCESS,$rsar['access']);
	$gui->add("textfield", "content_created_by_alias", _CONTENT_ITEM_PUBLISH_AALIAS, $rsar['created_by_alias']);

	$gui->add("date", "content_created", _CDATE, $rsar['created']);
	$gui->add("date", "content_modified", _MDATE, $time);
	$gui->add("tab_end");
   
	$gui->add("tab", _CONTENT_OPTIONS, '&nbsp;', "dtab");
	$gui->add('text', '', _CONTENT_OPTIONS_DESC);
	$gui->add('spacer');
	$flags = content_flags($rsar['mask']);
	$gui->add("boolean", "content_opts_hide_title", _CONTENT_ITEM_HIDE_TITLE, $flags['hide_title']);
	$gui->add("boolean", "content_opts_hide_email", _CONTENT_ITEM_HIDE_EMAIL, $flags['hide_email']);
	$gui->add("boolean", "content_opts_hide_print", _CONTENT_ITEM_HIDE_PRINT, $flags['hide_print']);
	$gui->add("boolean", "content_opts_hide_author", _CONTENT_ITEM_HIDE_AUTHOR, $flags['hide_author']);
	$gui->add("boolean", "content_opts_hide_created", _CONTENT_ITEM_HIDE_CREATED, $flags['hide_created']);
	$gui->add("boolean", "content_opts_hide_modified", _CONTENT_ITEM_HIDE_MODIFIED, $flags['hide_modified']);
	$gui->add("boolean", "content_opts_hide_permalink", _CONTENT_ITEM_HIDE_PERMALINK, $flags['hide_permalink']);
	$gui->add("boolean", "content_opts_hide_pdf", _CONTENT_ITEM_HIDE_PDF, $flags['hide_pdf']);
	$gui->add("tab_end");
   
	$gui->add("tab", _CONTENT_ITEM_META, _CONTENT_ITEM_META_EXP, "dtab");
	$gui->add("textarea", "content_metakey", _CONTENT_ITEM_META_KEYWORDS, $rsar['metakey']);
	global $d;
	$gui->add('text', '', '<input type="button" onclick="javascript:'.$d->EditorSaveJSMultiple(array('content_introtext', 'content_bodytext')).'ak_fill(document.adminform)" value="'._A_CONTENT_AUTO_FILL.'">');
	$gui->add("textarea", "content_metadesc", _CONTENT_ITEM_META_DESC, $rsar['metadesc']);
	$gui->add('text', '', '<input type="button" onclick="javascript:'.$d->EditorSaveJSMultiple(array('content_introtext', 'content_bodytext')).'ad_fill(document.adminform)" value="'._A_CONTENT_AUTO_FILL.'">');
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->add("tab_sel", "dtab", '', "1");
	global $d;
	$d->add_js('lang/'.$my->lang.'/js/commonwords.js');
	$d->add_js('components/content/content.autokeywords.js');
	
	$gui->generate();
}

function update_menu_category($id, $title, $old_title) {
	global $conn;
	$conn->Update('#__menu', 'name=\''.$title."'", ' WHERE link LIKE \'%&id='.$id.'%\' AND link_type=\'cc\' AND name=\''.sql_encode($old_title)."'");
}

function update_menu_section($id, $title, $old_title) {
	global $conn;
	$conn->Update('#__menu', 'name=\''.$title."'", ' WHERE link LIKE \'%&id='.$id.'%\' AND link_type=\'cs\' AND name=\''.sql_encode($old_title)."'");
}

?>