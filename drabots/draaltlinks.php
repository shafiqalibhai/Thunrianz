<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Language alternate links drabot
# @author legolas558
#

$_DRABOTS->registerFunction( 'onGenerateHead', 'botAlternateLinks' );

function botAlternateLinks() {
	global $d_root;
	
	$langs = read_dir($d_root.'lang/', 'dir');
	if (isset($langs[0])) {
		global $my, $d;
		$local = @CMSRequest::Querystring();
		if ($local!=='')
			$local = 'index.php?'.str_replace('&','&amp;', $local).'&amp;';
		else
			$local = 'index.php?';
		foreach ($langs as $lid) {
			//TODO: should check if language.xml exists under $lid subdir
			if ($lid!=$my->lang)
				echo '<link rel="alternate" href="'.$local.'option=user&amp;task=custom&amp;lang='.$lid.'" hreflang="'.$lid.'" />'."\n";
		}
	}
}

?>