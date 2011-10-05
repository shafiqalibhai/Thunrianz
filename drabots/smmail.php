<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Sendmail mailer implementation
# @author legolas558
#
# based on PhpMailer implementation
#

$_DRABOTS->registerFunction( 'onMail', 'mailer_smmail' );

function mailer_smmail($recipient, $subject, $message, $headers = '', $from='', $cc = null, $bcc = null) {
	global $_DRABOTS;
	$params = $_DRABOTS->GetBotParameters('mail', 'smmail');
	$sm_path = $params->get('sendmail_path', 'sendmail');
	// recipient will be added anyway, even if already specified in To: header, for broader compatibility
	if (strlen($from))
		$cmd = sprintf("%s -toi -f%s %s", $sm_path, escapeshellarg($from), escapeshellarg($recipient) );
	else
		$cmd = sprintf("%s -toi %s", $sm_path, escapeshellarg($recipient) );
	
	$headers = "To: ".$recipient."\nSubject: ".$subject."\n".$headers;

	$mail = @popen($cmd, "w"); // will fail if pipe cannot be opened
	$failed = ($mail === false);
	
	if (!$failed) {
		fputs($mail, $headers);
		fputs($mail, $message);
		
		$result = pclose($mail) & 0xFF;
		$failed = ($result == 127);
	}
	if ($failed) {	// broken pipe
		trigger_error( sprintf('Could not execute "%s"', $cmd), E_USER_WARNING);
		return false;
	}
        return ($result == 0);
}

?>