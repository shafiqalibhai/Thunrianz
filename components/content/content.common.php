<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

// function to allow {pagebreak} usage
function &pages_break(&$text, &$pagenav, $url) {
	$p = strpos($text, '{pagebreak}');
	if ($p!==false) {
		global $d_root;
		include_once $d_root.'classes/pagenav.php';
		$pn = new PageNav(1);
		$total = $pn->Total();
		if (!isset($total))
			$pn->SetTotal(substr_count($text, '{pagebreak}')+1);
		$page = $pn->Page();
		if ($page==1) {
			$pagenav = $pn->NavBar($url);
			$pg = substr($text, 0, $p);
			return $pg;
		}
		$np = $p;
		$len = strlen($text);
		$i = 2;
		do {
			$p = $np+11;
			$np = strpos($text, '{pagebreak}', $p);
			if ($np===false) {
				$np = $len;
//				if ($page>$i)
//					$page_count=$i;
				break;
			}
		} while (++$i <= $page);
		$pagenav = $pn->NavBar($url);
		$pg = substr($text, $p, $np-$p);
		return $pg;
	}
	return $text;
}

/*
$type = frontpage / normal / blog / archive
"inline" defined for newsflashes
*/
global $first_content;
$first_content = false;

// hack
global $content_page_breaker;
$content_page_breaker = '<div style="page-break-before: always;"></div>';

