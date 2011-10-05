<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Language selection module
# @author franky2004
#

global $d,$d_subsite,$d_root,$my;

$show_label = intval( $params->get( 'show_label', 1 ));
$show_type = trim( $params->get( 'show_type', 'images' ));
$show_active=1;

include_once $d_root.'includes/langsel.php';

$langActive = available_languages(true);

$outString = '';

switch ( $show_type ) {
	case 'dropdown':
//		if ( count($langActive) ) {
			$outString = '';
			if ($show_label)
				$outString .= '<label for="select_language">' ._SELECTLANG. '&nbsp;</label>';
			$outString .= select_language('select_language', $my->lang,
				' onchange="if (this.value!=\'\')document.location='.
				'\'index.php?option=user&amp;task=custom&amp;lang=\'+this.value"');
//		}
		break;

	case 'images':
	default:
		if ( count($langActive)>0 ) {
			$outString = '';
			
			global $d_subpath;			
			foreach( $langActive as $language => $lname) {
				$bord = ((int) ($show_active && ($language == $my->lang )))*2;

				$langImg = $d_subpath.'lang/'.$language.'/flag.png';

				if (!$bord) {
					$outString.='<a title="'.$lname.'" href="index.php?option=user&amp;task=custom&amp;lang='.
								$language. '">';
					$w = 16; $h = 11;
				}else { $w=32; $h=22; }
				$outString.='<img width="'.$w.'" height="'.$h.'" src="'.$langImg.'" border="'.$bord.'" alt="' .$lname. '" />';
				if (!$bord)
					$outString.='</a>';
				$outString.='&nbsp;&nbsp;';
			}
		}
		break;
}
echo '<br />'.$outString;
?>