<?php
class mngUser extends CRUDBase{
	private $table = 'user';
	public function __construct(ioConn $ioConn) {
		parent::__construct($ioConn);
		$this->setTableName($this->table);
	}
	public function getShortCode($code):string{
		$short_code ="";
		$exist_short_code = true;
		do {
			$short_code ='SMU-'.strtoupper(getCode(6)). strtoupper(substr($code, -3));
			$exist_estimate = $this->gets([ 'ext_code' => $short_code,'data'=>true ])['data'];
			if(empty($exist_estimate)) $exist_short_code= false;
		} while ($exist_short_code);
		return $short_code;
	}
	private function new(array $params = []) {
		if(empty($params['firstname']) ) return $this->error("Nome dell'utente assente");
		if(empty($params['email']) ) return $this->error("Email dell'utente assente");
		if( !empty( $this->gets([ 'email'=>$params['email'],'data'=>true ])['data'] ) ) return $this->error("L'Email è già stata usata");
		if(empty($params['password']) ) return $this->error("Password assente");

		if(empty($params['code']) ) $params['code'] = $this->conn->getUuid();
		if(empty($params['api_key']) ) $params['api_key'] = $this->conn->getUuid();
		if(empty($params['ext_code']) ) $params['ext_code'] = $this->getShortCode($params['code']);
		$params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
		if(isset($params['notification_settings'])) $params['notification_settings'] = json_encode($params['notification_settings']);

		$result = parent::create($params);
		if(!$result['success'] || empty($result['data'])) return $result;
		return parent::success(array_merge($result['data'],['code'=>$params['code']]));
	}
	public function edit(array $params = []) {
		if(empty($params['id']) && empty($params['code']) ) return $this->error("Identificativo assente");

		$conditions = [];
		if(!empty($params['id'])) $conditions['id'] = $params['id'];
		if(!empty($params['code'])) $conditions['code'] = $params['code'];
		recursive_unsets($params,['id','code']);

		if(!empty($params['notification_settings'])){
			$user = $this->get([ 'id'=>!empty($conditions['id'])?$conditions['id']:null,'code'=>!empty($conditions['code'])?$conditions['code']:null ])['data'];
			if(!empty($user)){
				if(empty($user['notification_settings'])) $user['notification_settings'] = [];
				if(!empty($params['notification_settings']['telegram']['send-to'])) $user['notification_settings']['telegram'] = $params['notification_settings']['telegram'];
				else if(!empty($params['notification_settings']['telegram']['actions'])) $user['notification_settings']['telegram']['actions'] = $params['notification_settings']['telegram']['actions'];
				if(!empty($user['notification_settings']['telegram']['actions'])) $user['notification_settings']['telegram']['actions'] = array_unique(array_values($user['notification_settings']['telegram']['actions']));
				
				if(!empty($params['notification_settings']['email'])) $user['notification_settings']['email'] = $params['notification_settings']['email'];
				if(!empty($user['notification_settings']['email']['actions'])) $user['notification_settings']['email']['actions'] = array_unique(array_values($user['notification_settings']['email']['actions']));
				$params['notification_settings'] = $user['notification_settings'];
			}
		}
		if(isset($params['notification_settings'])) $params['notification_settings'] = json_encode($params['notification_settings']);

		return parent::update($params,$conditions);
	}
	public function gets(array $params = []) {
		if(empty($params)) return $this->error("Nessun parametro fornito");

		$query = new ioQuery('select');
		$query->from($this->table);

		if(empty($params['data'])) $query->column('`'.$this->table.'`.*');
		else $query->column('`'.$this->table.'`.id')->column('`'.$this->table.'`.code');

		$query->where('AND',' `'.$this->table.'`.deleted = 0 ')
		->group_by(' `'.$this->table.'`.code ');

		if(!empty($params['data']) && is_array($params['data'])) foreach($params['data'] as $key => $value){
			$query->column( (!empty($value['func'])?$value['func']: ( ( !empty($value['table'])?$value['table']:'`'.$this->table.'`' ).'.'.$value['column'])) ,!empty($value['alias'])?$value['alias']:null);
		}

		if(!empty($params['code']))
			$query->where('AND'," `".$this->table."`.code = '".$this->conn->escape($params['code'])."' ");
		if(!empty($params['api_key']))
			$query->where('AND'," `".$this->table."`.api_key = '".$this->conn->escape($params['api_key'])."' ");
		if(!empty($params['id']))
			$query->where('AND',' `'.$this->table.'`.id = '.$this->conn->escape($params['id']).' ');
		if(!empty($params['ext_code']))
			$query->where('AND'," `".$this->table."`.ext_code = '".$this->conn->escape($params['ext_code'])."' ");
		if(!empty($params['email']))
			$query->where('AND'," `".$this->table."`.email = '".$this->conn->escape($params['email'])."' ");
		$result = $this->conn->query($query->get());
		if($result){ 
			if(empty($params['data']) || (is_array($params['data']) && in_array('otp_ext',array_column($params['data'],'column'))) )
				map_array_field($result,'otp_ext',function($row){ return !empty($row['otp_ext'])?date('Y-m-d H:i:s',strtotime($row['otp_ext'])):null; });
			if(empty($params['data']) || (is_array($params['data']) && in_array('creation_dt',array_column($params['data'],'column'))) )
				map_array_field($result,'creation_dt',function($row){ return !empty($row['creation_dt'])?date('Y-m-d H:i:s',strtotime($row['creation_dt'])):null; });
			if(empty($params['data']) || (is_array($params['data']) && in_array('last_update_dt',array_column($params['data'],'column'))) )
				map_array_field($result,'last_update_dt',function($row){ return !empty($row['last_update_dt'])?date('Y-m-d H:i:s',strtotime($row['last_update_dt'])):null; });
			if(empty($params['data']) || (is_array($params['data']) && in_array('cancellation_dt',array_column($params['data'],'column'))) )
				map_array_field($result,'cancellation_dt',function($row){ return !empty($row['cancellation_dt'])?date('Y-m-d H:i:s',strtotime($row['cancellation_dt'])):null; });
			if(empty($params['data']) || (is_array($params['data']) && in_array('notification_settings',array_column($params['data'],'column'))) )
				map_array_field($result,'notification_settings',function($row){ return !empty($row['notification_settings'])?_json_decode($row['notification_settings'],true):null; });
			
			return parent::success($result);
		}
		return parent::error('Nessun metodo trovato');
	}
	public function get(array $params = []):array
	{
		if(empty($params)) return $this->error("Nessun parametro fornito");
		if(empty($params['id']) && empty($params['code']) && empty($params['email'])) return $this->error("Identificativo assente");
		if(!empty($params['api_key']) && empty($params['code'])) return $this->error("Identificativo assente");
		$result  = $this->gets($params);
		if(!$result['success'] || empty($result['data'])) return !empty($result['error'])?$this->error($result['error']):$this->error("Utente non trovato");
		return $this->success(reset($result['data']));
	}
	public function verifyCredentials(string $email=null, string $password=null){
		if(empty($email)) return $this->error('Email mancante');
		if(empty($password)) return $this->error('Password mancante');
		$user = $this->get([ 'email'=>$email ])['data'];
		if(empty($user)) return $this->error('Credenziali errate');
		if( !password_verify($password, $user['password']) ) return $this->error('Credenziali errate');
		
		$this->generateOTP($user['code']);
		return parent::success([
			'UserId'=>$user['code'],
			'Firstname'=>$user['firstname'],
			'Lastname'=>$user['lastname'],
		]);
	}
	public function generateOTP(?string $code=null,?string $email=null){
		if(empty($code) && empty($email)) return $this->error('Identificativo dell\'utente assente');
		
		if(empty($code)){
			$user = $this->get([ 'email'=>$email ])['data'];
			if(empty($user)) return $this->error('Credenziali errate');
			$code = $user['code'];
		}

		$OTP = generateOTP();
		$minutes = 10;
		$otp_ext = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s').'+ '.$minutes.' minutes'));
		$addOTP_to_user = $this->edit([ 'code'=>$code,'otp'=>$OTP,'otp_ext'=>$otp_ext ]);
		if(!$addOTP_to_user['success']) return $this->error('Errore durante la generazione dell\'OTP');
		$user = $this->get([ 'code'=>$code,'data'=>[
			[ 'column'=>'notification_settings' ],
			[ 'column'=>'email' ]
		] ])['data'];
		if(empty($user)) return $this->error('Utente non trovato');
		
		new WrapperClass(['Communications']);
		$Communications = new Communications($this->conn);
		$Communications->PutEmailInQueue($user['email'],'support@smooney.it','Smooney',
			[
				'OTP'=>$OTP
			],
			1,
			'verification_code',
			'it'
		);

		if(!empty($user['notification_settings']['telegram']) && !empty($user['notification_settings']['telegram']['enabled']) && !empty($user['notification_settings']['telegram']['actions']) ){
			if(in_array('otp',$user['notification_settings']['telegram']['actions']) && !empty($user['notification_settings']['telegram']['send-to'])){
				$send_to = $user['notification_settings']['telegram']['send-to'];
				$message = "💬 <b>Smooney</b>\n";
				$message .= "Il tuo codice di sicurezza è <b>".$OTP."</b>.\n";
				$message .= "Ti preghiamo di non condividerlo e di utilizzarlo entro <b>".$minutes."</b> minuti\n";
				$message = str_replace(['<b>', '</b>'], '', $message);
				$message = str_replace(['<br>', '</br>'], "\n", $message);
				sendTelegramMessage($message, $send_to['chat-id']);
			}
		}

		return parent::success($user['code']);
	}
	public function verifyOTP(string $code, string $otp){
		if(empty($code)) return $this->error('Identificativo dell\'utente assente');
		if(empty($otp)) return $this->error('OTP mancante');
		$user = $this->get([ 'code'=>$code ])['data'];
		if(empty($user)) return $this->error('Utente non trovato');
		if( date('Y-m-d H:i:s') > $user['otp_ext'] ) return $this->error('L\'OTP inserito è scaduto');
		if( $user['otp'] != $otp ) return $this->error('L\'OTP inserito errato');

		return parent::success(true);
	}
	public function changePassword(string $code, string $old_password, string $new_password){
		if(empty($code)) return $this->error('Identificativo dell\'utente assente');
		if(empty($old_password)) return $this->error('Vecchia password assente');
		if(empty($new_password)) return $this->error('Nuova password assente');
		$user = $this->get([ 'code'=>$code ])['data'];
		if(empty($user)) return $this->error('Credenziali errate');
		if( !password_verify($old_password, $user['password']) ) return $this->error('La vecchia password inserita non è corretta');
		$new_password = password_hash($new_password, PASSWORD_DEFAULT);

		$update_password = $this->edit([ 'code'=>$code,'password'=>$new_password ]);
		if(!$update_password['success']) return parent::error('Errore durante il salvataggio della nuova password');

		if(!empty($user['notification_settings']['email']) && !empty($user['notification_settings']['email']['actions']) ){
			if(in_array('change-password',$user['notification_settings']['email']['actions'])){
				new WrapperClass(['Communications']);
				$Communications = new Communications($this->conn);
				$Communications->PutEmailInQueue($user['email'],'support@smooney.it','Smooney',
					[
						'firstname'	=> $user['firstname'],
						'lastname'	=> !empty($user['lastname'])?$user['lastname']:''
					],
					1,
					'change-password',
					'it'
				);
			}
		}

		if(!empty($user['notification_settings']['telegram']) && !empty($user['notification_settings']['telegram']['enabled']) && !empty($user['notification_settings']['telegram']['actions']) ){
			if(in_array('change-password',$user['notification_settings']['telegram']['actions']) && !empty($user['notification_settings']['telegram']['send-to'])){
				$send_to = $user['notification_settings']['telegram']['send-to'];
				$message = "💬 <b>Smooney</b>\n";
				$message .= "La password è stata modificata. Se non sei stato tu, ti preghiamo di accedere e cambiarla immediatamente.\n";
				$message = str_replace(['<b>', '</b>'], '', $message);
				$message = str_replace(['<br>', '</br>'], "\n", $message);
				sendTelegramMessage($message, $send_to['chat-id']);
			}
		}
		return parent::success('Password salvata');
	}

