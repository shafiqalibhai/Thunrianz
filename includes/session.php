<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Session include file
# @author legolas558
#
# core includes needed for session startup
# will trigger the relative BeforeSessionStart event
#

$_DRABOTS->loadCoreBotGroup('core');

$_DRABOTS->trigger('BeforeSessionStart');

// to prevent IE5.5 and IE6 download issues with Pragma: no-cache headers
// didn't find a testcase for this, maybe is useful for SSL connections only
session_cache_limiter('private, must-revalidate');

// the session should not have been started before
@session_start();
//RFC: what troubles causes error silencing here?

?>