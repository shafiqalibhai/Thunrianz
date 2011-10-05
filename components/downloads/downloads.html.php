<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$ordarr=array(
	array("name"=>_ORDERING,"sql"=>"ordering"),
	array("name"=>_NAME,"sql"=>"title"),
	array("name"=>_DOWNLOADS_LATEST,"sql"=>"mod_date"),
	array("name"=>_DOWNLOADS_TITLE,"sql"=>"hits"),
	array("name"=>_DOWNLOADS_FILESIZE,"sql"=>"filesize")
);

function view_categories() {
global $conn,$Itemid,$d_website,$params,$access_sql, $d;
 $d->add_head('<link rel="Downloads" href="index.php?option=downloads&amp;Itemid='.$Itemid.'" />');
 ?>
 <div class="dk_header"><h2><?php echo _DOWNLOADS_TITLE; ?></h2></div>
  <table width="100%" border="0" cellpadding="4" cellspacing="0" class="dk_content">
  <?php $desc = $params->get('description', ''); if (strlen($desc)) { ?>
  <tr><td colspan="2"><?php echo $desc; ?></td></tr>
  <tr><td colspan="2">&nbsp;</td></tr><?php } ?>
		<?php
		$rsa = submission_categories('com_downloads', ',description,count');
		if( count($rsa) ) {
			?>
			<tr><td colspan="2"><table width="100%">
            <tr>
    <td  class="dkcom_tableheader" width="80%"><?php echo _NAME; ?></td>
    <td  class="dkcom_tableheader" align="center" width="20%"><?php echo _DOWNLOADS_TITLE; ?></td>
  </tr>
			<?php
			$catname='';
			foreach($rsa as $row) { ?>
	    <tr> <td ><a href="index.php?option=downloads&amp;catid=<?php echo $row['id']; ?>&amp;Itemid=<?php echo $Itemid.content_sef($row['name']); ?>" ><?php echo $row['name']; ?></a><br /><?php echo $row['description']; ?></td>
          <td align="center"><?php echo $row['count']; ?></td>
          </tr>
				<?php } ?>
			</table></td></tr>
				<?php
	}
	echo '</table>';
}

