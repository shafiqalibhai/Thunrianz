<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function dir_table($view_dir='') {
	global $conn,$d_root, $fm_subpath;
	
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=fm&view=$view_dir");
	$gui->add("com_header",_FM_HEADER);
	$gui->add('spacer');
	$gui->add("com_info",'<h3><strong>::/</strong>'.$view_dir.'</h3>');
	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
	                                          array('title'=>'checkbox' , 'val'=>'path' , 'len'=>'1%','align'=>'center') ,
	                                          array('title'=>_NAME,'val'=>'id','len'=>'60%','ilink'=>'admin.php?com_option=fm&view=ivar1','ivar1'=>'path') ,
	                                          array('title'=>_SIZE,'val'=>'size','len'=>'10%','align'=>'center'),
	                                          array('title'=>_FM_PERMISSION,'val'=>'perms','len'=>'15%','align'=>'center'),
	                                           array('title'=>_CDATE,'val'=>'created','date'=>'1','len'=>'15%','align'=>'center')
	                                         );
		//TODO: use raw_read_dir
		$table_dir=read_dir($d_root.$fm_subpath.$view_dir,"dir",true);
		function fold_pic($s) {
			return '<img src="'.admin_template_pic($s).'" border="0" alt="" />';
		}
		// remove root path, add trailing slash and directory icon
		$rl = strlen($d_root.$fm_subpath);
		$c=count($table_dir);
		for($i=0;$i<$c;++$i) {
//			$table_dir[$i]['id'].='/';
			$table_dir[$i]['id']=fold_pic('folder.png').$table_dir[$i]['id'].'/';
			$table_dir[$i]['path'] = substr($table_dir[$i]['path'], $rl);
		}
		if($view_dir!=='') {
			$p = substr(dirname($d_root.$fm_subpath.$view_dir), $rl);
			if (strlen($p)) $p.='/';
			array_unshift($table_dir,
				array( 'id'=>fold_pic('back.png').'..','path'=>$p,'name'=>'..','size'=>_NA,
				'created'=>0, 'perms'=>_NA));
		}
		
		// remove root path
		$table_files = read_dir($d_root.$fm_subpath.$view_dir,"file",true);
		$c=count($table_files);
		for($i=0;$i<$c;++$i) {
			$table_files[$i]['path'] = substr($table_files[$i]['path'], $rl);
		}

	$table_data=array_merge($table_dir, $table_files);
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();
}

function edit_file($path) {
	global $d, $task, $d_root, $fm_subpath;
	
	$full_path = $d_root.$fm_subpath.$path;
	$dir=dirname($path)."/";
	
	if ($task=='new') {
		$thead = _FM_NEW_FILE;
		$tdesc = _FM_NEW_FILE_DESC;
		$data='';
	} else {
		$thead = _FM_EDIT_FILE;
		$tdesc = _FM_EDIT_DESC;
		if (!is_image($path))	
			$data=file_get_contents($full_path);
	}
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=fm");
	$gui->add('spacer');
	$gui->add("com_header",$thead);
	$gui->add("com_info",$tdesc);
	$gui->add("tab_head");
	$gui->add("tab_simple","", '::/'.$path);
	$gui->add('spacer');

	// show the current writability status
	$writable = is_writable($full_path);
	if ($writable)
		$extra = '';
	else $extra = ' disabled="disabled"';
	$gui->add('text', '', _FM_WRITABLE, $writable ? _YES : _NO);

	if (is_image($path))
		$gui->add("html","","","<img src='".$path."' >");
	else {
		$v = new ScriptedUI_Validation();
//		$v->not_empty = true;
		$gui->add("textfield","fm_file",_FM_FILE_NAME,$path, $v, $extra);
		//L: may not work on some browser (Internet explorer has a bad behaviour with html entities)
		//L: removed ' size="10"'
		if (is_utf8($data))
			$data = xhtml_safe($data);
		else {
			$gui->add('text', '', _FM_WARNING_ENCODING);
			// render data for XHTML
			$data = xhtml_safe($data);			//htmlspecialchars($data, ENT_COMPAT);
		}
		$gui->add("textarea_big","fm_data",_FM_FILE_CONTENT, $data, null, $extra);
	}
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
//	$gui->add('spacer');
	$gui->generate();
}

