<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function &available_languages($short = false) {
	global $d_root, $d;
	$tmp = $d->GetActiveLangs();
	$langs = array();
	if (!$short) {
		include_once $d_root.'classes/anyxml/anyxml.php';
		foreach($tmp as $lid) {
			$xml = new AnyXML();
			if (!$xml->fromString(file_get_contents($d_root.'lang/'.$lid.'/language.xml')))
				continue;
			$e = $xml->getElementByPath('languages/language/name');
//			if (!isset($e))				continue;
			$langs[$lid] = $e->getValue();
		}
	} else {
		foreach($tmp as $lid) {
			$langs[$lid] = raw_strtoupper($lid);
		}
	}
	return $langs;
}

function select_language($sel_name='sel_language',$sel_value = '',$extra='', $class='dk_inputbox', $void = true) {
	global $d_root;

	$select_drop  = '';
	$select_drop .= '<select class="'.$class.'" name="'.$sel_name.'" id="'.$sel_name.'"'.$extra.'>'."\n";
	if ($void) {
		$select_drop .= '<option value=""'.
		(($sel_value == '') ? ' selected="selected"' : '').
		'>'.'-- Auto --'.'</option>'."\n";
	}
	$langs =& available_languages();
	foreach($langs as $lid => $name) {
		if ($lid==$sel_value) $sel=' selected="selected"';
		else $sel='';
		$select_drop .= '<option value="'.$lid.'"'.$sel.'>'.$name.'</option>'."\n";
	}
	$select_drop .= '</select>'."\n";
	return $select_drop;
}

?>