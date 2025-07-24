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
	$router->setBasePath('/api/v1/config');

	$router->map( 'GET', '/', function(){
		header('Content-Type: text/html; charset=UTF-8');
		redirect('/api/v1/info');
	});

	$router->map( 'GET', '/tags', function() use($ioConn){
		new WrapperClass(['mngTag']);
		$mngTag = new mngTag($ioConn);

		$tags = $mngTag->gets([ 'user_code'=>UserId ])['data'];
		if(empty($tags)) return ['success'=>false,'error'=>'Nessun tag trovato'];
		$tags = array_map(function($tag){
			return [
				'id' 							=> $tag['id'],
				'name' 						=> $tag['name'],
			];
		},$tags);
		if(!empty($_GET['shortcuts-data'])){
			$tags = array_map(function($tag){
				return [
					'id' 						=> $tag['id'],
					'name' 						=> '#'.$tag['id'].' - '.$tag['name'],
				];
			},$tags);
			$tags = array_column($tags,'id','name');
		}
		
		return ['success'=>true,'data'=>$tags];
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