function view_downloads($catid, $list, $otype, $order) {
	global $conn,$Itemid,$d_website,$params,$access_sql, $d, $pathway;

	//show the downloads
	$row=$conn->SelectRow('#__categories', 'name,image,description,image_position,editgroup', " WHERE id=$catid $access_sql");
	if(!$row) {
		CMSResponse::Unauthorized();
		return;
	}
	$pathway->add($row['name']);
	$d->add_meta($row['description']);
	$tabclass_arr=array("dkcom_tablerow2","dkcom_tablerow1");
	$tabcnt = 0;
	?><div class="dk_header"><h2><?php echo $row['name']; ?></h2></div>
	<table width="100%" border="0" cellpadding="4" cellspacing="0" class="dk_content">
	  <tr>
	    <td colspan="2">
	      <?php
		  if(!empty($row['image']))echo '<img src="'.$GLOBALS['d_subpath'].'media/icons/'.$row['image'].'" align="'.$row['image_position'].'">';
		  echo $row['description'];
		?>
		</td>
	  </tr>
	  <?php

		global $ordarr;
		$AD=($otype)?"DESC":"ASC";
		$ORD=$ordarr[$order]['sql'];

		global $d_root;
		include_once $d_root.'classes/pagenav.php';
		$pn = new PageNav($params->get('show_count',10), true);
		$rsa = $pn->Slice('#__downloads', '*', "WHERE catid=$catid AND published=1", "ORDER BY $ORD $AD");

		global $my;
	 	if ($my->can_submit()) {?>
	  <tr><td colspan="5"><a href="index.php?option=downloads&amp;task=new&amp;catid=<?php echo $catid.'&amp;Itemid='.$Itemid; ?>"><?php echo _DOWNLOADS_SUBMIT_TO_CATEGORY; ?></a></td></tr><?php }
		
		if(!count($rsa)){
			echo '<tr><td colspan="5">'._DOWNLOADS_NA.'</td></tr>';
			echo '</table>';
			return;
		}
		
		 ?>
	  <tr>
	    <td width="20%" colspan="5" class="dkcom_tableheader">
			  		<form name="form1" method="get" action="index.php">
					<input type="hidden" name="option" value="downloads" />
					<input type="hidden" name="catid" value="<?php echo $catid; ?>" />
					<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
	      <table width="100%"  border="0" cellpadding="1" cellspacing="1">
	        <tr>
	          <td nowrap="nowrap">
			<select name="order" class="dk_inputbox">
					  <?php
						for($i=0;$i<count($ordarr);$i++) {
							echo "<option value=\"$i\" ".(($order==$i)?'selected="selected"':"")." >".$ordarr[$i]['name']."</option>";
						}
					  ?>
	                    </select> <select name="otype" class="dk_inputbox">
	                      <option value="0" <?php echo (!$otype)?"":'selected="selected"';?> ><?php echo _SORT_ASC;?></option>
	                      <option value="1" <?php echo ($otype)?'selected="selected"':"";?> ><?php echo _SORT_DESC;?></option>
	                    </select>
						</td><td align="right" rowspan="2">
						<input type="submit" value="<?php echo _DOWNLOADS_CHANGE_VIEW; ?>" class="dk_button" />
			           </td></tr>
					   <tr>
					   <td>						<?php echo _DOWNLOADS_VIEW_AS; ?>
						<label for="rblist"><input id="rblist" type="radio" name="list" value="0" <?php if (!$list) echo ' checked="checked"'; ?>/><?php echo _DOWNLOADS_VIEW_LIST; ?></label>
						<label for="rbtable"><input id="rbtable" type="radio" name="list" value="1" <?php if ($list) echo ' checked="checked"'; ?>/><?php echo _DOWNLOADS_VIEW_TABLE; ?></label>
				</td></tr><tr>
	          <td nowrap="nowrap" align="right"><?php  echo $pn->NavBar("option=downloads&amp;catid=$catid&amp;list=$list&amp;order=$order&amp;otype=$otype&amp;Itemid=$Itemid",'');?>&nbsp;</td>
	        </tr>
	      </table></form>

	      </td>
	  </tr><?php
	  
	  if ($list) { ?>
	  <tr class="dkcom_tableheader"><td><strong><?php echo _TITLE; ?></strong></td><td><strong><?php echo _DOWNLOADS_ADDED; ?></strong></td><td><strong><?php echo _DOWNLOADS_HITS; ?></strong></td><td colspan="2"><strong><?php echo _DOWNLOADS_FILESIZE; ?></strong></td></tr>
	  <?php }
	  
	  $rowcolor=2;
		foreach($rsa as $row) {
			if($rowcolor==1)$rowcolor=2;
				else $rowcolor=1;
						?>
		  <?php showitem($row,false,$rowcolor,$list); 
			if ($tabcnt == 1)
				$tabcnt = 0;
			else $tabcnt++;

		}
	?>
	<tr><td align="center" colspan="5"><a href="index.php?option=downloads&amp;Itemid=<?php echo $Itemid; ?>"><?php echo _DOWNLOADS_GO_BACK_TO.' '._DOWNLOADS_CATEGORIES; ?></a></td></tr>
	</table>

	<?php
}

