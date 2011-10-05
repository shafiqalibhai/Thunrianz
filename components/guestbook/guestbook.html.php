<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include_once $d_root.'includes/bbcode.php';

function viewbook() {
	global $conn,$Itemid,$params,$d_root;

	include_once $d_root.'classes/pagenav.php';
	$show = $params->get('show_count', 10);
	$pn = new PageNav($show);

	// get the resultset now so that the total number of items is set inside the PageNav instance $pn
	$rsa1 = $pn->Slice('#__guestbook', '*', '', 'ORDER BY date DESC');

	?>
	<div class="dk_header"><h2><?php echo _GUESTBOOK_VIEW;?></h2></div>
	<table width="100%"  border="0" cellpadding="0" cellspacing="0" class="dk_content">
		<tr> <td height="30"><a href="index.php?option=guestbook&amp;task=sign&amp;Itemid=<?php echo $Itemid; ?>"><?php echo _GUESTBOOK_SIGN;?></a></td>
	    <td><div align="right"><?php echo $pn->NavBar('option=guestbook&amp;Itemid=','',true); ?></div></td>
		</tr>
		<tr class="updown">
	    <td width="100%" height="20" colspan="2" class="dk_small"><?php echo _GUESTBOOK_TOTAL_ENTRIES.' '.$pn->Total(); ?> &nbsp;&nbsp;&nbsp;&nbsp;<?php echo _GUESTBOOK_VIEWS_PAGE.' '.$show; ?></td>
		</tr>
	      </table>
	<table width="100%" border="0">
	  <tr>
	    <td  class="dkcom_tableheader"><?php echo _NAME;?></td>
	    <td  class="dkcom_tableheader"><?php echo _GUESTBOOK_COMMENTS;?></td>
	  </tr>
	<?php
	$total = count($rsa1);
	if ($total) {

	$color=1;
	$num = $pn->Total() - ($pn->Page()-1) * $show;

	global $d, $my, $params;
	foreach($rsa1 as $row) {
		// obfuscate the email address
		if ($params->get('email_obfuscate', 1))
			$row['email']=$d->CloakEmail($row['email']);
		++$color;
	?><tr class="dkcom_tablerow<?php echo ($color % 2)?'2':'1';?>">
	    <td width="26%" valign="top">
		<table width="100%" border="0" cellspacing="0" cellpadding="2">
		<tr>
		  <td class="dk_small"><?php echo $num; ?> : <?php echo $row['name']; ?></td>
		</tr>
		<tr>
		  <td><?php echo $row['country']; ?></td>
		</tr>
		<tr>
		  <td>&nbsp;</td>
		</tr>
	      </table>
	    </td>
	    <td width="74%" valign="top">
	      <table width="100%" border="0" cellspacing="0" cellpadding="2">
		<tr>
		  <td class="dk_small"><?php echo _GUESTBOOK_POSTED.' '.lc_strftime(_DATE_FORMAT_LC.' %H:%M',$row["date"]); ?> <hr /></td>
		</tr>
		<tr><td><strong><?php echo $row['title']; ?></strong> : <br /><?php echo bbdecode($row['message'], false); ?></td>
		</tr><?php
		if(strlen($row['reply'])) { ?><tr><td>
		<?php echo '<strong>'._GUESTBOOK_REPLY.'</strong>:'.$row['reply'];?>
		</td></tr><?php } ?>
		<tr><td><?php
		global $my;
		if ($my->gid>=$params->get('email_show', 1))
			echo "<a href='mailto:".$row['email']."'>[ Email ]</a> ";
		if (is_url($row["url"]))
			echo _bburl_replace(array(0, '', $row['url']));
		?><br />
	</td>
		</tr>
	      </table>
	    </td>
	  </tr><?php
		$num--;
	}

	}?>
	</table>
<?php
}

