<?php
class mngActivityLog extends CRUDBase{
	private $table = 'activity_log';
	public function __construct(ioConn $ioConn) {
		parent::__construct($ioConn);
		$this->setTableName($this->table);
	}
	private function getUserId(string $code){
		if(empty($code) ) return $this->error("Identificativo dell'utente assente");

		$user = new ioQuery('select');
		$user->from('user')
		->column('`user`.id')->column('`user`.code')
		->where('AND',' `user`.deleted = 0 ')
		->where('AND'," `user`.code = '".$this->conn->escape($code)."' ")
		->group_by(' `user`.code ');
		$user = $this->conn->query($user->get());
		if(empty($user)) return $this->error("Identificativo dell'utente errato");
		$user = reset($user);
		$user_id = $user['id'];
		return parent::success($user_id);
	}
	private function new(array $params = []) {
		if(empty($params['user_id']) ) return $this->error("Identificativo dell'utente assente");
		if(empty($params['api_key']) ) $params['api_key'] = $this->conn->getUuid();
		if(empty($params['token']) ) $params['token'] = $this->conn->getUuid();
		$params['last_update_dt'] = date('Y-m-d H:i:s');
		$result = parent::create($params);
		if(!$result['success'] || empty($result['data'])) return $result;
		return parent::success(array_merge($result['data']));
	}
	private function edit(array $params = []) {
		if(empty($params['id']) && empty($params['user_id']) ) return $this->error("Identificativo assente");

		$conditions = [];
		if(!empty($params['id'])) $conditions['id'] = $params['id'];
		if(!empty($params['user_id'])) $conditions['user_id'] = $params['user_id'];
		if(!empty($params['ip'])) $conditions['ip'] = $params['ip'];
		recursive_unsets($params,['id','user_id','ip']);
		return parent::update($params,$conditions);
	}
	public function gets(array $params = []) {
		if(empty($params)) return $this->error("Nessun parametro fornito");

		$query = new ioQuery('select');
		$query->from($this->table);

		if(empty($params['data'])) $query->column('`'.$this->table.'`.*');
		else $query->column('`'.$this->table.'`.id')->column('`'.$this->table.'`.code');

		$query->where('AND',' `'.$this->table.'`.deleted = 0 ')
		->group_by(' `'.$this->table.'`.id ');

		if(!empty($params['data']) && is_array($params['data'])) foreach($params['data'] as $key => $value){
			$query->column( (!empty($value['func'])?$value['func']: ( ( !empty($value['table'])?$value['table']:'`'.$this->table.'`' ).'.'.$value['column'])) ,!empty($value['alias'])?$value['alias']:null);
		}

		if(!empty($params['ip']))
			$query->where('AND'," `".$this->table."`.ip = '".$this->conn->escape($params['ip'])."' ");
		if(!empty($params['api_key']))
			$query->where('AND'," `".$this->table."`.api_key = '".$this->conn->escape($params['api_key'])."' ");
		if(!empty($params['token']))
			$query->where('AND'," `".$this->table."`.token = '".$this->conn->escape($params['token'])."' ");
		if(!empty($params['id']))
			$query->where('AND',' `'.$this->table.'`.id = '.$this->conn->escape($params['id']).' ');
		if(!empty($params['user_id']))
			$query->where('AND',' `'.$this->table.'`.user_id = '.$this->conn->escape($params['user_id']).' ');
		if(!empty($params['active']))
			$query->where('AND'," `".$this->table."`.status = 0 ");
		if(!empty($params['off']))
			$query->where('AND'," `".$this->table."`.status = 1 ");

		if(!empty($params['order_by']) && is_array($params['order_by'])){
			foreach($params['order_by'] as $order_by) $query->order_by(reset($order_by),end($order_by));
		}

		$result = $this->conn->query($query->get());
		if($result){ 
			if(empty($params['data']) || (is_array($params['data']) && in_array('creation_dt',array_column($params['data'],'column'))) )
				map_array_field($result,'creation_dt',function($row){ return !empty($row['creation_dt'])?date('Y-m-d H:i:s',strtotime($row['creation_dt'])):null; });
			if(empty($params['data']) || (is_array($params['data']) && in_array('deactivate_dt',array_column($params['data'],'column'))) )
				map_array_field($result,'deactivate_dt',function($row){ return !empty($row['deactivate_dt'])?date('Y-m-d H:i:s',strtotime($row['deactivate_dt'])):null; });
			if(empty($params['data']) || (is_array($params['data']) && in_array('last_update_dt',array_column($params['data'],'column'))) )
				map_array_field($result,'last_update_dt',function($row){ return !empty($row['last_update_dt'])?date('Y-m-d H:i:s',strtotime($row['last_update_dt'])):null; });
			if(empty($params['data']) || (is_array($params['data']) && in_array('cancellation_dt',array_column($params['data'],'column'))) )
				map_array_field($result,'cancellation_dt',function($row){ return !empty($row['cancellation_dt'])?date('Y-m-d H:i:s',strtotime($row['cancellation_dt'])):null; });
			
			return parent::success($result);
		}
		return parent::error('Nessun metodo trovato');
	}
	public function get(array $params = []):array
	{
		if(empty($params)) return $this->error("Nessun parametro fornito");
		if(empty($params['id']) && (empty($params['api_key']) || empty($params['token']) || empty($params['ip']))) return $this->error("Identificativo assente");
		$result  = $this->gets($params);
		if(!$result['success'] || empty($result['data'])) return !empty($result['error'])?$this->error($result['error']):$this->error("Utente non trovato");
		return $this->success(reset($result['data']));
	}
	public function getBrowserAndOS($userAgent) {
		$browser = "Unknown Browser";
		$os = "Unknown OS";

		// Browser checks
		if (strpos($userAgent, 'Chrome') !== false) {
				$browser = 'Chrome';
		} elseif (strpos($userAgent, 'Firefox') !== false) {
				$browser = 'Firefox';
		} elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
				// Aggiunto il controllo su Safari escludendo Chrome su Android che include la stringa "Safari" 
				$browser = 'Safari';
		}

