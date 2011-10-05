<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function showsection($id) {
	global $conn,$Itemid,$d_website,$access_sql,$d_subpath,$pathway;

	$srow=$conn->SelectRow('#__sections', '*', " WHERE id=$id AND published=1 $access_sql");
	if (!isset($srow['id'])) {
		CMSResponse::Unauthorized();
		return;
	}

	$pathway->add($srow['title']);
	global $d;
	$d->add_meta($srow['description']);

	if ( !empty($srow) ) { ?>
	  <div class="dk_header"><h2><?php echo $srow['title']; ?></h2></div>
	  <div class="dkcom_section">
	    <div class="dk_content">
	    <?php
	    if($srow['image']!='')echo '<img src="media/icons/'.$srow['image'].'" align="'.$srow['image_position'].'" alt="'.$srow['title'].'" class="dk_category-icons" />';
	    echo $srow['description'];
	    ?>
	    </div>
	  </div>
	<?php
	/*
	  Jcauble - Have this in here twice as a div so
		    that the sections will be listed below
		    the image.  Allows it to clear by having the
		    clear:both tag in the css file.
	  */
	?><div class="dkcom_section">
	    <div>&nbsp;</div>
		<?php
		// display a row for each category
		$rsa = $conn->SelectArray('#__categories', 'id,name,description,image,count', " WHERE section=$id $access_sql ORDER BY ordering");
		global $time;
		foreach($rsa as $crow) {
			$checked_items = adjust_count($crow['count'], $crow['id'], '#__content', 'published=1 AND created<'.$time);
		?><table width="100%" border="0" cellspacing="0" cellpadding="6">
		  <tr><?php if($crow['image']!=='')echo '<td width="10%" valign="top" ><img src="media/icons/'.$crow['image'].'" alt="'.substr($crow['image'], 0, strpos($crow['image'], '.')).'" /></td>';?>
		<td  width="100%" valign="middle">
		  <a href="index.php?option=content&amp;task=category&amp;id=<?php echo $crow['id']; ?>&amp;Itemid=<?php echo $Itemid.content_sef($crow['name']); ?>"><?php echo $crow['name']; ?></a> <i>( <?php echo $checked_items.' '._CHECKED_IN_ITEMS; ?> )</i><br />
		<?php echo $crow['description']; ?></td>
		</tr>
	      </table>
	    <?php } ?>
	    </div>
	<?php
	}
}

function showblog($id) {
	global $conn,$Itemid,$my,$id,$d_website,$access_sql,$d,$d_subpath,$pathway,$params;
	$blog_show_count = $params->get('blog_show_count', 30);
	$crow=$conn->SelectRow('#__categories', '*', " WHERE id=$id $access_sql");
	if (!$crow) {
		CMSResponse::Unauthorized();
		return;
	}
	$pathway->add($crow['name']);
	?><div class="dk_header"><h2><?php echo $crow['name']; ?></h2></div>
	<div class="dkcom_section">
	<div class="dk_content">
      <?php
    if($crow['image']!=='')echo '<img src="media/icons/'.$crow['image'].'" align="'.$crow['image_position'].'" alt="'.$crow['name'].'" class="dk_category-icons"/>';
    echo $crow['description'];
  ?>
    </div>
  </div>
  <div>&nbsp;</div>
  <div class="dkcom_section">
	<?php // still to be completed
	//Jcauble - dup content_section class used to clear the description block.

	global $d_root, $time;
	include_once $d_root.'classes/pagenav.php';
	$pn = new PageNav($blog_show_count);
	$rsa = $pn->Slice('#__content', '*', "WHERE catid=$id AND published=1 $access_sql AND created<$time", 
					'ORDER BY ordering');
	
	foreach($rsa as $crow) {
		showcontent($crow['id'],"blog",true,$crow,'showblog');
	}
  ?>
  </div>
<div class="dkcom_tableheader" align="center"><?php
	echo $pn->NavBar("option=content&amp;task=showblog&amp;id=$id&amp;Itemid=$Itemid",'');
?></div><?php
}

