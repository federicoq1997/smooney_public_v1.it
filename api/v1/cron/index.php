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

	if ($headers['Api-Key'] != '018cefe0-96e3-7fcc-8d79-796139604819' ) {
		ob_start();
		header('HTTP/1.1 401 Unauthorized');
		header('Content-Type: application/json');
		exit_with_error('Invalid Key');
		exit;
	}
	if ($headers['Token'] != '018cefe0-c00c-78dc-970b-6945dd450044' ) {
		ob_start();
		header('HTTP/1.1 401 Unauthorized');
		header('Content-Type: application/json');
		exit_with_error('Invalid Token');
		exit;
	}
	$Signature = '';

	/* END AUTHORIZATION CHECK */

	$router = new ioRouter();
	$router->setBasePath('/api/v1/cron');

	$router->map( 'GET', '/', function(){
		header('Content-Type: text/html; charset=UTF-8');
		redirect('/api/v1/info');
	});

	$router->map('GET', '/send-all-email[/]{0,1}', function () use ($ioConn) {
		new WrapperClass(['Communications']);
		$QueueComunication =	new QueueComunication($ioConn);
		$QueueComunication->sendAllEmail();
		return ['success' => true, 'data'=>'request processed'];
	});
	// $router->map('GET', '/send-all-sms[/]{0,1}', function () use ($ioConn) {
	// 	new WrapperClass(['Communications']);
	// 	$QueueComunication =	new QueueComunication($ioConn);
	// 	$QueueComunication->sendAllSMS();
	// 	return ['success' => true, 'data'=>'request processed'];
	// });
	$router->map('GET', '/send-email/[*:id][/]{0,1}', function ($id=null) use ($ioConn) {
		new WrapperClass(['Communications']);
		$QueueComunication =	new QueueComunication($ioConn);
		$QueueComunication->sendEmail($id);
		return ['success' => true, 'data'=>'request processed'];
	});
	// $router->map('GET', '/send-sms/[*:id][/]{0,1}', function ($id=null) use ($ioConn) {
	// 	new WrapperClass(['Communications']);
	// 	$QueueComunication =	new QueueComunication($ioConn);
	// 	$QueueComunication->sendSMS($id);
	// 	return ['success' => true, 'data'=>'request processed'];
	// });
	$router->map('GET', '/delete-old-email-sent[/]{0,1}', function () use ($ioConn) {
		new WrapperClass(['Communications']);
		$QueueComunication =	new QueueComunication($ioConn);
		$QueueComunication->deleteOldEmailSent();
		return ['success' => true, 'data'=>'request processed'];
	});
	// $router->map('GET', '/delete-old-sms-sent[/]{0,1}', function () use ($ioConn) {
	// 	new WrapperClass(['Communications']);
	// 	$QueueComunication =	new QueueComunication($ioConn);
	// 	$QueueComunication->deleteOldSMSSent();
	// 	return ['success' => true, 'data'=>'request processed'];
	// });
	$router->map('GET', '/process-recurrent-payments[/]{0,1}', function () use ($ioConn) {
		new WrapperClass(['mngIntentTransaction']);
		$mngIntentTransaction =	new mngIntentTransaction($ioConn);
		$mngIntentTransaction->cronProcessRecurrentPayments();
		return ['success' => true, 'data'=>'request processed'];
	});
	$router->map('GET', '/process-postdated-payments[/]{0,1}', function () use ($ioConn) {
		new WrapperClass(['mngIntentTransaction']);
		$mngIntentTransaction =	new mngIntentTransaction($ioConn);
		$mngIntentTransaction->cronProcessPostdatedPayments();
		return ['success' => true, 'data'=>'request processed'];
	});
	$router->map('GET', '/process-recap-weekend[/]{0,1}', function () use ($ioConn) {
		new WrapperClass(['mngUser']);
		$mngUser =	new mngUser($ioConn);
		$mngUser->sendRecapWeekend();
		return ['success' => true, 'data'=>'request processed'];
	});
	$router->map('GET', '/generate-histories[/]{0,1}', function () use ($ioConn) {
		new WrapperClass(['mngWallet']);
		$mngWalletHistory =	new mngWalletHistory($ioConn);
		$mngWalletHistory->generateHistories();
		return ['success' => true, 'data'=>'request processed'];
	});
	$router->map('GET', '/generate-forecast[/]{0,1}', function () use ($ioConn) {
		new WrapperClass(['mngWallet']);
		$mngWalletForecast =	new mngWalletForecast($ioConn);
		$mngWalletForecast->generateAllForecast();
		return ['success' => true, 'data'=>'request processed'];
	});
	$router->map('GET', '/update-forecast/[*:user_id]/[*:wallet_id][][/]{0,1}', function ($user_id,$wallet_id) use ($ioConn) {
		new WrapperClass(['mngWallet']);
		$mngWalletForecast =	new mngWalletForecast($ioConn);
		$mngWalletForecast->generateForecast($user_id,$wallet_id);
		return ['success' => true, 'data'=>'request processed'];
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
