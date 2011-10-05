<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include com_path($d_type);

$task = in_raw('task', $_REQUEST, 'view');

switch ( $task ) {
	case "login":  
		$my->Login(1, $_POST);
		break;
	case "logout":
		out_session('chklo', 1);
		$my->Logout();
		break;
	case 'auth':
		if (!$d_http_auth) {
			CMSResponse::Unauthorized();
			return;
		}
		include $d_root.'classes/http_auth.php';
		HttpAuth::Authenticate(1, ($d_http_auth==2));
		return;
}

if ($my->id)
	logoutpage();
else
	loginpage();

?>