function submit_download($catid = null) {
	global $conn,$Itemid,$d, $access_sql,$pathway;
		$pathway->add(_SUBMIT_DOWNLOAD);
		
		$d->add_raw_js('
	function getSelectedValue( srcList ) {
		i = srcList.selectedIndex;
		if (i != null && i > -1) {
			return srcList.options[i].value;
		} else {
			return null;
		}
	}
	
	function submit_download() {
		var form = document.user_form;
		// do field validation
		if (form.download_title.value == ""){
			alert( "Download item must have a title" );
		} else if (!getSelectedValue(form.download_catid)) {
			alert( "You must select a category." );
		} else if ((form.download_url.value == "") && (form.download_file.value == "")) {
			alert( "You must specify an url or a file upload." );
		} else {
			return true;
		}
		return false;
	}
		
		');
?>
	<form enctype="multipart/form-data" action="index.php?option=downloads" method="post" name="user_form" id="user_form" onsubmit="return submit_download()">
<div class="dk_header"><h2><?php echo _SUBMIT_DOWNLOAD;?></h2></div>
  <table cellpadding="4" cellspacing="1" border="0" width="100%">
    <tr>
      <td valign="top" align="right"><?php echo _URL; ?></td>
      <td><input class="dk_inputbox" type="text" name="download_url" value="" size="45" /></td>
    </tr>
    <tr>
      <td valign="top" align="right"><?php echo _DOWNLOADS_FILE; ?>:</td>
      <td><?php echo file_input_field('download_file'); ?></td>
    </tr>
	<tr>
      <td colspan="2">* <?php echo text_to_html(_DOWNLOADS_SUBMIT_DESC); ?><hr /></td>
    </tr>
    <tr>
      <td width="20%" align="right">* <?php echo _TITLE; ?></td>
      <td width="80%">
				<input class="dk_inputbox" type="text" name="download_title" size="45" maxlength="255" value="" />
			</td>
    </tr>
    <tr>
      <td valign="top" align="right">* <?php echo _CAT; ?></td>
      <td><?php $rsa=$conn->SelectArray('#__categories', 'id,name', " WHERE section='com_downloads' ".$access_sql.' ORDER BY ordering'); ?>
        <select  class="dk_inputbox" name="download_catid">
          <option value=""><?php echo _SELECTCAT; ?></option>
		  <?php
		 foreach($rsa as $row) {
			echo '<option value="'.$row['id'].'"';
			if (isset($catid) && ($row['id']==$catid))
				echo 'selected="selected"';
			echo '>'.$row['name']."</option>"; }
		  ?>
        </select>			</td>
    </tr>
    <tr>
      <td width="20%" align="right"><?php echo _AUTHOR; ?></td>
      <td width="80%">
		<input class="dk_inputbox" type="text" name="download_author" size="45" maxlength="250" value="" />
			</td>
    </tr>
    <tr>
      <td width="20%" align="right"><?php echo _DOWNLOADS_WEBSITE; ?></td>
      <td width="80%">
				<input class="dk_inputbox" type="text" name="download_website" size="45" value="" />
			</td>
    </tr>
    <tr>
      <td valign="top" align="right"><?php echo _DESC; ?></td>
      <td><textarea class="dk_inputbox" cols="30" rows="6" name="download_description" style="width:300px;"></textarea>
			</td>
    </tr>
	<?php global $my; if ( $my->can_publish()) { ?>
    <tr>
      <td>&nbsp;</td><td><label for="download_published"><input class="dk_inputbox" name="download_published" id="download_published" type="checkbox" /><?php echo _PUBLISHED; ?></label></td>
		</tr><?php } ?>
    <tr>
      <td valign="top" align="right">&nbsp;</td>
      <td><input type="submit" name="Submit" class="dk_button" value="<?php echo _SUBMIT; ?>" />
        &nbsp;&nbsp;<input type="reset" class="dk_button" value="<?php echo _E_RESET; ?>" /></td>
    </tr>

  </table>

  <input type="hidden" name="task" value="newdownload" />
</form>

	<?php
}

function confirm_submission($catid, $published) {
	global $option, $pathway;
	$pathway->add(_SUBMIT_SUCCESS);
	?>
	<div class="dk_header"><h2><?php echo _SUBMIT_SUCCESS; ?></h2></div>
	<div class="dkcom_userconfirmation"><?php if ($published) echo _ITEM_SAVED_PUBLISHED; else echo _E_ITEM_SAVED; ?>
	<hr /><div align="center">
	<a href="index.php?option=<?php echo $option; ?>&amp;catid=<?php echo $catid; ?>"><?php echo _RETURN_TO_CAT; ?></a></div>
	</div>
	<?php
}


?>