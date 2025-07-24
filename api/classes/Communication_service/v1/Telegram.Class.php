<?php
require_once( dirname(__FILE__).'/../../../environment/autoload.php' );

class Telegram{

	private $cn; //connection
	private $base_url;//getUpdates
	public function __construct( $cn){
		$configEnvironment = EnvironmentType::getConfig();
		$bot_token = !empty($configEnvironment['telegram']['token_public'])? $configEnvironment['telegram']['token_public']:"";
		$this->base_url = 'https://api.telegram.org/'.$bot_token;
		$this->cn = $cn;
	}

	public function connect(?string $userId,?string $group_name,?string $telegram_id){
		if(empty($userId)) return ['success'=>false,'error'=>'Identificativo dell\'utente assente'];
		if(empty($group_name)) return ['success'=>false,'error'=>'Nome del gruppo assente'];
		if(empty($telegram_id)) return ['success'=>false,'error'=>'ID Telegram assente'];
		$telegram_id = str_replace('@','',$telegram_id);
		new WrapperClass(['ioRequest']);
		$ioRequest = new ioRequest();
		$ioRequest->setServer($this->base_url);
		$response = $ioRequest->get('/getUpdates')['data'];
		if(!$response['ok'] || empty($response['result'])) return $response;
		$response = $response['result'];
		$chatID = null;
		foreach($response as $info){
			$el = !empty($info['my_chat_member'])?$info['my_chat_member']:(!empty($info['message'])?$info['message']:null);
			if(empty($el['chat']['title']) || $el['chat']['title'] != $group_name ) continue;
			if(empty($el['from']['username']) || $el['from']['username'] != $telegram_id ) continue;
			$chatID = $el['chat']['id'];
			break;
		}
		if(empty($chatID)) return ['success'=>false,'error'=>'Nessuna chat è stata trovata'];
		new WrapperClass(['mngUser']);
		$mngUser = new mngUser($this->cn);
		$user = $mngUser->get([ 'code'=>$userId ])['data'];
		if(empty($user)) return ['success'=>false,'error'=>'Utente non trovato'];
		$update = $mngUser->edit([
			'code'=>$userId,
			'notification_settings'=>[
				'telegram'=>[
					'actions'=>$user['notification_settings']['telegram']['actions'],
					'enabled'=>true,
					'send-to'=>[
						"title"=> $group_name,
						"medium"=> "telegram",
						"chat-id"=> $chatID
					]
				]
			]
		]);
		if(!$update['success']) return $update;
		$message = "✨ <b>Smooney</b>\n";
		$message .= "Ciao! Sono <b>Smooney Bot</b> 🤖, qui per inviarti notifiche su nuovi accessi, codici OTP, e molto altro ancora!\n";
		sendTelegramMessage($message,$chatID);
		return ['success'=>true,'error'=>'Connesso'];
	}
}
?>