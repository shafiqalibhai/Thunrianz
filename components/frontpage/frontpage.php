<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path('html'));

require_once(com_path('common', 'content'));

require com_lang($my->lang, 'content');

frontpage();
?>