<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Latest news module
# @author legolas558
#
# common include file
#

function _news_list($rsa, $has_description, $inst) { ?>
	<ul><?php
	foreach($rsa as $row) {
	?><li><a href="index.php?option=content&amp;task=view&amp;id=<?php echo $row['id'].content_sef($row['title']).$inst; ?>"><?php echo $row['title']; ?></a>
		<?php if ($has_description)
			echo "<br />".content_summary($row['introtext'], 100);
		?></li>
		<?php }  ?>
	</ul>
<?php
}

?>