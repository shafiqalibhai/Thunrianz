<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Syslog logger
# @author legolas558
#

$_DRABOTS->registerFunction( 'OnLog', 'syslog_logger' );


function syslog_logger($priority, $message) {
	//L: is the below really necessary?
	define_syslog_variables();
	return syslog($priority, $message);
}

?>