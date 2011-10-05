<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function frontpage() {
	global $conn,$Itemid,$d,$my,$d_website,$access_sql,$params,$d_root;
	
	$icons = $params->get('icons',true) ;
	$show = (int)$params->get('show_count',10) ;
	$column = $params->get('show_column',false) ;

	include_once $d_root.'classes/pagenav.php';
	$pn = new PageNav($show);
	
	$pn->QueryCount();

	// (1) setup a recordset object to fetch all the ids
	$rs=$conn->Select('#__content_frontpage', 'id', ' ORDER BY ordering');

	?><table width="100%" cellspacing="0" cellpadding="0" border="0" class="dk_content"><?php
	// (2) feed in the PageNav instance, and also build the actual content rows when
	$rsa = array();
	while ($row = $rs->GetArray(1)) {
		$row = current($row);
		$row=$conn->SelectRow('#__content', 'id', ' WHERE id='.$row['id']." AND published=1 $access_sql");
		if (!count($row)) {
			// skip empty rows
//			echo '<hr />empty row skipped <br />';
			continue;
		}
		switch ($pn->QueryAddFlag()) {
			case 2:
				// the selected array slice is complete
				break 2;
			case 1:
				// this row can be selected into the array
				$rsa[] = $conn->SelectRow('#__content', '*', ' WHERE id='.$row['id']);
				break;
			case 0:
				// this row should be skipped (also used for counting up to the last page)
			;
		}
	}
	// (3) use the first row (if any) to create the pathway location
//	if (isset($rsa[0]))
		//TODO: check that $rsa[0]['title'] is used for the pathway caption
//		$d->add_meta($rsa[0]['metadesc'], $rsa[0]['metakey']);
	
	// (4) output all the selected content items
	
	// take apart the even news
	if ($column && count($rsa)%2) {
		$last_news = array_pop($rsa);
	} else $last_news = null;
	
	$col_count=0;
	foreach($rsa as $crow) {
		if($col_count%2==0 && $column)echo '<tr>';
		else if(!$column)echo '<tr>';
		?><td valign="top" <?php if($column) echo " width=\"50%\""; ?>>
			<?php
				showcontent($crow['id'],"frontpage",$icons,$crow);
			?></td>
			<?php
			$col_count++;
		if($col_count%2==0 && $column)echo "</tr>";
		else if (!$column)echo "</tr>";
	}
	
	// show the even news, if any
	if (isset($last_news)) {
		?><tr><td valign="top" colspan="2">
			<?php
				showcontent($last_news['id'],"frontpage",$icons,$last_news);
			?></td></tr>
			<?php
	}
	
	// (5) show the navigation bar
	?></table>
	<?php
	global $pathway;
	
	// prevent the nasty frontpage pathway error
	if ($pn->Total() == 0) {
		$pathway->add(_FRONTPAGE_ERROR);
		?><div style="font-size: larger; color: red; font-weight: bold; text-align:center;"><?php echo _FRONTPAGE_ERROR_NO_ITEM; ?></div><?php
	}
	
	echo $pn->NavBar(xhtml_safe($pathway->Current()));
}

?>