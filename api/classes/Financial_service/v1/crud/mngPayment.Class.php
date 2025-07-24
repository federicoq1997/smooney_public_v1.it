<?php
class mngPayment extends CRUDBase{
	private $table = 'payment';
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
	public function new(array $params = []) {
		if(empty($params['user_code']) && empty($params['user_id']) ) return $this->error("Identificativo dell\'utente assente");
		if(empty($params['amount']) ) return $this->error("Importo assente");
		if(empty($params['date']) ) return $this->error("Giorno di validazione");
		if(empty($params['transaction_id']) ) return $this->error("Identificativo della transazione assente");
		if(empty($params['wallet_id']) ) return $this->error("Identificativo del Wallet assente");
		if(empty($params['code']) ) $params['code'] = $this->conn->getUuid();

		if(empty($params['user_id'])){
			$user_id = $this->getUserId($params['user_code']);
			if(!$user_id['success']) return $user_id;
			$params['user_id'] = $user_id['data'];
		}
		$params['amount'] = floatval(strval($params['amount']));
		if($params['amount']>0) $params['sign'] = 1;
		else $params['sign'] = -1;

		if(isset($params['description']) && !empty($params['user_id'])) 
			$params['description'] = $this->cryptData($params['description'],null,$params['user_id']);
		if(isset($params['amount']) && !empty($params['user_id'])) 
			$params['amount'] = $this->cryptData($params['amount'],null,$params['user_id']);

		$result = parent::create($params);
		if(!$result['success'] || empty($result['data'])) return $result;
		$this->updateAmountsWalletByPayment($params['wallet_id'],!empty($result['data']['id'])?$result['data']['id']:null,$params['user_id'],null,!empty($params['type'])?$params['type']:null);
		return parent::success(array_merge($result['data']));
	}
	private function edit(array $params = []) {
		if(empty($params['id']) && empty($params['user_id']) ) return $this->error("Identificativo assente");

		$conditions = [];
		if(!empty($params['id'])) $conditions['id'] = $params['id'];
		if(!empty($params['user_id'])) $conditions['user_id'] = $params['user_id'];
		if(!empty($params['transaction_id'])) $conditions['transaction_id'] = $params['transaction_id'];
		if(!empty($params['wallet_id'])) $conditions['wallet_id'] = $params['wallet_id'];

		if(isset($params['description']) && !empty($params['id'])) 
			$params['description'] = $this->cryptData($params['description'],$params['id']);
		if(isset($params['amount']) && !empty($params['id'])) 
			$params['amount'] = $this->cryptData($params['amount'],$params['id']);

		recursive_unsets($params,['id','user_id','transaction_id','wallet_id']);
		return parent::update($params,$conditions);
	}
	public function gets(array $params = []) {
		if(empty($params)) return $this->error("Nessun parametro fornito");

		$query = new ioQuery('select',$this->conn);
		$query->from($this->table);

		if(empty($params['data'])) $query->column('`'.$this->table.'`.*');
		else $query->column('`'.$this->table.'`.id')->column('`'.$this->table.'`.code');
		$query->column('`user`.crypt_key');
		$query->left_join('intent_transaction',null,'intent_transaction.id = `'.$this->table.'`.transaction_id')
		->inner_join('user',null,'user.id = `'.$this->table.'`.user_id AND user.deleted = 0')
		->left_join('wallet',null,'wallet.id = `'.$this->table.'`.wallet_id AND wallet.deleted = 0')
		->left_join('tag',null,'tag.id = `'.$this->table.'`.tag_id')
		->where('AND',' `'.$this->table.'`.deleted = 0 ')
		->where('AND',' intent_transaction.deleted = 0 ')
		->group_by(' `'.$this->table.'`.id ');

		if(!empty($params['data']) && is_array($params['data'])) foreach($params['data'] as $key => $value){
			$query->column( (!empty($value['func'])?$value['func']: ( ( !empty($value['table'])?$value['table']:'`'.$this->table.'`' ).'.'.$value['column'])) ,!empty($value['alias'])?$value['alias']:null);
		}

		if(!empty($params['id']))
			$query->where('AND'," `".$this->table."`.id = ".$this->conn->escape($params['id'])." ");
		if(!empty($params['code']))
			$query->where('AND'," `".$this->table."`.code = '".$this->conn->escape($params['code'])."' ");
		if(!empty($params['ext_code']))
			$query->where('AND'," `".$this->table."`.ext_code = '".$this->conn->escape($params['ext_code'])."' ");
		if(!empty($params['user_id']))
			$query->where('AND'," `".$this->table."`.user_id = ".$this->conn->escape($params['user_id'])." ");
		if(!empty($params['wallet_id']))
			$query->where('AND'," `".$this->table."`.wallet_id = ".$this->conn->escape($params['wallet_id'])." ");
		if(!empty($params['wallet_ids'])){
			foreach($params['wallet_ids'] as $k=>$v) $params['wallet_ids'][$k] = $this->conn->escape($v);
			$query->where('AND'," `".$this->table."`.wallet_id IN (".implode(', ',$params['wallet_ids']).") ");
		}
		if(!empty($params['transaction_id']))
			$query->where('AND'," `".$this->table."`.transaction_id = ".$this->conn->escape($params['transaction_id'])." ");
		if(!empty($params['transaction_code']))
			$query->where('AND'," `intent_transaction`.code = '".$this->conn->escape($params['transaction_code'])."' ");
		if(!empty($params['not_hidden']))
			$query->where('AND'," `".$this->table."`.hidden = 0 ");
		if(!empty($params['hidden']))
			$query->where('AND'," `".$this->table."`.hidden = 1 ");
		if(!empty($params['date']))
			$query->where('AND'," `".$this->table."`.date = '".$this->conn->escape(date('Y-m-d',strtotime($params['date'])))."' ");
		if(!empty($params['in_range']) && !empty($params['in_range']['from']) && !empty($params['in_range']['to']))
			$query->where('AND'," '".$this->conn->escape(date('Y-m-d',strtotime($params['in_range']['from'])))."'  <= `".$this->table."`.date AND `".$this->table."`.date <= '".$this->conn->escape(date('Y-m-d',strtotime($params['in_range']['to'])))."' ");
		if(!empty($params['user_code']))
			$query->where('AND'," `user`.code = '".$this->conn->escape($params['user_code'])."' ");
			
		$isLimited = (!empty($params['limit']) && is_numeric($params['limit']) && $params['limit']>0);
		$ordered = (!empty($params['sort']) && in_array($params['sort'],['ASC','DESC']));

		$result = $this->conn->query($query->get().($ordered?' ORDER BY `'.$this->table.'`.id '.$params['sort']:'').($isLimited?' LIMIT '.$params['limit']:''));
		if($result){ 
			foreach($result as $key=>$row){
				$crypt_key = !empty($row['crypt_key'])?$row['crypt_key']:null;
				unset($result[$key]['crypt_key']);

				if(empty($params['data']) || (is_array($params['data']) && in_array('description',array_column($params['data'],'column'))) )
					$result[$key]['description'] = $this->decryptData('string',$row['description'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('amount',array_column($params['data'],'column'))) )
					$result[$key]['amount'] = $this->decryptData('number',$row['amount'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('date',array_column($params['data'],'column'))) )
					$result[$key]['date'] = date('Y-m-d H:i:s',strtotime($row['date'])); 
				if(empty($params['data']) || (is_array($params['data']) && in_array('creation_dt',array_column($params['data'],'column'))) )
					$result[$key]['creation_dt'] = date('Y-m-d H:i:s',strtotime($row['creation_dt'])); 
				if(empty($params['data']) || (is_array($params['data']) && in_array('last_update_dt',array_column($params['data'],'column'))) )
					$result[$key]['last_update_dt'] = !empty($row['last_update_dt'])? date('Y-m-d H:i:s',strtotime($row['last_update_dt'])) :null; 
				if(empty($params['data']) || (is_array($params['data']) && in_array('cancellation_dt',array_column($params['data'],'column'))) )
					$result[$key]['cancellation_dt'] = !empty($row['cancellation_dt'])? date('Y-m-d H:i:s',strtotime($row['cancellation_dt'])) :null; 
			}
			return parent::success($result);
		}
		return parent::error('Nessun pagamento trovato');
	}

