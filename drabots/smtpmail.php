<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## SMTP mailer drabot
#
# based on PhpMailer implementation
#

$_DRABOTS->registerFunction( 'onMail', 'mailer_smtpmail' );

global $d_root;
require_once $d_root.'classes/smtp.php';

global $_SMTP;
$_SMTP = new SMTP();

function mailer_smtpmail($recipient, $subject, $message, $headers = '', $from = null, $cc = null, $bcc = null) {
	global $_SMTP, $_DRABOTS;
	
	$headers = "Subject: ".$subject."\n".$headers;
	$headers .= "To: ".$recipient."\n";
	
	$params = $_DRABOTS->GetBotParameters('mail', 'smtpmail');
	// use 5 for full debugging, 1 for normal error reporting
	$_SMTP->do_debug = $params->get('debug_level', 1);
	
	$_SMTP->SMTP_PORT = $params->get('port', 25);
	
	$_SMTP->SMTPAuth = ($params->get('username', '') !== '');
	$_SMTP->Username = $params->get('username', '');
	$_SMTP->Password = $params->get('password', '');
	
	if (!$_SMTP->smtp_conn) {
//		if (!$_SMTP->Connect($params->get('server', 'localhost')))			return false;
		if (!$_SMTP->DoHandshake($params->get('server', 'localhost')))
			return false;
	}
	
	// apply the From: override if present
	$smtp_from = $params->get('from', '');
	if (strlen($smtp_from))
		$from = $smtp_from;
	
        if (!$_SMTP->Mail($from)) {
//		$d->log(3, 'SMTP FROM failed');
		return false;
	}
	
	if (!$_SMTP->Recipient($recipient)) {
//		$d->log(3, 'SMTP RCPT TO failed');
		$_SMTP->Reset();
		return false;
        }

        if (!$_SMTP->Data($headers.$message)) {
//		$d->log(3, 'SMTP DATA not accepted');
		$this->_SMTP->Reset();
		return false;
        }
	$_SMTP->Reset();	// used to keep-alive the connection
//	$_SMTP->SmtpClose();

        return true;
}

?>