function post_entry() {
	global $Itemid,$d_root,$my,$params; ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="dk_content">
  <tr>
    <td colspan="2"><table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="100%" class="dk_header"><?php echo _GUESTBOOK_SIGN;?></td>
        </tr>
                <tr>
          <td>&nbsp;</td>
        </tr>
                <tr>
          <td>
<script language="javascript" type="text/javascript">

function smile(type) {
	var f=document.forms.message;
	f.gb_message.value = f.gb_message.value + " " + type + " ";
	f.gb_message.focus();
}

function check_gb_form() {
	var f=document.message;
	if (<?php if (!$my->gid) { ?>(f.gb_name.value=='')||(f.gb_email.value=='')||<?php } ?>(f.gb_title.value=='')||(f.gb_message.value=='')) {
		alert('<?php echo str_replace("'", "\\'", _FORM_NC);?>');
	    return false;
	}<?php if (!$my->gid) {?>
	var email_regex = new RegExp("^[\\w-_\.]*[\\w-_\.]\@[\\w]\.+[\\w]+[\\w]$");
	if (!email_regex.test(f.gb_email.value)) {
		alert('<?php echo js_enc(_GUESTBOOK_INVALID_EMAIL);?>');
	    return false;
	}<?php } ?>
	return true;
}
</script>
<form name="message" method="post" action="index.php?option=guestbook&amp;Itemid=<?php echo $Itemid; ?>" onsubmit="return check_gb_form();">
              <table width="98%" border="0" align="center" cellpadding="4" cellspacing="0">
<?php if (!$my->gid) { ?>
                <tr>
                  <td><label for="gb_name"><strong><?php echo _NAME;?>*:</strong></label></td>
                  <td> <input name="gb_name" type="text" id="gb_name" size="30" maxlength="20" class="dk_inputbox" />
      </td>
                </tr>
                <tr>
                  <td><label for="gb_email"><strong><?php echo _EMAIL;?>*:</strong></label></td>
                  <td><input name="gb_email" type="text" id="gb_email" size="30" maxlength="50"  class="dk_inputbox" /></td>
                </tr>
<?php } ?>
                <tr>
                  <td><label for="gb_url"><?php echo _URL;?>:</label></td>
                  <td><input name="gb_url" type="text"  class="dk_inputbox" id="gb_url" value="http://" size="30" maxlength="100" /></td>
                </tr>
                <tr>
                  <td><label for="gb_country"><?php echo _GUESTBOOK_COUNTRY;?>:</label></td>
                  <td><input name="gb_country" type="text" id="gb_country" size="30" maxlength="20"  class="dk_inputbox" /></td>
                </tr>
                <tr>
                  <td><label for="gb_title"><strong><?php echo _GUESTBOOK_HEADLINE;?>*:</strong></label></td>
                  <td><input name="gb_title" type="text" id="gb_title" size="30" maxlength="50" class="dk_inputbox" /></td>
                </tr>
                <tr>
                  <td valign="top"><label for="gb_message"><strong><?php echo _GUESTBOOK_MESSAGE;?>*:</strong>&nbsp;&nbsp;</label></td>
                  <td><textarea name="gb_message" cols="25" rows="5" id="gb_message" class="dk_inputbox"></textarea></td>
                </tr>
                <tr>
                  <td valign="top"><input name="task" type="hidden" id="task" value="insert" /></td>
                  <td><?php include $d_root.'includes/smileys.php'; ?></td>
                </tr>
<?php	$cl = $params->get('captcha', 1);
	if ( ($cl != 9) && ($my->gid < $cl)) {
			global $_DRABOTS;
			$_DRABOTS->loadBotGroup('captcha');
			?><tr><td><?php
				$_DRABOTS->trigger('OnCaptchaRender', array('guestbook'));
		    ?></td></tr>
                <?php } ?>
                <tr>
                  <td>&nbsp;</td>
                  <td><input type="submit" name="Submit" value="<?php echo _GUESTBOOK_SIGN;?>" class="dk_button" />
                    &nbsp;
                    <input type="button" value="<?php echo _CANCEL;?>" class="dk_button" onclick="document.location='index.php?option=guestbook&amp;Itemid=<?php echo $Itemid; ?>'" /></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td><strong>*</strong><?php echo _GUESTBOOK_REQUIRED;?></td>
                </tr>
              </table>
            </form>
</td>
        </tr>
      </table></td></tr></table>

<?php }

?>