	public function getsWithPaging(array $params = []){
		if(empty($params)) return $this->error("Nessun parametro fornito");

		$query = new ioQuery('select');
		$query->from($this->table);

		if(empty($params['data'])) $query->column('`'.$this->table.'`.*');
		else $query->column('`'.$this->table.'`.id')->column('`'.$this->table.'`.code');
		$query->column('`user`.crypt_key');
		$query->left_join('intent_transaction',null,'intent_transaction.id = `'.$this->table.'`.transaction_id')
		->left_join('tag',null,'tag.id = `'.$this->table.'`.tag_id')
		->left_join('wallet',null,'wallet.id = `'.$this->table.'`.wallet_id')
		->left_join('`wallet`','parent_wallet',' `wallet`.parent_wallet_id IS NOT NULL AND parent_wallet.id = `wallet`.parent_wallet_id')
		->inner_join('user',null,'user.id = `'.$this->table.'`.user_id AND user.deleted = 0')
		->where('AND',' `'.$this->table.'`.deleted = 0 ')
		->where('AND',' intent_transaction.deleted = 0 ');

		if(!empty($params['data']) && is_array($params['data'])) foreach($params['data'] as $key => $value){
			$query->column( (!empty($value['func'])?$value['func']: ( ( !empty($value['table'])?$value['table']:'`'.$this->table.'`' ).'.'.$value['column'])) ,!empty($value['alias'])?$value['alias']:null);
		}

		if(!empty($params['id']))
			$query->where('AND'," `".$this->table."`.id = ".$this->conn->escape($params['id'])." ");
		if(!empty($params['user_id']))
			$query->where('AND'," `".$this->table."`.user_id = ".$this->conn->escape($params['user_id'])." ");
		if(!empty($params['wallet_id']))
			$query->where('AND'," `".$this->table."`.wallet_id = ".$this->conn->escape($params['wallet_id'])." ");
		if(!empty($params['wallet_ids'])){
			foreach($params['wallet_ids'] as $k=>$v) $params['wallet_ids'][$k] = $this->conn->escape($v);
			$query->where('AND'," `".$this->table."`.wallet_id IN (".implode(', ',$params['wallet_ids']).") ");
		}
		if(!empty($params['transaction_id']))
			$query->where('AND'," `".$this->table."`.transaction_id = ".$this->conn->escape($params['transaction_id'])." ");
		if(!empty($params['not_hidden']))
			$query->where('AND'," `".$this->table."`.hidden = 0 ");
		if(!empty($params['hidden']))
			$query->where('AND'," `".$this->table."`.hidden = 1 ");
		if(!empty($params['date']))
			$query->where('AND'," `".$this->table."`.date = '".$this->conn->escape(date('Y-m-d',strtotime($params['date'])))."' ");
		if(!empty($params['in_range']) && !empty($params['in_range']['from']) && !empty($params['in_range']['to']))
			$query->where('AND'," '".$this->conn->escape(date('Y-m-d',strtotime($params['in_range']['from'])))."'  <= `".$this->table."`.date AND `".$this->table."`.date <= '".$this->conn->escape(date('Y-m-d',strtotime($params['in_range']['to'])))."' ");
		if(!empty($params['user_code']))
			$query->where('AND'," `user`.code = '".$this->conn->escape($params['user_code'])."' ");
		if(!empty($params['search_text'])){
			$params['search_text'] = mb_strtolower($params['search_text']);
			// if(!empty($params['group_by_category'])) $collate_ut8 = "";
			// else $collate_ut8 = "collate utf8_general_ci";
			$whrc = " LOWER(`".$this->table."`.description) LIKE LOWER('%".$this->conn->escape($params['search_text'])."%')  ";
			$whrc .= " OR LOWER(`tag`.name) LIKE LOWER('%".$this->conn->escape($params['search_text'])."%')  ";
			$query->where('AND',$whrc);
		}

		$count_query =  $query->count_query();
		$count_query->column(' COUNT(DISTINCT `'.$this->table.'`.id) ', ' rows ');

		$order_by = 'ORDER BY `'.$this->table.'`.id DESC';

		$paging_info =[];
		$results_per_page = (isset($params['results_per_page']) && is_numeric($params['results_per_page']))?$params['results_per_page']:30;
		$start= (isset($params['paging_start']) && is_numeric($params['paging_start']))?$params['paging_start']:0;
		$stats = $querycount = null;
		$stop = $results_per_page;
		$group_by=' `'.$this->table.'`.id ';

		$r = $this->conn->queryWithPaging($query->get(), $start, $stop, $order_by, $stats, $count_query->get(), $group_by); 
		$paging_info = $r['paging'];
		$result = $r['data'];

		if($result){ 
			foreach($result as $key=>$row){
				$crypt_key = !empty($row['crypt_key'])?$row['crypt_key']:null;
				unset($result[$key]['crypt_key']);

				if(empty($params['data']) || (is_array($params['data']) && in_array('description',array_column($params['data'],'column'))) )
					$result[$key]['description'] = $this->decryptData('string',$row['description'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('amount',array_column($params['data'],'column'))) )
					$result[$key]['amount'] = $this->decryptData('number',$row['amount'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('date',array_column($params['data'],'column'))) )
					$result[$key]['date'] = date('Y-m-d H:i:s',strtotime($row['date'])); 
				if(empty($params['data']) || (is_array($params['data']) && in_array('creation_dt',array_column($params['data'],'column'))) )
					$result[$key]['creation_dt'] = date('Y-m-d H:i:s',strtotime($row['creation_dt'])); 
				if(empty($params['data']) || (is_array($params['data']) && in_array('last_update_dt',array_column($params['data'],'column'))) )
					$result[$key]['last_update_dt'] = !empty($row['last_update_dt'])? date('Y-m-d H:i:s',strtotime($row['last_update_dt'])) :null; 
				if(empty($params['data']) || (is_array($params['data']) && in_array('cancellation_dt',array_column($params['data'],'column'))) )
					$result[$key]['cancellation_dt'] = !empty($row['cancellation_dt'])? date('Y-m-d H:i:s',strtotime($row['cancellation_dt'])) :null; 
			}			
			return parent::success($result,$paging_info);
		}
		return parent::error('Nessun pagamento trovato');

	}

