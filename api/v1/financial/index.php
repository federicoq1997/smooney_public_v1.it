<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	ini_set('memory_limit','2048M');

	require_once(dirname(__FILE__) .  '/../../_wrapper.php');

	new WrapperClass(['ioRouter','mngUser']);


	/* START AUTHORIZATION CHECK */
	$headers = getallheaders();
	if(empty($headers['AToken']) && !empty($headers['Atoken']) ) $headers['AToken'] = $headers['Atoken'];
	unset($headers['Atoken']);
	if (empty($headers['AToken']) ) {
		ob_start();
		header('HTTP/1.1 401 Unauthorized');
		header('Content-Type: application/json');
		exit_with_error('Invalid AToken');
		exit;
	}
	$Signature = '';
	new WrapperClass(['mngUser']);
	$mngUser = new mngUser($ioConn);
	$info = $mngUser->verifyJWTAPI($headers['AToken']);
	if(empty($info['data'])){
		ob_start();
		header('HTTP/1.1 401 Unauthorized');
		header('Content-Type: application/json');
		exit_with_error('Unauthorized or invalid AToken');
		exit;
	}
	$info = $info['data'];
	define('UserId', (!empty($info['UserId'])?$info['UserId']:null));
	define('Language', (!empty($info['Language'])?$info['Language']:null));
	define('Ip', (!empty($info['Ip'])?$info['Ip']:null));

	/* END AUTHORIZATION CHECK */

	$router = new ioRouter();
	$router->setBasePath('/api/v1/financial');

	$router->map( 'GET', '/', function(){
		header('Content-Type: text/html; charset=UTF-8');
		redirect('/api/v1/info');
	});

	$router->map( 'GET', '/wallets', function() use($ioConn){
		new WrapperClass(['mngWallet']);
		$mngWallet = new mngWallet($ioConn);

		$wallets = $mngWallet->gets([ 'user_code'=>UserId ])['data'];
		if(empty($wallets)) return ['success'=>false,'error'=>'Nessun wallet trovato'];
		$wallets = array_map(function($wallet){
			return [
				'id' 							=> $wallet['id'],
				'code' 						=> $wallet['code'],
				'name' 						=> $wallet['name'],
				'amount_balance' 	=> $wallet['amount_balance'],
			];
		},$wallets);
		if(!empty($_GET['minimal-data'])) recursive_unsets($wallets,['amount_balance']);
		if(!empty($_GET['shortcuts-data'])){
			recursive_unsets($wallets,['amount_balance']);
			$wallets = array_map(function($wallet){
				return [
					'code' 						=> $wallet['code'],
					'name' 						=> '#'.$wallet['id'].' - '.$wallet['name'],
				];
			},$wallets);
			$wallets = array_column($wallets,'code','name');
		}
		
		return ['success'=>true,'data'=>$wallets];
	});
	$router->map( 'POST', '/intent-transaction/payment[/]{0,1}', function() use($ioConn){
		new WrapperClass(['mngIntentTransaction','mngWallet']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		if(empty($params['walletCode'])) return ['success'=>false,'error'=>'Nessun wallet trovato']; 
		if(empty($params['description'])) return ['success'=>false,'error'=>'Descrizione assente']; 
		if(empty($params['amount'])) return ['success'=>false,'error'=>'Importo assente'];
		if(empty($params['date'])) return ['success'=>false,'error'=>'Data assente'];

		$mngWallet = new mngWallet($ioConn);
		$wallet = $mngWallet->get([ 'user_code'=>UserId,'code'=>$params['walletCode'] ])['data'];
		if(empty($wallet)) return ['success'=>false,'error'=>'Nessun wallet trovato'];
		if(!empty($params['tagCode'])) $params['tag_id'] = $params['tagCode'];

		if(!empty($params['date'])){
			try{
				$params['date'] = convertToYmd($params['date']);
			}catch(Exception $e){
				return ['success'=>false,'error'=>$e->getMessage()];
			}
		}
		$params['type'] = 0;
		$params['amount'] = -1 * abs($params['amount']);

		$mngIntentTransaction = new mngIntentTransaction($ioConn);
		$params['user_code']= UserId;
		$params['language'] = !empty($params['language'])? $params['language'] : 'it';
		$params['dest_wallet_id'] = $wallet['id'];
		return $mngIntentTransaction->generate($params);
	});


	

	$route_data = $router->match();

	// call closure or throw 404 status
	if( is_array($route_data) && is_callable( $route_data['target'] ) ) {
		$content = call_user_func_array( $route_data['target'], $route_data['params'] );
		header('Content-Type: application/json');
		header('Signature-Smooney: '.hash('sha256', json_encode($content).'|'.$headers['AToken']));
		echo json_encode($content);
		exit();
	} else {
		// no route was matched
		header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
		die('Resource not found.');
	}

?>
