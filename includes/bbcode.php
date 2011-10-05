<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## BBcode include file
# @author legolas558
#
# BBcode general functions

global $sm_img, $sm_chars, $sm_desc;
$sm_chars = array (  'b)', ';)', ':)', ':ohmy:', ':sick:', ':angry:', ':blink:', ':(',
					':unsure:', ':kiss:', ':woohoo:', ':lol:', ':silly:', ':pinch:', ':side:', ':whistle:',
					':evil:', ':s', ':blush:', ':cheer:', ':huh:', ':dry:', ':d', ':p' );

$sm_img = array ( 'cool', 'wink', 'smile', 'shocked', 'sick', 'angry', 'blink', 'sad',
					'unsure', 'kissing', 'w00t', 'grin', 'silly', 'pinch', 'sideways', 'whistling',
					'devil', 'dizzy', 'blush', 'cheerful', 'wassat', 'ermm', 'laughing', 'tongue' );
	
include $d_root.'lang/'.$my->lang.'/includes/smileys.php';
	
$sm_desc = array();
foreach($sm_img as $img) {
	$c = '_SMILEY_'.raw_strtoupper($img);
	$sm_desc[] = constant($c);
}

global $sm;
// create the regex values array
$sm = array();
foreach($sm_chars as $s) {
	$sm[] = '/(\\s|^)('.preg_quote($s).')(\\)|\\s|$)/im';
}

function sm_replace_cb($m) {
	global $sm_img, $d_subpath, $sm_chars, $sm_desc;
	$c=count($sm_chars);
	$it=strtolower($m[2]);
	$m[3] = trim($m[3]);
	for($i=0;$i<$c;$i++) {
		if ($sm_chars[$i] == $it)
			return $m[1].'<img src="'.$d_subpath.'media/common/smilies/'.$sm_img[$i].'.png" alt="'.$sm_desc[$i].'" />'.$m[3];
	}
}

function _q_safe_url($url) {
	return str_replace('"', '%22', $url);
}

function _list_parser($m) {
	if (!strlen($m[1])) {
		$tagStart = '<ul>';
		$tagEnd = '</ul>';
	} else {
		$tagStart = '<ol type="'.$m[1].'">';
		$tagEnd = '</ol>';
	}
	// only [li] items are valid
	if (!preg_match_all('#\\[li\\](.*?)\\[/li\\]#si', $m[2], $items))
		return $m[0];
	$html = '';
	foreach($items[1] as $item) {
		$html .= '<li>'.$item.'</li>';
	}
	return $tagStart.$html.$tagEnd;
}

function _bburl_replace($m) {
	if (strlen($m[1])==0) $m[1] = $m[2];
	$url = $m[1];
	$label = safe_html_entity_decode($m[2]);
	if (!preg_match('/(\\w+)\\:\\/\\/(.)/A', $url, $u, PREG_OFFSET_CAPTURE)) {
		if (!lcms_ctype_alpha($url[0]))
			$url = 'about:blank';
		else {
			$url = _q_safe_url($url);
			if (preg_match('/www./Ai', $url, $m))
				$url = 'http://'.$url;
		}
	} else
		$url = $u[1][0].'://'._q_safe_url(substr($url, $u[2][1]));
	if (strlen($label)>=65)
		$label = substr($label, 0, 65).'...';
	return '<a title="'.$url.'" href="'.$url.'" target="_blank" rel="nofollow" >'.xhtml_safe($label).'</a>';
}

global $bb_random_marker, $bb_code_snippets;
$bb_random_marker = random_string(6).':';

function _bb_code_snippets($m) {
	global $bb_random_marker, $bb_code_snippets;
	$bb_code_snippets[] = $m[1];
	return "<strong>"._FORUM_CODE.":</strong><div class='code'>".
			$bb_random_marker.(count($bb_code_snippets)-1).'</div>';
}

function _bb_code_replacer($m) {
	global $bb_code_snippets;
	return $bb_code_snippets[$m[1]];
}

// [size] ranges from 1 to 10, and they correspond to 80% -> 180%
// [size=3] does nothing
function _size_parser($m) {
	$pc = $m[1];
	if (isset($pc[0])) {
		$pc = (int)substr($pc, 1);
		$pc = max($pc, 1);
		$pc = min($pc, 10);
		if ($pc==3) return $m[2];
	} else return $m[0];
	$pc+=7;
	return '<span style="font-size: '.($pc*10).'%">'.$m[2].'</span>';
}

function _bb_quote_replacer($m) {
	if (count($m)>1)
		$s = sprintf(_FORUM_QUOTE_BY, substr($m[1], 1));
	else
		$s = _FORUM_QUOTE;
	return '<strong>'.$s.':</strong><div class="quote">';
}

