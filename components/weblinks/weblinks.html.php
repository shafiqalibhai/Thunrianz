<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function view_links($catid) {
	global $conn,$Itemid,$d_website,$params, $d, $access_sql;

	$show = $params->get('show_count',30) ;
	$hide_hits = $params->get('hide_hits', 0);

if (!isset($catid)) {
 ?><div class="dk_header"><h2><?php echo _WEBLINKS_TITLE; ?></h2></div>
 <table width="100%" border="0" cellpadding="4" cellspacing="0" class="dk_content">
 <tr><td colspan="2"> <?php echo _WEBLINKS_DESC; ?></td></tr>
  <tr><td>&nbsp;</td></tr>
  	<?php
		$rsa=$conn->SelectArray('#__categories', 'id,name,description,ordering,count'," WHERE section = 'com_weblinks' $access_sql ORDER BY ordering ASC");
		if (isset($rsa[0])) {
			?>
			<tr><td colspan="2"><table width="100%" cellpadding="2" cellspacing="0">
      <tr>
    <td  class="dkcom_tableheader" width="80%"><?php echo _NAME; ?></td>
    <td  class="dkcom_tableheader" align="center" width="20%"><?php echo _WEBLINKS_TITLE; ?></td>
  </tr>
			<?php
			$catname='';
		foreach ($rsa as $row) {?>
	    <tr > <td ><a href="index.php?option=weblinks&amp;catid=<?php echo $row['id']; ?>&amp;Itemid=<?php echo $Itemid.content_sef($row['name']); ?>" ><?php echo $row['name']; ?></a><br /><?php echo$row['description']; ?></td>
          <td align="center"><?php echo $row['count']; ?></td>
          </tr>
			<?php } ?>
			</table></td></tr>
			<?php
				}
			} else {
				$row=$conn->SelectRow('#__categories', '*', " WHERE id=$catid $access_sql");
				if (empty($row)) {
					echo '</table>';
					return;
				}
				//L: should check for no categories here
				global $pathway;
				 $pathway->add($row['name'], "option=weblinks&catid=$catid&Itemid=$Itemid");
				?><div class="dk_header"><h2><?php echo $row['name'];?></h2></div>
 <table width="100%" border="0" cellpadding="4" cellspacing="0" class="dk_content">
  <tr><td colspan="2">      <?php
	  if($row['image']!='')echo '<img src="'.$GLOBALS['d_subpath'].'media/icons/'.$row['image'].'" align="'.$row['image_position'].'" alt="'.$row['name'].'" />';
	  echo $row['description'];
	?>
	</td></tr>
					<tr><td>
			<table width="100%" cellpadding="2" cellspacing="0">
        <tr>
          <td  class="dkcom_tableheader" width="80%"><?php echo _WEBLINKS_TITLE; ?></td>
	  <?php if (!$hide_hits) { ?>
          <td  class="dkcom_tableheader" align="center" width="20%"><?php echo _HITS; ?></td>
	  <?php } ?>
        </tr>
        <?php
			global $d_root;
			include_once $d_root.'classes/pagenav.php';
			$pn = new PageNav($show);
			$rsa = $pn->Slice('#__weblinks', 'id,title,description'.($hide_hits ? '' : ',hits'), 'WHERE catid='.$catid.' AND published=1', 'ORDER BY ordering ASC');
			foreach($rsa as $row) { ?>
	        <tr >
				<td><a href="index.php?option=weblinks&amp;task=visit&amp;id=<?php echo $row['id'].'&amp;Itemid='.$Itemid.content_sef($row['title']); ?>" target="_blank"><?php echo $row['title']; ?></a><br />
            	<?php echo $row['description']; ?></td>
		<?php if (!$hide_hits) { ?>
		        <td align="center"><?php echo $row['hits']; ?></td>
		<?php } ?>
    	    </tr>
	        <?php }		?>
			<tr>
        	<td colspan="2"><?php
			echo $pn->NavBar("option=weblinks&amp;catid=$catid&amp;Itemid=$Itemid"); ?></td>
	        </tr>
			</table>
			</td>
			</tr>
<?php } ?>
</table><?php
}

function submitlink() {
	global $conn,$Itemid,$d, $access_sql, $pathway;
	
	$pathway->add(_SUBMIT_LINK);

	functionJS();
?>
	<form action="index.php?option=weblinks" method="post" name="adminForm" id="adminForm" onsubmit="return submit_link()">
<div class="dk_header"><h2><?php echo _SUBMIT_LINK;?></h2></div>
  <table cellpadding="4" cellspacing="1" border="0" width="100%">

    <tr>

      <td width="20%" align="right"><label for="link_title"><?php echo _NAME; ?></label></td>
      <td width="80%">
				<input class="dk_inputbox" type="text" name="link_title" id="link_title" size="50" maxlength="250" value="" />
			</td>
    </tr>
    <tr>
      <td valign="top" align="right"><label for="link_catid"><?php echo _SECTION; ?></label></td>
      <td><?php $rsa=$conn->SelectArray('#__categories', 'id,name', " WHERE section='com_weblinks' $access_sql"); ?>
        <select  class="dk_inputbox" name="link_catid" id="link_catid">
          <option value=""><?php echo _SELECTCAT; ?></option>
		  <?php
			foreach($rsa as $row) { echo "<option value='".$row['id']."'>".$row['name']."</option>"; }
		  ?>
        </select>			</td>
    </tr>
    <tr>
      <td valign="top" align="right"><label for="link_url"><?php echo _URL; ?></label></td>
      <td>
			<input class="dk_inputbox" type="text" name="link_url" id="link_url" value="" size="50" />
			</td>
    </tr>
    <tr>
      <td valign="top" align="right"><label for="link_description"><?php echo _DESC; ?></label></td>
      <td>
				<textarea class="dk_inputbox" cols="30" rows="6" name="link_description" id="link_description" style="width:300px;"></textarea>
			</td>
    </tr>
    <tr>
      <td valign="top" align="right">&nbsp;</td>
      <td><input class="dk_button" type="submit" name="Submit" value="<?php echo _ADD; ?>" />
        <input class="dk_button" type="reset" value="<?php echo _E_RESET; ?>" /></td>
    </tr>


  </table>

  <input type="hidden" name="task" value="newlink" />
</form>
<?php
}

function confirm_submission($catid) {
	global $option;
	
	?>
	<h2 class="dk_header" ><?php echo _SUBMIT_SUCCESS; ?></h2>
	<div class="dkcom_userconfirmation"><?php echo _E_ITEM_SAVED; ?>
	<hr /><div align="center">
	<a href="index.php?option=<?php echo $option; ?>&amp;task=category&amp;id=<?php echo $catid; ?>"><?php echo _RETURN_TO_CAT; ?></a></div>
	</div>
	<?php
}


?>