	private function updateAmountsWalletByPayment($wallet_id,$paymentId,$user_id=null,$user_code=null,$type=null){
		if(empty($user_code) && empty($user_id)) return $this->error("Identificativo dell'utente assente");
		if(empty($wallet_id)) return $this->error("Identificativo del wallet assente");
		if(empty($paymentId)) return $this->error("Identificativo del pagamento assente");

		if(!empty($user_code) && empty($user_id)){
			$user_id = $this->getUserId($user_code);
			if(!$user_id['success']) return $user_id;
			$user_id = $user_id['data'];
		}
		$payment = $this->get([ 
			'user_id'=>$user_id,
			'wallet_id'=>$wallet_id,
			'id'=>$paymentId,
		])['data'];
		if(empty($payment)) return $this->error('Nessun pagamento è stato trovato');
		new WrapperClass(['mngWallet']);
		$thisMonth = date('Y-m');

		$mngWallet = new mngWallet($this->conn);
		$wallet = $mngWallet->get([ 'id'=>$wallet_id ])['data'];
		if(empty($wallet)) return $this->error('Errore durante l\'aggiornamento dei totali');
		if($payment['amount']>=0){ 
			if(empty($type))
				$wallet['amount_income'] += $payment['amount'];
			$wallet['amount_balance'] += $payment['amount'];
		}
		else{ 
			if(empty($type))
				$wallet['amount_expenses'] += $payment['amount'];
			$wallet['amount_balance'] += $payment['amount'];
		}
		unset($wallet['name']);
		$mngWallet->updateInfoWallet($wallet);
		
		if(date('Y-m',strtotime($payment['date'])) == $thisMonth && empty($type)){

			if(!empty($payment['tag_id']) && $payment['amount']<0){
				new WrapperClass(['mngTag']);
				$mngTag = new mngTag($this->conn);
				$tagInfo = $mngTag->get([ 'id'=>$payment['tag_id'] ])['data'];
				if(!empty($tagInfo)){
					$mngTag->edit([ 'id'=>$payment['tag_id'],'amount'=>($tagInfo['amount']+abs($payment['amount'])) ]);
				}		
			}
		}
		else{
			$mngWalletHistory = new mngWalletHistory($this->conn);
			$wallet = $mngWalletHistory->get([ 
				'wallet_id'=>$payment['wallet_id'],
				'month'=>date('m',strtotime($payment['date'])),
				'year'=>date('Y',strtotime($payment['date']))
			])['data'];
			if(empty($wallet)) return $this->error('Errore durante l\'aggiornamento dei totali');
			if($payment['amount']>=0){
				if(empty($type))
					$wallet['amount_income'] -= $payment['amount'];
				$wallet['amount_balance'] -= $payment['amount'];
			}
			else{ 
				if(empty($type))
					$wallet['amount_expenses'] -= $payment['amount'];
				$wallet['amount_balance'] += $payment['amount'];
			}
			if(!empty($payment['tag_id']) && $payment['amount']<0 && !empty($wallet['info_tag'][$payment['tag_id']]) && empty($type)){
				$wallet['info_tag'][$payment['tag_id']] += abs($payment['amount']);
			}
			$mngWalletHistory->edit($wallet);
		}
		return $this->success('Wallet aggiornato');
	}

