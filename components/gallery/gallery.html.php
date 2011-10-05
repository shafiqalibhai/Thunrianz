<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include com_path('functions');

// show a single picture
function show_pic($id) {
	global $conn,$d,$Itemid,$pathway,$access_sql;
	$row=$conn->GetRow("SELECT catid,title,description,url FROM #__gallery WHERE id = $id ");
	if (!count($row))
		return;
	change_val("gallery",$id,"hits",1);

	$crow=$conn->SelectRow('#__categories', 'name', " WHERE id=".$row['catid'].' '.$access_sql);
	if (!$crow) {
		CMSResponse::Unauthorized();
		return;
	}
	$pathway->add($crow['name'], 'option=gallery&catid='.$row['catid'].'&Itemid='.$Itemid);
	$pathway->add($row['title'], 'option=gallery&id='.$id.'&Itemid='.$Itemid);
	$d->add_meta($row['description']);
	
	$extra = $conn->SelectRow('#__gallery_category', 'gallery_path', ' WHERE id='.$row['catid']);
	
	?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0"  class="dkcom_section">
	  <tr>
	    <td><div align="center" class="gallery_title"><?php echo $row['title']; ?></div></td>
	  </tr>
	  <tr>
	    <td><div align="center"><img src="<?php
		if (!is_url($row['url']))
			$photo_url = $extra['gallery_path'].$row['url'];
		else
			$photo_url = $row['url'];
			
		echo $photo_url; ?>" hspace="5" vspace="5" alt="<?php echo xhtml_safe($row['title']); ?>" /></div></td>
	  </tr>
	<?php if($row['description']!='')echo '<tr><td><div align="center" class="gallery_desc">'.text_to_html($row['description']).'</div></td></tr>';
	global $params;
	?>
	  <tr>
	    <td>
		<div align="center"><?php
		//TODO: navigation bar (?)
		$show_mode = $params->get('show_mode', 'thickbox');
		switch ($show_mode) {
//			case 'thickbox':			break;
			case 'window':
			case 'popup':
			?>
		<a href="#" onclick="window.close();"><?php echo _PROMPT_CLOSE; ?></a></div>
		<?php
		break;
		case 'inset':
		?>
	  <br /><a href="index.php?option=gallery&catid=<?php echo $row['catid'].'&amp;Itemid='.$Itemid; ?>"><?php echo _GALLERY_GO_BACK_TO.' '.$crow['name']; ?></a><br />
	  <?php } //switch ?>
	  </td>
	  </tr>
	</table>
	<?php
}