## this is probably the most important function of the CMS
function showcontent($id,$type,$buttons=true,$crow=false, $task='view', $pop = false) {
	global	$conn,$d,$access_sql,$my,$viewtype,$Itemid, $d_subpath,$pathway, $time;
	global	$_DRABOTS, $first_content;

	// check if this is a popup request
	if (!$pop && in_num('pop'))
		$pop = true;
	// is this a direct content item menu item?
	$pcontent = in_num('pcontent');
	
	// if no content row was provided, check that the user has access to this content item
	if (!$crow) {
		$crow = $conn->SelectRow('#__content', '*', " WHERE id=$id AND published<>0 $access_sql AND created<$time");
		if (!$crow) {
			CMSResponse::ContentUnauthorized($type == 'inline');
			return null;
		}
	}
	
	// check that user has access to the content category
	$catrow = $conn->SelectRow('#__categories', 'name,section', ' WHERE id='.$crow['catid'].
							' '.$access_sql);
	if (!$catrow) {
		CMSResponse::ContentUnauthorized($type == 'inline');
		return null;
	}

	// check that user has access to the section - unless this is an inline content item (no section check for it)
	//L: RFC: why?
//	if ($type!='inline')  {
		$sectrow = $conn->SelectRow('#__sections', 'title', ' WHERE id='.$catrow['section'].
								' '.$access_sql);
		if (!$sectrow) {
			CMSResponse::ContentUnauthorized($type == 'inline');
			return null;
		}
//	}
	// set the global $viewtype to $type for drabots
	$viewtype = $type;
	// load bots for this content section
	$_DRABOTS->loadBotGroup( 'content', $crow['sectionid'] );
	
	// execute content preparation events
	$results = $_DRABOTS->trigger( 'onPrepareContent', array( &$crow ) );

	// add some meta properties if this is not a view (?)
	if ($task=='view')
		//TODO: use $crow['name'] in pathway caption
		$d->add_meta($crow['metadesc'],$crow['metakey']);
	
	// if this is the first content of a list, update the pathway accordingly (section -> category -> content title)
	//L: maybe valid only for frontpage?
	$was_fc = $first_content;
	if (!$first_content) {
		$first_content = true;
		$pathway->add($sectrow['title'], 'option=content&task=section&id='.$catrow['section'].'&Itemid='.$Itemid);
		$pathway->add($catrow['name'], 'option=content&task=category&id='.$crow['catid'].'&Itemid='.$Itemid);
		global $option;
		$pathway->add($crow['title'], 'option='.$option.'&task='.$task.'&id='.$crow['id'].'&Itemid='.$Itemid);
	}
	
	// fetch the content flags
	$flags = content_flags($crow['mask']);

	// displays full content item
/*	if (isset($pcontent)) { ?>
		<table width="98%" border="0" align="center" cellpadding="0" cellspacing="1" class="dkcom_section">
		<tr>
   		 <td width="100%">
    	  <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <?php if(!$flags['hide_title']) { ?><tr>
          <td class="dk_header" ><?php echo $crow['title'];?>
		  <?php if( $my->can_edit($crow['userid']) ) {?>
		  <a href="index.php?option=content&amp;task=edit&amp;id=<?php echo $crow['id']; // no  Itemid here!  ?>" title="<?php echo _E_EDIT;?>"><img src="<?php echo $d_subpath; ?>media/common/edit_btn.png" border="0" alt="<?php echo _E_EDIT;?>" /></a>
          <?php  } ?></td>
		</tr><?php } ?>
		<tr>
		<td><?php
		$pagenav = null;
		if (!strlen($crow['bodytext']))
			echo $crow['introtext'];
		else {
			echo pages_break($crow['bodytext'], $pagenav, "option=$type&amp;task=$task&amp;id=".$crow['id'].'&amp;Itemid='.$Itemid);
		}
		?>
		</td><?php
			if (isset($pagenav)) { ?>
	<tr>
		<td align="center"><?php echo $pagenav; ?></td>
	</tr><?php	}	?>
		</tr></table>
		</td></tr></table><?php

		// this event is triggered only if the mode is not inline
		if ($type!='inline') {
			$results = $_DRABOTS->trigger( 'onAfterDisplayContent', array( &$crow ) );
			echo implode( "\n", $results );
		}
		// exit point #1
  		return $crow;
	} */

	// extract general and relative content parameters
	if ($type == 'inline' || isset($pcontent))
		$params = new param_class();
	else
		$params = $GLOBALS['params'];
	$hide_email = $params->get('hide_email', 0) || $flags['hide_email'];
	$hide_print = $params->get('hide_print', 0) || $flags['hide_print'];
	$hide_author = $params->get('hide_author', 0) || $flags['hide_author'];
	$hide_created = $params->get('hide_created', 0) || $flags['hide_created'];
	$hide_modified = $params->get('hide_modified', 0) || $flags['hide_modified'];
	$hide_permalink = $params->get('hide_permalink', 0) || $flags['hide_permalink'];
	$hide_pdf = $params->get('hide_pdf', 0) || $flags['hide_pdf'];
	
//general content show
if ($flags['hide_title'] && $hide_email && $hide_print  && $hide_permalink && $hide_pdf) { } else { 
	/* many thanks to http://www.spartanicus.utvinternet.ie/left_and_right_alignment_using_css.htm */
	if (!$was_fc) $d->add_raw_css('
	div.ct_container {text-align:right;margin:1em 0;padding:0}
	div.ct_container div{display:inline;white-space:nowrap;float:left}
	.dkcom_section div.ct_container {display:table;width:100%}
	.dkcom_section div.ct_container div{display:table-cell;float:none;text-align:left}
	.dkcom_section div.ct_container div.ct_rightpan{text-align:right}');
?><table width="98%" border="0" align="center" cellpadding="0" cellspacing="0" class="dkcom_section">
  <tr>
    <td>
  <div class="ct_container<?php if (!$flags['hide_title']) echo ' dk_header'; ?>">
	<?php if (!$flags['hide_title']) echo $crow['title'];
		if( $my->can_edit($crow['userid']) && !$pop ) {?>
		  <a href="index.php?option=content&amp;task=edit&amp;id=<?php echo $crow['id']; /* no Itemid here */ ?>" title="<?php echo _E_EDIT;?>"><img src="<?php echo $d_subpath; ?>media/common/edit_btn.png" border="0" alt="<?php echo _E_EDIT;?>" /></a>
          <?php  }
		if($buttons){
			echo '<div class="ct_rightpan">';
	if (!$hide_pdf && !$pop && (strnatcmp(phpversion(), '5.0')>=0)) {	// the DOMPDF class works only on PHP5
	?><a rel="alternate" href="index2.php?option=content&amp;no_html=1&amp;task=export&amp;format=pdf&amp;id=<?php echo $crow['id']; ?>" title="<?php echo _CONTENT_EXPORT_PDF;?>"><img src="<?php echo $d_subpath; ?>media/common/pdf.png" border="0" alt="<?php echo _CONTENT_EXPORT_PDF;?>" /></a>
<?php
	}
	
	if (!$hide_permalink && !$pop) {
			global $d_website;
			?><a target="_blank" title="<?php echo sprintf(_CONTENT_PERMALINK_TO, $crow['title']); ?>" href="<?php echo $d_website."index.php?option=content&amp;Itemid=".$Itemid.'&amp;task='.$task.'&amp;id='.$crow['id']; ?>"><img src="<?php echo $d_subpath; ?>media/common/permalink.png" border="0" alt="<?php echo sprintf(_CONTENT_PERMALINK_TO, $crow['title']);?>" /></a>
		  <?php
		  }		  
		  
		   if(!$hide_email && !$pop) {?>
		  <a rel="nofollow" href="javascript:<?php echo $d->popup_js("'index2.php?option=content&task=email&id=".$crow['id']."&pop=1'", 400,400); ?>" title="<?php echo _EMAIL;?>"><img src="<?php echo $d_subpath; ?>media/common/email_btn.png" border="0" alt="<?php echo _EMAIL;?>" /></a>
		  <?php echo $d->alternate_url("index2.php?option=content&task=email&id=".$crow['id']."&pop=1", '<img src="'.$d_subpath.'media/common/email_btn.png" border="0" alt="'._EMAIL.'" />'); ?>
		  <?php  }
		  if (!$hide_print && !$pop) {?>
		  <a rel="nofollow" href="javascript:<?php echo $d->popup_js("'index2.php?option=content&task=$task&id=".$crow['id']."&pop=1'",640,480,'yes'); ?>" title="<?php echo _PRINT;?>"><img src="<?php echo $d_subpath; ?>media/common/print_btn.png" border="0" alt="<?php echo _PRINT;?>" /></a>
		  <?php echo $d->alternate_url("index2.php?option=content&task=$task&id=".$crow['id']."&pop=1", '<img src="'.$d_subpath.'media/common/print_btn.png" border="0" alt="'._PRINT.'" />'); ?>
          <?php }
		   if ($pop) { ?>
		  <a href="#" onclick="javascript:window.print(); return false" title="<?php echo _PRINT;?>"><img src="<?php echo $d_subpath; ?>media/common/print_btn.png" border="0" alt="<?php echo _PRINT;?>" /></a>
          <?php }
		echo '</div>';
		  } ?>
		</div>
	</td>
  </tr>
  </table><?php
	}

	if ($type != 'inline') {
		$results = $_DRABOTS->trigger( 'onAfterDisplayTitle', array( &$crow ) );
		echo implode( "\n", $results );
	}

	if(!$hide_author) { ?>
	<sup><?php echo _WRITTEN_BY.' ';
	if (!strlen($crow['created_by_alias']))
		echo _ANONYMOUS;
	else echo $crow['created_by_alias'];	?></sup>
  <?php }
  
	if(!$hide_created) { ?>
      <div class="createdate"><?php echo $d->DateFormat($crow['created']);?></div>
	  <?php
	}

	$results = $_DRABOTS->trigger( 'onBeforeDisplayContent', array( &$crow ) );
	echo implode( "\n", $results );

	//TODO: move page breaking into a drabot, if viable
	$pagenav = null;
	if (!strlen($crow['bodytext']) || $type=="frontpage" || $type=="archive" || $type == 'blog')
		echo $crow['introtext'];
	else {
		if (!$pop)
			echo pages_break($crow['bodytext'], $pagenav, "option=$type&amp;task=$task&amp;id=".$crow['id'].'&amp;Itemid='.$Itemid);
		else {
			global $content_page_breaker;
			echo str_replace('{pagebreak}', $content_page_breaker, $crow['bodytext']);
		}
	}
?><table width="98%" border="0" align="center" cellpadding="0" cellspacing="1" class="dkcom_section">	
  <?php if (!$hide_modified) { ?>
  <tr>
    <td class="modifydate" colspan="2" align="left"><?php echo _LAST_UPDATED; ?> ( <?php echo $d->DateFormat($crow['modified']);?> )</td>
  </tr>
<?php }

// show read more button
if (strlen($crow['bodytext'])) {
	// if it is of the allowed types (frontpage, archive, blog) then show the read more button
	if (($type=="frontpage" || $type=='archive' || $type=='blog')) {
		?><tr><td  align="left"><br /><a href="index.php?option=content&amp;task=<?php echo 'viewpost';
		?>&amp;id=<?php echo $crow['id']./* no Itemid here! */content_sef($crow['title']);
		?>" class="readon"><?php echo _READ_MORE; ?></a></td></tr>
		<?php
	}
}
// post-content events
if ($type!='inline') {
	$results = $_DRABOTS->trigger( 'onAfterDisplayContent', array( &$crow ) );

	if (isset($results[0])) { ?>
	<tr><td><br />
	<?php
		echo implode( "\n", $results );
	?>
	</td>
	</tr>
	<?php
	}
}
// show navigation bar
if (isset($pagenav)) {
	//TODO: convert to DIV
	?>
	<tr>
		<td align="center"><?php echo $pagenav; ?></td>
	</tr><?php
}

	if ($pop && $pop!==2) {  ?>
	<tr>
		<td colspan="2" align="center">
			<a href="javascript:window.close()"><span class="dk_small"><?php echo _PROMPT_CLOSE;?></span></a>
		</td>
	</tr>
      <?php } ?>
	  <tr><td colspan="2" style="height: 0px">&nbsp;</td></tr>
</table>
<?php
	return $crow;
}

//TODO: move functions not needed elsewhere to admin backend code

function content_flags($i) {
	$flags = array();
	$flags['hide_title'] = ($i & 1)>0;
	$flags['hide_email'] = ($i & 2)>0;
	$flags['hide_print'] = ($i & 4)>0;
	$flags['hide_author'] = ($i & 8)>0;
	$flags['hide_created'] = ($i & 16)>0;
	$flags['hide_modified'] = ($i & 32)>0;
	$flags['hide_permalink'] = ($i & 64)>0;
	$flags['hide_pdf'] = ($i & 128)>0;
	return $flags;
}

function mk_content_flags($flags) {
	$i = 0;
	if ($flags['hide_title'])
		$i |= 1;
	if ($flags['hide_email'])
		$i |= 2;
	if ($flags['hide_print'])
		$i |= 4;
	if ($flags['hide_author'])
		$i |= 8;
	if ($flags['hide_created'])
		$i |= 16;
	if ($flags['hide_modified'])
		$i |= 32;
	if ($flags['hide_permalink'])
		$i |= 64;
	if ($flags['hide_pdf'])
		$i |= 128;
	return $i;
}

function update_menu_content($id, $title, $old_title) {
	global $conn;
	$conn->Update('#__menu', 'name=\''.$title."'", ' WHERE link=\'index.php?option=content&pcontent=1&task=view&id='.$id.'\' AND link_type=\'ci\' AND name=\''.sql_encode($old_title)."'");
}

?>