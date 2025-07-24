<?php
class mngCookie
{

	private $Key;

	function __construct(){
		include(dirname(__FILE__) . '/../../assets/secretKey.php');
		$wrapper = new WrapperClass(['ioAsymChiper']);
		$this->Key = $key;
	}
	public function generateToken($params = array()){
		$data = $params['data'];
		$ioAsymChiper = new ioAsymChiper();
		$publicEncrypt = $ioAsymChiper->publicEncrypt(json_encode($data));
		$key=  $this->Key;
		$issure_claim = "pos.ionet.it";
		$audience_claim = "pos.ionet.it";
		$issuedat_claim = time();
		$notbefore_claim = $issuedat_claim +10;

		if(empty($params['expire'])){
			if(empty($params['rememberMe'])) $expire_claim = $issuedat_claim + (3600*24*3); // expire: 1g=3600sec*24h
			else $expire_claim = $issuedat_claim + (3600*24*360); // expire: 1y
		}else $expire_claim = $issuedat_claim + ($params['expire']);
		$payload = array(
			"iss"=> $issure_claim,
			"aud" => $audience_claim,
			"iat" => $issuedat_claim,
			"nbf" => $notbefore_claim,
			"exp" => $expire_claim,
			"data" => $publicEncrypt
		);
		$jwt = Firebase\JWT\JWT::encode($payload,$key);
		return array(
			"success" => true,
			"data" => array("jwt" =>$jwt,"exp"=>$expire_claim)
		);
	}

	public function verifyToken($token = ""){
		Firebase\JWT\JWT::$leeway = 60;
		$key = $this->Key;
		try {
			$dec_token = Firebase\JWT\JWT::decode($token, $key, array('HS256'));
			$dec_token = (array) $dec_token;
			if(!empty($dec_token)){
				if($dec_token['exp'] > time()){
					return array("success"=>true,"data"=>$dec_token);
				}
				return array("success"=>false, "error"=>"Dati assenti");
			}
			return array("success"=>false, "error"=>"Dati assenti");
		}catch(Exception $e){
			sendTelegramSystemNotification("Qualche b******o ha provato a modificare un cookie.");
		}
	}
}
?>
