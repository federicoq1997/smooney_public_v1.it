<?php
	require_once(dirname(__FILE__) .'/../../api/_wrapper.php');

	new WrapperClass(['ioRouter','mngUser','ioAsymChiper','mngActivityLog']);
	$router = new ioRouter();
	$mngUser = new mngUser($ioConn);
	$mngActivityLog = new mngActivityLog($ioConn);
	$headers = getallheaders();

	// if(empty($headers['Authorization']) || $mngActivityLog->checkCredetials($headers['Authorization']) ){
	// 	ob_start();
	// 	header('HTTP/1.1 401 Unauthorized');
	// 	header('Content-Type: application/json');
	// 	exit_with_error('Invalid Key and Token');
	// 	exit;
	// }

	$router->setBasePath('/action/auth');

	$router->map( 'POST', '/verify-credentials[/]{0,1}', function() use($ioConn){
		new WrapperClass(['mngUser']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngUser = new mngUser($ioConn);
		return $mngUser->verifyCredentials(!empty($params['email'])?$params['email']:null,!empty($params['password'])?$params['password']:null);
	});	
	
	$router->map( 'POST', '/login[/]{0,1}', function() use($ioConn){
		new WrapperClass(['mngUser']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngUser = new mngUser($ioConn);
		return $mngUser->login(!empty($params['code'])?$params['code']:null,!empty($params['otp'])?$params['otp']:null,!empty($params['RememberMe']));
	});

	$router->map( 'POST', '/send-otp[/]{0,1}', function() use($USERDATA,$ioConn){
		new WrapperClass(['mngUser']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngUser = new mngUser($ioConn);
		return $mngUser->generateOTP(!empty($params['code'])?$params['code']:null,!empty($params['email'])?$params['email']:null);
	});
	$router->map( 'POST', '/recover-password[/]{0,1}', function() use($USERDATA,$ioConn){
		new WrapperClass(['mngUser']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngUser = new mngUser($ioConn);
		return $mngUser->resetPassword(!empty($params['email'])?$params['email']:null,!empty($params['otp'])?$params['otp']:null);
	});
	$router->map( 'POST', '/account-request[/]{0,1}', function() use($USERDATA,$ioConn){
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$message = "🥰 <b>Smooney</b>\n";
		$message .= "Hai ricevuto una nuova richiesta!\n";
		$message .= "<b>Nome</b>: ".(!empty($params['user'])?$params['user']:'')." \n";
		$message .= "<b>Email</b>: ".(!empty($params['email'])?$params['email']:'')." \n";
		$message .= "<b>Data</b>: ".(date('d/m/Y H:i:s'))." \n";
		sendTelegramMessage($message,'-4141148369');
		return ['success'=>true,'data'=>'DONE'];
	});

	$route_data = $router->match();
	// Processing the matched path
	if( is_array($route_data) && is_callable( $route_data['target'] ) ) {
		$content = call_user_func_array( $route_data['target'], $route_data['params'] );
		header('Content-Type: application/json');
		echo json_encode($content);
		exit();
	} else {
		// no route was matched
		header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
		die('Resource not found.');
	}
?>
