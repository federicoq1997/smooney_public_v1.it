<?php
	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);
	require_once(dirname(__FILE__) .'/api/_wrapper.php');
	
	new WrapperClass(['ioRouter']);
	$router = new ioRouter();

	$_404 = '/pages/error-pages/404.php';
	$_403 = '/pages/error-pages/403.php';
	$_500 = '/pages/error-pages/500.php';
	$_502 = '/pages/error-pages/502.php';
	$_503 = '/pages/error-pages/503.php';
	$_coming_soon = '/pages/error-pages/coming-soon.php';
	$_maintenance = '/pages/error-pages/maintenance.php';



	$request_uri = $_SERVER['REQUEST_URI'];
	$user_log = array();
		
	if(isset($_COOKIE['sm_user']) && empty($USERDATA)){
		new WrapperClass(['mngCookie','mngUser','ioAsymChiper']);
		$mngCookie = new mngCookie();
		$token_verification = $mngCookie->verifyToken($_COOKIE['sm_user']);
		if(!empty($token_verification['data'])){
			$ioAsymChiper= new ioAsymChiper();
			$user_log = (array) $token_verification['data'];
			$USERDATA = $ioAsymChiper->privDecrypt($user_log['data']);
			if(empty($USERDATA)){
				unset($_COOKIE['sm_user']);
				setcookie('sm_user', "", time() - 3600, '/',false,false);
				redirect('/logout'.(!empty($request_uri)?'?request_uri='.urlencode($request_uri):''));
				die();
			}
			$mngUser = new mngUser($ioConn);
			$checkUser = $mngUser->get(['code'=> $USERDATA['UserId']]);
			if(!$checkUser['success'] || empty($checkUser['data'])) redirect('/logout'.(!empty($request_uri)?'?request_uri='.urlencode($request_uri):''));
		}
	}	
	if(!isset($_COOKIE['sm_lang']) && !empty($USERDATA['Language'])){
		setcookie("sm_lang",$USERDATA['Language'],(time() + (365 * 24 * 60 * 60)),'/',false,false);
	}





	$_basedir = '';
	
	/**
	* LOGIN --------------------------------------------------------------------
	*/
	$router->map( 'GET', $_basedir.'/', function() use ($USERDATA,$_coming_soon) {
		// $info = getInfoUser();
		// if(isset($info['ip']) && $info['ip'] != '37.182.162.98') return $_coming_soon;
		if(empty($USERDATA)) return '/pages/login.php';
		return '/pages/dashboard.php';
	});
	$router->map( 'GET', $_basedir.'/recover-password', function() use ($USERDATA) {
		return '/pages/recover-password.php';
	});
	$router->map( 'GET', $_basedir.'/account-request', function() use ($USERDATA) {
		return '/pages/account-request.php';
	});
	$router->map( 'GET|POST', $_basedir.'/[dashboard|home:page][/]{0,1}', function($page) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/dashboard.php';
	});
	/**
	* PROFILE --------------------------------------------------------------------
	*/
	$router->map( 'GET|POST', $_basedir.'/[profile:page][/]{0,1}', function($page) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/profile/information.php';
	});
	$router->map( 'GET|POST', $_basedir.'/[profile-notifications:page][/]{0,1}', function($page) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/profile/notifications.php';
	});
	$router->map( 'GET|POST', $_basedir.'/[profile-activity:page][/]{0,1}', function($page) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/profile/activity.php';
	});
	$router->map( 'GET|POST', $_basedir.'/[password-change:page][/]{0,1}', function($page) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/profile/password-change.php';
	});
	$router->map( 'GET|POST', $_basedir.'/[developer:page][/]{0,1}', function($page) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/profile/developer.php';
	});
	$router->map( 'GET|POST', $_basedir.'/app-ios/download/shortcut[/]{0,1}', function() use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/profile/actions/download-shortcut.php';
	});
	/**
	* TAGS --------------------------------------------------------------------
	*/
	$router->map( 'GET|POST', $_basedir.'/[tags:page][/]{0,1}', function($page) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/tag/list.php';
	});
	$router->map( 'GET', $_basedir.'/modal-tag[/]{0,1}', function() use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/tag/modal.add-new-tag.php';
	});
	$router->map( 'GET', $_basedir.'/table-tag-list[/]{0,1}', function() use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/tag/table.tag-lits.php';
	});
	/**
	* WALLET --------------------------------------------------------------------
	*/
	$router->map( 'GET|POST', $_basedir.'/[wallet:page][/]{0,1}', function($page) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/wallet/list.php';
	});
	$router->map( 'GET|POST', $_basedir.'/[wallet:page]/[*:code][/]{0,1}', function($page,$code) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/wallet/edit.php';
	});
	$router->map( 'GET', $_basedir.'/modal-types-transaction[/]{0,1}', function() use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/wallet/modal.ask-type-transaction.php';
	});
	$router->map( 'GET', $_basedir.'/modal-list-wallets[/]{0,1}', function() use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/wallet/modal.list-wallets.php';
	});
	$router->map( 'GET', $_basedir.'/modal-wallet/[*:walletId]?[/]{0,1}', function($walletId=null) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/wallet/modal.add-new-wallet.php';
	});
	$router->map( 'GET', $_basedir.'/table-payment-list[/]{0,1}', function() use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/wallet/table.payment-lits.php';
	});
	$router->map( 'GET', $_basedir.'/modal-transaction/[i:walletId]/[*:transactionCode]?[/]{0,1}', function($walletId,$transactionCode=null) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/wallet/modal.add-transaction.php';
	});
	$router->map( 'GET', $_basedir.'/modal-transaction-recurring/[i:walletId]/[*:transactionCode]?[/]{0,1}', function($walletId,$transactionCode=null) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/wallet/modal.add-transaction-recurring.php';
	});

	/**
	* ANALYTICS --------------------------------------------------------------------
	*/
	$router->map( 'GET|POST', $_basedir.'/[analytics:page][/]{0,1}', function($page) use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/analytics/view.php';
	});
	$router->map( 'GET', $_basedir.'/analytics/table-payment-list[/]{0,1}', function() use ($USERDATA) {
		if(empty($USERDATA)) redirect('/');
		return '/pages/analytics/ajax/table.payment-lits.php';
	});
	/**
	* LOGOUT --------------------------------------------------------------------
	*/
	$router->map( 'GET', $_basedir.'/logout', function() {
		unset($_COOKIE['sm_user']);
		setcookie('sm_user', "", time() - 3600, '/',false,false);
		$_url = !empty($_REQUEST['request_uri'])?$_REQUEST['request_uri']:null;
		redirect('/'.(!empty($_url)?'?request_uri='.$_url:''));
	});
	/**
	* erro page --------------------------------------------------------------------
	*/
	$router->map( 'GET|POST', $_basedir.'/403', function() use($_403){
		return $_403;
	});
	$router->map( 'GET|POST', $_basedir.'/404', function() use($_404){
		return $_404;
	});
	$router->map( 'GET|POST', $_basedir.'/500', function() use($_500){
		return $_500;
	});
	$router->map( 'GET|POST', $_basedir.'/502', function() use($_502){
		return $_502;
	});
	$router->map( 'GET|POST', $_basedir.'/503', function() use($_503){
		return $_503;
	});
	$router->map( 'GET|POST', $_basedir.'/coming-soon', function() use($_coming_soon){
		return $_coming_soon;
	});
	$router->map( 'GET|POST', $_basedir.'/maintenance', function() use($_maintenance){
		return $_maintenance;
	});

	/**
	* Website --------------------------------------------------------------------
	*/

	$router_match = $router->match();

	// Processing the matched path
	if( is_array($router_match) && is_callable( $router_match['target'] ) ) {
			$result = call_user_func_array( $router_match['target'], $router_match['params'] );
			if(!empty($result) && file_exists(dirname(__FILE__).$result)!==false) {
					if(!empty($router_match['params'])){
							foreach($router_match['params'] as $k=>$v){
									$_GET[$k] = $v;
									$_REQUEST[$k] = $v;
							}
					}
					require_once(dirname(__FILE__).$result);
					exit();
			}elseif(file_exists(dirname(__FILE__).$_404)!==false){
					http_response_code(404);
					$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
					if(!checkExtension($actual_link)){
						sendTelegramSystemNotification("error 404:".$actual_link);
					}
					require_once(dirname(__FILE__).$_404);
					exit();
			}else header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
	} else {
		if(file_exists(dirname(__FILE__).$_404)!==false){
				http_response_code(404);
				$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				if(!checkExtension($actual_link)){
					sendTelegramSystemNotification("error 404:".$actual_link);
				}
				require_once(dirname(__FILE__).$_404);
				exit();
		}
		else header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
	}
?>
