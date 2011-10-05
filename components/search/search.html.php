<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

//TODO: check the safety of this function
function search_html($arr, $common_words, $stricken_words) {
	global $searchword,$params,$Itemid;
	
	$sp = in_raw('sp', $_GET, 'any');

?><div class="dk_header"><h2><?php echo _SEARCH_TITLE; ?></h2></div>
<div style="width:100%;" class="dk_content">
	<form action="index.php" method="get">
	<input type="hidden" name="option" value="search" />
		
            <label for="q1"><?php echo _PROMPT_KEYWORD; ?>: </label><br />
            	<input name="q" id="q1" type="text" class="dk_inputbox" value="<?php
			// removed html_entity_decode() here
			echo $searchword;?>" size="15" />&nbsp;
				<input type="submit" value="<?php echo _SEARCH_TITLE;?>" class="dk_button" />
			
		
		<ul class="dk_content">
			<li style="display:inline"><input id="sp1" type="radio" name="sp" value="any" <?php
if ($sp=='any') echo ' checked="checked"'; ?> />
				<label for="sp1"><?php echo _SEARCH_ANYWORDS;?></label>
			</li>
			<li style="display:inline"><input id="sp2" type="radio" name="sp" value="all" <?php
if ($sp=='all') echo ' checked="checked"'; ?> />
				<label for="sp2"><?php echo _SEARCH_ALLWORDS; ?></label>
			</li>
			<li style="display:inline"><input id="sp3" type="radio" name="sp" value="exact" <?php
if ($sp=='exact') echo ' checked="checked"'; ?> />
				<label for="sp3"><?php echo _SEARCH_PHRASE;?></label>
			</li>
		</ul>
		
	</form>
<?php
if (isset($searchword)) {
?>	<p><?php
//L: needs further optimization
	if ($common_words) {
		//TODO: test & improve
		echo _SEARCH_IGNOREKEYWORD.' (strong>'.implode(', ', $common_words).'</strong>) <br />';
	}
	if ($stricken_words) {
		//TODO: improve it
		echo _SEARCH_REDUNDANT_KEYWORDS.': <strike>'.implode(' ', $stricken_words).'</strike><br />';
	}
	$c=count($arr);
	if (!$c)
		echo _SEARCH_NOKEYWORD;
	else {
		echo _PROMPT_KEYWORD.": <strong>".$searchword."</strong> ";
		echo sprintf(_NUM_RESULTS, $c);
	}
?></p>
	<br />
<?php 
	if ($c>0) {
		global $d_root;
		include_once $d_root.'classes/pagenav.php';
		$pn = new PageNav($params->get('show_count',10));
		$arr = $pn->ArraySlice($arr); ?>
		<dl><?php
		foreach($arr as $row) {
?>			<dt><a href="<?php echo $row['link'].content_sef($row['title']); ?>"><?php echo $row['title']; ?></a></dt>
				<dd><?php 
			$text = $row['desc'];

			$text = preg_replace( '/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is','\2 (\1)', $text);
			$text = preg_replace( '/<!--.+?-->/', '', $text);
			$text = html_to_text( $text );
			$text_found = strpos(strtolower($text), strtolower($searchword));
			$desc_len = 200;
			$text_len = strlen($text);
			if (($text_len < $desc_len) or ($text_found < $desc_len/2)) {
				$text_start = 0;
			} elseif ($text_len < $text_found + $desc_len/2) {
				$text_start = $text_len - $desc_len;
			} else {
				$text_start= $text_found - $desc_len/2;
			}
			$text = substr( $text, $text_start, $desc_len);
			$text = preg_replace( "/".str_replace('/', '\\/', preg_quote($searchword))."/iU", "<strong class=\"highlight\">\\0</strong>", $text);
			echo $text; ?>
			</dd>
<?php
		} // end foreach
?>
		</dl>
		<br />
<?php
		$searchword = rawurlencode($searchword);
		$pagenav_search = $pn->NavBar("option=search&amp;q=$searchword&amp;Itemid=$Itemid");
		if (strlen($pagenav_search)) {
?>		<div style="width:100%;"><?php echo $pagenav_search; ?></div>
<?php
		} // endif
	} // endif
?>
<?php
	}
?>
</div>
<?php
} // end of function search_html()
?>