function showcategory($id) {
	global $conn, $Itemid, $d_website, $access_sql, $d_subpath, $d, $pathway, $params;
	$show = $params->get('category_show_count', 10);
	$crow=$conn->SelectRow('#__categories', '*', " WHERE id=$id $access_sql");

	if ($crow)  {
		$srow = $conn->SelectRow('#__sections', 'title', ' WHERE id='.$crow['section'].' '.$access_sql);
		if (!$srow) {
			CMSResponse::Unauthorized();
			return;
		}		
		$pathway->add($srow['title'], 'option=content&task=section&id='.$crow['section'].
					'&Itemid='.$Itemid);
		$pathway->add($crow['name']);

		$d->add_meta($crow['description']);
	?>
	  <div class="dk_header"><h2><?php echo $crow['name']; ?></h2></div>
	  <div class="dkcom_section">
	    <div class="dk_content">
	    <?php
	    if($crow['image']!='')echo '<img src="media/icons/'.$crow['image'].'" align="'.$crow['image_position'].'" alt="'.$crow['name'].'" class="dk_category-icons"/>';
	    echo $crow['description'];
	    ?>
	    </div>
	  </div>
	  <div class="dkcom_section">
	    <div>&nbsp;</div>
	  <?php
		global $d_root, $time;
		include_once $d_root.'classes/pagenav.php';
		$pn = new PageNav($show);
		$rsa = $pn->Slice('#__content',
			'id,title,catid,mask,created,created_by_alias,published,ordering,hits', "WHERE catid=$id AND published=1 $access_sql AND created<$time",
			'ORDER BY ordering');
		
		$colspan = 4;

	    if (isset($rsa[0])) {
	  ?><div>
	      <table width="100%" border="0" cellpadding="1" cellspacing="0">
		      <tr>
				<?php $hide_created = $params->get('hide_created', 0);
				  if (!$hide_created) { ?>
			<td align="center" class="dkcom_tableheader"><?php echo _PUBLISHED; ?></td>
				<?php } else --$colspan; ?>
			<td class="dkcom_tableheader"><?php echo _TITLE; ?></td>
					<?php 
					$hide_author = $params->get('hide_author', 0);
					if (!$hide_author) { ?>
			<td align="center" class="dkcom_tableheader"><?php echo _AUTHOR; ?></td>
					<?php } else --$colspan; ?>
			<td align="center" class="dkcom_tableheader"><?php echo _HITS; ?></td>
		      </tr>
		      <?php
			$tabclass_arr=array("dkcom_tablerow1","dkcom_tablerow2");
			$tabcnt = 0;
			
			// display a row for each content item
			foreach($rsa as $row) {
			$flags = content_flags($row['mask']);
			?>
		    <tr class='<?php echo $tabclass_arr[$tabcnt]; ?>'>
				<?php if (!$hide_created) { ?>
			<td align="center" width="15%"><?php if ($flags['hide_created']) echo _NA; else echo $d->DateFormat($row['created']); ?></td>
				<?php } ?>
			<td><a href="index.php?option=content&amp;task=view&amp;id=<?php echo $row['id']; ?>&amp;catid=<?php echo $id; ?>&amp;Itemid=<?php echo $Itemid.content_sef($row['title']); ?>"><?php echo $row['title']; ?></a></td><?php if (!$hide_author) { ?>
			<td align="center" width="10%" ><?php echo $flags['hide_author']?_NA:$row['created_by_alias']; ?></td>
					<?php } ?>
			<td align="center" width="5%" ><?php echo $row['hits']; ?></td>
		      </tr>
		      <?php
		    if ($tabcnt == 1)$tabcnt = 0;
		    else $tabcnt++;
		}
		?>
		      <tr>
			<td colspan="<?php echo $colspan; ?>"  class="dkcom_tableheader"><?php
		    echo $pn->NavBar("option=content&amp;task=category&amp;id=$id&amp;Itemid=$Itemid",'');
		     ?></td>
		      </tr>
	      </table>
	    </div>
	   <?php
	     }
	   ?>
	  </div>

	<?php
	} else {
		CMSResponse::Unauthorized();
	}

}

  /**
  * Writes Email form for filling in the send destination

  *  JCauble - Left this alone except to normalize the class names
               it is more trouble than it's worth to try and convert
               this table to be tableless for a popup.
  */