	public function login(?string $code, ?string $otp, bool $remember_me=false){
		if(empty($code)) return $this->error('Identificativo dell\'utente assente');
		if(empty($otp)) return $this->error('OTP mancante');

		$user = $this->get([ 'code'=>$code ])['data'];
		if(empty($user)) return $this->error('Utente non trovato');
		$notificationSystemTelegram = $notificationSystemEmail= null;
		if(!empty($user['notification_settings']['telegram']) && !empty($user['notification_settings']['telegram']['enabled']) && !empty($user['notification_settings']['telegram']['actions']) ){
			if(in_array('new_ip',$user['notification_settings']['telegram']['actions']) && !empty($user['notification_settings']['telegram']['send-to'])){
				$notificationSystemTelegram = $user['notification_settings']['telegram']['send-to']['chat-id'];
			}
		}
		if(!empty($user['notification_settings']['email']) && !empty($user['notification_settings']['email']['actions']) ){
			if(in_array('new_ip',$user['notification_settings']['email']['actions'])){
				$notificationSystemEmail = [
					'email'			=> $user['email'],
					'firstname'	=> $user['firstname'],
					'lastname'	=> !empty($user['lastname'])?$user['lastname']:null,
				];
			}
		}

		$verifyOTP = $this->verifyOTP($code,$otp)['data'];
		if(empty($verifyOTP)) return parent::error('Codice OTP errato');
		
		new WrapperClass(['mngActivityLog']);
		$mngActivityLog = new mngActivityLog($this->conn);
		$logId = $mngActivityLog->generateLog($user['id'],$notificationSystemTelegram,$notificationSystemEmail);
		if(empty($logId['data']['id'])) return $logId;

		$log = $mngActivityLog->get(['id'=>$logId['data']['id']])['data'];
		if(empty($log)) return parent::error('Errore durante la verifica dell\'IP');
		$Authorization = base64_encode($log['api_key'].':'.$log['token']);

		new WrapperClass(['mngCookie']);
		$mngCookie = new mngCookie();
		$jwt = $mngCookie->generateToken([
			'data'=>[
				'User'=>$user['id'],
				'UserId'=>$user['code'],
				'Authorization'=>$Authorization,
				'Language'=>$user['language'],
				'Gender'=>$user['gender'],
				'Ip'=>$_SERVER['REMOTE_ADDR'],
			],
			'rememberMe'=>!empty($remember_me)
		]);
		if(empty($jwt['success'])) return parent::error('Errore durante la generazione dei Cookies');
		$jwt = $jwt['data'];
		setcookie("sm_user",$jwt['jwt'],$jwt['exp'],'/',false,false);
		return parent::success([
			'JWT'=>$jwt['jwt']
		]);
	}

