<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Wrapper drabot
## @author legolas558
##
##
#

// derty fix to global wrapper params (not plugins params)
//global $params;
//$params->page_title = 0;

$_DRABOTS->registerFunction( 'onPrepareContent', 'botWrappers' );
$_DRABOTS->registerFunction( 'onContentSave', 'botWrapperFilter' );

function _drawrapper_replacer($m) {
	require_once com_path('common', 'wrapper');
	$wimp = trim($m[1]);
	ob_start();
	// try to get the wrapper reference, even if unpublished
	if (lcms_ctype_digit($wimp)) {
		if (!request_wrapper($wimp, true)) {
			ob_end_clean();
			return 'MENU ITEM NOT FOUND: '.$wimp;
		}
	} else {	 // manual URL specification, will use default wrapping parameters
		global $_DRABOTS;
		displayWrap($wimp, '', true, $_DRABOTS->GetBotParameters('content', 'drawrapper'));
	}
	$ct = ob_get_clean();
	return '<span class="dkbot_wrapper">'.$ct.'</span>';
}

// disallow {drawrapper *} constructs depending on user ACL 
function botWrapperFilter( $row ) {
	//TODO: make access level an option
	global $my;
	// managers and above can add wrappers
	if ($my->is_manager())
		return;
 	// expression to search for
	$regex = '/{drawrapper\s+(.*?)}/i';

	//for intro and body text
	$row['introtext'] = preg_replace_callback( $regex, '_drawrapper_forbid', $row['introtext']);
	$row['bodytext'] = preg_replace_callback( $regex, '_drawrapper_forbid', $row['bodytext']);
}

function _drawrapper_forbid($m) {
	$wimp = trim($m[1]);
	global $d, $my, $_DKW_CID;
	$d->log(3, sprintf('%s attempted to add wrapper in content item %d: %s', $my->LogInfo(), $_DKW_CID, $wimp));
	return '';
}

function botWrappers( &$row ) {
 	// expression to search for
	$regex = '/{drawrapper\s+(.*?)}/i';
	global $_DKW_CID;
	$_DKW_CID = $row['id'];
	//for intro and body text
	$row['introtext'] = preg_replace_callback( $regex, '_drawrapper_replacer', $row['introtext']);
	$row['bodytext'] = preg_replace_callback( $regex, '_drawrapper_replacer', $row['bodytext']);
	unset($_DKW_CID);
}

?>