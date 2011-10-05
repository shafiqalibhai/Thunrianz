<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Remote blogging server
# @author legolas558
#
# Listens to XML-RPC calls to perform remote blogging.
# The metaWeblog API is the only one currently supported,
# read more at http://www.xmlrpc.com/metaWeblogApi
#

$service_params = GetComponentParamsRaw('service');
if (!$service_params->get('xmlrpc', 0)) {
	CMSResponse::Unavailable();
	return;
}

$is_serving = (in_raw('no_html', $_GET)==='1');

if (!$is_serving) {
	$pathway->add_head('Remote blogging server');
	$url = 'index2.php?option=remoteblog&Itemid='.$Itemid.'&no_html=1';
	?><p>The remote blogging XMLRPC server is available at the following address:</p>
	<a href="<?php echo xhtml_safe($url); ?>" rel="nofollow" target="_blank"><big><?php echo xhtml_encode($d_website.$url); ?></big></a>
	<p>The following remote blogging APIs are supported:</p>
	<ul>
	<li><a href="http://www.xmlrpc.com/metaWeblogApi" rel="nofollow" target="_blank">metaWeblog</a></li>
	</ul>
	<p>You can use one of the following desktop clients to interface to this remote server:</p>
	<ul>
	<li><a href="http://wbloggar.com/" target="_blank">w.bloggar</a> - multiplatform client for Windows, Linux, Mac OS X etc.</li>
	</ul>
	<?php
//	CMSResponse::Redir('$url);
	return;
}

require $d_root.'classes/xmlrpc/functions.php';
require $d_root.'classes/xmlrpc/wrappers.php';
require $d_root.'classes/xmlrpc/server.php';

$xmlrpc_internalencoding = $d_encoding;
$xmlrpc_defencoding = 'UTF-8';

$functionDefs = array();

// include the common private functions
require com_path('common');

// include the Blogger API implementation
require com_path('blogger');

// include the metaWeblog API implementation
require com_path('metaweblog');

// create server
$s = new xmlrpc_server( $functionDefs );

?>