function bbdecode($message, $bbcode = true, $smileys = true, $img_support = false) {
	global $_DRABOTS;
	$_DRABOTS->loadCoreBotGroup('content');
	$rs = $_DRABOTS->trigger('OnBBDecode', array(&$message, &$bbcode, &$smileys, &$img_support));
	foreach ($rs as $r) {
		if ($r === false)
			return $message;
	}
	
	if ($smileys) {
		global $sm;
		$message = preg_replace_callback($sm, 'sm_replace_cb', $message);
	}

	if ($bbcode) {
		global $bb_code_snippets;
		$bb_code_snippets = array();
		# Convert BB Code to HTML commands
		$message = preg_replace_callback('/\\[(?i:code)\\](.*?)\\[\\/(?i:code)\\]/s', '_bb_code_snippets', $message);
		
		$message = preg_replace_callback('/\\[(?i:quote)(=[^\\]]+)?\\]/',
				'_bb_quote_replacer', $message);
				
		$message = preg_replace('/\\[\\/(?i:quote)\\]/', '</div>', $message);

		$message = preg_replace("#\\[b\\](.*?)\\[/b\\]#si", "<strong>\\1</strong>", $message);
		$message = preg_replace("#\\[i\\](.*?)\\[/i\\]#si", "<em>\\1</em>", $message);
		$message = preg_replace("#\\[u\\](.*?)\\[/u\\]#si", "<u>\\1</u>", $message);

		$message = preg_replace_callback("/\\[url=?(.*?)\\](.*?)\\[\\/url\\]/si", '_bburl_replace', $message);
		$message = preg_replace("#\[email\](.*?)\[/email\]#si", "<a href=\"mailto:\\1\">\\1</a>", $message);
		//TODO: proper alt tag for [img] tags
		if ($img_support) $message = preg_replace("#\\[img\\](.*?)\\[/img\\]#si", '<img src="\\1" alt="" />', $message);
		$message = preg_replace_callback("#\\[list(=[a1])?\\](.*?)\\[/list\\]\\s*#si", '_list_parser', $message);

		$message = preg_replace_callback("#\\[size(=\\d+)?\\](.*?)\\[/size\\]#si", '_size_parser', $message);

		global $bb_code_snippets, $bb_random_marker;
		if (isset($bb_code_snippets[0])) {
			$message = preg_replace_callback('/'.$bb_random_marker.'(\\d+)/', '_bb_code_replacer', $message);
		}
	}
	
	// Convert CR/LF to HTML <BR /> tag
	$message = preg_replace("/(\015\012)|(\015)|(\012)/",'<br />', $message);

	return $message;
}

function bbcode_to_text($message) {
	//TODO: convert BB code to  rich text
	return $message;

	global $bb_code_snippets;
	$bb_code_snippets = array();
	# Convert BB Code to HTML commands
	$message = preg_replace_callback('/\\[(?i:code)\\](.*?)\\[\\/(?i:code)\\]/s', '_bb_code_snippets', $message);
	
	$message = preg_replace_callback('/\\[(?i:quote)(=[^\\)]+)?\\]/',
			'_bb_quote_replacer', $message);
			
	$message = preg_replace('/\\[\\/(?i:quote)\\]/', '</div>', $message);

    $message = preg_replace("#\[b\](.*?)\[/b\]#si", "<strong>\\1</strong>", $message);
    $message = preg_replace("#\[i\](.*?)\[/i\]#si", "<em>\\1</em>", $message);
    $message = preg_replace("#\[u\](.*?)\[/u\]#si", "<u>\\1</u>", $message);

    $message = preg_replace_callback("/\\[url=?(.*?)\\](.*?)\\[\\/url\\]/si", '_bburl_replace', $message);
    $message = preg_replace("#\[email\](.*?)\[/email\]#si", "<a href=\"mailto:\\1\">\\1</a>", $message);
	if ($img_support) $message = preg_replace("#\[img\](.*?)\[/img\]#si", '<img src="\\1" alt="" />', $message);
    $message = preg_replace_callback("#\[list(=[a1])?\](.*?)\[/list\]#si", '_list_parser', $message);

    $message = preg_replace_callback("#\[size(=\\d+)?\](.*?)\[/size\]#si", '_size_parser', $message);
  		global $bb_code_snippets, $bb_random_marker;
		if (isset($bb_code_snippets[0])) {
			$message = preg_replace_callback('/'.$bb_random_marker.'(\\d+)/', '_bb_code_replacer', $message);
		}

  // Convert CR/LF to HTML <BR /> tag
  $message = preg_replace("/(\015\012)|(\015)|(\012)/",'<br />', $message);

  return $message;
}

?>
