<?php 
## Templateless admin backend
# @author legolas558
# @see admin.php
#
# content-only admin backend output

//TODO: move stuff from admin.php here

if (isset($_GET['no_html']) && $_GET['no_html'])
	define('_NO_TEMPLATE', 1);
else
	define('_RAW_TEMPLATE', 1);

include 'admin.php';

?>