	public function resetPassword(?string $email, ?string $otp){
		if(empty($email)) return $this->error('Email assente');
		if(empty($otp)) return $this->error('OTP mancante');

		$user = $this->get([ 'email'=>$email ])['data'];
		if(empty($user)) return $this->error('Utente non trovato');

		$verifyOTP = $this->verifyOTP($user['code'],$otp)['data'];
		if(empty($verifyOTP)) return parent::error('Codice OTP errato');

		$newPassword = generateStrongPassword();
		$new_password = password_hash($newPassword, PASSWORD_DEFAULT);
		$update_password = $this->edit([ 'code'=>$user['code'],'password'=>$new_password ]);
		if(!$update_password['success']) return parent::error('Errore durante il salvataggio della nuova password');

		if(!empty($user['notification_settings']['telegram']) && !empty($user['notification_settings']['telegram']['enabled']) && !empty($user['notification_settings']['telegram']['actions']) && !empty($user['notification_settings']['telegram']['send-to']) ){
			$send_to = $user['notification_settings']['telegram']['send-to'];
			$message = "💬 <b>Smooney</b>\n";
			$message .= "La tua nuova password temporanea è <b>".$newPassword."</b>.\n";
			$message .= "Ti consigliamo di cambiarla quanto prima\n";
			$message = str_replace(['<b>', '</b>'], '', $message);
			$message = str_replace(['<br>', '</br>'], "\n", $message);
			sendTelegramMessage($message, $send_to['chat-id']);
		}
		new WrapperClass(['Communications']);
		$Communications = new Communications($this->conn);
		$result = $Communications->PutEmailInQueue($user['email'], 'support@smooney.it', 'Smooney',
			[
				'firstname'			=> $user['firstname'],
				'lastname'			=> !empty($user['lastname'])?$user['lastname']:'',
				'new_password'	=> $newPassword,
				'year'					=> date('Y'),
			],
			'1',
			'reset-password',
			'it'
		);
		
		return parent::success('Email è stata cambiata');
	}

