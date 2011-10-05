<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Mailbox mailer drabot
# @author legolas558
#
# emails are appended to a mailbox file
#

$_DRABOTS->registerFunction( 'onMail', 'mailer_mailbox' );

function mailer_mailbox($recipient, $subject, $message, $headers = '', $from = '', $cc = array(), $bcc = array()) {
	global $d_root, $d_private, $_DRABOTS;
	$params = $_DRABOTS->GetBotParameters('mail', 'mailbox');
	
	require_once $d_root.'admin/classes/fs.php';
	$fs = new FS();
	$mbfile = $d_root.$params->get('mbfile', $d_private.'mailbox.php');
	if (!file_exists($mbfile) && (file_ext($mbfile)=='php')) {
		// place some PHP protection code in the beginning
		if (!$fs->put_contents($mbfile, '<'.'?php die; ?'.">\n\n\n"))
			return false;
	}
	$f = $fs->append_open($mbfile);
	if ($f === false)
		return false;
	fwrite($f, "To: ".$recipient."\nSubject: ".$subject."\n".$headers.$message."\n.\n\n");
	$fs->write_close($f, $mbfile);
	return true;
}

?>