<?php
class Communications extends CRUDBase{
	private $table = 'templates_of_communication';
	public function __construct(ioConn $ioConn) {
		parent::__construct($ioConn);
		$this->setTableName($this->table);
	}
	private function new(array $params = []) {
		if(empty($params['user_id']) ) return $this->error("Identificativo dell'utente assente");
		if(empty($params['alias']) ) return $this->error("Alias assente");
		if(empty($params['lang']) ) return $this->error("Lingua assente");
		if(!isset($params['type']) ) return $this->error("Tipologia assente");

		if(empty($params['code']) ) $params['code'] = $this->conn->getUuid();

		$result = parent::create($params);
		if(!$result['success'] || empty($result['data'])) return $result;
		return parent::success(array_merge($result['data'],['code'=>$params['code']]));
	}
	public function edit(array $params = []) {
		if(empty($params['id']) && empty($params['code']) && empty($params['alias_id'])) return $this->error("Identificativo assente");

		$conditions = [];
		if(!empty($params['id'])) $conditions['id'] = $params['id'];
		if(!empty($params['code'])) $conditions['code'] = $params['code'];
		if(!empty($params['alias_id'])) $conditions['alias_id'] = $params['alias_id'];
		recursive_unsets($params,['id','code','alias_id','user_id']);
		return parent::update($params,$conditions);
	}
	public function gets(array $params = []) {
		if(empty($params)) return $this->error("Nessun parametro fornito");

		$query = new ioQuery('select');
		$query->from($this->table)
		->column('`'.$this->table.'`.*')
		->column('`_types`.alias')
		->column('`_types`.recipient_type')
		->column('`_types`.name')
		->left_join('template_of_communication_types','_types',' _types.id = `'.$this->table.'`.alias_id ')
		->where('AND',' `_types`.deleted = 0 ')
		->where('AND',' `'.$this->table.'`.deleted = 0 ');

		if(!empty($params['code']))
			$query->where('AND'," `".$this->table."`.code = '".$this->conn->escape($params['code'])."' ");
		if(!empty($params['id']))
			$query->where('AND',' `'.$this->table.'`.id = '.$this->conn->escape($params['id']).' ');
		if(!empty($params['alias_id']))
			$query->where('AND'," `".$this->table."`.alias_id = '".$this->conn->escape($params['alias_id'])."' ");
		if(!empty($params['alias']))
			$query->where('AND'," `_types`.alias = '".$this->conn->escape($params['alias'])."' ");
		if(!empty($params['lang']))
			$query->where('AND'," `".$this->table."`.lang = '".$this->conn->escape($params['lang'])."' ");
		if(isset($params['type']))
			$query->where('AND',' `'.$this->table.'`.type = '.$this->conn->escape($params['type']).' ');
		if(isset($params['recipient_type']))
			$query->where('AND'," `_types`.recipient_type = '".$this->conn->escape($params['recipient_type'])."' ");
		if(!empty($params['user_id'])){
			$default = clone $query;
			$query->where('AND',' `'.$this->table.'`.user_id = '.$this->conn->escape($params['user_id']).' ');
			$default->where('AND',' `'.$this->table.'`.user_id IS NULL ');
		}
		$result = $this->conn->query((!empty($default)?$default->get().PHP_EOL.'UNION'.PHP_EOL:'').$query->get().PHP_EOL.' GROUP BY `'.$this->table.'`.id ');
		if($result){ 
			$result = array_map(function($row) {
				return [
					'id'=>$row['id'],
					'alias_id'=>$row['alias_id'],
					'alias'=>$row['alias'],
					'user_id'=>$row['user_id'],
					'type'=>$row['type'],
					'name'=>$row['name'],
					'lang'=>$row['lang'],
					'object'=>$row['object'],
					'message'=>$row['message'],
					'created'=>date('Y-m-d H:i:s',strtotime($row['creation_dt'])),
					'deleted'=>$row['deleted'],
				];
			},$result);
			if(empty($params['get-default'])){
				$result_filtered = [];
				foreach ($result as $item) {
					$key = $item['lang'] . '|' . $item['alias_id'];
					if (!isset($bestElements[$key]) || (!is_null($item['user_id']) && is_null($bestElements[$key]['user_id']))) 
					$result_filtered[$key] = $item;
				}
				$result = array_values($result_filtered);
			}
			return parent::success($result);
		}
		return parent::error('Nessun metodo trovato');
	}