	public function loginAPI($code,$apiKey){
		$user = $this->get([ 'code'=>$code,'api_key'=>$apiKey ])['data'];
		if(empty($user)) return $this->error('Incorrect user identifiers');

		$notificationSystemTelegram = null;
		if(!empty($user['notification_settings']['telegram']) && !empty($user['notification_settings']['telegram']['enabled']) && !empty($user['notification_settings']['telegram']['actions']) ){
			if(in_array('new_ip',$user['notification_settings']['telegram']['actions']) && !empty($user['notification_settings']['telegram']['send-to'])){
				$notificationSystemTelegram = $user['notification_settings']['telegram']['send-to']['chat-id'];
			}
		}
		if(!empty($user['notification_settings']['email']) && !empty($user['notification_settings']['email']['actions']) ){
			if(in_array('new_ip',$user['notification_settings']['email']['actions'])){
				$notificationSystemEmail = [
					'email'			=> $user['email'],
					'firstname'	=> $user['firstname'],
					'lastname'	=> !empty($user['lastname'])?$user['lastname']:null,
				];
			}
		}

		new WrapperClass(['mngActivityLog']);
		$mngActivityLog = new mngActivityLog($this->conn);
		$logId = $mngActivityLog->generateLog($user['id'],$notificationSystemTelegram,$notificationSystemEmail,true);
		if(empty($logId['data']['id'])) return $logId;

		$log = $mngActivityLog->get(['id'=>$logId['data']['id']])['data'];
		if(empty($log)) return parent::error('Errore durante la verifica dell\'IP');
		$Authorization = base64_encode($log['api_key'].':'.$log['token']);

		new WrapperClass(['mngCookie']);
		$mngCookie = new mngCookie();
		$jwt = $mngCookie->generateToken([
			'data'=>[
				'UserId'=>$user['code'],
				'Authorization'=>$Authorization,
				'Language'=>$user['language'],
				'Ip'=>$_SERVER['REMOTE_ADDR'],
			],
			'rememberMe'=>!empty(true)
		]);
		if(empty($jwt['success'])) return parent::error('Errore durante la generazione dei Cookies');
		$jwt = $jwt['data'];

		return parent::success([
			'JWT'=>$jwt['jwt'],
			'Exp'=>$jwt['exp']
		]);
	}

