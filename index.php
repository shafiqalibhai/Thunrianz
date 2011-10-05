<?php
## Main component provider
# @see classes/cms.php
#
# main output script
# This is considered output source 1

require 'core.php';

include $d_root.'includes/header.php';

if (empty($d_template)) {
	//TODO: when a template is not available a barebone simple HTML template should be used
	//L: the currently used alternate output is not a good choice, since index2.php is for another purpose
	include 'index2.php';
	exit;
}

// website is offline, show online only for admins and managers
if(!$d_online and $my->gid < 4) {
	// force hard logout
	$my->RemoveLoginInfo();
	CMSResponse::ServerError();
}

//L: this output buffering should be skipped if we improve the pathway generation
//L: and make it independent from the content generation.
//L: note: probably unfeasible, should stay as is

//TODO: cache-not-active branch (when cache is active instead no output buffering should ever be triggered) {
ob_start();

	// generate the main output
	include $d_root.'includes/component.php';
	
$d->content = ob_get_clean();

//TODO: end of cache-not-active branch } $d->content = file_get_contents($cache_file);

// generate the pathway

//TODO: the pathways should be cached independently and luckly they only depend from querystring
$d->pathway = $pathway->Generate();
$pathway = null;

// preload all modules into memory
$d->PreloadModules();

CMSResponse::Start();

//TODO: the normal template is executed, but if caching is active the cached "fragments" will be sent to output
// instead of including the PHP code that will query the DB and generate output
// if caching is implemented efficiently, no DB queries have been executed before or after the cache fragments loading
include $d_root.'templates/'.$d_template.'/index.php';

//PHP4
@session_write_close();
?>