		// OS checks
		if (strpos($userAgent, 'Windows') !== false) {
			$os = 'Windows';
		} elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
			// Aggiunto il controllo per dispositivi iOS come iPhone e iPad
			$os = 'iOS';
		}  elseif (strpos($userAgent, 'Macintosh') !== false || strpos($userAgent, 'Mac OS') !== false) {
			$os = 'Mac OS';
		}  elseif (strpos($userAgent, 'Android') !== false) {
			// Aggiunto il controllo per dispositivi Android
			$os = 'Android';
		}

		return "$browser su $os";
	}
	public function generateLog($user_id,$notificationSystemTelegram=null,$notificationSystemEmail=null,$apiLogin=false){
		if(empty($user_id) ) return $this->error("Identificativo dell'utente assente");
		$params['user_id'] = $user_id;
		$params['user_agent'] = $_SERVER['HTTP_USER_AGENT']; 
		$params['ip'] = $_SERVER['REMOTE_ADDR']; 
		$params['time'] = date("Y-m-d H:i:s");
		// $params['user_agent'] = $this->getBrowserAndOS($userAgent);
		$last_log = $this->gets([ 'ip'=>$params['ip'],'user_id'=>$user_id ])['data'];
		if(empty($last_log)) {
			if(!empty($notificationSystemTelegram)){
				$message = "💬 <b>Smooney</b>\n";
				$message .= "Hai appena effettuato l'accesso al tuo account".($apiLogin?" tramite API":"").".\n";
				$message .= "<b>IP:</b> ".$params['ip']."\n";
				$message .= "<b>User-agent:</b> ".$params['user_agent']."\n";
				$message .= "\n<b>Data:</b> ".(date('d/m/Y H:i:s',strtotime($params['time']))).".\n\n";
				$message .= "Se non sei stato tu, ti consigliamo di accedere immediatamente e bloccare l'IP o di segnalarci l'attività sospetta\n";
				$message = str_replace(['<b>', '</b>'], '', $message);
				$message = str_replace(['<br>', '</br>'], "\n", $message);
				sendTelegramMessage($message, $notificationSystemTelegram);
			}
			if(!empty($notificationSystemEmail)){
				new WrapperClass(['Communications']);
				$Communications = new Communications($this->conn);
				$Communications->PutEmailInQueue($notificationSystemEmail['email'], 'support@smooney.it', 'Smooney',
					[
						'firstname'			=> $notificationSystemEmail['firstname'],
						'lastname'			=> !empty($notificationSystemEmail['lastname'])?$notificationSystemEmail['lastname']:'',
						'device'				=> $this->getBrowserAndOS($params['user_agent'])." ( ".$params['user_agent']." )",
						'device_short'	=> $this->getBrowserAndOS($params['user_agent']),
						'ip'						=> $params['ip'],
						'hour'					=> date('d/m/Y H:i:s',strtotime($params['time'])),
						'year'					=> date('Y'),
					],
					'1',
					'new-login',
					'it'
				);
			}
			return $this->new($params);
		}
		if(in_array(1,array_column($last_log,'status'))) return $this->error('IP bloccato');
		$params['id'] = reset($last_log)['id'];
		$params['last_update_dt'] = date('Y-m-d H:i:s');
		$update = $this->edit($params);
		if(!$update['success']) return $update;
		return parent::success(['id'=>$params['id']]);
	}
	public function deactivate($id,$ip, $user_code){
		if(empty($id) || empty($ip) || empty($user_code) ) return $this->error("Identificativo dell'utente assente");

		$user_id = $this->getUserId($user_code);
		if(!$user_id['success']) return $user_id;
		$user_id = $user_id['data'];

		$deactivate = $this->edit([ 'id'=>$id,'ip'=>$ip,'user_id'=>$user_id,'status'=>1,'deactivate_dt'=>date('Y-m-d H:i:s') ]);
		if(!$deactivate['success']) return parent::error(' Errore durante la disattivazione');
		return parent::success(true);
	}
	public function active($id,$ip, $user_code){
		if(empty($id) || empty($ip) || empty($user_code) ) return $this->error("Identificativo dell'utente assente");

		$user_id = $this->getUserId($user_code);
		if(!$user_id['success']) return $user_id;
		$user_id = $user_id['data'];

		$active = $this->edit([ 'id'=>$id,'ip'=>$ip,'user_id'=>$user_id,'unset|status'=>true,'unset|deactivate_dt'=>true ]);
		if(!$active['success']) return parent::error(' Errore durante la disattivazione');
		return parent::success(true);
	}

	public function checkCredetials(string $auth): bool {
		$ip = $_SERVER['REMOTE_ADDR']; 
		list( $api_key, $token ) = explode(':',base64_decode($auth));
		$log = $this->get([ 'ip'=>$ip, 'api_key'=>$api_key, 'token'=>$token, 'active'=>true ])['data'];
		return !empty($log);
	}
}