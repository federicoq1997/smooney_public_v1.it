<?php
class mngWallet extends CRUDBase{
	private $table = 'wallet';
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
		if(empty($params['user_code']) ) return $this->error("Identificativo dell\'utente assente");
		if(empty($params['name']) ) return $this->error("Nome del wallet assente");

		$user_id = $this->getUserId($params['user_code']);
		if(!$user_id['success']) return $user_id;
		$params['user_id'] = $user_id['data'];

		if(empty($params['code']) ) $params['code'] = $this->conn->getUuid();
		$params['amount_income'] = !empty( $params['amount_income'] ) ? $params['amount_income']:0;
		$params['amount_expenses'] = !empty( $params['amount_expenses'] ) ? $params['amount_expenses']:0;
		if(!isset($params['amount_balance']))
			$params['amount_balance'] = $params['amount_income'] - $params['amount_expenses'];

		if(isset($params['amount_income']) && !empty($params['user_id'])) 
			$params['amount_income'] = $this->cryptData($params['amount_income'],null,$params['user_id']);
		if(isset($params['amount_expenses']) && !empty($params['user_id'])) 
			$params['amount_expenses'] = $this->cryptData($params['amount_expenses'],null,$params['user_id']);
		if(isset($params['amount_balance']) && !empty($params['user_id'])) 
			$params['amount_balance'] = $this->cryptData($params['amount_balance'],null,$params['user_id']);

