<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Content email cloaker drabot
# @author legolas558
#
# conceal email addresses found in shown content items (modified by Ingo Linde)
#

$_DRABOTS->registerFunction( 'onPrepareContent', 'ctmailcloak_prepare' );

define('_CTMAILCLOAK_EMAIL_REGEX', "[\\w\\.\\-]+@\\w+[\\w\\.\\-]*?(?>\\.\\w+[\\w\\.\\-]*?)?\\.\\w{2,4}");

function _ctmailcloak_replacer($m) {
	global $_ctmailcloak_params;
	
	$email = $m[1];
	switch ($_ctmailcloak_params->get('method', 'html')) {
		case 'punctuation':
			global $d;

			if(count($m) > 2) // $m[2] exists and contains link description
				$email = $m[2].' ('.$d->ObfuscateEmail($email).')';
			else // no link description available -> use email instead
				$email = $d->ObfuscateEmail($email);
			break;
		case 'html':
			global $d;

			if(count($m) > 2) // $m[2] exists and contains link description
				$email = $m[2].' ('.$d->ComplexEmail($email).')';
			else // no link description available -> use email instead
				$email = $d->ComplexEmail($email);
			break;
		case 'javascript':
			global $d;

			if(count($m) > 2) // $m[2] exists and contains link description
				$email= '<a href="mailto:'.$email.'" title="'.$email.'">'.$m[2].'</a>';
			else // no link description available -> use email instead
				$email= '<a href="mailto:'.$email.'" title="'.$email.'">'.$email.'</a>';

			$l = strlen($email);
			$js = '';
			$tot_var = array();
			for($i=0;$i<$l;$i+=4) {
				$trp_var = 'trp_'.random_string(4);
				$js .= 'var '.$trp_var.' = "'.js_encode(substr($email, $i, 4)).'";';
				$tot_var[] = $trp_var;
			}
			$js .= 'document.write('.implode(' + ',$tot_var).');';
			$email = $d->script($js);
			break;
		default:
			break;
	}
	return $email;
}

function ctmailcloak_prepare( &$row ) {
	global $_DRABOTS, $_ctmailcloak_params;
	if (!isset($_ctmailcloak_params))
		$_ctmailcloak_params = $_DRABOTS->GetBotParameters('content', 'ctmailcloak');
/*
  // Handle simple BBCode
  $row['introtext'] = preg_replace_callback('/\\[e?mail\\]([^\\[]*)\\[\\/e?mail\\]/', '_ctmailcloak_replacer', $row['introtext']);
  $row['bodytext'] = preg_replace_callback('/\\[e?mail\\]([^\\[]*)\\[\\/e?mail\\]/', '_ctmailcloak_replacer', $row['bodytext']);
  // Handle complex BBCode
  $row['introtext'] = preg_replace_callback('/\\[e?mail="?([^"\\]]*)"?\\]([^<]*)\\[\\/e?mail\\]/', '_ctmailcloak_replacer', $row['introtext']);
  $row['bodytext'] = preg_replace_callback('/\\[e?mail="?([^"\\]]*)"?\\]([^<]*)\\[\\/e?mail\\]/', '_ctmailcloak_replacer', $row['bodytext']);
*/
  // Handle mailto links
  $row['introtext'] = preg_replace_callback('/<a[^>]*href="mailto:([^"]*)"[^>]*>([^<]*)<\\/a>/', '_ctmailcloak_replacer', $row['introtext']);
  $row['bodytext'] = preg_replace_callback('/<a[^>]*href="mailto:([^"]*)"[^>]*>([^<]*)<\\/a>/', '_ctmailcloak_replacer', $row['bodytext']);
  // Handle unmarked emails
  $row['introtext'] = preg_replace_callback('/('._CTMAILCLOAK_EMAIL_REGEX.')/', '_ctmailcloak_replacer', $row['introtext']);
  $row['bodytext'] = preg_replace_callback('/('._CTMAILCLOAK_EMAIL_REGEX.')/', '_ctmailcloak_replacer', $row['bodytext']);
}

?>