<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## PHP mail drabot
# @author legolas558
#

$_DRABOTS->registerFunction( 'onMail', 'mailer_phpmail' );

function mailer_phpmail($recipient, $subject, $message, $headers = '', $from = '', $cc = array(), $bcc = array()) {
	return mail($recipient, $subject, $message, $headers);
}

?>