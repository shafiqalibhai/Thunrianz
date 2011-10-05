<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/System/Massmail';

//TODO: validate email, must not be empty
$toolbar->add_custom(_MASSMAIL_SEND,"send");
$toolbar->add("cancel");

?>