// show all gallery categories (albums)
function view_category() {
	global $conn,$Itemid,$d_website,$d_root,$params,$access_sql,$d;

	$showcat = $params->get('show_cat',3);
	$showitemrow = $params->get('show_item_row',3) ;
	$showcat *= $showitemrow;

	?>
	<div class="dk_header"><h2><?php echo _GALLERY_TITLE; ?></h2></div>
	<table width="100%" border="0" cellpadding="4" cellspacing="0" class="dk_content">
	<?php $desc = $params->get('description', ''); if (strlen($desc)) { ?>
	<tr><td colspan="2"><?php echo $desc; ?></td></tr><?php } ?>
	<?php
	include_once $d_root.'classes/pagenav.php';
	$pn = new PageNav($showcat, true);
	$rsa = $pn->Slice('#__categories', 'id,name,image,description,count', "WHERE section = 'com_gallery' $access_sql", 'ORDER BY ordering ');
	
	if (isset($rsa[0])) {
		?>
        <tr><td colspan="2">
        <?php echo $pn->NavBar('option=gallery&amp;Itemid='.$Itemid,''); ?>
        </td></tr>
        <tr><td>
		<table border="0" cellspacing="0" cellpadding="10">
        <?php
        $img_row=$showitemrow;
        $cur_row=1;
        $img_row_count=1;
        foreach($rsa as $row) {
		// output table row for 1st image in row
		if ($img_row_count==1)
			echo '<tr>';
		$extra = $conn->SelectRow('#__gallery_category', 'thumbs_path', ' WHERE id='.$row['id']); ?>
		<td valign="bottom" align="center">
			<p class="gallery_title">
			<a href="index.php?option=gallery&amp;catid=<?php echo $row['id']; ?>&amp;Itemid=<?php echo $Itemid; ?>" ><?php echo $row['name'].' ('.$row['count'].')'; ?></a></p><br />
			<div class="imagelist">
			<p><a href="index.php?option=gallery&amp;catid=<?php echo $row['id']; ?>&amp;Itemid=<?php echo $Itemid; ?>" title="<?php echo $row['description']; ?>">
			<img src="<?php
			
			$th = $extra['thumbs_path'].$row['image'];
			// if no image was set, get the first image of the folder (if any)
			if (($row['image']==='') && $row['count']) {
				$imrow = $conn->SelectRow('#__gallery', 'url', ' WHERE catid='.$row['id'].' ORDER BY ordering');
				$th = gallery_thumb($imrow['url'], $extra['thumbs_path']);
			} else if (!is_file($d_root.$th))
				$th = $GLOBALS['d_subpath'].'media/common/noimage.png';
			
			echo $th;
			?>" border="0" alt="<?php echo $row['description']; ?>" /></a></p>
			<p><?php echo $row['description']; ?></p></div></td>
		<?php
		if ($img_row_count<$img_row)
			$img_row_count++;
           	else
           		$img_row_count=1;
		// output closing row when counter was reset to 1
		if ($img_row_count==1)
			echo '</tr>';
		$cur_row++;
	}
	if ($img_row_count != 1) echo '</tr>';
	?>
         </table>
         </td>
         </tr>

		 <?php
	} ?>
	 </table>
	<?php
}

function gallery_thumb($url, $thumbs_path) {
	global $d_root, $d;
	$photo_th = $thumbs_path;
	if (is_url($url)) {
		global $d_subpath;
		$photo_th .= clear_name($url);
		// subsite path only when checking file existance
		if (!file_exists($d_root.$d->SubsitePath().$photo_th))
			$photo_th = $d_subpath.'media/common/extimage.png';
	} else {
		// local file thumbnail
		$photo_th .= $url;
		// subsite path only when checking file existance
		if (($url==='') || !is_file($d_root.$d->SubsitePath().$photo_th))
			$photo_th = $GLOBALS['d_subpath'].'media/common/noimage.png';
	}
	return $photo_th;
}

