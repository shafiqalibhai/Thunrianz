<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include(com_path($d_type));
$task = in_raw('task', $_POST);

$pathway->add_head(_POLLS_TITLE);

$pollid = in_num('pollid', $_REQUEST);

switch ($task) {
	case "vote": 
	    if (null === ($voteid = in_num('voteid')))
			CMSResponse::Redir('index.php?option=polls', _FORM_NC);

		vote($voteid, $pollid);
		break;
	default:
	case "results":
		view_polls($pollid);
}

function vote($voteid, $pollid) {
    global $conn, $time,$my, $params;
    // if anonymous user
	$vote_user = '';
	// if registered user 
	if ($my->gid>0) $vote_user = "userid=".$my->id." AND";
	
    $vote_delay = $params->get('vote_delay', 60 * 60 * 24);
	
	//if ($my->id == 1) $vote_delay = 0; //example of vote unblocking
    
	$but_val = _CONTINUE;
    $onclick = "window.history.go(-1);";
    if (!isset($voteid)) {
        $msg = _NO_SELECTION;
    } else {
        $row = $conn->SelectRow('#__polls_votes', '*', " WHERE $vote_user ip='".sql_encode($my->GetIP()). "' AND pollid=$pollid AND date>" . ($time-$vote_delay));
        if (count($row))
            $msg = _ALREADY_VOTE;
        else {
		$msg = _THANKS;
		$but_val = _BUTTON_RESULTS;
		$onclick = "window.location='" . "index.php?option=polls&amp;pollid=$pollid". "'";
		change_val("polls_data", $voteid, "hits", 1);
		//if anonymous user
//		if ($my->gid<1) $my->id=0;
		
		$conn->Insert('#__polls_votes', '(userid,ip,pollid,date)', $my->GetID().', \''.sql_encode($my->GetIP()) . "',$pollid,'$time'");
        }
    }

    ?>
    <div class="dk_aligncenter"><h3><?php echo $msg; ?></h3>
      <input class="dk_button" type="button" value="<?php echo $but_val;?>" onclick="<?php echo $onclick;?>" />
    </div>
    <div class="dk_clr"></div>
<?php }
?>