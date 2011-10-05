<?php
## 404 error
# @author legolas558
#
# redirect end (server-side) for 404 errors

require 'core.php';

include $d_root.'includes/servererror.php';

header('Status: 404 Not Found', true, 404);

service_msg('404 - Not found', 'Page not found');

?>