<?php
class mngTag extends CRUDBase{
	private $table = 'tag';
	public function __construct(ioConn $ioConn) {
		parent::__construct($ioConn);
		$this->setTableName($this->table);
	}
	public function getUserCryptKey($id){
		if(empty($id) ) return $this->error("Identificativo dell'utente assente");

		$user = new ioQuery('select');
		$user->from('user')
		->column('`user`.id')->column('`user`.code')->column('`user`.crypt_key')
		->where('AND',' `user`.deleted = 0 ')
		->where('AND'," `user`.id = '".$this->conn->escape($id)."' ")
		->group_by(' `user`.code ');
		$user = $this->conn->query($user->get());
		if(empty($user)) return $this->error("Identificativo dell'utente errato");
		$user = reset($user);
		$crypt_key = $user['crypt_key'];
		return parent::success($crypt_key);
	}
	public function getUserId(string $code){
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
	public function new(array $params = []) {
		if(empty($params['user_code']) ) return $this->error("Identificativo dell\'utente assente");
		if(empty($params['name']) ) return $this->error("Nome del wallet assente");

		$user_id = $this->getUserId($params['user_code']);
		if(!$user_id['success']) return $user_id;
		$params['user_id'] = $user_id['data'];

		if(empty($params['code']) ) $params['code'] = $this->conn->getUuid();

		$result = parent::create($params);
		if(!$result['success'] || empty($result['data'])) return $result;
		return parent::success(array_merge($result['data'],['code'=>$params['code']]));
	}
	public function edit(array $params = []) {
		if(empty($params['id']) && empty($params['code']) ) return $this->error("Identificativo assente");

		$conditions = [];
		if(!empty($params['id'])) $conditions['id'] = $params['id'];
		if(!empty($params['code'])) $conditions['code'] = $params['code'];
		if(!empty($params['user_code']) ){
			$user_id = $this->getUserId($params['user_code']);
			if(!$user_id['success']) return $user_id;
			$conditions['user_id'] = $user_id['data'];
		}
		if(isset($params['amount']) && !empty($params['id'])){
			$params['amount'] = $this->cryptData($params['amount'],$params['id']);
		}

		recursive_unsets($params,['id','code','user_code','user_id']);
		return parent::update($params,$conditions);
	}
	public function gets(array $params = []) {
		if(empty($params)) return $this->error("Nessun parametro fornito");

		$query = new ioQuery('select');
		$query->from($this->table);

		if(empty($params['data'])) $query->column('`'.$this->table.'`.*');
		else $query->column('`'.$this->table.'`.id')->column('`'.$this->table.'`.code');
		$query->column('`user`.crypt_key');
		$query->inner_join('user',null,'user.id = `'.$this->table.'`.user_id AND user.deleted = 0')
		->where('AND',' `'.$this->table.'`.deleted = 0 ')
		->group_by(' `'.$this->table.'`.id ');

		if(!empty($params['data']) && is_array($params['data'])) foreach($params['data'] as $key => $value){
			$query->column( (!empty($value['func'])?$value['func']: ( ( !empty($value['table'])?$value['table']:'`'.$this->table.'`' ).'.'.$value['column'])) ,!empty($value['alias'])?$value['alias']:null);
		}

		if(!empty($params['id']))
			$query->where('AND'," `".$this->table."`.id = ".$this->conn->escape($params['id'])." ");
		if(!empty($params['user_id']))
			$query->where('AND'," `".$this->table."`.user_id = ".$this->conn->escape($params['user_id'])." ");
		if(!empty($params['user_code']))
			$query->where('AND'," `user`.code = '".$this->conn->escape($params['user_code'])."' ");
		if(!empty($params['search_text']))
			$query->where('AND'," LOWER(`user`.name) LIKE LOWER('%".$this->conn->escape($params['user_code'])."%') ");
		$result = $this->conn->query($query->get());
		if($result){ 
			foreach($result as $key => $row){
				$crypt_key = !empty($row['crypt_key'])?$row['crypt_key']:null;
				unset($result[$key]['crypt_key']);

				if(empty($params['data']) || (is_array($params['data']) && in_array('amount',array_column($params['data'],'column'))) )
					$result[$key]['amount'] =  $this->decryptData('number',$row['amount'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('creation_dt',array_column($params['data'],'column'))) )
					$result[$key]['creation_dt'] = !empty($row['creation_dt'])?date('Y-m-d H:i:s',strtotime($row['creation_dt'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('last_update_dt',array_column($params['data'],'column'))) )
					$result[$key]['last_update_dt'] = !empty($row['last_update_dt'])?date('Y-m-d H:i:s',strtotime($row['last_update_dt'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('cancellation_dt',array_column($params['data'],'column'))) )
					$result[$key]['cancellation_dt'] = !empty($row['cancellation_dt'])?date('Y-m-d H:i:s',strtotime($row['cancellation_dt'])):null;
			}
			
			return parent::success($result);
		}
		return parent::error('Nessun wallet trovato');
	}
	

	private function cryptData($data,$recordId){
		$record = $this->get([ 'id'=>$recordId  ])['data'];
		if(empty($record)) return null;

		$info = $this->getUserCryptKey($record['user_id']);
		if(!$info['success']) return null;
		$crypt_key = (string) $info['data'];

		$userKey = EncryptionManager::decryptUserKey($crypt_key);
		$encryptedData = EncryptionManager::encryptData($data, $userKey);
		return $encryptedData;
	}


	private function decryptData($type,$data,$cryptKey,$defaultValue=null){
		switch($type){
			case 'number':
				if(!isset($defaultValue)) $defaultValue = 0.00;
				if(empty($cryptKey)) return floatval(strval( (isset($data)?$data:$defaultValue) ));
				$userKey = EncryptionManager::decryptUserKey($cryptKey);
				return floatval(strval( !empty($userKey) && !empty($data) ? EncryptionManager::decryptData($data, $userKey) : (isset($data)?$data:$defaultValue) ));
			break;
		}
	}
	
}