function chmod_cid($cid) {
	global $view, $d;

	$d->add_raw_js('
function ctl_inc( ctl, val, inc){
        if( ! inc )
                val = -val;
        var old_val = ctl.value;
        if( ! old_val)
                old_val = 0;
//        ctl.value = parseInt(old_val) + parseInt(val);
        ctl.value = format_chmod( parseInt(old_val, 10) + parseInt(val, 10) );
       }
function fill_chmod( dig, r_ctl, w_ctl, x_ctl){
        var r_mask = 4; var w_mask = 2; var x_mask = 1;
        if( dig & r_mask)
                r_ctl.checked = true;
        if( dig & w_mask)
                w_ctl.checked = true;
        if( dig & x_mask)
                x_ctl.checked = true;
}

function format_chmod(dig){
        var str = new String("" + dig);
        var final_length = 3;
        var add_length = final_length - str.length;
        for(var i=0; i < add_length; i++){
                str = "0" + str;
                }
        return str;
}
');

$content = '
<table>
<tr align="center">
<td width=25>&nbsp;</td>
<td width=225 align="left">Name</td>
<td width="75" colspan="3">User</td>
<td width="75" colspan="3">Group</td>
<td width="75" colspan="3">Others</td>
<td width=50>Mode</td>
</tr>
<tr align="center">
<td></td>
<td></td>
        <td width=25>R</td>
        <td width=25>W</td>
        <td width=25>X</td>

        <td width=25>R</td>
        <td width=25>W</td>
        <td width=25>X</td>

        <td width=25>R</td>
        <td width=25>W</td>
        <td width=25>X</td>
<td></td>
</tr> ';

	foreach($cid as $i => $var) {
		$base_name=basename($var);
		$show_name=$base_name;
		if(is_dir($var))$show_name.='/';
		//$base_name = str_replace('.','_',$base_name);
		$perms = @fileperms($var);
		if ($perms !== false) {
			$mode=decoct($perms & 0x0fff);
			$content.= '<tr align="center">
<td><input type="checkbox" name="index[]" value="'.$i.'" checked="checked"></td>
<td align="left">'.$show_name.'<input type="hidden" name="items[]" value="'.$var.'"></td>
<td><input type="checkbox" class="chmodbox" name="ur_'.$i.'" onclick="ctl_inc(this.form.mode_'.$i.', 400, this.checked)"></td>
<td><input type="checkbox" class="chmodbox" name="uw_'.$i.'" onclick="ctl_inc(this.form.mode_'.$i.', 200, this.checked)"></td>
<td><input type="checkbox" class="chmodbox" name="ux_'.$i.'" onclick="ctl_inc(this.form.mode_'.$i.', 100, this.checked)"></td>
<td><input type="checkbox" class="chmodbox" name="gr_'.$i.'" onclick="ctl_inc(this.form.mode_'.$i.', 40, this.checked)"></td>
<td><input type="checkbox" class="chmodbox" name="gw_'.$i.'" onclick="ctl_inc(this.form.mode_'.$i.', 20, this.checked)"></td>
<td><input type="checkbox" class="chmodbox" name="gx_'.$i.'" onclick="ctl_inc(this.form.mode_'.$i.', 10, this.checked)"></td>
<td><input type="checkbox" class="chmodbox" name="or_'.$i.'" onclick="ctl_inc(this.form.mode_'.$i.', 4, this.checked)"></td>
<td><input type="checkbox" class="chmodbox" name="ow_'.$i.'" onclick="ctl_inc(this.form.mode_'.$i.', 2, this.checked)"></td>
<td><input type="checkbox" class="chmodbox" name="ox_'.$i.'" onclick="ctl_inc(this.form.mode_'.$i.', 1, this.checked)"></td>
<td><input size="4" type="text" name="mode_'.$i.'" value="'.$mode.'" readonly="readonly"></td>
</tr>
<script language="javascript">
        f = document.adminform;
        fill_chmod( f.mode_'.$i.'.value.substring(0, 1),  f.ur_'.$i.', f.uw_'.$i.', f.ux_'.$i.' );
        fill_chmod( f.mode_'.$i.'.value.substring(1, 2),  f.gr_'.$i.', f.gw_'.$i.', f.gx_'.$i.' );
        fill_chmod( f.mode_'.$i.'.value.substring(2, 3),  f.or_'.$i.', f.ow_'.$i.', f.ox_'.$i.' );
</script>';
		} else {
			$content .= 'Mode not available for '.$var;
			$mode = null;
		}

	}
	$content.=  '<br />';

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=fm&chmod=true&view=".$view);
	$gui->add('spacer');
	$gui->add("com_header","Chmod selected");
	$gui->add("com_info",_FM_INFO);
	$gui->add("tab_head");
	$gui->add("tab_simple"," ",_FM_CHMOD_CALC);
	$gui->add("html","","",$content);
	if (isset($mode))
		$gui->add("textfield","file_mode",_FM_NEW_MODE,$mode);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->add('spacer');
	$gui->generate();
}

function create_dir($view) {
	global $d, $d_root, $fm_subpath;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=fm&view=".$view);
	$gui->add('spacer');
	$gui->add("com_header",_FM_NEW_DIR);
	$gui->add("com_info",_FM_NEW_DIR_DESC);
	$gui->add("tab_head");
	$gui->add("tab_simple","",'::/'.$view);
	$gui->add('spacer');
/*	WTF? if (is_image($view))
		$gui->add("html","","","<img src='".$view."' />");
	else	*/
	$v = new ScriptedUI_Validation();
		$gui->add("textfield","fm_file",_FM_DIRECTORY_NAME,$view, $v);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->add('spacer');
	$gui->generate();
}

// added by legolas558
function rename_form($path, $view) {
	global $d, $d_root, $fm_subpath;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=fm&view=".$view);
	$gui->add('spacer');
	$gui->add("com_header",_FM_RENAME);
	$gui->add("com_info",_FM_RENAME_DESC);
	$gui->add("tab_head");
	$gui->add("tab_simple","",'::/'.dirname($path));
	$gui->add('spacer');
	$gui->add("hidden","fm_ofile",'',$path);
	$v = new ScriptedUI_Validation();
		$gui->add("textfield","fm_file",_FM_RENAME_NEW,$path, $v);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->add('spacer');
	$gui->generate();
}

?>
