<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Frontend file browser component for Lanius CMS
# @author legolas558

function is_allowed_path($path, $allowed) {
	if (is_windows())
		$test_path = strtolower($path);
	else
		$test_path = $path;
		
	// scan the allowed paths array to find a matching one
	// e.g. when the the inquiried path is under an allowed path
	foreach($allowed as $path) {
		if (!strlen($path))
			return true;
		if (strpos($test_path, $path)===0)
			return true;
	}
	
	return false;
}

class FileBrowser {
	// FROM $_GET
	var $fi;					// form index
	var $fi_name;				// field name (of the opener form)
	var $preview;				// preview flag
	var $absurl;				// absolute URLs?
	var $onplink;				// provide an open in new page link for selected file?
	var $dirs, $files;			// allow selection of files/directories?
	var $file_upload;			// is file upload form accessible? GID clearance is anyway applied
	// FROM $_SESSION
	var $excluded_dirs;			// directories which are excluded
	var $ext;					// valid extensions
	
// returns the url of the current page with all the GET variables in place
function _this_url($path) {
	global $d_website, $Itemid;
	return $d_website.'index2.php?option=fb&Itemid='.$Itemid.'&preview='.$this->preview.
	'&absurl='.$this->absurl.'&fi='.$this->fi.'&fi_name='.rawurlencode($this->fi_name).
	'&files='.$this->files.'&file_upload='.$this->file_upload.
	'&dirs='.$this->dirs.'&onplink='.$this->onplink.'&path='.$path;
}

function ShowInterface() {

global $d, $d_root, $my, $d_max_upload_size, $d_website, $d_subpath;

include $d_root.'lang/'.$my->lang.'/admin/admin.php';

$site_root = $d_root.$d->SubsitePath();

/*** now the output begins ***/

CMSResponse::NoCache();

ob_start(); ?>

var file_selection = '<?php echo $this->cur_file; ?>';
<?php if ($this->preview) { ?>
function _erase_preview() {
	var previewer = document.getElementById('previewer');
	previewer.src = '<?php echo $d_website; ?>media/common/spacer.png';
}
<?php } ?>
function select_dir(dir) {
<?php if ($this->preview) { ?>
_erase_preview();
<?php } ?>
var msg = document.getElementById('msg');
	msg.innerHTML = "<?php echo js_enc(_FB_DOUBLE_CLICK_BROWSE); ?> "+dir;
	setSelection(<?php if ($this->dirs) echo 'dir'; else echo "''"; ?>);
}

function select_file(file) {
<?php if ($this->preview) { ?>
_erase_preview();
	var msg = document.getElementById('msg');
<?php if ($this->preview == 1) { ?>
msg.innerHTML = "<?php echo js_enc(_FB_DOUBLE_CLICK_PREVIEW); ?> "+file;
<?php } else { ?>
msg.innerHTML = "";
		var previewer = document.getElementById('previewer');
		var pic1 = new Image();
		pic1.src = file;
		
		var a = dk_apply_ratio(pic1.width, pic1.height);
		previewer.src = file;
		previewer.width = a[0];
		previewer.height = a[1];
<?php } ?>
<?php } ?>
setSelection(file);
}

function dk_apply_ratio(w, h) {
	var ratio = 1.0;
	if (w>100) {
		ratio = 100/(ratio*w);
		w = 100;
		h = Math.floor(h*ratio);
	} else if (h>100) {
		ratio = 100/(ratio*h);
		h = 100;
		w = Math.floor(w*ratio);
	}
	var a = [w, h];
	return a;
}

function enter_file(file) {
<?php if ($this->preview==1) { ?>
var previewer = document.getElementById('previewer');
	previewer.src = file;
<?php } ?>
}

function enter_dir(dir) {
	document.location = '<?php
	
	echo $this->_this_url("'+escape(dir)+'");
	
	?>';
}

function setSelection(file) {
	file_selection = file;
	var cur_selection = document.getElementById('cur_selection');
	var ok_btn = document.getElementById('ok_btn');
	if (file === '') {
		cur_selection.innerHTML = "<?php echo js_enc(_FB_NO_SELECTION); ?>";
<?php if ($this->fi_name) { ?>
ok_btn.disabled = true;
<?php } ?>
} else {
<?php if ($this->fi_name) { ?>
ok_btn.disabled = false;
<?php } ?>
var tmp = '<?php echo js_enc(_FB_SELECTION); ?>: <strong>', p_html = '';
<?php
		if ($this->onplink) { ?>
if (file.substring(file.length-1)!='/') {
			tmp += '<a href="<?php echo $d_website; ?>'+file+'" target="_blank">';
			p_html = "<" + "/" + "a>";
		}
		
<?php } ?>
tmp += file + p_html + '<' + '/strong>';
		
		cur_selection.innerHTML = tmp;
	}
}

function elem_changed(obj) {
	var val = obj.options[obj.selectedIndex].value;
	if (val.substring(val.length-1)=='/')
		select_dir(val);
	else
		select_file(val);
}

function is_dir_path(val) {
	return (val.substring(val.length-1)=='/');
}

function kbd_hook(obj, orig_e)
{
	if (!orig_e)
		e = window.event;
	else
		e = orig_e;
	
	if (e.keyCode==13) {
		var val = obj.options[obj.selectedIndex].value;
		if (is_dir_path(val))
			enter_dir(val);
		else
			enter_file(val);
	}	

	return orig_e;
}

function glob_enter_item(obj) {
	var val = obj.value;
	if (is_dir_path(val))
		enter_dir(val);
	else
		enter_file(val);
}

function submitSelection() {
<?php if ($this->fi_name) { ?>
var field_name = '<?php echo $this->fi_name;
	?>';
	<?php if ($this->fi != 'id') { ?>
	var obj = opener.opener_window.document.forms[<?php echo $this->fi; ?>].elements[field_name];
	<?php } else { ?>
	var obj = opener.opener_window.document.getElementById(field_name);
	<?php } ?>
	obj.value =
<?php if ($this->absurl) echo '"'.$d_website.'" + '; ?>
file_selection;
<?php } ?>
opener.opener_window = null;
	top.close()
}

function _startup_script() {
	var fbd = document.getElementById('fb_directory');
	fbd.focus();
}

<?php

$d->add_raw_js(ob_get_clean());

$d->add_js_onload('_startup_script');

$d->add_raw_css('body {
	background-color: #FFFFFF;
}');

// body onload="window.focus()"
?><table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td width="50%" align="center"<?php if (!$this->preview) echo ' colspan="2"'; ?>><strong><?php echo _BROWSE; ?></strong></td>
    <?php if ($this->preview) { ?>
    <td width="50%" align="center"><strong><?php echo _FB_PREVIEW; ?></strong></td>
    <?php } ?>
  </tr>
  <tr><td colspan="2"><big>::/<?php echo xhtml_safe($this->path); ?></big></td></tr>
  <tr>
    <td <?php if ($this->preview) echo 'width="50%"'; else echo 'colspan="2"'; ?>><select name="fb_directory" id="fb_directory" size="10" style="width:100%" onchange="elem_changed(this)" onkeypress="kbd_hook(this, event)" ondblclick="glob_enter_item(this)">
        <?php
	
	$abs_ed = array();
	foreach($this->excluded_dirs as $dir) {
		$abs_ed[] = $site_root.$dir;
	}
	
	// get the full files/directories listing
	$files = raw_read_dir($site_root.$this->path, $this->ext, $abs_ed, _RRD_SORTED); $abs_ed = null;
	
	if ($this->path !== $this->root_path) {
		$s=substr($this->path, 0, -1);
		$p=strrpos($s, '/');
		if ($p===false)
			$s='../';
		else
			$s = substr($s, 0, $p+1);
		array_unshift($files, $site_root.$s);
	}
	
	// output each file in the selection list
	$l = strlen($site_root);
	$preload_js = "var pwImg = []\n";
	$i = 0;
	foreach($files as $file) {
		$file = substr($file, $l);
//		if ($file=='./')			continue;

		if ($file[strlen($file)-1]=='/') {
			$js = 'dir';
		} else {
			$js = 'file';
			// add the javascript for image preloading
			$preload_js .= "\npwImg[$i] = new Image();\npwImg[$i].src = \"".$file."\";\n";
			++$i;
		}
//		$enter_js = 'enter_'.$js.'(\''.js_enc($file).'\')';		
		$js = 'select_'.$js.'( \''.js_enc($file).'\' )';
		
//		$add_js = ' onclick="'.$js.'"'; // ondblclick="'.$enter_js.'"';
		
		echo '<option value="'.xhtml_safe($file).'"'; //.$add_js;
		
		if ($file === $this->cur_file)
			echo ' selected="selected"';		
		echo '>'.xhtml_safe($file).'</option>';
	}
	
	// preloading is necessary for IE preview
	$preload_js .= "\npwImg = null;\n";
	global $d;$d->add_raw_js($preload_js);
	
	?>
      </select></td>
    <?php if ($this->preview) { ?>
    <td align="center"><img id="previewer" src="<?php
	echo $d_website.$d_subpath.'media/common/spacer.png';
	if (strlen($this->cur_file)) {
		$d->add_raw_js('function click1st_img() { select_file(\''.js_enc($this->cur_file).'\'); }');
		$d->add_js_onload('click1st_img');
	}
	?>" border="1" alt="0" /></td>
    <?php } ?>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2">
      <div style="font-size: smaller" id="msg"></div>
      </td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2" align="center"><div id="cur_selection">
        <?php if (strlen($this->cur_file)) {
		echo _FB_CURRENT_SELECTION.': <strong>';
		if ($this->onplink)
			echo '<a href="'.xhtml_safe($d_website.$this->cur_file).'" target="_blank">';
		echo $this->cur_file;
		if ($this->onplink)
			echo '</a>';
		echo '</strong>';
	} else
		echo _FB_NO_SELECTION;
	?>
    </div></td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <?php
  // in order to allow file upload user must have publication rights
  if ($my->can_publish() /* || ($this->path == $this->root_path)*/) { ?>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <?php if ($this->file_upload) { ?>
  <tr>
    <td colspan="2"><form enctype="multipart/form-data" method="post" action="<?php echo $this->_this_url(rawurlencode($this->path)); ?>">
        <?php echo _FILE_UPLOAD; ?>:
        <?php echo file_input_field('fb_upload'); ?>
        <input type="submit" value="Submit"/>
      </form>
      <?php if ($this->ext) { ?>
      <div><?php echo sprintf(_UPLOAD_DISALLOWED_EXT, implode(', ', array_map('raw_strtoupper', $this->ext))); ?></div>
      <?php } ?>
      <div><?php echo _FB_PUBLIC_NOTICE; ?></div></td>
  </tr>
  <?php }
	}  ?>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td align="center"><input style="width: 80px; font-weight:bold" id="ok_btn" <?php if (!strlen($this->cur_file)) echo 'disabled="disabled"'; ?> type="button" value="OK" onclick="submitSelection()" /></td>
    <td align="center"><input style="width: 80px" type="button" value="<?php echo _CANCEL; ?>" onclick="top.close()" /></td>
  </tr>
</table>
<?php }

}

?>