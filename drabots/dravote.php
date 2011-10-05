<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$_DRABOTS->registerFunction( 'onAfterDisplayTitle', 'botContentVoting' );

function botContentVoting( $row ) {
	global $d_root, $conn, $my;
	global $pop, $viewtype;

	if (isset($pop)) return '';
	
	if ($viewtype=='inline') return '';
	
	require_once $d_root.'includes/rating.php';
	
	global $_DRABOTS;
	$_DRABOTS->AddCSS('dravote');
	
	ob_start();
	
	echo '<span class="dkbot_vote">';
	
	if ($viewtype=='archive')
		$crow=$conn->SelectRow('#__rating', '*', " WHERE itemid=".$row['id'].' AND component=\'content\'');
	else
		$crow = _handle_rating($row, 'content');
		
	_rating_results($crow);
	
	if ($viewtype=="content" || $viewtype=="blog" || $viewtype=='frontpage')
		_rating_form($row['id'], 'content');
	
	return ob_get_clean().'</span>';
}

?>