	public function generateTemplate(array $params = []){
		if(empty($params['user_id']) ) return $this->error("Identificativo dell'utente assente");
		if(empty($params['lang']) ) return $this->error("Lingua assente");
		if(empty($params['alias_id']) ) return $this->error("Alias assente");

		$old = $this->gets(['user_id'=>$params['user_id'],'alias_id'=>$params['alias_id'],'type'=>$params['type'],'lang'=>$params['lang'],'get-default'=>true])['data'];
		$oldOrg = array_filter($old,function($row){ return !empty($row['user_id']); });
		$oldOrg = reset($oldOrg);
		if(!empty($oldOrg)){
			recursive_unsets($oldOrg,['created','deleted']);
			$params['id']= $oldOrg['id'];
			$differences = array_diff_extended($params,$oldOrg);

			if(empty($differences)) return parent::success(['id'=>$oldOrg['id'],'code'=>$oldOrg['code']]);
			return $this->edit(array_merge(['id'=>$oldOrg['id']],$params));
		}
		unset($params['id']);
		return $this->new($params);
	}

	public function PutEmailInQueue( $recipient, $sender, $senderName=null, $params=[], $userId=null, $alias = '', $lang='it', $channel=null, $templateId=null ){
		$template = $this->gets(array_merge([ 'user_id'=>$userId,'type'=>0,'lang'=>$lang ],!empty($alias)?['alias'=>$alias]:[],!empty($templateId)?['id'=>$templateId]:[]))['data'];
		if(empty($template)) return parent::error('Nessun template trovato');
		$template = end($template); // poiché c'è sempre per primo quello con org_id NULL, prendo ove possibile quello con org_id valorizzato
		if(empty($channel)) $channel = 1;
		$result=[
			'sender'=>$sender,
			'sender_name'=>$senderName,
			'user_id'=>$userId,
			'language'=>$lang,
			'system_type'=>0,
			'system'=>$channel,
			'recipient'=>$recipient,
			'object'=>$template['object'],
			'message'=>$template['message'],
		];
		// if(!empty($template['translations'])) foreach($template['translations'] as $key => $value){
		// 	$translation = !empty($value[$lang])?$value[$lang]:$key;
		// 	$result['message']= str_replace($key,urlencode($translation), $result['message']);
		// }
		foreach($params as $key=>$var) {
			$result['message']		= 	str_replace("%7B_".$key."_%7D",urlencode($var), $result['message']);
			$result['message']		= 	str_replace("{_".$key."_}",urlencode($var), $result['message']);
			if(!empty($result['object'])){
				$result['object']		=	str_replace("{_".$key."_}",($var), $result['object']);
				$result['object']		=	str_replace("%7B_".$key."_%7D",($var), $result['object']);
			}
		}
		$result['message'] = urldecode($result['message']);
		$QueueComunication = new QueueComunication($this->conn);
		return $QueueComunication->new($result);
	}
	public function PutSMSInQueue( $recipient, $sender=null, $senderName=null, $params=[], $userId=null, $alias = '', $lang='it', $channel=null, $templateId=null  ){
		$template = $this->gets(array_merge([ 'user_id'=>$userId,'type'=>1,'lang'=>$lang ],!empty($alias)?['alias'=>$alias]:[],!empty($templateId)?['id'=>$templateId]:[]))['data'];
		if(empty($template)) return parent::error('Nessun template trovato');
		$template = end($template); // poiché c'è sempre per primo quello con org_id NULL, prendo ove possibile quello con org_id valorizzato
		if(empty($channel)) return parent::error('Nessun canale di comunicazione specificato');

		$software = new ioQuery('select');
		$software->from('software')
		->column('communication_config.id')
		->column('communication_config.config')
		->left_join('communication_config',null,'communication_config.user_id = '.$this->conn->escape($userId).' AND communication_config.deleted = 0 AND communication_config.software_id = software.id ')
		->where('AND',"communication_config.id = '".$this->conn->escape($channel)."' ")
		->where('AND',"communication_config.alias = 'send_sms' ")
		->where('AND',"software.deleted = 0 ");
		$software = $this->conn->query($software->get());
		if(empty($software)) return ['success'=>false,'error'=>'Canale di comunicazione non trovato'];
		$software = reset($software);

		$result=[
			'sender'=>$sender,
			'sender_name'=>$senderName,
			'user_id'=>$userId,
			'language'=>$lang,
			'system_type'=>1,
			'system'=>$channel,
			'recipient'=>$recipient,
			'object'=>!empty($template['object'])?getTranslationLanguages($template['object'],$lang):null,
			'message'=>$template['message'],
		];
		// if(!empty($template['translations'])) foreach($template['translations'] as $key => $value){
		// 	$translation = !empty($value[$lang])?$value[$lang]:$key;
		// 	$result['message']= str_replace($key,urlencode($translation), $result['message']);
		// }
		foreach($params as $key=>$var) {
			$result['message']		= 	str_replace("%7B_".$key."_%7D",urlencode($var), $result['message']);
			$result['message']		= 	str_replace("{_".$key."_}",urlencode($var), $result['message']);
			if(!empty($result['object'])){
				$result['object']		=	str_replace("{_".$key."_}",($var), $result['object']);
				$result['object']		=	str_replace("%7B_".$key."_%7D",($var), $result['object']);
			}
		}
		$result['message'] = urldecode($result['message']);
		$result['message']= str_replace('<br>',"\n", $result['message']);
		$QueueComunication = new QueueComunication($this->conn);
		return $QueueComunication->new($result);

	}

	
}