// view all photos in the specified gallery category
function view_gallery($catid) {
	global $conn,$Itemid,$d_website,$d_root,$params,$access_sql,$d,$my,$pathway;

	$showitem = $params->get('show_item',3) ;
	$showitemrow = $params->get('show_item_row',3) ;
	$showitem *= $showitemrow;
	// Here the gallery with photos display start
	$crow=$conn->SelectRow('#__categories', 'name,description,editgroup', " WHERE id=$catid $access_sql");
	if ($crow) {
		$pathway->add($crow['name'], "option=gallery&catid=$catid&Itemid=$Itemid"); ?>
	<div class="dk_header"><h2><?php echo $crow['name']; ?></h2></div>
	<table width="100%" border="0" cellpadding="4" cellspacing="0" class="dk_content">
	<?php if ($my->can_submit()) { ?>
	<tr><td colspan="2"><a href="index.php?option=gallery&amp;task=new&amp;Itemid=<?php echo $Itemid.'&amp;catid='.$catid; ?>"><?php echo _GALLERY_SUBMIT_TO_CATEGORY; ?></a></td></tr>
	<?php } if (strlen($crow['description'])) { ?>
    <tr><td colspan="2">
	<?php echo text_to_html($crow['description']); ?>
    </td></tr><?php } ?>
	<tr><td>
	<table width="100%">
    <?php
	include_once $d_root.'classes/pagenav.php';
	$pn = new PageNav($showitem, true);
	$rsa = $pn->Slice('#__gallery', 'id,title,description,url', "WHERE catid=$catid AND published=1", 'ORDER BY ordering');
	
	if (isset($rsa[0])) {
	// get the show mode
	$show_mode = $params->get('show_mode', 'thickbox');
	if ($show_mode == 'thickbox') {
		$d->add_js_once('includes/js/jquery.js');
		$d->add_css_once('includes/css/thickbox.css');		
		$d->add_js_once('includes/js/thickbox.js');		
		$ef = ',gallery_path';
	} else $ef = '';
	
	$extra = $conn->SelectRow('#__gallery_category', 'thumbs_path'.$ef, ' WHERE id='.$catid); ?>
	<tr><td>
		<table border="0" cellspacing="0" cellpadding="10"><?php
        $img_row=$showitemrow;
        $cur_row=1;
        $img_row_count=1;
        foreach($rsa as $row) {
		if ($img_row_count==1) echo '<tr>'; ?>
		<td valign="bottom" align="center">
			<div class="imagelist">
			<p class="gallery_title"><?php echo $row['title'];?></p>
			<p><a href="<?php 

			$photo_th = gallery_thumb($row['url'], $extra['thumbs_path']);
			
			$qs = 'option=gallery&catid='.$catid.'&Itemid='.$Itemid.'&id='.$row['id'].
					'&task=show';
			// this is the alternative url for js-based views
			$alt_url = $d->alternate_url("index2.php?".$qs."&pop=1", '<img src="'.$photo_th.'" border="0" alt="'.$row['title'].'" />');
			switch ($show_mode) {
				case 'window':
					$qs = 'index2.php?'.$qs.'" target="_blank';
					$alt_url = '';
					break;
				case 'inset':
					$qs = 'index.php?'.$qs;
					$alt_url = '';
					break;
				case 'thickbox':
					if (!is_url($row['url']))
						$url = $extra['gallery_path'].$row['url'];
					else $url = $row['url'];
					$qs = $url.'" rel="'.rawurlencode(basename($extra['gallery_path'])).'" class="thickbox';
					break;
				case 'popup':
					$qs = "javascript:".$d->popup_js("'index2.php?".$qs."&amp;pop=1'", 640, 480).'" rel="nofollow';
				}
			echo $qs;
			
			?>" title="<?php echo $row['title']; ?>">
		<img src="<?php	echo $photo_th; ?>" border="0" alt="<?php echo $row['title']; ?>" /></a>
		</p><?php echo $alt_url; ?>
		<p class="gallery_desc"><?php echo text_to_html($row['description']);?></p>
			</div></td>
            <?php
		if ($img_row_count<$img_row)
			$img_row_count++;
		else $img_row_count=1;
		if ($img_row_count==1) echo '</tr>';
		$cur_row++;
	}
	if ($img_row_count != 1) echo '</tr>'; ?>
         </table>
         </td></tr>
         <?php } ?>
		<tr><td colspan="<?php echo $showitemrow; ?>"><?php
		$nb = $pn->NavBar("option=gallery&amp;catid=".$catid."&amp;Itemid=".$Itemid,'');
		if (strlen($nb))
			echo '<big style="text-align: center">'.$nb.'</big>';
		else
			echo '&nbsp;';
        ?></td></tr>
      </table>
	  </td></tr>
  	  <tr>
	  <td align="center"><br /><a href="index.php?option=gallery&amp;Itemid=<?php echo $Itemid; ?>"><?php echo _GALLERY_GO_BACK_TO.' '._GALLERY_GALLERIES; ?></a><br />
	  </td>
	  </tr>
	   </table>
	  <?php } else CMSResponse::Unauthorized();
	}
	