	public function verifyJWTAPI($jwt = ""){
		new WrapperClass(['mngCookie']);
		$mngCookie = new mngCookie();
		$info = $mngCookie->verifyToken($jwt);
		if(empty($info['data'])) return parent::error("JWT invalid");
		$info = $info['data'];
		new WrapperClass(['ioAsymChiper']);
		$ioAsymChiper= new ioAsymChiper();
		$user_log = $ioAsymChiper->privDecrypt($info['data']);
		new WrapperClass(['mngActivityLog']);
		$mngActivityLog = new mngActivityLog($this->conn);
		$checkAuthorization = $mngActivityLog->checkCredetials($user_log['Authorization']);
		if(!$checkAuthorization) return parent::error("JWT invalid");
		return $this->success($user_log);
	}


	public function generateUser($firstname=null,$lastname=null,$email=null,$gender='male'){
		if(empty($firstname) ) return $this->error("Nome dell'utente assente");
		if(empty($email) ) return $this->error("Email dell'utente assente");

		$password = $this->generaPasswordCasuale();
		$userKey = EncryptionManager::generateUserKey();
		$encryptedUserKey = EncryptionManager::encryptUserKey($userKey);

		$user = $this->new([
			'firstname'							=> $firstname,
			'email'									=> $email,
			'password'							=> $password,
			'lastname'							=> $lastname,
			'crypt_key'							=> $encryptedUserKey,
			'gender'								=> $gender == 'female' ? 1 : 0,
			'notification_settings'	=> [
				'email'=>[
					'actions'=>[ "otp", "change-password", "new_ip"]
				],
				'telegram'=>[
					'actions'=>[ "otp", "change-password", "new_ip"],
					"enabled"=> true,
				],
			]
		]);
		if(!$user['success']) return $user;
		$user = $user['data'];

		new WrapperClass(['mngTag','mngWallet','Communications']);
		$mngTag = new mngTag($this->conn);
		$mngWallet = new mngWallet($this->conn);
		$Communications = new Communications($this->conn);
		$defaultTags = [
			[ 'name'=>'Food',						'type'=>1,	'color'=>'#16A085',	'text_color'=>'#181818' ],
			[ 'name'=>'Sport',					'type'=>3,	'color'=>'#C0392B',	'text_color'=>'#ffffff' ],
			[ 'name'=>'Abbonamenti',		'type'=>3,	'color'=>'#F39C12',	'text_color'=>'#181818' ],
			[ 'name'=>'Macchina',				'type'=>1,	'color'=>'#2980B9',	'text_color'=>'#ffffff' ],
			[ 'name'=>'Benzina',				'type'=>1,	'color'=>'#3498DB',	'text_color'=>'#181818' ],
			[ 'name'=>'Telefono',				'type'=>3,	'color'=>'#9B59B6',	'text_color'=>'#ffffff' ],
			[ 'name'=>'Abbigliamento',	'type'=>3,	'color'=>'#BDC3C7',	'text_color'=>'#181818' ],
			[ 'name'=>'Casa',						'type'=>1,	'color'=>'#D35400',	'text_color'=>'#ffffff' ],
			[ 'name'=>'Investimenti',		'type'=>2,	'color'=>'#2C3E50',	'text_color'=>'#ffffff' ],
			[ 'name'=>'Risparmio',			'type'=>2,	'color'=>'#2C3E50',	'text_color'=>'#ffffff' ],
			[ 'name'=>'Bollette',				'type'=>1,	'color'=>'#D35400',	'text_color'=>'#ffffff' ],
			[ 'name'=>'Altro',					'type'=>3,	'color'=>'#BDC3C7',	'text_color'=>'#181818' ],
		];
		$defaultWallets = [
			[ 'name'=>'Contanti',	'icon'=>'iconly-Bold-Wallet' ],
			[ 'name'=>'Banca',		'icon'=>'ri-bank-fill' ],
		];
		foreach($defaultTags as $tag){
			$mngTag->new(array_merge($tag,[
				'user_code'	=> $user['code']
			]));
		}
		foreach($defaultWallets as $wallet){
			$mngWallet->updateInfoWallet(array_merge($wallet,[
				'user_code'	=> $user['code']
			]));
		}

		$Communications->PutEmailInQueue($email, 'support@smooney.it', 'Smooney',
			[
				'firstname'	=> $firstname,
				'email'			=> $email,
				'password'	=> $password,
				'lastname'	=> !empty($lastname)?$lastname:'',
				'crypt_key'	=> $userKey,
				'year'			=> date('Y'),
			],
			'1',
			'new-user',
			'it'
		);

		return parent::success($user);
	}


