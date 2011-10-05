<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Rating/voting utility functions
# @author legolas558
#
#

function _handle_rating($row, $component) {
	$user_rating = in_num('user_rating', $_POST);
	$vote_id = in_num('vote_id', $_POST, $row['id']);
//	if ($vote_id !== (int)$row['id'])
		$vote_id = (int)$row['id'];
	
	global $conn, $my;
	$crow=$conn->SelectRow('#__rating', '*', " WHERE itemid=".$row['id'].' AND component=\''.$component.'\'');
	if (!count($crow)){
		$conn->Insert('#__rating', '(itemid,component,lastip)', $row['id'].', \''.$component.'\', \'\'');
		$crow['rating_count'] = $crow['rating_sum']=0;
		$crow['lastip'] = '';
	}

	//TODO: have ratings as separate rows
	if (isset($vote_id) && isset($user_rating)) {
		if ($my->GetIP()==$crow['lastip'] && $vote_id==$row['id']) {
			echo _ALREADY_VOTE;
			return null;
		} else if ( $vote_id==$row['id'] ) {
			//TODO: do not allow content voting when the user is the author
			$crow['rating_sum'] = (int)(( $crow['rating_sum'] * $crow['rating_count'] + $user_rating ) /  ( $crow['rating_count'] + 1 ));
			$crow['rating_count']++;
		
			$conn->Update('#__rating', "rating_sum = ".$crow['rating_sum'].", rating_count = ".$crow['rating_count']." , lastip = '".$my->GetIP()."'", " WHERE itemid=".$row['id'].' AND component=\''.$component.'\'');
			$crow['lastip']=$my->GetIP();
			echo _THANKS.'<br />';
		}
	}
	
	return $crow;
}

function _rating_results($row) {
	if (!isset($row)) return;
	$starImageOn = template_pic( 'rating_star.png' );
	$starImageOff = template_pic( 'rating_star_blank.png');
	$img ="";
	$pick = ceil($row['rating_sum']);
	for ($i=0; $i < $pick; $i++)
		$img .= $starImageOn;
	
	for ($i=$pick; $i < 5; $i++)
		$img .= $starImageOff;
	echo '<p><strong>'._USER_RATING.'</strong>: ';
	echo '<span class="dkbot_content_rating" style="vertical-align:middle">'.$img.' / '.intval( $row['rating_count'] ).'</span>';
	?></p><?php
}

function _rating_form($id, $component) {
	global $conn, $my;
	$rs = $conn->Select('#__rating', 'itemid', ' WHERE itemid='.$id.' AND component=\''.$component.'\' AND lastip=\''.$my->GetIP()."'");
	if ($rs->RecordCount())
		return;
	?><form method="post" action="">
	<span class="dkbot_content_vote"><?php echo _VOTE_POOR; ?>
	<input type="radio" alt="vote 1 star" name="user_rating" value="1" />
	<input type="radio" alt="vote 2 star" name="user_rating" value="2" />
	<input type="radio" alt="vote 3 star" name="user_rating" value="3" checked="checked" />
	<input type="radio" alt="vote 4 star" name="user_rating" value="4" />
	<input type="radio" alt="vote 5 star" name="user_rating" value="5" /><?php echo _VOTE_BEST; ?>
	&nbsp;<input class="dk_button" type="submit" value="<?php echo _RATE_BUTTON; ?>" />
	<input type="hidden" name="vote_id" value="<?php echo $id; ?>" />
	</span>
	</form>
	<?php
}

?>