		$result = parent::create($params);
		if(!$result['success'] || empty($result['data'])) return $result;
		return parent::success(array_merge($result['data'],['code'=>$params['code']]));
	}
	private function edit(array $params = []) {
		if(empty($params['id']) && empty($params['code']) ) return $this->error("Identificativo assente");

		$conditions = [];
		if(!empty($params['id'])) $conditions['id'] = $params['id'];
		if(!empty($params['code'])) $conditions['code'] = $params['code'];
		if(!empty($params['user_code']) ){
			$user_id = $this->getUserId($params['user_code']);
			if(!$user_id['success']) return $user_id;
			$conditions['user_id'] = $user_id['data'];
			$params['user_id'] = $user_id['data'];
		}
		if(empty($params['delete'])){
			$params['amount_income'] = !empty( $params['amount_income'] ) ? $params['amount_income']:0;
			$params['amount_expenses'] = !empty( $params['amount_expenses'] ) ? $params['amount_expenses']:0;
			if(!isset($params['amount_balance']))
				$params['amount_balance'] = $params['amount_income'] - abs($params['amount_expenses']);
		}

		if(isset($params['amount_income']) && (!empty($params['id']) || !empty($params['user_id'])) ) 
			$params['amount_income'] = $this->cryptData($params['amount_income'],!empty($params['id'])?$params['id']:null,!empty($params['user_id'])?$params['user_id']:null);
		if(isset($params['amount_expenses']) && (!empty($params['id']) || !empty($params['user_id'])) ) 
			$params['amount_expenses'] = $this->cryptData($params['amount_expenses'],!empty($params['id'])?$params['id']:null,!empty($params['user_id'])?$params['user_id']:null);
		if(isset($params['amount_balance']) && (!empty($params['id']) || !empty($params['user_id'])) ) 
			$params['amount_balance'] = $this->cryptData($params['amount_balance'],!empty($params['id'])?$params['id']:null,!empty($params['user_id'])?$params['user_id']:null);
		if(isset($params['extra_data']) && (!empty($params['id']) || !empty($params['user_id'])) ) 
			$params['extra_data'] = $this->cryptData($params['extra_data'],!empty($params['id'])?$params['id']:null,!empty($params['user_id'])?$params['user_id']:null);

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
		$query->column('`parent_wallet`.name','parent_wallet_name');
		$query->inner_join('user',null,'user.id = `'.$this->table.'`.user_id AND user.deleted = 0')
		->left_join('`'.$this->table.'`','parent_wallet',' `'.$this->table.'`.parent_wallet_id IS NOT NULL AND parent_wallet.id = `'.$this->table.'`.parent_wallet_id')
		->where('AND',' `'.$this->table.'`.deleted = 0 ')
		->group_by(' `'.$this->table.'`.code ');

		if(!empty($params['data']) && is_array($params['data'])) foreach($params['data'] as $key => $value){
			$query->column( (!empty($value['func'])?$value['func']: ( ( !empty($value['table'])?$value['table']:'`'.$this->table.'`' ).'.'.$value['column'])) ,!empty($value['alias'])?$value['alias']:null);
		}

		if(!empty($params['code']))
			$query->where('AND'," `".$this->table."`.code = '".$this->conn->escape($params['code'])."' ");
		if(!empty($params['id']))
			$query->where('AND'," `".$this->table."`.id = ".$this->conn->escape($params['id'])." ");
		if(!empty($params['ids'])){
			foreach($params['ids'] as $k=>$v) $params['ids'][$k] = $this->conn->escape($v);
			$query->where('AND'," `".$this->table."`.id IN (".implode(', ',$params['ids']).") ");
		}
		if(!empty($params['no-child']))
			$query->where('AND'," `".$this->table."`.parent_wallet_id IS NULL ");
		if(!empty($params['parent_wallet_id']))
			$query->where('AND'," `".$this->table."`.parent_wallet_id = ".$this->conn->escape($params['parent_wallet_id'])." ");
		if(!empty($params['user_id']))
			$query->where('AND'," `".$this->table."`.user_id = ".$this->conn->escape($params['user_id'])." ");
		if(!empty($params['user_code']))
			$query->where('AND'," `user`.code = '".$this->conn->escape($params['user_code'])."' ");
		$result = $this->conn->query($query->get());
		if($result){ 
			foreach($result as $key=>$row){
				$crypt_key = !empty($row['crypt_key'])?$row['crypt_key']:null;
				unset($result[$key]['crypt_key']);

				if(empty($params['data']) || (is_array($params['data']) && in_array('name',array_column($params['data'],'column'))) ){
					if(!empty($result[$key]['parent_wallet_name'])){
						$result[$key]['name'] = $result[$key]['parent_wallet_name'].' | '.$result[$key]['name'];
						unset($result[$key]['parent_wallet_name']);
					}
				}
				if(empty($params['data']) || (is_array($params['data']) && in_array('amount_income',array_column($params['data'],'column'))) )
					$result[$key]['amount_income'] = $this->decryptData('number',$row['amount_income'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('amount_expenses',array_column($params['data'],'column'))) )
					$result[$key]['amount_expenses'] = $this->decryptData('number',$row['amount_expenses'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('amount_balance',array_column($params['data'],'column'))) )
					$result[$key]['amount_balance'] =  $this->decryptData('number',$row['amount_balance'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('creation_dt',array_column($params['data'],'column'))) )
					$result[$key]['creation_dt'] =  !empty($row['creation_dt'])?date('Y-m-d H:i:s',strtotime($row['creation_dt'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('last_update_dt',array_column($params['data'],'column'))) )
					$result[$key]['last_update_dt'] =  !empty($row['last_update_dt'])?date('Y-m-d H:i:s',strtotime($row['last_update_dt'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('cancellation_dt',array_column($params['data'],'column'))) )
					$result[$key]['cancellation_dt'] =  !empty($row['cancellation_dt'])?date('Y-m-d H:i:s',strtotime($row['cancellation_dt'])):null;
				if(empty($params['extra_data']) || (is_array($params['extra_data']) && in_array('extra_data',array_column($params['data'],'column'))) )
					$result[$key]['extra_data'] =  $this->decryptData('json',$row['extra_data'],$crypt_key);
			}
			
			return parent::success($result);
		}
		return parent::error('Nessun wallet trovato');
	}
	
	public function updateInfoWallet(array $params=[]){
		if(empty($params['user_id']) && empty($params['user_code'])) return $this->error("Identificativo dell'utente assente");
		if(empty($params['code'])) return $this->new($params);
		if(isset($params['extra_data'])) $params['extra_data'] = json_encode($params['extra_data']);
		// recursive_unsets($params,['amount_income','amount_expenses','amount_balance']);
		return $this->edit($params);
	}
	public function deleteWallet(string $user_code, string $code){
		if(empty($user_code)) return $this->error("Identificativo dell'utente assente");
		if(empty($code)) return $this->error("Identificativo dell'utente assente");
		$params['user_code'] = $user_code;
		$params['code'] = $code;
		$params['deleted'] = 1;
		$params['cancellation_dt'] = date('Y-m-d H:i:s');
    $wallet = $this->get([ 'user_code'=>$user_code,'code'=>$code ])['data'];
		if(!empty($wallet)){
			$child_wallets = $this->gets([ 'user_code'=>$user_code,'parent_wallet_id'=>$wallet['id'] ])['data'];
		}
		$result = $this->edit($params);
		if($result['success'] && !empty($child_wallets)){
			foreach($child_wallets as $child_wallet)
				$this->edit([
					'user_code'=>$user_code,
					'code'=>$child_wallet['code'],
					'deleted'=>1,
					'cancellation_dt'=>date('Y-m-d H:i:s'),
				]);
		}
		return $result;
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

	private function cryptData($data,$recordId,$userId=null){
		if(empty($userId) && !empty($recordId)){
			$record = $this->get([ 'id'=>$recordId  ])['data'];
			if(empty($record)) return null;
			$userId = $record['user_id'];
		}

		$info = $this->getUserCryptKey($userId);
		if(!$info['success']) return $data;
		$crypt_key = (string) $info['data'];

		$userKey = EncryptionManager::decryptUserKey($crypt_key);
		if(is_array($data)) $data = json_encode($data);
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
			case 'json':
				if(!isset($defaultValue)) $defaultValue = "{}";
				if(empty($cryptKey)) return _json_decode( isset($data)?$data:$defaultValue ,true);
				$userKey = EncryptionManager::decryptUserKey($cryptKey);
				return _json_decode( !empty($userKey) && !empty($data) ? EncryptionManager::decryptData($data, $userKey) : (isset($data)?$data:$defaultValue) ,true);
			break;
		}
	}

}
class mngWalletHistory extends CRUDBase{
	private $table = 'history_wallet';
	public function __construct(ioConn $ioConn) {
		parent::__construct($ioConn);
		$this->setTableName($this->table);
	}

	private function generateId():string {
		return $this->conn->query("SELECT UUID_SHORT() AS id")[0]['id'];
	}
	private function getUserId(string $code){
		if(empty($user_code) ) return $this->error("Identificativo dell'utente assente");

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
	private function new(array $params = []) {
		if(empty($params['user_id']) ) return $this->error("Identificativo dell\'utente assente");
		if(empty($params['wallet_id']) ) return $this->error("Identificativo del wallet assente");
		if(empty($params['month']) ) return $this->error("Mese assente");
		if(empty($params['year']) ) return $this->error("Anno assente");
		$params['id'] = $this->generateId();
		if(isset($params['info_tag'])) $params['info_tag'] = json_encode($params['info_tag']);

		if(isset($params['amount_income']) && !empty($params['user_id'])) 
			$params['amount_income'] = $this->cryptData($params['amount_income'],null,$params['user_id']);
		if(isset($params['amount_expenses']) && !empty($params['user_id'])) 
			$params['amount_expenses'] = $this->cryptData($params['amount_expenses'],null,$params['user_id']);
		if(isset($params['amount_balance']) && !empty($params['user_id'])) 
			$params['amount_balance'] = $this->cryptData($params['amount_balance'],null,$params['user_id']);
		if(isset($params['info_tag']) && !empty($params['user_id'])) 
			$params['info_tag'] = $this->cryptData($params['info_tag'],null,$params['user_id']);

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
		if(isset($params['info_tag'])) $params['info_tag'] = json_encode($params['info_tag']);

		if(isset($params['amount_income']) && !empty($params['id'])) 
			$params['amount_income'] = $this->cryptData($params['amount_income'],$params['id']);
		if(isset($params['amount_expenses']) && !empty($params['id'])) 
			$params['amount_expenses'] = $this->cryptData($params['amount_expenses'],$params['id']);
		if(isset($params['amount_balance']) && !empty($params['id'])) 
			$params['amount_balance'] = $this->cryptData($params['amount_balance'],$params['id']);
		if(isset($params['info_tag']) && !empty($params['id'])) 
			$params['info_tag'] = $this->cryptData($params['info_tag'],$params['id']);

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
		$query->left_join('user',null,'user.id = `'.$this->table.'`.user_id AND user.deleted = 0')
		->where('AND',' `'.$this->table.'`.deleted = 0 ')
		->group_by(' `'.$this->table.'`.id ');

		if(!empty($params['data']) && is_array($params['data'])) foreach($params['data'] as $key => $value){
			$query->column( (!empty($value['func'])?$value['func']: ( ( !empty($value['table'])?$value['table']:'`'.$this->table.'`' ).'.'.$value['column'])) ,!empty($value['alias'])?$value['alias']:null);
		}

		if(!empty($params['month']))
			$query->where('AND'," `".$this->table."`.month = '".$this->conn->escape($params['month'])."' ");
		if(!empty($params['year']))
			$query->where('AND'," `".$this->table."`.year = '".$this->conn->escape($params['year'])."' ");
		if(!empty($params['code']))
			$query->where('AND'," `".$this->table."`.code = '".$this->conn->escape($params['code'])."' ");
		if(!empty($params['id']))
			$query->where('AND'," `".$this->table."`.id = '".$this->conn->escape($params['id'])."' ");
		if(!empty($params['user_id']))
			$query->where('AND'," `".$this->table."`.user_id = ".$this->conn->escape($params['user_id'])." ");
		if(!empty($params['wallet_id']))
			$query->where('AND'," `".$this->table."`.wallet_id = ".$this->conn->escape($params['wallet_id'])." ");
		if(!empty($params['wallet_ids'])){
			foreach($params['wallet_ids'] as $k=>$v) $params['wallet_ids'][$k] = $this->conn->escape($v);
			$query->where('AND'," `".$this->table."`.wallet_id IN (".implode(', ',$params['wallet_ids']).") ");
		}
		if(!empty($params['from']))
			$query->where('AND'," CONCAT(`".$this->table."`.year, '-', LPAD(`".$this->table."`.month, 2, '0')) >= '".$this->conn->escape(date('Y-m',strtotime($params['from'])))."' ");
		if(!empty($params['user_code']))
			$query->where('AND'," `user`.code = '".$this->conn->escape($params['user_code'])."' ");
		$result = $this->conn->query($query->get());
		if($result){ 
			foreach($result as $key=>$row){
				$crypt_key = !empty($row['crypt_key'])?$row['crypt_key']:null;
				unset($result[$key]['crypt_key']);

				if(empty($params['data']) || (is_array($params['data']) && in_array('amount_income',array_column($params['data'],'column'))) )
					$result[$key]['amount_income'] = $this->decryptData('number',$row['amount_income'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('amount_expenses',array_column($params['data'],'column'))) )
					$result[$key]['amount_expenses'] = $this->decryptData('number',$row['amount_expenses'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('amount_balance',array_column($params['data'],'column'))) )
					$result[$key]['amount_balance'] =  $this->decryptData('number',$row['amount_balance'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('creation_dt',array_column($params['data'],'column'))) )
					$result[$key]['creation_dt'] =  !empty($row['creation_dt'])?date('Y-m-d H:i:s',strtotime($row['creation_dt'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('last_update_dt',array_column($params['data'],'column'))) )
					$result[$key]['last_update_dt'] =  !empty($row['last_update_dt'])?date('Y-m-d H:i:s',strtotime($row['last_update_dt'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('cancellation_dt',array_column($params['data'],'column'))) )
					$result[$key]['cancellation_dt'] =  !empty($row['cancellation_dt'])?date('Y-m-d H:i:s',strtotime($row['cancellation_dt'])):null;
				if(empty($params['info_tag']) || (is_array($params['extra_data']) && in_array('info_tag',array_column($params['data'],'column'))) )
					$result[$key]['info_tag'] =  $this->decryptData('json',$row['info_tag'],$crypt_key);
			}
			return parent::success($result);
		}
		return parent::error('Nessun wallet trovato');
	}

	private function generateHistory($user_id,$wallet_id){

		if(empty($user_id)) return $this->error("Identificativo dell'utente assente");
		if(empty($wallet_id)) return $this->error("Identificativo del wallet assente");
		$date = date('Y-m-d', strtotime('first day of last month'));
		
		$mngWallet = new mngWallet($this->conn);
		$wallet = $mngWallet->get([ 'id'=>$wallet_id,'user_id'=>$user_id ])['data'];
		if(empty($wallet)) return $this->error('Il wallet non è stato trovato');
		
		$info_tag = [];
		new WrapperClass(['mngTag']);
		$mngTag = new mngTag($this->conn);
		$tags = $mngTag->gets([ 'user_id'=>$user_id ])['data'];
		$info_tag = array_column($tags,'amount','id');
		
		$create = $this->new([
			'user_id'=>$user_id,
			'wallet_id'=>$wallet_id,
			'month'=>date('m',strtotime($date)),
			'year'=>date('Y',strtotime($date)),
			'amount_income'=>$wallet['amount_income'],
			'amount_expenses'=>$wallet['amount_expenses'],
			'amount_balance'=>$wallet['amount_balance'],
			'info_tag'=>$info_tag
		]);
		$this->conn->update("UPDATE tag SET amount = 0 WHERE id IN (".implode(',',array_column($tags,'id')).") ");
		$this->conn->update("UPDATE wallet SET amount_expenses = 0,amount_income=0 WHERE id = $wallet_id ");
		return $create;
	}

	public function generateHistories(){
		if (date('j') != 1) return $this->error("Non è possibile generare la history del mese");

		new WrapperClass(['mngUser']);
		$mngUser = new mngUser($this->conn);
		$mngUser->sendRecapMonth();
		
		$users = new ioQuery('select');
		$users->from('user')
		->column('`user`.id')->column('`user`.code')->column('`wallet`.id','wallet_id')
		->left_join('wallet',null,'wallet.user_id = user.id')
		->where('AND',' `wallet`.deleted = 0 ')
		->where('AND',' `user`.deleted = 0 ')
		->group_by(' `user`.code ')
		->group_by(' `wallet`.id ');
		$users = $this->conn->query($users->get());
		if(empty($users)) return $this->error("Nessun utente trovato per cui generare una history");
		foreach($users as $user){
			$this->generateHistory($user['id'],$user['wallet_id']);
		}
		return parent::success('done');
	}

	
	private function cryptData($data,$recordId,$userId=null){
		if(empty($userId)){
			$record = $this->get([ 'id'=>$recordId  ])['data'];
			if(empty($record)) return null;
			$userId = $record['user_id'];
		}

		$info = $this->getUserCryptKey($userId);
		if(!$info['success']) return $data;
		$crypt_key = (string) $info['data'];
		$userKey = EncryptionManager::decryptUserKey($crypt_key);
		if(is_array($data)) $data = json_encode($data);
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
			case 'json':
				if(!isset($defaultValue)) $defaultValue = "[]";
				if(empty($cryptKey)) return _json_decode( isset($data)?$data:$defaultValue ,true);
				$userKey = EncryptionManager::decryptUserKey($cryptKey);
				return _json_decode( !empty($userKey) && !empty($data) ? EncryptionManager::decryptData($data, $userKey) : (isset($data)?$data:$defaultValue) ,true);
			break;
		}
	}
}
class mngWalletForecast extends CRUDBase{
	private $table = 'forecast_wallet';
	public function __construct(ioConn $ioConn) {
		parent::__construct($ioConn);
		$this->setTableName($this->table);
	}

	private function generateId():string {
		return $this->conn->query("SELECT UUID_SHORT() AS id")[0]['id'];
	}
	private function getUserId(string $code){
		if(empty($user_code) ) return $this->error("Identificativo dell'utente assente");

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
	private function new(array $params = []) {
		if(empty($params['user_id']) ) return $this->error("Identificativo dell\'utente assente");
		if(empty($params['wallet_id']) ) return $this->error("Identificativo del wallet assente");
		if(empty($params['month']) ) return $this->error("Mese assente");
		if(empty($params['year']) ) return $this->error("Anno assente");
		$params['id'] = $this->generateId();
		if(isset($params['info_tag'])) $params['info_tag'] = json_encode($params['info_tag']);

		if(isset($params['amount_income']) && !empty($params['user_id'])) 
			$params['amount_income'] = $this->cryptData($params['amount_income'],null,$params['user_id']);
		if(isset($params['amount_expenses']) && !empty($params['user_id'])) 
			$params['amount_expenses'] = $this->cryptData($params['amount_expenses'],null,$params['user_id']);
		if(isset($params['amount_balance']) && !empty($params['user_id'])) 
			$params['amount_balance'] = $this->cryptData($params['amount_balance'],null,$params['user_id']);

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
		if(isset($params['info_tag'])) $params['info_tag'] = json_encode($params['info_tag']);

		if(isset($params['amount_income']) && !empty($params['id'])) 
			$params['amount_income'] = $this->cryptData($params['amount_income'],$params['id']);
		if(isset($params['amount_expenses']) && !empty($params['id'])) 
			$params['amount_expenses'] = $this->cryptData($params['amount_expenses'],$params['id']);
		if(isset($params['amount_balance']) && !empty($params['id'])) 
			$params['amount_balance'] = $this->cryptData($params['amount_balance'],$params['id']);

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
		$query->left_join('user',null,'user.id = `'.$this->table.'`.user_id AND user.deleted = 0')
		->where('AND',' `'.$this->table.'`.deleted = 0 ')
		->group_by(' `'.$this->table.'`.id ');

		if(!empty($params['data']) && is_array($params['data'])) foreach($params['data'] as $key => $value){
			$query->column( (!empty($value['func'])?$value['func']: ( ( !empty($value['table'])?$value['table']:'`'.$this->table.'`' ).'.'.$value['column'])) ,!empty($value['alias'])?$value['alias']:null);
		}

		if(!empty($params['month']))
			$query->where('AND'," `".$this->table."`.month = '".$this->conn->escape($params['month'])."' ");
		if(!empty($params['year']))
			$query->where('AND'," `".$this->table."`.year = '".$this->conn->escape($params['year'])."' ");
		if(!empty($params['code']))
			$query->where('AND'," `".$this->table."`.code = '".$this->conn->escape($params['code'])."' ");
		if(!empty($params['id']))
			$query->where('AND'," `".$this->table."`.id = '".$this->conn->escape($params['id'])."' ");
		if(!empty($params['user_id']))
			$query->where('AND'," `".$this->table."`.user_id = ".$this->conn->escape($params['user_id'])." ");
		if(!empty($params['wallet_id']))
			$query->where('AND'," `".$this->table."`.wallet_id = ".$this->conn->escape($params['wallet_id'])." ");
		if(!empty($params['wallet_ids'])){
			foreach($params['wallet_ids'] as $k=>$v) $params['wallet_ids'][$k] = $this->conn->escape($v);
			$query->where('AND'," `".$this->table."`.wallet_id IN (".implode(', ',$params['wallet_ids']).") ");
		}
		if(!empty($params['from']))
			$query->where('AND'," CONCAT(`".$this->table."`.year, '-', LPAD(`".$this->table."`.month, 2, '0')) >= '".$this->conn->escape(date('Y-m',strtotime($params['from'])))."' ");
		if(!empty($params['user_code']))
			$query->where('AND'," `user`.code = '".$this->conn->escape($params['user_code'])."' ");

		$result = $this->conn->query($query->get());
		if($result){ 
			foreach($result as $key=>$row){
				$crypt_key = !empty($row['crypt_key'])?$row['crypt_key']:null;
				unset($result[$key]['crypt_key']);

				if(empty($params['data']) || (is_array($params['data']) && in_array('amount_income',array_column($params['data'],'column'))) )
					$result[$key]['amount_income'] = $this->decryptData('number',$row['amount_income'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('amount_expenses',array_column($params['data'],'column'))) )
					$result[$key]['amount_expenses'] = $this->decryptData('number',$row['amount_expenses'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('amount_balance',array_column($params['data'],'column'))) )
					$result[$key]['amount_balance'] =  $this->decryptData('number',$row['amount_balance'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('creation_dt',array_column($params['data'],'column'))) )
					$result[$key]['creation_dt'] =  !empty($row['creation_dt'])?date('Y-m-d H:i:s',strtotime($row['creation_dt'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('last_update_dt',array_column($params['data'],'column'))) )
					$result[$key]['last_update_dt'] =  !empty($row['last_update_dt'])?date('Y-m-d H:i:s',strtotime($row['last_update_dt'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('cancellation_dt',array_column($params['data'],'column'))) )
					$result[$key]['cancellation_dt'] =  !empty($row['cancellation_dt'])?date('Y-m-d H:i:s',strtotime($row['cancellation_dt'])):null;
			}
			return parent::success($result);
		}
		return parent::error('Nessun wallet trovato');
	}

	public function generateForecast($user_id,$wallet_id){

		if(empty($user_id)) return $this->error("Identificativo dell'utente assente");
		if(empty($wallet_id)) return $this->error("Identificativo del wallet assente");
		$dateFrom = date('Y-m-d', strtotime('first day of next month'));
		$dateTo = date('Y-m-d',strtotime($dateFrom.' +6month'));
		$dateRange =createPeriod( $dateFrom, $dateTo, 'P1M', true);
    new WrapperClass(['mngIntentTransaction']);
    $mngIntentTransaction = new mngIntentTransaction($this->conn);
    $recurringTransactions = $mngIntentTransaction->gets([
			'user_id'=>$user_id,
			'dest_wallet_id'=>$wallet_id,
			'is_recurry'=>true,  
			'valid_recurry'=>$dateFrom,  
    ])['data'];
    $postdatedTransactions = $mngIntentTransaction->gets([
			'user_id'=>$user_id,
			'postdated_wallet_id'=>$wallet_id,
			'postdated'=>true,  
			'valid_postdated'=>$dateFrom,  
		])['data'];

		$all_forecast = $this->gets([ 
			'user_id'=>$user_id,
			'wallet_id'=>$wallet_id,
			'from'=>$dateFrom
		])['data'];
    map_array_field($all_forecast,'ym',function($r){ return $r['year'].'-'.str_pad($r['month'], 2, "0", STR_PAD_LEFT); });
    $all_forecast = array_column($all_forecast,null,'ym');

		foreach ($dateRange as $date) {
			$Ym = $date->format('Y-m');
			$data = [
				'user_id'=>$user_id,
				'wallet_id'=>$wallet_id,
				'month'=>$date->format('m'),
				'year'=>$date->format('Y'),
				'amount_income'=>0.00,
				'amount_expenses'=>0.00,
				'amount_balance'=>0.00,
			];

			$recurringTransactionsInRange = array_filter($recurringTransactions,function($r) use($Ym){
				// Ottieni i range "from" e "to" come 'Y-m'
        $from = (new DateTime($r['from']))->format('Y-m');
        $to = isset($r['to']) ? (new DateTime($r['to']))->format('Y-m') : null;
        // Verifica se la data corrente è nel range
        return $Ym >= $from && (!$to || $Ym <= $to); 
			});
			if(!empty($recurringTransactionsInRange)) foreach($recurringTransactionsInRange as $recurringTransaction){
				if($recurringTransaction['sign']>0) //income
					$data['amount_income'] = abs($recurringTransaction['amount']);
				else
					$data['amount_expenses'] = -1*abs($recurringTransaction['amount']);
			}

			$postdatedTransactionsInRange = array_filter($postdatedTransactions,function($r) use($Ym){
        $date = (new DateTime($r['date']))->format('Y-m');
        return $Ym == $date; 
			});
			if(!empty($postdatedTransactionsInRange)) foreach($postdatedTransactionsInRange as $postdatedTransaction){
				if(!empty($postdatedTransaction['source_wallet_id']) && $postdatedTransaction['source_wallet_id'] == $wallet_id) $sign = -1;
				else $sign = $postdatedTransaction['sign'];
				if($sign>0) //income
					$data['amount_income'] = abs($postdatedTransaction['amount']);
				else
					$data['amount_expenses'] = -1*abs($postdatedTransaction['amount']);
			}
			$forecastValid = false;
			if( round($data['amount_income'],2)!=0.00 || round($data['amount_expenses'],2)!=0.00 || round($data['amount_balance'],2)!=0.00 ) 
				$forecastValid = true;
			if(!$forecastValid) continue;

			if(!empty( $all_forecast[$Ym] )) 
				$this->edit(array_merge($data,[ 'id'=>$all_forecast[$Ym]['id'] ]));
			else
				$this->new($data);
		}

		return parent::success('Forecast aggiornato');
	}

	public function generateAllForecast(){
		// if (date('j') != 1) return $this->error("Non è possibile generare la history del mese");

		new WrapperClass(['mngUser']);
		$mngUser = new mngUser($this->conn);
		$mngUser->sendRecapMonth();
		
		$users = new ioQuery('select');
		$users->from('user')
		->column('`user`.id')->column('`user`.code')->column('`wallet`.id','wallet_id')
		->left_join('wallet',null,'wallet.user_id = user.id')
		->where('AND',' `wallet`.deleted = 0 ')
		->where('AND',' `user`.deleted = 0 ')
		->group_by(' `user`.code ')
		->group_by(' `wallet`.id ');
		$users = $this->conn->query($users->get());
		if(empty($users)) return $this->error("Nessun utente trovato per cui generare una history");
		foreach($users as $user){
			$this->generateForecast($user['id'],$user['wallet_id']);
		}
		return parent::success('done');
	}

	
	private function cryptData($data,$recordId,$userId=null){
		if(empty($userId)){
			$record = $this->get([ 'id'=>$recordId  ])['data'];
			if(empty($record)) return null;
			$userId = $record['user_id'];
		}

		$info = $this->getUserCryptKey($userId);
		if(!$info['success']) return $data;
		$crypt_key = (string) $info['data'];
		$userKey = EncryptionManager::decryptUserKey($crypt_key);
		if(is_array($data)) $data = json_encode($data);
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
			case 'json':
				if(!isset($defaultValue)) $defaultValue = "[]";
				if(empty($cryptKey)) return _json_decode( isset($data)?$data:$defaultValue ,true);
				$userKey = EncryptionManager::decryptUserKey($cryptKey);
				return _json_decode( !empty($userKey) && !empty($data) ? EncryptionManager::decryptData($data, $userKey) : (isset($data)?$data:$defaultValue) ,true);
			break;
		}
	}
}