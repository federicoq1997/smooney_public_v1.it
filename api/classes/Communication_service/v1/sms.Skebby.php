<?php
class smsSkebby{
	private $cn; //connection
	private $base_url = 'https://api.skebby.it/API/v1.0/REST/';
	
	public function __construct( $cn){
		$this->cn = $cn;
		$this->localize();
	}
	/*
		default internal localization
	*/
	private function localize(){
		if (!defined('MESSAGE_HIGH_QUALITY')) define("MESSAGE_HIGH_QUALITY", "GP");
		if (!defined('MESSAGE_MEDIUM_QUALITY')) define("MESSAGE_MEDIUM_QUALITY", "TI");
		if (!defined('MESSAGE_LOW_QUALITY')) define("MESSAGE_LOW_QUALITY", "SI");
	}

	private function login($username,$password){
		
		$ch = curl_init();
		$credentials = base64_encode($username . ':' . $password);

		curl_setopt($ch, CURLOPT_URL, $this->base_url . 'login');
		curl_setopt($ch, CURLOPT_HTTPGET, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);

		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		if ($info['http_code'] != 200) {
			sendTelegramSystemNotification('Error smsSkebby! http code: ' . $info['http_code'] . ', body message: ' . $response);
			return null;
			//  throw new Exception('Error! http code: ' . $info['http_code'] . ', body message: ' . $response);
		}

		return explode(";", $response);
	}

	private function sendSMS($auth, $sendSMS) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->base_url . 'sms');
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Content-type: application/json',
				'user_key: ' . $auth[0],
				'Session_key: ' . $auth[1]
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendSMS));
		
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if ($info['http_code'] != 201){
			$_response = new stdClass();
			$_response->result = 'ERROR '.$info['http_code'];
			$_response->data = $response;
			return $_response;
			//  throw new Exception('Error! http code: ' . $info['http_code'] . ', body message: ' . $response);
		}

		return json_decode($response);
	}
	private function isValidPhoneNumber($number) {
    return preg_match('/^\+?\d+$/', $number) === 1;
	}

	private function replaceUrl($testo) {
    $pattern = '/\bhttps?:\/\/\S+/i';
    
    preg_match($pattern, $testo, $match);

    if (!empty($match)) {
        $testo = preg_replace($pattern, '%RICHURL________%', $testo, 1);
        return [$testo, $match[0]];
    }
    return [$testo,null];
	}

	public function send($orderId,$recipient,$txt,$userId){
		if(!$this->isValidPhoneNumber($recipient)) return ['success'=>false,'data'=>'Numero non valido'];
		$software = new ioQuery('select');
		$software->from('software')
		->column('communication_config.id')
		->column('communication_config.config')
		->left_join('communication_config',null,'communication_config.user_id = '.$this->cn->escape($userId).' AND communication_config.deleted = 0 AND communication_config.software_id = software.id ')
		->where('AND',"software.alias = 'skebby' ")
		->where('AND',"communication_config.alias = 'send_sms' ")
		->where('AND',"software.deleted = 0 ");
		$software = $this->cn->query($software->get());
		if($software){
			$software = reset($software);
			$software['config'] = !empty($software['config'])?_json_decode($software['config'],true):null;
		}else return ['success'=>false,'error'=>'Software non abilitato'];
		if(empty($software['config']['reg_email']) || empty($software['config']['password_api']) ) return ['success'=>false,'error'=>'Software con credenziali incomplete'];
		$txt = str_replace("%RICHURL________%", "", $txt);
		$auth = $this->login($software['config']['reg_email'], $software['config']['password_api']);
		if(empty($auth)) ['success'=>false,'error'=>'Login skebby fallito'];
		// list($txt,$url) = $this->replaceUrl($txt);
		$smsSent = $this->sendSMS($auth, array_merge([
			"message" =>$txt,
			"message_type" => MESSAGE_HIGH_QUALITY,
			"returnCredits" => false,
			"recipient" => [$recipient], // "+prefix"."phone"
			"order_id"=>$orderId,
			"sender" => null,     
		],!empty($url)?['richsms_url'=>$url]:[]));
		
		if ($smsSent->result == "OK") return ['success'=>true,'data'=>'Message sent'];
		return ['success'=>false,'error'=>'Invio del sms fallito','data'=>$smsSent];
	}
}
?>