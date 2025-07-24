<?php
require_once(dirname(__FILE__) .'/../api/_wrapper.php');
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
new WrapperClass(['mngActivityLog']);
$mngActivityLog = new mngActivityLog($ioConn);
if(empty($USERDATA['Authorization']) || !$mngActivityLog->checkCredetials($USERDATA['Authorization']) ){
	unset($_COOKIE['sm_user']);
	setcookie('sm_user', "", time() - 3600, '/',false,false);
	redirect('/logout'.(!empty($request_uri)?'?request_urii='.urlencode($request_uri):''));
	die();
}
?>