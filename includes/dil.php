<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Dynamic image loader
# @author legolas558
#
# dynamic image loader interface and script

global $has_dil_js;
$has_dil_js = false;
function dynamic_image_list($name, $images, $show_image, $folder, $extra = '') {
	global $d_subpath, $d, $has_dil_js;
	$select_drop="<select name='$name' class=\"tf\" size='6' onchange='javascript:changeImage(this,\"$name\")' $extra>\n";
	
	$default = $show_image;
	$pl_js = '';
	$i_i = 0;
	foreach($images as $row) {
		if (isset($row['selected'])) {
			$sel='selected=\'selected\'';
			if( strlen($row['value']) ) $show_image=$folder.$row['value'];
		} else $sel='';
		$select_drop.='<option value="'.$row['value'].'" '.$sel.' >'.$row['name'].'</option>'."\n";
		if ( strlen($row['value']) ) {
			$pl_js.='var tmpi_'.$i_i.' = new Image();'."\n".'tmpi_'.$i_i.'.src="'.js_enc($folder).$row['value'].'";'."\n";
			++$i_i;
		}
	}
//	if (!isset($show_image))		$show_image = $d_subpath.$images[0]['value'];
//	$d->add_raw_js('var dil_default_src = \''.js_enc($show_image).'\';');

	if (!$has_dil_js) {
		$d->add_raw_js('
	var dil_folder = \''.js_enc($folder).'\';
	var dil_default_src = \''.js_enc($default).'\';
	
	function changeImage(srcObj,srcListName) {
		var im=document.getElementById("image_"+srcListName);
		var obj_v = srcObj.value;
		if (obj_v==null || obj_v=="") im.src = dil_default_src; 
		else im.src = dil_folder+obj_v;
	}

	');
	}

	$select_drop.='</select>';
	$select_drop='<table border="0" cellspacing="0" cellpadding="0"><tr>
	<td>'.$select_drop.'</td>
	<td><img src="'.$show_image.'" id="image_'.$name.'" name="image_'.$name.'" border="2" alt="" /></td>
</tr></table>'."\n";
	//L: now preload the images
	return $select_drop.$d->script($pl_js);
}

?>