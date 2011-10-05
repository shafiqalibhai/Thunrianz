<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Wrapper module
# @author legolas558
#
#

require_once com_path('common', 'wrapper');

$wimp = $params->get('link', '');

// try to get the wrapper reference, even if unpublished
if (lcms_ctype_digit($wimp)) {
	if (!request_wrapper($wimp, true)) {
		return 'MENU ITEM NOT FOUND: '.$wimp;
	}
} else {	 // manual URL specification, will use default wrapping parameters
	displayWrap($wimp, '', true, $params);
}

?>