	public function deleteUser(?string $code){
		if(empty($code)) return $this->error('Identificativo dell\'utente assente');
		$user = $this->get([ 'code'=>$code ])['data'];
		if(empty($user)) return $this->error('Utente non trovato');

		$this->conn->update("DELETE FROM  `".$this->table."` WHERE id = ".$this->conn->escape($user['id'])." ");
		$this->conn->update("DELETE FROM  `tag` WHERE user_id = ".$this->conn->escape($user['id'])." ");
		$this->conn->update("DELETE FROM  `intent_transaction` WHERE user_id = ".$this->conn->escape($user['id'])." ");
		$this->conn->update("DELETE FROM  `payment` WHERE user_id = ".$this->conn->escape($user['id'])." ");
		$this->conn->update("DELETE FROM  `history_wallet` WHERE user_id = ".$this->conn->escape($user['id'])." ");
		$this->conn->update("DELETE FROM  `forecast_wallet` WHERE user_id = ".$this->conn->escape($user['id'])." ");
		$this->conn->update("DELETE FROM  `wallet` WHERE user_id = ".$this->conn->escape($user['id'])." ");

		new WrapperClass(['Communications']);
		$Communications = new Communications($this->conn);
		$Communications->PutEmailInQueue($user['email'], 'support@smooney.it', 'Smooney',
			[
				'firstname'	=> $user['firstname'],
				'lastname'	=> !empty($user['lastname'])?$user['lastname']:'',
				'year'			=> date('Y'),
			],
			'1',
			'delete-user',
			'it'
		);
		return $this->success([
			'id'=>$user['id'],
			'code'=>$user['code'],
			'firstname'=>$user['firstname'],
			'lastname'=>$user['lastname'],
			'email'=>$user['email'],
		]);
	}

	private static function generaPasswordCasuale($lunghezzaPassword = 10) {
    $caratteriPermessi = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $password = '';
    $lunghezzaCaratteri = strlen($caratteriPermessi);

    for ($i = 0; $i < $lunghezzaPassword; $i++) {
        $carattereCasuale = $caratteriPermessi[rand(0, $lunghezzaCaratteri - 1)];
        $password .= $carattereCasuale;
    }
    return $password;
	}