	public function delete(array $conditions = []){
		$payment = $this->get($conditions)['data'];
		if(empty($payment)) return $this->error('Nessun pagamento è stato trovato');

		$columnNames = array_keys($this->tableSchema);
		$params=[];
		if(in_array('deleted',$columnNames)) $params['deleted'] = 1;
		if(in_array('cancellation_dt',$columnNames)) $params['cancellation_dt'] = date('Y-m-d H:i:s');
		if(empty($params)) return $this->error('Il record non può essere cancellato');
		$delete =  $this->update($params,$conditions);
		if(!$delete['success']) return $delete;
		new WrapperClass(['mngWallet']);
		$thisMonth = date('Y-m');
		if(date('Y-m',strtotime($payment['date'])) == $thisMonth){
			$mngWallet = new mngWallet($this->conn);
			$wallet = $mngWallet->get([ 'id'=>$payment['wallet_id'] ])['data'];
			if(empty($wallet)) return $delete;
			if($payment['amount']>=0 && round($wallet['amount_income'],2)!=0.00){
				$wallet['amount_income'] -= $payment['amount'];
			}
			else if($payment['amount']<0 && round($wallet['amount_expenses'],2)!=0.00){
				$wallet['amount_expenses'] -= $payment['amount'];
			}
			if($payment['amount']>=0) 
				$wallet['amount_balance'] = $wallet['amount_balance'] - abs($payment['amount']);
			else 
				$wallet['amount_balance'] = $wallet['amount_balance'] + abs($payment['amount']);
			unset($wallet['name']);
			$mngWallet->updateInfoWallet($wallet);

			if(!empty($payment['tag_id']) && $payment['amount']<0){
				new WrapperClass(['mngTag']);
				$mngTag = new mngTag($this->conn);
				$tagInfo = $mngTag->get([ 'id'=>$payment['tag_id'] ])['data'];
				if(!empty($tagInfo)){
					$mngTag->edit([ 'id'=>$payment['tag_id'],'amount'=>($tagInfo['amount']-abs($payment['amount'])) ]);
				}		
			}
		}
		else{
			$mngWalletHistory = new mngWalletHistory($this->conn);
			$wallet = $mngWalletHistory->get([ 
				'wallet_id'=>$payment['wallet_id'],
				'month'=>date('m',strtotime($payment['date'])),
				'year'=>date('Y',strtotime($payment['date']))
			])['data'];
			if(empty($wallet)) return $delete;
			if($payment['amount']>=0  && round($wallet['amount_income'],2)!=0.00){
				$wallet['amount_income'] -= $payment['amount'];
			}
			else if($payment['amount']<0 && round($wallet['amount_expenses'],2)!=0.00){ 
				$wallet['amount_expenses'] -= $payment['amount'];
			}
			if($payment['amount']>=0) 
				$wallet['amount_balance'] = $wallet['amount_balance'] - abs($payment['amount']);
			else 
				$wallet['amount_balance'] = $wallet['amount_balance'] + abs($payment['amount']);

			if(!empty($payment['tag_id']) && $payment['amount']<0 && !empty($wallet['info_tag'][$payment['tag_id']])){
				$wallet['info_tag'][$payment['tag_id']] -= abs($payment['amount']);
			}
			$mngWalletHistory->edit($wallet);
		}
		return $delete;
	}

	public function deletePayment(string $user_code, string $code){
		if(empty($user_code)) return $this->error("Identificativo dell'utente assente");
		if(empty($code)) return $this->error("Identificativo dell'utente assente");

		$user_id = $this->getUserId($user_code);
		if(!$user_id['success']) return $user_id;
		$userId = $user_id['data'];

		return $this->delete([ 'code'=>$code,'user_id'=>$userId ]);
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
			case 'string':
				if(!isset($defaultValue)) $defaultValue = "";
				if(empty($cryptKey)) return isset($data)?$data:$defaultValue;
				$userKey = EncryptionManager::decryptUserKey($cryptKey);
				try{
					return !empty($userKey) && !empty($data) ? EncryptionManager::decryptData($data, $userKey) : (isset($data)?$data:$defaultValue);
				}catch(Exception $e){
					return isset($data)?$data:$defaultValue;
				}
			break;
		}
	}

}