class QueueComunication extends CRUDBase{
	private $table = 'queue_of_communication';
	private $api_key= '018cefe0-96e3-7fcc-8d79-796139604819';
	private $token= '018cefe0-c00c-78dc-970b-6945dd450044';

	public function __construct(ioConn $ioConn) {
		parent::__construct($ioConn);
		$this->setTableName($this->table);
	}
	public function new(array $params = []) {
		if(empty($params['user_id']) ) return $this->error("Identificativo dell'organizzazione assente");
		if(empty($params['message']) ) return $this->error("Messaggio assente");
		if(!isset($params['system_type']) ) return $this->error("Tipologia di sistema assente");

		if(empty($params['id']) ) $params['id'] = $this->conn->getToken();
		if(!empty($params['message'])) $params['message'] = urlencode($params['message']);
		parent::create($params);
		return parent::success(['id'=>$params['id']]);
	}
	public function edit(array $params = []) {
		if(empty($params['id']) ) return $this->error("Identificativo assente");

		$conditions = [];
		if(!empty($params['id'])) $conditions['id'] = $params['id'];
		recursive_unsets($params,['id','user_id']);
		return parent::update($params,$conditions);
	}
	public function gets(array $params = []) {
		if(empty($params)) return $this->error("Nessun parametro fornito");

		$query = new ioQuery('select');
		$query->from($this->table)
		->column('`'.$this->table.'`.*')
		->where('AND',' `'.$this->table.'`.deleted = 0 ')
		->where('AND',' `'.$this->table.'`.attempts < 3 ')
		->group_by('`'.$this->table.'`.id');

		if(!empty($params['id']))
			$query->where('AND'," `".$this->table."`.id = '".$this->conn->escape($params['id'])."' ");
		if(!empty($params['user_id']))
			$query->where('AND',' `'.$this->table.'`.user_id = '.$this->conn->escape($params['user_id']).' ');
		if(isset($params['system_type']))
			$query->where('AND',' `'.$this->table.'`.system_type = '.$this->conn->escape($params['system_type']).' ');
		if(!empty($params['system']))
			$query->where('AND',' `'.$this->table.'`.system = '.$this->conn->escape($params['system']).' ');
		if(!empty($params['status']) && is_array($params['status'])){
			foreach($params['status'] as $k=>$v)
				if(isset($v)) $params['status'][$k] = $this->conn->escape($v);
				else unset($params['status'][$k]);
			$query->where('AND',' `'.$this->table.'`.status IN ('.implode(', ',$params['status']).') ');
		}
		$result = $this->conn->query($query->get());
		if($result){ 
			map_array_field($result,'extra_data',function($row){ return !empty($row['extra_data'])?_json_decode($row['extra_data'],true):null; });
			map_array_field($result,'message',function($row){ return !empty($row['message'])?urldecode($row['message']):null; });
			return parent::success($result);
		}
		return parent::error('Nessun metodo trovato');
	}
	public function sendAllEmail(){
		$elements = $this->gets(['system_type'=>0,'status'=>[0,2]])['data'];
		if(empty($elements)) return parent::error('Nessuna email da inviare');
		$url = 'https://smooney.it/api/v1/cron/send-email';
		
		foreach($elements as $element){
			if(empty($element['system'])) continue;
			if($element['status'] == 2 && strtotime($element['last_update_dt'].' +1minutes')>=strtotime(date('Y-m-d H:i:s')) ) continue;
			$check_running_process = shell_exec('ps -C wget -f');
			$headers = ['--header="Api-Key: '.$this->api_key.'"','--header="Token: '.$this->token.'"'];
			$command = 'wget -qO- --no-check-certificate --timeout=100 '.implode(' ',$headers).' '.$url.'/'.$element['id'];
			if(!empty($check_running_process))
				preg_match('/wget (.*) '.str_replace('/','\/',$url.'/'.$element['id']).'/', $check_running_process, $matches , 0, 0);
			if(empty($check_running_process) || empty($matches)){
				exec( $command . ' > /dev/null 2>&1 &');
			}
		}
		return parent::success(true);
	}
	public function sendAllSMS(){
		$elements = $this->gets(['system_type'=>1,'status'=>[0,2]])['data'];
		if(empty($elements)) return parent::error('Nessuna email da inviare');
		$url = 'https://smooney.it/api/v1/cron/send-sms';

		foreach($elements as $element){
			if(empty($element['system'])) continue;
			$check_running_process = shell_exec('ps -C wget -f');
			$headers = ['--header="Api-Key: '.$this->api_key.'"','--header="Token: '.$this->token.'"'];
			$command = 'wget -qO- --no-check-certificate --timeout=100 '.implode(' ',$headers).' '.$url.'/'.$element['id'];
			if(!empty($check_running_process))
				preg_match('/wget (.*) '.str_replace('/','\/',$url.'/'.$element['id']).'/', $check_running_process, $matches , 0, 0);
			if(empty($check_running_process) || empty($matches)){
				exec( $command . ' > /dev/null 2>&1 &');
			}
		}
		return parent::success(true);
	}
	public function sendEmail($id){
		if(empty($id)) return parent::error('Id assente');
		$element = $this->get(['system_type'=>0,'status'=>[2,0],'id'=>$id])['data'];
		if(empty($element)) return parent::error('Nessuna email da inviare');
		if(empty($element['system'])) return parent::error('Nessuna sistema specificato da inviare');
		$this->edit(['id'=>$element['id'],'attempts'=>$element['attempts']+1]);
		
		$configEmail = new ioQuery('select');
		$configEmail->from('software')
		->column('communication_config.id')
		->column('communication_config.config')
		->left_join('communication_config',null,'communication_config.user_id = '.$this->conn->escape($element['system']==1?1:$element['user_id']).' AND communication_config.deleted = 0 AND communication_config.software_id = software.id ')
		->where('AND',"communication_config.alias = 'email' ")
		->where('AND',"communication_config.id = ".$this->conn->escape($element['system'])." ")
		->where('AND',"software.deleted = 0 ");
		$configEmail = $this->conn->query($configEmail->get());
		if($configEmail){
			$configEmail = reset($configEmail);
			$configEmail = !empty($configEmail['config'])?_json_decode($configEmail['config'],true):null;
		}else $configEmail = null;


		new WrapperClass(['systemMailer']);
		$systemMailer = new systemMailer($this->conn);
		$send = $systemMailer->send($element['recipient'],$element['object'],$element['message'],$element['sender_name'],$element['sender'],!empty($element['sender'])?$element['sender_name']:null,null,[$configEmail]);
		if(!$send['success']) $this->edit(['id'=>$element['id'],'status'=>2]);
		else{
			$this->edit(['id'=>$element['id'],'status'=>1]);
		}
		if(!$send['success']) return $send;

		return parent::success(true);
	}
	public function sendSMS($id){
		if(empty($id)) return parent::error('Id assente');
		$element = $this->get(['system_type'=>1,'status'=>[2,0],'id'=>$id])['data'];
		if(empty($element)) return parent::error('Nessuna email da inviare');
		if(empty($element['system'])) return parent::error('Nessuna sistema specificato da inviare');
		$this->edit(['id'=>$element['id'],'attempts'=>$element['attempts']+1]);
		$configSMS = new ioQuery('select');
		$configSMS->from('external_software')
		->column('external_software_service.id')
		->column('external_software.alias')
		->column('external_software_service.config')
		->left_join('external_software_service',null,'external_software_service.user_id = '.$this->conn->escape($element['user_id']).' AND external_software_service.deleted IS NULL AND external_software_service.external_software_id = external_software.id ')
		->where('AND',"external_software_service.id = ".$this->conn->escape($element['system'])." ")
		->where('AND',"external_software_service.alias = 'send_sms' ")
		->where('AND',"external_software.deleted = 0 ");
		$configSMS = $this->conn->query($configSMS->get());
		if($configSMS){
			$configSMS = reset($configSMS);
			$configSMS['config'] = !empty($configSMS['config'])?_json_decode($configSMS['config'],true):null;
		}else $configSMS = null;


		switch($configSMS['alias']){
			case 'skebby':
				new WrapperClass(['smsSkebby']);
				$smsSkebby = new smsSkebby($this->conn);
				$send = $smsSkebby->send($element['id'],$element['recipient'],$element['message'],$element['user_id'],!empty($element['extra_data']['url'])?$element['extra_data']['url']:null);
				if(!$send['success']) $this->edit(['id'=>$element['id'],'status'=>2]);
				else{
					$this->edit(['id'=>$element['id'],'status'=>1]);
				}
				break;
			default:
				return parent::error('Sistema non identificato');
				break;
		}				

		return parent::success(true);
	}

