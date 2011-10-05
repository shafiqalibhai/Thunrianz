<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## DB queue mail drabot
# @author legolas558
#
# queue the mail in a database table
# a separate program must take care of the table deletion

$_DRABOTS->registerFunction( 'onMail', 'mailer_dbmail' );

function mailer_dbmail($recipient, $subject, $message, $headers = '', $from = '', $cc = array(), $bcc = array()) {
	global $conn, $time;
	$r=$conn->Insert('#__mail_queue', '(queued, recipient, subject, body, headers,_from,cc,bcc)',
                "$time, '".sql_encode($recipient)."', '".
                sql_encode($subject)."', '".sql_encode($message)."','".
                sql_encode($headers)."', '".
                sql_encode($from)."', '".
                sql_encode(serialize($cc))."', '".
                sql_encode(serialize($bcc))."'");
	return ($conn->Affected_Rows()!=0);
}

?>