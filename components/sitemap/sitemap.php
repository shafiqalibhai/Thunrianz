<?php if (!defined('_VALID')) {header('Status: 404 Not Found'); die;}

$d->add_js(usr_rel_com_path('tree.js'));

$pathway->add_head(_SITEMAP_TITLE);

?><div class="dk_header"><h2><?php echo $d_title.' '._SITEMAP_TITLE; ?></h2></div>
<?php echo $d_desc; ?><br />
<ul class="tree">
	<?php
	$rs = $conn->Select('#__sections', 'id,title', ' WHERE access<='.$my->gid.' ORDER BY ordering ASC');

		//sections
		while ($rsa_a = $rs->GetArray(1)) {
			$row = $rsa_a[0];
			//categories
			$crs = $conn->Select('#__categories', 'id,name', ' WHERE section='.$row['id']." $access_sql ORDER BY ordering");

			echo "<li><a href=\"#\">".$row['title']."</a>"; // sections

			if (($crow_total = $crs->RecordCount()) > 0) {
				echo "<ul>";

				while ($crsa_a = $crs->GetArray(1))
				{
					$ccrow = $crsa_a[0];

					//content items
					$icrsa = $conn->GetArray("SELECT id,title FROM #__content WHERE sectionid=".$row['id'].
						" AND catid=".$ccrow['id']." AND published=1 $access_sql ORDER BY created DESC");

					$icrow_total = count($icrsa);
					
					if ($icrow_total) {
						echo " <li><a href=\"#\">".$ccrow['name']."</a>"; //categories

						echo "<ul>";

						foreach ($icrsa as $iccrow)
						{	//for the content items
							echo "<li><a href=\"index.php?option=content&amp;task=view&amp;id=".$iccrow['id'].content_sef($iccrow['title'])."\">";
							echo $iccrow['title']. "</a></li>";
						} //foreach ($icrsa as $iccrow)

						echo  "</ul>"; //items

					} else {
						echo " <li><a href=\"index.php?option=content&amp;task=category&amp;id=".$ccrow['id'].content_sef($ccrow['name'])."\">".$ccrow['name']."</a>"; //categories link
					}

					echo "</li>";

				} //while ($crsa_a = $crs->GetArray(1))

				echo  "</ul>"; //categories

			} //if (($crow_total = $crs->RecordCount()) > 0)

			echo "</li>";

		} //while ($rsa_a = $rs->GetArray(1))

		//sections
	?>
</ul>