	public function sendRecapWeekend(){
		if(date('w') != 1) return parent::error('La settimana non è ancora finita');

		$users = $this->gets([ 'data'=>false ])['data'];
		if(empty($users)) return parent::error('Nessun utente da elaborare');
		$users = array_column($users,null,'id');

		new WrapperClass(['mngWallet','mngTag']);
		$mngWallet = new mngWallet($this->conn);
		$wallets = $mngWallet->gets([ 'data'=>false ])['data'];
		if(empty($wallets)) return parent::error('Nessun wallet da elaborare');

		$mngTag = new mngTag($this->conn);
		$tags = $mngTag->gets([ 'data'=>false ])['data'];

		
		foreach($wallets as $wallet){
			if(empty($users[ $wallet['user_id'] ])) continue;
			if(empty($users[ $wallet['user_id'] ]['recap_weekend'])) $users[ $wallet['user_id'] ]['recap_weekend'] = [];
			$recap_weekend = [
				'name'=>$wallet['name'],
				'amount_income'		=> $wallet['amount_income'],
				'amount_expenses'	=> $wallet['amount_expenses'],
				'amount_balance'	=> $wallet['amount_balance'],
				'comparison'=>[]
			]; 
			if(!empty($wallet['extra_data']['last_week']['amount_income']))
				$recap_weekend['comparison']['amount_income'] = (($wallet['amount_income'] - $wallet['extra_data']['last_week']['amount_income']) / $wallet['extra_data']['last_week']['amount_income'])*100;
			if(!empty($wallet['extra_data']['last_week']['amount_expenses']))
				$recap_weekend['comparison']['amount_expenses'] = ((abs($wallet['amount_expenses']) - abs($wallet['extra_data']['last_week']['amount_expenses'])) / abs($wallet['extra_data']['last_week']['amount_expenses']))*100;
			if(!empty($wallet['extra_data']['last_week']['amount_balance']))
				$recap_weekend['comparison']['amount_balance'] = (($wallet['amount_balance'] - $wallet['extra_data']['last_week']['amount_balance']) / $wallet['extra_data']['last_week']['amount_balance'])*100;

			$users[ $wallet['user_id'] ]['recap_weekend'][] = $recap_weekend;

			if(empty($wallet['extra_data'])) $wallet['extra_data'] = [];
			$wallet['extra_data']['last_week'] = [
				'amount_income'		=> $wallet['amount_income'],
				'amount_expenses'	=> $wallet['amount_expenses'],
				'amount_balance'	=> $wallet['amount_balance']
			];
			unset($wallet['name']);
			$mngWallet->updateInfoWallet($wallet);

		}

		foreach($users as $user){
			if(empty($user['recap_weekend'])) continue;
			$userTags = array_filter($tags,function($row) use($user) { return $row['user_id'] == $user['id']; });

			if(!empty($user['notification_settings']['telegram']['actions']) && !empty($user['notification_settings']['telegram']['enabled']) && in_array('recap_weekend',$user['notification_settings']['telegram']['actions']) && !empty($user['notification_settings']['telegram']['send-to'])){
				
				$send_to = $user['notification_settings']['telegram']['send-to'];
				$message = "💬 <b>Smooney</b>\n";
				$message .= "Ecco un riepilogo dei tuoi progressi finanziari dell'ultima settimana.\n\n";
				foreach($user['recap_weekend'] as $recap_weekend)
					$message .= $this->creaMessaggioRecap($recap_weekend)."\n";
				if(!empty($userTags)){
					usort($tags, function($a, $b) {
							return $b['amount'] <=> $a['amount'];
					});
					$top_tags = array_slice($userTags, 0, 3);
				}
				if (!empty($top_tags)) {
					$message .= "\n🔝 Categorie principali di spesa:\n";
					foreach ($top_tags as $tag) {
						if(!empty($tag['amount']))
							$message .= "- " . $tag['name'] . ": €" . number_format($tag['amount'], 2) . "\n";
					}
				}
				$message = str_replace(['<b>', '</b>'], '', $message);
				$message = str_replace(['<br>', '</br>'], "\n", $message);
				sendTelegramMessage($message, $send_to['chat-id']);

			}
			if(!empty($user['notification_settings']['email']['actions']) && in_array('recap_weekend',$user['notification_settings']['email']['actions'])){
				
				

			}
		}
		return parent::success("Tutti i dati sono stati elaborati");
	}

	public function sendRecapMonth(){
		if(date('j') != 1) return parent::error('Il mese non è ancora finito');

		$users = $this->gets([ 'data'=>false ])['data'];
		if(empty($users)) return parent::error('Nessun utente da elaborare');
		$users = array_filter($users,function($user){
			$telegramEnabled = ( !empty($user['notification_settings']['telegram']['actions']) && !empty($user['notification_settings']['telegram']['enabled']) && in_array('recap_month',$user['notification_settings']['telegram']['actions']) );
			$emailEnabled = ( !empty($user['notification_settings']['email']['actions']) && !empty($user['notification_settings']['email']['enabled']) && in_array('recap_month',$user['notification_settings']['email']['actions']) );
			return $telegramEnabled || $emailEnabled;
		});
		if(empty($users)) return parent::error('Nessun utente da elaborare');
		$users = array_column($users,null,'id');

		new WrapperClass(['mngWallet','mngTag']);
		$mngWallet = new mngWallet($this->conn);
		$wallets = $mngWallet->gets([ 'data'=>false ])['data'];
		if(empty($wallets)) return parent::error('Nessun wallet da elaborare');

		$mngWalletHistory = new mngWalletHistory($this->conn);
		$date = date('Y-m-d', strtotime('first day of last month'));
		$histories = $mngWalletHistory->gets([
			'year'	=> date('Y',strtotime($date)),
			'month'	=> date('m',strtotime($date))
		])['data'];

		$mngTag = new mngTag($this->conn);
		$tags = $mngTag->gets([ 'data'=>false ])['data'];

		
		foreach($wallets as $wallet){
			if(empty($users[ $wallet['user_id'] ])) continue;
			if(empty($users[ $wallet['user_id'] ]['recap_month'])) $users[ $wallet['user_id'] ]['recap_month'] = [];

			$userHistory = array_filter($histories,function($row) use($wallet) { return $row['user_id'] == $wallet['user_id'] && $row['wallet_id'] == $wallet['id']; });
			$userHistory = !empty($userHistory)?reset($userHistory):null;

			$recap_month = [
				'name'=>$wallet['name'],
				'amount_income'		=> $wallet['amount_income'],
				'amount_expenses'	=> $wallet['amount_expenses'],
				'amount_balance'	=> $wallet['amount_balance'],
				'comparison'=>[]
			]; 
			if(!empty($userHistory['amount_income']))
				$recap_month['comparison']['amount_income'] = (($wallet['amount_income'] - $userHistory['amount_income']) / $userHistory['amount_income'])*100;
			if(!empty($userHistory['amount_expenses']))
				$recap_month['comparison']['amount_expenses'] = ((abs($wallet['amount_expenses']) - abs($userHistory['amount_expenses'])) / abs($userHistory['amount_expenses']))*100;
			if(!empty($userHistory['amount_balance']))
				$recap_month['comparison']['amount_balance'] = (($wallet['amount_balance'] - $userHistory['amount_balance']) / $userHistory['amount_balance'])*100;

			$users[ $wallet['user_id'] ]['recap_month'][] = $recap_month;
		}

		foreach($users as $user){
			if(empty($user['recap_month'])) continue;
			$userTags = array_filter($tags,function($row) use($user) { return $row['user_id'] == $user['id']; });

			if(!empty($user['notification_settings']['telegram']['actions']) && !empty($user['notification_settings']['telegram']['enabled']) && in_array('recap_month',$user['notification_settings']['telegram']['actions']) && !empty($user['notification_settings']['telegram']['send-to'])){
				
				$send_to = $user['notification_settings']['telegram']['send-to'];
				$message = "💬 <b>Smooney</b>\n";
				$message .= "Ecco un riepilogo dei tuoi progressi finanziari dell'ultimo mese.\n\n";
				foreach($user['recap_month'] as $recap_month)
					$message .= $this->creaMessaggioRecap($recap_month)."\n";

				if(!empty($userTags)){
					usort($tags, function($a, $b) {
							return $b['amount'] <=> $a['amount'];
					});
					$top_tags = array_slice($userTags, 0, 3);
				}
				if (!empty($top_tags)) {
					$message .= "\n🔝 Categorie principali di spesa:\n";
					foreach ($top_tags as $tag) {
						if(!empty($tag['amount']))
							$message .= "- " . $tag['name'] . ": €" . number_format($tag['amount'], 2) . "\n";
					}
				}
				$message .= "\n";
				$message = str_replace(['<b>', '</b>'], '', $message);
				$message = str_replace(['<br>', '</br>'], "\n", $message);
				sendTelegramMessage($message, $send_to['chat-id']);

			}
			if(!empty($user['notification_settings']['email']['actions']) && !empty($user['notification_settings']['email']['enabled']) && in_array('recap_month',$user['notification_settings']['email']['actions'])){
				
				

			}
		}
		return parent::success("Tutti i dati sono stati elaborati");
	}

