<?php
	require_once(dirname(__FILE__) .'/../api/_wrapper.php');

	new WrapperClass(['ioRouter','mngActivityLog']);
	$router = new ioRouter();
	$mngActivityLog = new mngActivityLog($ioConn);

	if(empty($USERDATA) && isset($_COOKIE['sm_user'])){
		new WrapperClass(['mngCookie','mngUser','ioAsymChiper']);
		$mngCookie = new mngCookie();
		$token_verification = $mngCookie->verifyToken($_COOKIE['sm_user']);
		if(!empty($token_verification['data'])){
			$ioAsymChiper= new ioAsymChiper();
			$user_log = (array) $token_verification['data'];
			$USERDATA = $ioAsymChiper->privDecrypt($user_log['data']);
			$mngUser = new mngUser($ioConn);
			$checkUser = $mngUser->get(['code'=> $USERDATA['UserId']]);
			if(!$checkUser['success'] || empty($checkUser['data'])) $USERDATA = null;
		}
	}
	$language = isset($_COOKIE["sm_lang"]) ? $_COOKIE["sm_lang"] : 'it';

	if(empty($USERDATA)){
		ob_start();
		header('HTTP/1.1 401 Unauthorized');
		header('Content-Type: application/json');
		exit_with_error('Unauthorized');
		exit;
	}

	if(empty($USERDATA['Authorization']) || !$mngActivityLog->checkCredetials($USERDATA['Authorization']) ){
		ob_start();
		header('HTTP/1.1 401 Unauthorized');
		header('Content-Type: application/json');
		exit_with_error('Invalid Key and Token');
		exit;
	}

	$router->setBasePath('/action');

	$router->map( 'POST', '/update-profile[/]{0,1}', function() use($USERDATA,$ioConn){
		new WrapperClass(['mngUser']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngUser = new mngUser($ioConn);
		if(!empty($params['language'])){
			setcookie("sm_lang",$params['language'],(time() + (365 * 24 * 60 * 60)),'/',false,false);
		}
		return $mngUser->edit([
			'code'=>!empty($USERDATA['UserId'])?$USERDATA['UserId']:null,
			'firstname'=>!empty($params['firstname'])?$params['firstname']:null,
			'lastname'=>!empty($params['lastname'])?$params['lastname']:null,
			// 'email'=>!empty($params['email'])?$params['email']:null,
			'language'=>!empty($params['language'])?$params['language']:null,
			'notification_settings'=>!empty($params['notification_settings'])?$params['notification_settings']:null,
		]);
	});
	$router->map( 'POST', '/verify-crypt-key[/]{0,1}', function() use($USERDATA,$ioConn){
		new WrapperClass(['mngUser']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngUser = new mngUser($ioConn);
		return $mngUser->verifyCryptKey(!empty($USERDATA['UserId'])?$USERDATA['UserId']:null,null,!empty($params['crypt-key'])?$params['crypt-key']:null);
	});
	$router->map( 'POST', '/update-api-key[/]{0,1}', function() use($USERDATA,$ioConn){
		new WrapperClass(['mngUser']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngUser = new mngUser($ioConn);
		return $mngUser->updateApikey(!empty($USERDATA['UserId'])?$USERDATA['UserId']:null,null);
	});
	$router->map( 'POST', '/connect-telegram[/]{0,1}', function() use($USERDATA,$ioConn){
		new WrapperClass(['Telegram']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$Telegram = new Telegram($ioConn);
		return $Telegram->connect(
			!empty($USERDATA['UserId'])?$USERDATA['UserId']:null,
			!empty($params['group_name'])?$params['group_name']:null,
			!empty($params['telegram_id'])?$params['telegram_id']:null
		);
	});
	$router->map( 'POST', '/change-password[/]{0,1}', function() use($USERDATA,$ioConn){
		new WrapperClass(['mngUser']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngUser = new mngUser($ioConn);
		return $mngUser->changePassword(
			!empty($USERDATA['UserId'])?$USERDATA['UserId']:null,
			!empty($params['old-password'])?$params['old-password']:null,
			!empty($params['new-password'])?$params['new-password']:null,
		);
	});
	$router->map( 'POST', '/toggle-ip/[i:id][/]{0,1}', function($id) use($USERDATA,$ioConn){
		new WrapperClass(['mngActivityLog']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngActivityLog = new mngActivityLog($ioConn);
		if (empty($params['status']) || !in_array($params['status'],['deactivate','active'])) return ['success'=>false,'error'=>'Metodo non supportato'];
		$method = $params['status'];
		return $mngActivityLog->$method($id,!empty($params['ip'])?$params['ip']:null,!empty($USERDATA['UserId'])?$USERDATA['UserId']:null);
	});
	$router->map( 'GET', '/search-page[/]{0,1}', function() use($USERDATA,$ioConn){
		new WrapperClass(['mngSidebar']);
		$params = $_GET;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngSidebar = new mngSidebar($ioConn);
		$params['user_code']=!empty($USERDATA['UserId'])?$USERDATA['UserId']:null;
		return $mngSidebar->searchPage([
			'search_text'=>!empty($params['search_text'])?$params['search_text']:null,
			'not-collapsed'=>!empty($params['not-collapsed']),
			'user_code'=>$params['user_code'],
		]);
	});


	$router->map( 'POST', '/tag[/]{0,1}', function() use($USERDATA,$ioConn){
		new WrapperClass(['mngTag']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngTag = new mngTag($ioConn);
		$params['user_code'] = !empty($USERDATA['UserId'])?$USERDATA['UserId']:null;
		return $mngTag->new($params);
	});	
	$router->map( 'POST', '/tag/[i:id][/]{0,1}', function($id) use($USERDATA,$ioConn){
		new WrapperClass(['mngTag']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngTag = new mngTag($ioConn);
		$params['user_code'] = !empty($USERDATA['UserId'])?$USERDATA['UserId']:null;
		$params['id'] = $id;
		return $mngTag->edit($params);
	});
	$router->map( 'DELETE', '/tag/[i:id][/]{0,1}', function($id) use($USERDATA,$ioConn){
		new WrapperClass(['mngTag']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngTag = new mngTag($ioConn);
		$user_id = $mngTag->getUserId(!empty($USERDATA['UserId'])?$USERDATA['UserId']:null);
		if(!$user_id['success']) return $user_id;
		return $mngTag->delete(['id'=>$id,'user_id'=> $user_id['data'] ]);
	});


	$router->map( 'POST', '/wallet-info/[*:code]?[/]{0,1}', function($code=null) use($USERDATA,$ioConn){
		new WrapperClass(['mngWallet']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngWallet = new mngWallet($ioConn);
		$params['user_code']=!empty($USERDATA['UserId'])?$USERDATA['UserId']:null;
		$params['code'] = $code;
		return $mngWallet->updateInfoWallet($params);
	});
	$router->map( 'DELETE', '/wallet/[*:code][/]{0,1}', function($code) use($USERDATA,$ioConn){
		new WrapperClass(['mngWallet']);
		parse_str(file_get_contents('php://input'), $params);
		$mngWallet = new mngWallet($ioConn);
		return $mngWallet->deleteWallet(!empty($USERDATA['UserId'])?$USERDATA['UserId']:null,$code);
	});

	$router->map( 'POST', '/transaction/[*:code]?[/]{0,1}', function($code=null) use($USERDATA,$ioConn,$language){
		new WrapperClass(['mngIntentTransaction']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngIntentTransaction = new mngIntentTransaction($ioConn);
		$params['user_code']=!empty($USERDATA['UserId'])?$USERDATA['UserId']:null;
		$params['code'] = $code;
		$params['language'] = $language;
		if(empty($code)) return $mngIntentTransaction->generate($params);
		return $mngIntentTransaction->editIntentTransaction(!empty($USERDATA['UserId'])?$USERDATA['UserId']:null,$code,$params);
	});
	$router->map( 'DELETE', '/recurrent-transaction/[*:code][/]{0,1}', function($code) use($USERDATA,$ioConn,$language){
		new WrapperClass(['mngIntentTransaction']);
		$params = $_POST;
		if (empty($params)) $params = _json_decode(file_get_contents('php://input'), true);
		if(!empty($params['jdata'])) $params = _json_decode($params['jdata'],true);
		$mngIntentTransaction = new mngIntentTransaction($ioConn);
		return $mngIntentTransaction->deactivateRecurrentTransaction(!empty($USERDATA['UserId'])?$USERDATA['UserId']:null,$code);
	});

	$router->map( 'DELETE', '/transaction/[*:code][/]{0,1}', function($code) use($USERDATA,$ioConn){
		new WrapperClass(['mngIntentTransaction']);
		parse_str(file_get_contents('php://input'), $params);
		$mngIntentTransaction = new mngIntentTransaction($ioConn);
		return $mngIntentTransaction->deleteTransaction(!empty($USERDATA['UserId'])?$USERDATA['UserId']:null,$code);
	});
	$router->map( 'DELETE', '/payment/[*:code][/]{0,1}', function($code) use($USERDATA,$ioConn){
		new WrapperClass(['mngPayment']);
		parse_str(file_get_contents('php://input'), $params);
		$mngPayment = new mngPayment($ioConn);
		return $mngPayment->deletePayment(!empty($USERDATA['UserId'])?$USERDATA['UserId']:null,$code);
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
