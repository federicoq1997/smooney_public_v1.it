<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	ini_set('memory_limit','2048M');

	require_once(dirname(__FILE__) .  '/../../_wrapper.php');

	$wrapper = new WrapperClass(['ioRouter']);


	/* START AUTHORIZATION CHECK */
	$headers = getallheaders();
	if(empty($headers['Api-Key']) && !empty($headers['Api-key']) ) $headers['Api-Key'] = $headers['Api-key'];

	if (empty($headers['Api-Key']) || empty($headers['Token']) ) {
		ob_start();
		header('HTTP/1.1 401 Unauthorized');
		header('Content-Type: application/json');
		exit_with_error('Invalid Key and Token');
		exit;
	}

	if ($headers['Api-Key'] != 'f034bf9a-74fc-47e8-bb2b-91cfe2f15117' ) {
		ob_start();
		header('HTTP/1.1 401 Unauthorized');
		header('Content-Type: application/json');
		exit_with_error('Invalid Key');
		exit;
	}
	if ($headers['Token'] != '19846b14-bbe5-4efb-8381-3ff22c3875c1' ) {
		ob_start();
		header('HTTP/1.1 401 Unauthorized');
		header('Content-Type: application/json');
		exit_with_error('Invalid Token');
		exit;
	}
	$Signature = '';

	/* END AUTHORIZATION CHECK */

	$router = new ioRouter();
	$router->setBasePath('/api/v1/auth');

	$router->map( 'GET', '/', function(){
		header('Content-Type: text/html; charset=UTF-8');
		redirect('/api/v1/info');
	});

	$router->map('POST', '/login[/]{0,1}', function () use ($ioConn) {
		new WrapperClass(['mngUser']);
		$mngUser = new mngUser($ioConn);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);

		if(empty($params['Code']) || empty($params['ApiKey'])){
			ob_start();
			header('HTTP/1.1 401 Unauthorized');
			header('Content-Type: application/json');
			exit_with_error('Incorrect credentials');
			exit;
		}

		$result = $mngUser->loginAPI($params['Code'],$params['ApiKey']);
		if(empty($result['data'])){
			ob_start();
			header('HTTP/1.1 401 Unauthorized');
			header('Content-Type: application/json');
			exit_with_error(!empty($result['error'])?$result['error']:'Incorrect credentials');
			exit;
		}
		return $result['data'];
	});

	$router->map('POST', '/new-user[/]{0,1}', function () use ($ioConn,$headers) {

		if (empty($headers['Smooney']) || $headers['Smooney'] != '17bab1a3-5b74-4511-9740-777a71f64337' ){
			header('HTTP/1.1 401 Unauthorized');
			header('Content-Type: application/json');
			exit_with_error('Incorrect credentials');
			exit;
		}

		new WrapperClass(['mngUser']);
		$mngUser = new mngUser($ioConn);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);

		$result = $mngUser->generateUser(
			!empty($params['Firstname'])	?	$params['Firstname']	:	null,
			!empty($params['Lastname'])		?	$params['Lastname']		:	null,
			!empty($params['Email'])			?	$params['Email']			:	null,
			!empty($params['Gender'])			?	$params['Gender']			:	null,
		);
		if(!$result['success']){
			$message = "🚨 <b>Smooney | Errore durante la registrazione dell'utente</b>\n";
			$message .= "<pre>".json_encode($params)."</pre>\n";
			$message .= "Response:\n<pre>".json_encode($result)."</pre>\n";
			$message .= "\n<b>Data:</b> ".(date('d/m/Y H:i:s')).".\n\n";
			$message = str_replace(['<b>', '</b>'], '', $message);
			$message = str_replace(['<br>', '</br>'], "\n", $message);
		}else{
			$params['Id'] = !empty($result['data']['id'])?$result['data']['id']:null;
			$params['Code'] = !empty($result['data']['code'])?$result['data']['code']:null;
			$message = "✅ <b>Smooney | Nuovo Utente registrato</b>\n";
			$message .= "<pre>".json_encode($params)."</pre>\n";
			$message .= "\n<b>Data:</b> ".(date('d/m/Y H:i:s')).".\n\n";
			$message = str_replace(['<b>', '</b>'], '', $message);
			$message = str_replace(['<br>', '</br>'], "\n", $message);
		}
		sendTelegramMessage($message,'-4170380050');
		return $result;
	});
	$router->map('DELETE', '/user[/]{0,1}', function () use ($ioConn,$headers) {

		if (empty($headers['Smooney']) || $headers['Smooney'] != '17bab1a3-5b74-4511-9740-777a71f64337' ){
			header('HTTP/1.1 401 Unauthorized');
			header('Content-Type: application/json');
			exit_with_error('Incorrect credentials');
			exit;
		}

		new WrapperClass(['mngUser']);
		$mngUser = new mngUser($ioConn);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);

		$result = $mngUser->deleteUser(
			!empty($params['Code'])	?	$params['Code']	:	null,
		);
		if(!$result['success']){
			$message = "🚨 <b>Smooney | Errore durante l'eliminazione dell'account</b>\n";
			$message .= "<pre>".json_encode($params)."</pre>\n";
			$message .= "Response:\n<pre>".json_encode($result)."</pre>\n";
			$message .= "\n<b>Data:</b> ".(date('d/m/Y H:i:s')).".\n\n";
			$message = str_replace(['<b>', '</b>'], '', $message);
			$message = str_replace(['<br>', '</br>'], "\n", $message);
		}else{
			$params['Id'] = !empty($result['data']['id'])?$result['data']['id']:null;
			$params['Code'] = !empty($result['data']['code'])?$result['data']['code']:null;
			$message = "✅ <b>Smooney | Account eliminato</b>\n";
			$message .= "<pre>".json_encode($params)."</pre>\n";
			$message .= "\n<b>Data:</b> ".(date('d/m/Y H:i:s')).".\n\n";
			$message = str_replace(['<b>', '</b>'], '', $message);
			$message = str_replace(['<br>', '</br>'], "\n", $message);
		}
		sendTelegramMessage($message,'-4170380050');
		return $result;
	});

	

	$route_data = $router->match();

	// call closure or throw 404 status
	if( is_array($route_data) && is_callable( $route_data['target'] ) ) {
		$content = call_user_func_array( $route_data['target'], $route_data['params'] );
		header('Content-Type: application/json');
		header('Signature-Smooney: '.hash('sha256', json_encode($content).'|'.$headers['Api-Key']));
		echo json_encode($content);
		exit();
	} else {
		// no route was matched
		header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
		die('Resource not found.');
	}

?>
