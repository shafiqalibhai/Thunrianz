<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$_DRABOTS->registerFunction( 'onDownloadVote', 'botDownloadVote' );
$_DRABOTS->registerFunction( 'onDownloadVoteForm', 'botDownloadVoteForm' );

function botDownloadVote(&$row) {
	global $d_root;
	require_once $d_root.'includes/rating.php';
	_rating_results(_handle_rating($row, 'download'));
}

function botDownloadVoteForm($id) {
	_rating_form($id, 'download');
}

?>