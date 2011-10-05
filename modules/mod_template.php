<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

global $d_subpath,$d_root, $d_template;

?><img id="templateimage" src="<?php echo $d_subpath; ?>templates/<?php echo $d_template; ?>/template_thumbnail.png" width="140" height="90" border="1" alt="<?php echo $d_template; ?>" />
<?php $d_templates = read_dir($d_root."templates/", 'dir');
if (count($d_templates)) {
	global $d;
$d->add_raw_js("function changeTemplateImage(srcObj) {
	var preview_img = document.getElementById('templateimage');
	if(srcObj.value=='' || srcObj.value==null)preview_img.src='".$d_subpath.'templates/'.$d_template."/template_thumbnail.png';
	else preview_img.src='".$d_subpath."templates/'+srcObj.value+'/template_thumbnail.png';
	}");

?><br />
<form action="" name="templateform" method="get">
	<input type="hidden" name="option" value="user" />
	<input type="hidden" name="task" value="custom" />
	<select name="template" onchange="changeTemplateImage(this)" class="dk_inputbox">
  <?php
	foreach($d_templates as $var) {
		$sel='';
		if($d_template==$var)$sel='selected="selected"';
		echo "<option value=\"$var\" $sel>$var</option>";
	}
  ?>
  </select>
  <input class="dk_button" type="submit" value="<?php echo _SELECT;?>" />
</form>
<?php } else { ?>
<em><?php echo _TEMPLATE_NOT_AVAIL; ?></em>
<?php } ?>