function submit_gallery($catid = null) {
	global $conn,$Itemid,$d, $access_sql, $d_pic_extensions,$pathway;
		$pathway->add(_GALLERY_SUBMIT_PICTURE, "option=gallery&task=new".(isset($catid)?'&catid='.$catid : '').'&Itemid='.$Itemid);
		$d->add_raw_js('
	function submit_picture() {
		var form = document.user_form;
		// do field validation
		if (form.gallery_title.value == ""){
			alert( "Download item must have a title" );
		} else if (!getSelectedValue(form.gallery_catid)) {
			alert( "You must select a category." );
		} else if ((form.gallery_url.value == "") && (form.gallery_file.value == "")) {
			alert( "You must specify an url or a file upload." );
		} else {
			return true;
		}
		return false;
	}
		
		');
?>
	<form enctype="multipart/form-data" action="index.php?option=gallery&amp;Itemid=<?php echo $Itemid; ?>" method="post" name="user_form" id="user_form" onsubmit="return submit_picture()">
<div class="dk_header"><h2><?php echo _GALLERY_SUBMIT_PICTURE;?></h2></div>
  <table cellpadding="4" cellspacing="1" border="0" width="100%">
    <tr>
      <td valign="top" align="right"><?php echo _URL; ?></td>
      <td><input class="dk_inputbox" type="text" name="gallery_url" value="" size="45" /></td>
    </tr>
    <tr>
      <td valign="top" align="right"><?php echo sprintf(_GALLERY_FILE_WITH_EXT, implode(', ', $d_pic_extensions)); ?>:</td>
      <td><?php echo file_input_field('gallery_file'); ?></td>
    </tr>
	<tr>
      <td colspan="2">* <?php echo text_to_html(_GALLERY_SUBMIT_DESC); ?><hr /></td>
    </tr>
    <tr>
      <td width="20%" align="right">* <?php echo _TITLE; ?></td>
      <td width="80%">
	<input class="dk_inputbox" type="text" name="gallery_title" size="45" maxlength="250" value="" />
	</td>
    </tr>
    <tr>
      <td valign="top" align="right">* <?php echo _CAT; ?></td>
      <td><?php $rsa = submission_categories('com_gallery'); ?>
        <select  class="dk_inputbox" name="gallery_catid">
          <option value=""><?php echo _SELECTCAT; ?></option>
		  <?php
		 foreach($rsa as $row) {
			echo '<option value="'.$row['id'].'"';
			if (isset($catid) && ($row['id']==$catid))
				echo 'selected="selected"';
			echo '>'.$row['name']."</option>"; }
		  ?>
		</select>
	</td>
    </tr>
    <tr>
      <td valign="top" align="right"><?php echo _DESC; ?></td>
      <td>
				<textarea class="dk_inputbox" cols="30" rows="6" name="gallery_description" style="width:300px;"></textarea>
			</td>
    </tr>
	<?php global $my; if ($my->can_publish()) { ?>
    <tr>
      <td>&nbsp;</td><td>
<label for="gallery_published">
<input id="gallery_published" name="gallery_published" type="checkbox" /><?php echo _PUBLISHED; ?></label>
	  </td>
		</tr><?php } ?>
    <tr>
      <td valign="top" align="right">&nbsp;</td>
      <td><input class="dk_button" type="submit" name="Submit" value="<?php echo _SUBMIT; ?>" />
        &nbsp;&nbsp;<input class="dk_button" type="reset" value="<?php echo _E_RESET; ?>" /></td>
    </tr>

  </table>

  <input type="hidden" name="task" value="newgallery" />
</form>

	<?php
}

function confirm_submission($catid) {
	global $option;
	
	?>
	<div class="dk_header"><h2><?php echo _SUBMIT_SUCCESS; ?></h2></div>
	<div class="dkcom_userconfirmation"><?php echo _E_ITEM_SAVED; ?>
	<hr /><div align="center">
	<a href="index.php?option=<?php echo $option; ?>&amp;catid=<?php echo $catid; ?>"><?php echo _RETURN_TO_CAT; ?></a></div>
	</div>
	<?php

}

	
?>