function email_form($id) {
	global $d_title,$conn,$access_sql,$my, $pathway, $d, $time;
	$crow=$conn->SelectRow('#__content', 'title', " WHERE id=$id AND published=1 $access_sql AND created<$time");
	if (empty($crow)) {
		CMSResponse::Unauthorized();
		return;
	}
	$content_title=$crow['title'];
	
	$pathway->add($content_title);

//    var email_regex = new RegExp("^[0-9a-z\\\\._]+@[0-9a-z]+\\\\..+$","i");
	$d->add_raw_js('
    var email_regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,4}$/;

    function frontendForm_validate() {
      var form = document.getElementById(\'send_to_friend_form\');
      // do field validation
      if (!email_regex.test(form.f_email.value)
        || !email_regex.test(form.f_youremail.value)
        ) {
        alert("'.js_enc(_EMAIL_ERR_NOINFO).'");
        return false;
      }
      return true;
    }');
?><br /><br /><div id="container"><div id="content"><form action="index2.php?option=content&amp;task=send_email&amp;id=<?php echo $id;?>" id="send_to_friend_form" method="post" onsubmit="return frontendForm_validate();">
    <table cellspacing="0" cellpadding="0" border="0">
    <tr>
      <td colspan="2">
      <?php echo _EMAIL_FRIEND; ?>
      </td>
    </tr>
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td width="130">
      <?php echo _EMAIL_FRIEND_ADDR; ?>
      </td>
      <td>
      <input type="text" name="f_email" class="dk_inputbox" size="25"/>
      </td>
    </tr>
    <tr>
      <td height="27">
      <?php echo _EMAIL_YOUR_NAME; ?>
      </td>
      <td>
      <input type="text" name="f_yourname" class="dk_inputbox" size="25" value="<?php echo $my->name;?>"/>
      </td>
    </tr>
    <tr>
      <td>
      <?php echo _EMAIL_YOUR_MAIL; ?>
      </td>
      <td>
      <input type="text" name="f_youremail" class="dk_inputbox" size="25" value="<?php echo $my->email;?>"/>
      </td>
    </tr>
    <tr>
      <td>
      <?php echo _SUBJECT_PROMPT; ?>
      </td>
      <td>
      <input type="text" name="f_subject" class="dk_inputbox" maxlength="100" size="40" value="<?php echo $content_title;?>"/>
      </td>
    </tr>
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2"><?php
		global $params;
		$cl = $params->get('email_captcha_level', 1);
		if (($cl != 9) && ($my->gid < $cl)) {
			global $_DRABOTS;
			$_DRABOTS->loadBotGroup('captcha');
			$_DRABOTS->trigger('OnCaptchaRender', array('content'));
		}
      ?></td>
    </tr>
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2">
      <input type="submit" name="btnsubmit" class="dk_button" value="<?php echo _SEND; ?>" />
      &nbsp;&nbsp; <input type="button" name="cancel" value="<?php echo _CANCEL; ?>" class="dk_button" onclick="window.close();"/>
      </td>
    </tr>
    </table>
    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    </form></div></div>
    <?php
  }

function email_sent( $id ) {
	global $d_title,$d_website,$d,$pathway, $params, $my;
	$cl = $params->get('email_captcha_level', 1);
	if ($my->gid < $cl) {
		if (!$my->valid_captcha('content'))
			return;
	}

	if ( '' === ($f_email = in_raw('f_email', $_POST, '', 100))
		or '' === ($f_youremail = in_raw('f_youremail', $_POST, '', 100))
		or '' === ($f_yourname = in_raw('f_yourname', $_POST, '', 100))
		or '' === ($f_subject = in_raw('f_subject', $_POST, '', 200))
		) {
	      CMSResponse::Redir('index.php?option=content&task=email&id='.$id, _FORM_NC);
	      break;
	}
	if (!is_email($f_youremail) || !is_email($f_email)) {
		CMSResponse::Redir('index.php?option=content&task=email&id='.$id, _EMAIL_NOT_VALID);
		break;
	}
	  
	$pathway->add(_EMAIL_TITLE);

	$msg = sprintf( _EMAIL_MSG, $d_title, $f_yourname, $f_youremail,
				$d_website.'index.php?option=content&task=view&id='.$id.'&Itemid='.$GLOBALS['Itemid']);
	global $d_root;
	include_once $d_root.'classes/gelomail.php';
	$m = new GeloMail();

	?><br /><br /><div id="container"><div id="content"><span class="dk_header"><?php
	if ($m->Send($f_email, $f_subject, $msg, $f_yourname, $f_youremail))
		echo sprintf(_EMAIL_SENT, xhtml_safe($f_email));
	else
		echo sprintf(_EMAIL_NOT_SENT, xhtml_safe($f_email));
	?></span> <br />
    <br />
    <br />
    <a href='javascript:window.close();'>
    <span class="dk_small"><?php echo _PROMPT_CLOSE;?></span>
    </a></div></div><?php
}

function _content_editing_auth() {
	//L: this function allows to submit news also to a specific section (currently not used)
	//TODO: sections could theorically be merged to categories (see SQL audit tracker item)
	global $conn,$my,$access_sql,$access_acl;
	$srows=$conn->SelectArray('#__sections', 'id,title', ' WHERE '.$access_acl.' ORDER BY title');
	if (!count($srows)) {
		CMSResponse::ContentUnauthorized();
		return null;
	}

//L: this function needs major debugging
	$allowed_cats = section_submission_categories($srows);
	if (count($allowed_cats)==0) {
		CMSResponse::ContentUnauthorized(true, '<br />'._CONTENT_NO_EDITOR_ACCESS);
		return null;
	}

	return array($allowed_cats, $srows);
}

function new_content($sid = null, $alias = '') {
	$a = _content_editing_auth();
	if (!isset($a))
		return;
	list($allowed_cats, $rsa) = $a;

	if (!isset($sid))	// new item
		$section_row = $rsa[0];
	else {
	    foreach ($rsa as $sect) {
			if ($sect['id'] == $sid) {
				$section_row = $sect;
				break;
			}
		}
	}
	
	// the section was not accessible
	if (!isset($section_row))
		trigger_error('SECTION NOT ACCESSIBLE');
	
	content_editor($section_row, $allowed_cats, null, $alias);
}

function edit_content($id) {
	$a = _content_editing_auth();
	if (!isset($a))
		return;
	list($allowed_cats, $srows) = $a;

	// check if the single content item was not accessible
	global $conn, $access_sql, $time;
	$crow=$conn->SelectRow('#__content', '*', " WHERE id=$id $access_sql AND created<$time");
	if (!count($crow)) {
		CMSResponse::ContentUnauthorized();
		return;
	}
	
	$section_row = null;
	foreach ($srows as $sect) {
		if ($sect['id'] == $crow['sectionid']) {
			$section_row = $sect;
			break;
		}
	}
	
	// the section was not accessible
	if (!isset($section_row)) {
		CMSResponse::ContentUnauthorized();
		return;
	}
	
	global $my;
	if (!$my->can_edit($crow['userid'])) {
		// show inset error here
		CMSResponse::ContentUnauthorized(true);
		return;
	}
	
	content_editor($section_row, $allowed_cats, $crow);
}

function content_editor($section_row, &$allowed_categories, $content_row = null, $alias = '') {
global $d, $my, $d_root, $pathway;

$d->add_js('lang/'.$my->lang.'/js/commonwords.js');

$d->add_js(rel_com_path('autokeywords.js'));

$d->add_raw_js('function submitcontent() {
    var form = document.adminForm;
    // do field validation
      if (form.content_title.value == "") {
			alert ( \''.js_enc(_E_WARNTITLE).'\' );
      } else if (!getSelectedValue(document.adminForm.content_catid)) {
			alert ( \''.js_enc(_E_WARNCAT).'\' );
        } else if (form.content_introtext.value == "") {
			alert ( \''.js_enc(_E_WARNTEXT).'\' );
      } else {
		var af=document.getElementById("autofills");
		if (af.checked) {
			var k=document.getElementById("content_metakey");
			var d=document.getElementById("content_metadesc");
			if (k.value=="")
				ak_fill(form);
			if (d.value=="")
				ad_fill(form);
		}
        form.submit();
      }
  }');

	global $Itemid;
	$pathway->add_head($section_row['title'], 'option=content&task=section&id='.$section_row['id'].'&Itemid='.$Itemid);
	if(isset($content_row)) {
		global $_DRABOTS;
		$_DRABOTS->loadCoreBotGroup('editor');
		$_DRABOTS->trigger('OnContentEdit', array(&$content_row['introtext']));
		$_DRABOTS->trigger('OnContentEdit', array(&$content_row['bodytext']));
		$title = _E_EDIT;
	} else $title=_ADD;
  $title .= ' '._E_CONTENT;
  
  $pathway->add($title.' '.@$content_row['title']);
  global $Itemid;
?><div class="dk_header"><h2><?php echo $title; ?></h2></div>
<form name="adminForm" method="post" action="index.php?option=content&amp;Itemid=<?php echo $Itemid; ?>">
  <div class="dk_form">
    <div class="dk_content"><?php echo _E_TITLE; ?></div>
    <div class="dk_content"><input class="dk_inputbox" type="text" name="content_title" size="45" maxlength="100" value="<?php    if(isset($content_row)) echo $content_row['title'];?>" /></div>
	<div class="dk_content"><?php echo _CONTENT_TITLE_ALIAS; ?>:</div>
    <div class="dk_content"><input class="dk_inputbox" type="text" name="content_alias" size="45" maxlength="100" value="<?php if(isset($content_row)) echo $content_row['title_alias']; else echo $alias; ?>" /></div>
    <div class="dk_content"><?php echo _CAT; ?></div>
    <div class="dk_content">
      <select class="dk_inputbox"  name="content_catid">
          <option value=""><?php echo _SELECTCAT; ?></option>
      <?php
      foreach($allowed_categories as $row) {
        $sel='';
        if($content_row and $row['id']==$content_row['catid'])$sel='selected="selected"';
        echo "<option value='".$row['id']."' $sel >".$row['title']."</option>";
    } ?>
        </select><?php if (isset($content_row)) { ?>
      <input type="hidden" name="content_ocatid" value="<?php echo $content_row['catid'];?>"/>
	  <?php } ?>
    </div>
    <div class="dk_content"><?php echo _E_INTRO.' ('._REQUIRED.')'; ?></div>
    <div class="dk_content">
      <?php
	if (isset($content_row))
		$intro = $content_row['introtext'];
	else $intro = '';
	global $d;
	echo $d->AdvancedEditor('content_introtext',$intro,20,60,"width=500");
      ?>
    </div>
    <div class="dk_content"><?php echo _E_MAIN.' ('._OPTIONAL.')'; ?>:</div>
    <div class="dk_content">
	<?php
	if (isset($content_row))
		$body = $content_row['bodytext'];
	else $body = '';
	echo $d->AdvancedEditor('content_bodytext',$body,20,60,"width=500");

	?>
    </div>
	<div class="dk_content">
	<p><?php echo _CONTENT_KEYWORDS; ?></p>
	<textarea id="content_metakey" name="content_metakey" rows="2" cols="10"><?php echo $content_row['metakey']; ?></textarea>
	<input class="dk_button" type="button" onclick="<?php
	//FIXME: inconsistent for single area save
	echo $d->EditorSaveJSMultiple(array('content_introtext', 'content_bodytext'));?>ak_fill(document.adminForm)" value="<?php echo _CONTENT_AUTO_FILL; ?>"/>
	</div>
	<div class="dk_content">
	<p><?php echo _CONTENT_DESCRIPTION; ?></p>
	<textarea id="content_metadesc" name="content_metadesc" rows="2" cols="30" style="width:300px"><?php echo $content_row['metadesc']; ?></textarea>
	<input class="dk_button" type="button" onclick="javascript:<?php echo $d->EditorSaveJSMultiple(array('content_introtext', 'content_bodytext'));?> ad_fill(document.adminForm)" value="<?php echo _CONTENT_AUTO_FILL; ?>"/>
	</div>
	<div class="dk_content">
	<label for="autofills"><input id="autofills" checked="checked" type="checkbox"/><?php echo _CONTENT_AUTO_META; ?></label>
	</div>
	<?php if ($my->can_publish()) { ?>
<div class="dk_content">
        <label for="content_frontpage">
<input id="content_frontpage" name="content_frontpage" type="checkbox" <?php if ($content_row && $content_row['frontpage']) echo ' checked="checked"'; ?>/><?php echo _CONTENT_SHOW_ON_FRONTPAGE; ?></label>
    </div>
    <div class="dk_content">
        <label for="content_published">
<input id="content_published" name="content_published" type="checkbox" <?php if ($content_row && $content_row['published']) echo ' checked="checked"'; ?>/><?php echo _PUBLISHED; ?></label>
</div>
    <?php } ?>
    <div class="dk_content">
        <input type="hidden" name="content_secid" value="<?php echo $section_row['id'];?>"/>
        <input type="hidden" name="content_id" value="<?php echo @$content_row['id'];?>"/>
        <input type="hidden" name="task" value="<?php echo ($content_row!=null)?'edit':'new'; ?>_content"/>
        <input class="dk_button" type="button" onclick="<?php echo $d->EditorSaveJSMultiple(array('content_introtext', 'content_bodytext')); ?>submitcontent()" name="Submit" value="<?php if ($content_row) echo _E_SAVE;else echo _SUBMIT; ?>"/>
        <input class="dk_button" type="reset" value="<?php echo _E_RESET; ?>" />
    </div>
  </div>
</form>
<?php
}

function showarchive($year, $month = null) {
	global $time,$conn,$access_sql,$my,$d,$d_website, $pathway, $Itemid;

	$pathway->add(_CONTENT_ARCHIVE);
	
	// get all dates and split them into months
	$cdates = $conn->SelectColumn('#__content', 'created', ' WHERE published=4 '.$access_sql.
		' AND created<'.$time.' ORDER BY created DESC');

	// get number of content items per month
	if (count($cdates)) {
		$end_y = lc_date("Y", $cdates[0]);
		$start_y = lc_date("Y", $cdates[count($cdates)-1]);

		$v_months = array();
		for($i=1;$i<12;++$i) {
			$sdate = lc_mktime(0,0,0,$i,1,$year);
			// check for December
			if ($i==12)
				$edate = lc_mktime(0,0,0,1,1,$year+1);
			else
				$edate = lc_mktime(0,0,0,$i+1,1,$year);

			foreach($cdates as $c_date) {
				// check if there is a content item in this month
				if (($c_date>$sdate) && ($c_date<$edate)) {
					// set or increment
					if (!isset($v_months[$i]))
						$v_months[$i] = 1;
					else
						++$v_months[$i];
				}
			}
		}
	} else {
		$start_y = $end_y = lc_date('Y');
		$v_months = array();
	}
	
?><div class="dkcom_section">
   <form name="form1" method="post" action="index.php?option=content&amp;task=archive&amp;Itemid=<?php echo $Itemid; ?>">
    <select name="month" class="dk_inputbox">
	<option value="0" <?php if(!isset($month))echo "selected=\"selected\"";	?>>-- <?php echo _CONTENT_ARCHIVE_ALL_MONTHS; ?></option>
    <?php  foreach($v_months as $i => $tc) { ?>
      <option value="<?php echo $i; ?>" <?php if($i==$month)echo "selected=\"selected\""; ?>><?php echo lc_strftime('%B',lc_mktime(0,0,0,$i,1,$year), 'ucfirst').' ('.$tc.')'; ?></option>
    <?php } ?>
    </select>
    <select name="year" class="dk_inputbox" style="width:60px;">
    <?php
    
	for ($i=$start_y;$i<=$end_y;$i++) { ?>
	<option value="<?php echo $i; ?>" <?php if($i==$year)echo"selected=\"selected\""; ?>><?php echo $i;?></option>
    <?php
    }
    ?>
    </select>
  <input type="submit" class="dk_button" />
</form>
  </div>
  <div class="dkcom_section">
  <?php
	// full year
	if (!isset($month)) {
		$s_month = 1; $e_month = 12;
	} else {
		$s_month = $month; $e_month = $month+1;
	}
	$sdate=lc_mktime(0,0,0,$s_month,1,$year);
	if (isset($month))
		$str_success=sprintf(_ARCHIVE_SEARCH_SUCCESS,lc_strftime('%B', $sdate, 'ucfirst'),$year);
	else
		$str_success=sprintf(_ARCHIVE_SEARCH_SUCCESS_YEAR, $year);
	if ($e_month==12) {
		$month=1; $t_year=$year+1;
	} else $t_year = $year;
	$edate=lc_mktime(0,0,0,$e_month,1,$t_year);
	
	$rsa=$conn->SelectArray('#__content', /*'id,created,published,access'*/'*', " WHERE published=4  AND created>$sdate AND created<$edate $access_sql ORDER BY ordering");

  if (count($rsa)) { ?>
    <div class="dk_aligncenter"><?php echo $str_success;?><br /><br /></div>
    <div>
      <?php
		foreach($rsa as $crow) {
			showcontent($crow['id'],"archive",true,$crow);
		}
       ?>
     </div>
     <?php
       }else { ?>
       <div  class="dk_aligncenter"><br /><br />
       <?php
	if (isset($month))
		echo sprintf(_ARCHIVE_SEARCH_FAILURE,lc_strftime('%B', $sdate, 'ucfirst'), $year);
	else
		echo sprintf(_ARCHIVE_SEARCH_FAILURE_YEAR, $year);
	?><br /><br/>
       </div>
       <?php
       }
?>
  </div>
<?php

}

function confirm_submission($catid) {
	global $option, $pathway;
	
	$pathway->add(_SUBMIT_SUCCESS);
	
	?>
	<div class="dk_header" ><h2><?php echo _SUBMIT_SUCCESS; ?></h2></div>
	<div class="dkcom_userconfirmation"><?php echo _E_ITEM_SAVED; ?>
	<hr /><div align="center">
	<a href="index.php?option=<?php echo $option; ?>&amp;task=category&amp;id=<?php echo $catid; ?>"><?php echo _RETURN_TO_CAT; ?></a></div>
	</div>
	<?php
}


?>