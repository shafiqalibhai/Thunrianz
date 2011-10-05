<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$pathway->add(_AMENU_HELP_ABOUT);

$d__help_context = '';

require usr_com_path('common.php');

require $d_root.'lang/'.$my->lang.'/components/about.php';

about_page();

?>