	public function deleteOldEmailSent(){
		$attempts = 0;
		$count = 0;
		while ($attempts < 500) {
			$q = "SELECT id, last_update_dt
					FROM `".$this->table."`
					WHERE status = 1 AND system_type = 0
					HAVING last_update_dt < '".date('Y-m-d', strtotime('-1 days'))."'
					ORDER BY id ASC LIMIT 1000 ";
			$r = $this->conn->query($q);
			if($r){
				$r = array_column($r,'id');
				$count += count($r);
				$q = "DELETE FROM `".$this->table."` WHERE id IN ('".implode("', '",$r)."') ";
				$this->conn->update($q);
				$attempts++;
			}
			else break;
		}
	}
	public function deleteOldSMSSent(){
		$attempts = 0;
		$count = 0;
		while ($attempts < 500) {
			$q = "SELECT id, last_update_dt
					FROM `".$this->table."`
					WHERE status = 1 AND system_type = 1
					HAVING last_update_dt < '".date('Y-m-d', strtotime('-1 days'))."'
					ORDER BY id ASC LIMIT 1000 ";
			$r = $this->conn->query($q);
			if($r){
				$r = array_column($r,'id');
				$count += count($r);
				$q = "DELETE FROM `".$this->table."` WHERE id IN ('".implode("', '",$r)."') ";
				$this->conn->update($q);
				$attempts++;
			}
			else break;
		}
	}
	 
	
}

?>