	static private function creaMessaggioRecap($recap) {
    $name = $recap['name'];
    $income = $recap['amount_income'];
    $expenses = $recap['amount_expenses'];
    $balance = $recap['amount_balance'];
    $comparison = !empty($recap['comparison'])?$recap['comparison']:[];

    $message = "Wallet | <b>$name</b>\n\n";
    $message .= "📈 Entrate: <b>€ ".number_format($income, 2)."</b>";
    if (isset($comparison['amount_income']) && round($comparison['amount_income'],2)!=0.00) {
        $message .= " (" . ($comparison['amount_income'] > 0 ? '+' : '') . number_format($comparison['amount_income'], 2) . "% rispetto alla settimana scorsa)";
    }
    $message .= "\n";

    $message .= "📉 Spese: <b>€ " .number_format($expenses, 2)."</b>";
    if (isset($comparison['amount_expenses']) && round($comparison['amount_expenses'],2)!=0.00) {
        $message .= " (" . ($comparison['amount_expenses'] > 0 ? '+' : '') . number_format($comparison['amount_expenses'], 2) . "% rispetto alla settimana scorsa)";
    }
    $message .= "\n";

    $message .= "💰 Saldo: <b>€ " .number_format($balance, 2)."</b>";
    if (isset($comparison['amount_balance']) && round($comparison['amount_balance'],2)!=0.00) {
        $message .= " (" . ($comparison['amount_balance'] > 0 ? '+' : '') . number_format($comparison['amount_balance'], 2) . "% rispetto alla settimana scorsa)";
    }
    $message .= "\n";

    return $message;
	}



	public function verifyCryptKey($userCode,$userId,$cryptKey){
		$user = $this->get([ 'code'=> $userCode, 'id'=>$userId ])['data'];
		if(empty($user)) return parent::error('Nessun utente trovato');
		$_crypt_key = $user['crypt_key'];
    $_crypt_key = EncryptionManager::decryptUserKey($_crypt_key);
		if( $_crypt_key == $cryptKey ) return parent::success('Chiave di crittografia valida');
		return parent::error('Chiave di crittografia non valida');
	}

	public function updateApikey($userCode,$userId){
		return $this->edit([
			'code'=> $userCode, 
			'id'=>$userId,
			'api_key'=>$this->conn->getUuid()
		]);
	}
}