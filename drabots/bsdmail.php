<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Shell mail mailer implementation
# @author legolas558
#

$_DRABOTS->registerFunction( 'onMail', 'mailer_bsdmail' );

define('_MAIL_PATH', 'mail');

// written by legolas558 to use BSD-shell mail services
function mailer_bsdmail($recipient, $subject, $message, $headers = '', $from = '', $cc = array(), $bcc = array()) {
	// headers are not sent as headers
	// the needed headers are created by mail itself
	$headers = '';

	$cmd = sprintf('%s -s %s %s', _MAIL_PATH, escapeshellarg($subject), escapeshellarg($recipient));
	
	if (count($cc))
		$cmd .= ' -c '.escapeshellarg(implode(', ', $cc));
	if (count($bcc))
		$cmd .= ' -b '.escapeshellarg(implode(', ', $bcc));

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