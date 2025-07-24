<?php
class mngIntentTransaction extends CRUDBase{
	private $table = 'intent_transaction';
	private $api_key= '018cefe0-96e3-7fcc-8d79-796139604819';
	private $token= '018cefe0-c00c-78dc-970b-6945dd450044';

	public $types = [
		[
			'title'=>['it'=>'Entrata','en'=>'Income'],
			'sign'=>1,
			'id'=>0
		],
		[
			'title'=>['it'=>'Spesa','en'=>'Expense'],
			'sign'=>-1,
			'id'=>0
		],
		[
			'title'=>['it'=>'Giro Conto','en'=>'Internal Transfer'],
			'sign'=>0,
			'id'=>1
		]
	];
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
	private function new(array $params = []) {
		if(empty($params['user_code']) && empty($params['user_id']) ) return $this->error("Identificativo dell\'utente assente");
		if(empty($params['amount']) ) return $this->error("Importo assente");
		if(empty($params['date']) && empty($params['is_recurry']) ) return $this->error("Giorno di validazione");
		if(empty($params['code']) ) $params['code'] = $this->conn->getUuid();

		$params['amount'] = floatval(strval($params['amount']));
		if($params['amount']>0) $params['sign'] = 1;
		else $params['sign'] = -1;

		if(empty($params['user_id'])){
			$user_id = $this->getUserId($params['user_code']);
			if(!$user_id['success']) return $user_id;
			$params['user_id'] = $user_id['data'];
		}
		if(isset($params['description']) && (!empty($params['id']) || !empty($params['user_id']))) 
			$params['description'] = $this->cryptData($params['description'],!empty($params['id'])?$params['id']:null,!empty($params['user_id'])?$params['user_id']:null);
		if(isset($params['amount']) && (!empty($params['id']) || !empty($params['user_id']))) 
			$params['amount'] = $this->cryptData($params['amount'],!empty($params['id'])?$params['id']:null,!empty($params['user_id'])?$params['user_id']:null);
		if(isset($params['ancillary_expenses']) && (!empty($params['id']) || !empty($params['user_id']))) 
			$params['ancillary_expenses'] = $this->cryptData($params['ancillary_expenses'],!empty($params['id'])?$params['id']:null,!empty($params['user_id'])?$params['user_id']:null);

		$result = parent::create($params);
		if(!$result['success'] || empty($result['data'])) return $result;
		return parent::success(array_merge($result['data'],['code'=>$params['code']]));
	}
	private function edit(array $params = []) {
		if(empty($params['id']) && empty($params['code']) && empty($params['user_id']) ) return $this->error("Identificativo assente");

		if(!empty($params['user_code']) ){
			$user_id = $this->getUserId($params['user_code']);
			if(!$user_id['success']) return $user_id;
			$conditions['user_id'] = $user_id['data'];
		}

		$conditions = [];
		if(!empty($params['id'])) $conditions['id'] = $params['id'];
		if(!empty($params['code'])) $conditions['code'] = $params['code'];
		if(!empty($params['user_id'])) $conditions['user_id'] = $params['user_id'];

		if(isset($params['description']) && (!empty($params['id']) || !empty($params['user_id']))) 
			$params['description'] = $this->cryptData($params['description'],!empty($params['id'])?$params['id']:null,!empty($params['user_id'])?$params['user_id']:null);
		if(isset($params['amount']) && (!empty($params['id']) || !empty($params['user_id']))) 
			$params['amount'] = $this->cryptData($params['amount'],!empty($params['id'])?$params['id']:null,!empty($params['user_id'])?$params['user_id']:null);
		if(isset($params['ancillary_expenses']) && (!empty($params['id']) || !empty($params['user_id']))) 
			$params['ancillary_expenses'] = $this->cryptData($params['ancillary_expenses'],!empty($params['id'])?$params['id']:null,!empty($params['user_id'])?$params['user_id']:null);
		recursive_unsets($params,['id','user_id','code']);
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
		->left_join('wallet','wallet_source','wallet_source.id = `'.$this->table.'`.source_wallet_id AND wallet_source.deleted = 0')
		->left_join('wallet','wallet_dest','wallet_dest.id = `'.$this->table.'`.dest_wallet_id AND wallet_dest.deleted = 0')
		->where('AND',' `wallet_dest`.deleted = 0 ')
		->where('AND',' `'.$this->table.'`.deleted = 0 ')
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
		if(isset($params['type']))
			$query->where('AND'," `".$this->table."`.type = ".$this->conn->escape($params['type'])." ");
		if(isset($params['source_wallet_id']))
			$query->where('AND'," `".$this->table."`.source_wallet_id = ".$this->conn->escape($params['source_wallet_id'])." ");
		if(isset($params['dest_wallet_id']))
			$query->where('AND'," `".$this->table."`.dest_wallet_id = ".$this->conn->escape($params['dest_wallet_id'])." ");
		if(!empty($params['dest_wallet_ids'])){
			foreach($params['dest_wallet_ids'] as $k=>$v) $params['dest_wallet_ids'][$k] = $this->conn->escape($v);
			$query->where('AND'," `".$this->table."`.dest_wallet_id IN (".implode(', ',$params['dest_wallet_ids']).") ");
		}
		if(isset($params['postdated_wallet_id']))
			$query->where('AND'," ( `".$this->table."`.source_wallet_id IS NOT NULL AND  `".$this->table."`.source_wallet_id = ".$this->conn->escape($params['postdated_wallet_id']).") OR ( `".$this->table."`.dest_wallet_id = ".$this->conn->escape($params['postdated_wallet_id'])." ) ");
		if(!empty($params['postdated_wallet_ids'])){
			foreach($params['postdated_wallet_ids'] as $k=>$v) $params['postdated_wallet_ids'][$k] = $this->conn->escape($v);
			$query->where('AND'," ( `".$this->table."`.source_wallet_id IS NOT NULL AND  `".$this->table."`.source_wallet_id IN (".implode(', ',$params['postdated_wallet_ids']).") ) OR ( `".$this->table."`.dest_wallet_id IN (".implode(', ',$params['postdated_wallet_ids']).") ) ");
		}
		if(isset($params['is_recurry']))
			$query->where('AND'," `".$this->table."`.is_recurry = 1 ");
		if(isset($params['not_recurry']))
			$query->where('AND'," `".$this->table."`.is_recurry = 0 ");
		if(isset($params['postdated']))
			$query->where('AND'," `".$this->table."`.postdated = 1 ");
		if(isset($params['not_postdated']))
			$query->where('AND'," `".$this->table."`.postdated = 0 ");
		if(!empty($params['user_code']))
			$query->where('AND'," `user`.code = '".$this->conn->escape($params['user_code'])."' ");
		if(!empty($params['valid_recurry']) && is_bool($params['valid_recurry']))
			$query->where('AND',"  `".$this->table."`.to >= '".$this->conn->escape(date('Y-m-d'))."' AND `".$this->table."`.from <= '".$this->conn->escape(date('Y-m-d'))."' ");
		if(!empty($params['valid_recurry']) && is_string($params['valid_recurry'])){
			$query->where('AND',"  `".$this->table."`.to >= '".$this->conn->escape(date('Y-m-d',strtotime($params['valid_recurry'])))."' AND `".$this->table."`.from <= '".$this->conn->escape(date('Y-m-d',strtotime($params['valid_recurry'])))."' ");
		}
		if(!empty($params['valid_postdated']) && is_bool($params['valid_postdated']))
			$query->where('AND',"  `".$this->table."`.date >= '".$this->conn->escape(date('Y-m-d'))."' ");
		if(!empty($params['valid_postdated']) && is_string($params['valid_postdated']))
			$query->where('AND',"  `".$this->table."`.date >= '".$this->conn->escape(date('Y-m-d',strtotime($params['valid_postdated'])))."' ");
		if(!empty($params['in_range']) && !empty($params['in_range']['from']) && !empty($params['in_range']['to']))
			$query->where('AND'," ( '".$this->conn->escape(date('Y-m-d',strtotime($params['in_range']['from'])))."'  <= `".$this->table."`.date AND `".$this->table."`.date <= '".$this->conn->escape(date('Y-m-d',strtotime($params['in_range']['to'])))."') 
				OR ( `".$this->table."`.is_recurry = 1 AND '".$this->conn->escape(date('Y-m-d',strtotime($params['in_range']['from'])))."'  <= `".$this->table."`.from AND `".$this->table."`.to <= '".$this->conn->escape(date('Y-m-d',strtotime($params['in_range']['to'])))."' ) ");
		$result = $this->conn->query($query->get());
		if($result){ 
			foreach($result as $key => $row){
				$crypt_key = !empty($row['crypt_key'])?$row['crypt_key']:null;
				unset($result[$key]['crypt_key']);

				if(empty($params['data']) || (is_array($params['data']) && in_array('description',array_column($params['data'],'column'))) )
					$result[$key]['description'] = $this->decryptData('string',$row['description'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('amount',array_column($params['data'],'column'))) )
					$result[$key]['amount'] = $this->decryptData('number',$row['amount'],$crypt_key);
				if(empty($params['data']) || (is_array($params['data']) && in_array('ancillary_expenses',array_column($params['data'],'column'))) )
					$result[$key]['ancillary_expenses'] = isset($row['ancillary_expenses'])? $this->decryptData('number',$row['ancillary_expenses'],$crypt_key) : null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('from',array_column($params['data'],'column'))) )
					$result[$key]['from']= !empty($row['from'])? date('Y-m-d H:i:s',strtotime($row['from'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('last_executed',array_column($params['data'],'column'))) )
					$result[$key]['last_executed']= !empty($row['last_executed'])? date('Y-m-d',strtotime($row['last_executed'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('to',array_column($params['data'],'column'))) )
					$result[$key]['to']= !empty($row['to'])? date('Y-m-d H:i:s',strtotime($row['to'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('date',array_column($params['data'],'column'))) )
					$result[$key]['date']= !empty($row['date'])? date('Y-m-d',strtotime($row['date'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('creation_dt',array_column($params['data'],'column'))) )
					$result[$key]['creation_dt']= date('Y-m-d H:i:s',strtotime($row['creation_dt']));
				if(empty($params['data']) || (is_array($params['data']) && in_array('last_update_dt',array_column($params['data'],'column'))) )
					$result[$key]['last_update_dt']= !empty($row['last_update_dt'])? date('Y-m-d H:i:s',strtotime($row['last_update_dt'])):null;
				if(empty($params['data']) || (is_array($params['data']) && in_array('cancellation_dt',array_column($params['data'],'column'))) )
					$result[$key]['cancellation_dt']= !empty($row['cancellation_dt'])? date('Y-m-d H:i:s',strtotime($row['cancellation_dt'])):null;
			}
			
			return parent::success($result);
		}
		return parent::error('Nessuna transazione trovata');
	}

	public function generate(array $params = []):array
	{
		if(empty($params['user_code']) ) return $this->error("Identificativo dell\'utente assente");
		if(empty($params['amount']) ) return $this->error("Importo assente");
		if(empty($params['dest_wallet_id']) ) return $this->error("Identificativo del wallet assente");
		if(!isset($params['type']) ) return $this->error("Tipologia di transazione assente");
		if(empty($params['date']) && empty($params['is_recurry'])) return $this->error("Giorno assente");
		if($params['type'] == 1 && empty($params['source_wallet_id'])) return $this->error("Identificativo del wallet da cui prelevare assente");
		if($params['type'] == 0) unset($params['source_wallet_id']);

		if(!empty($params['is_recurry']) && ( empty($params['from']) || empty($params['to']) || empty($params['repeat_each']) ) ) return $this->error("Informazioni per pagamenti ricorrenti incomplete");
		
		if(empty($params['is_recurry']) && !empty($params['date']) && date('Y-m-d')<date('Y-m-d',strtotime($params['date'])) )
			$params['postdated'] = 1;
		if(!empty($params['is_recurry']) || !empty($params['postdated'])) $params['status'] = 1;
		else $params['status'] = 2;
		// die(json_encode($params));

		$user_id = $this->getUserId($params['user_code']);
		if(!$user_id['success']) return $user_id;
		$params['user_id'] = $user_id['data'];
		if(isset($params['ancillary_expenses']) && empty($params['ancillary_expenses'])) unset($params['ancillary_expenses']);
		$transaction = $this->new($params);
		if(!$transaction['success']) return $transaction;
		$transaction = $transaction['data'];

		if(empty($params['postdated']) && empty($params['is_recurry'])){
			new WrapperClass(['mngPayment']);
			$mngPayment = new mngPayment($this->conn);
			$mngPayment->new([
				'type'=>$params['type'],
				'description'=>!empty($params['description'])?$params['description']:null,
				'user_id'=>$params['user_id'],
				'transaction_id'=>$transaction['id'],
				'wallet_id'=>$params['dest_wallet_id'],
				'ext_code'=>!empty($params['ext_code'])?$params['ext_code']:null,
				'tag_id'=>!empty($params['tag_id'])?$params['tag_id']:null,
				'amount'=>$params['amount'],
				'currency_code'=>!empty($params['currency_code'])?$params['currency_code']:null,
				'date'=>$params['date'],
			]);
			if($params['type'] == 1 && !empty($params['source_wallet_id'])){
				$mngPayment->new([
					'type'=>$params['type'],
					'description'=>!empty($params['description'])?$params['description']:null,
					'user_id'=>$params['user_id'],
					'transaction_id'=>$transaction['id'],
					'wallet_id'=>$params['source_wallet_id'],
					'ext_code'=>!empty($params['ext_code'])?$params['ext_code']:null,
					'tag_id'=>!empty($params['tag_id'])?$params['tag_id']:null,
					'amount'=>-1*abs($params['amount']),
					'currency_code'=>!empty($params['currency_code'])?$params['currency_code']:null,
					'date'=>$params['date'],
					'hidden'=>!empty($params['hidden'])?1:0,
				]);
				if(isset($params['ancillary_expenses'])){
					$mngPayment->new([
						'type'=>0,
						'description'=>getTranslationLanguages(['it'=>'Spese accessorie','en'=>'Ancillary expenses'], !empty($params['language']) && in_array($params['language'],['it','en'])?$params['language']:'en'),
						'user_id'=>$params['user_id'],
						'transaction_id'=>$transaction['id'],
						'wallet_id'=>$params['source_wallet_id'],
						'ext_code'=>!empty($params['ext_code'])?$params['ext_code']:null,
						'tag_id'=>!empty($params['tag_id'])?$params['tag_id']:null,
						'amount'=>-1*abs( $params['ancillary_expenses']),
						'currency_code'=>!empty($params['currency_code'])?$params['currency_code']:null,
						'date'=>$params['date'],
						'hidden'=>!empty($params['hidden'])?1:0,
					]);
				}
			}
		}
		if(!empty($params['postdated']) || !empty($params['is_recurry'])){
			if(!empty($params['source_wallet_id']))
				$this->triggerUpdateForecast($params['user_id'],$params['source_wallet_id']);
			if(!empty($params['dest_wallet_id']))
				$this->triggerUpdateForecast($params['user_id'],$params['dest_wallet_id']);
		}
		return $this->success($transaction);
	}

	public function editIntentTransaction(string $user_code, string $code,array $params = []){
		$userId = null;
		if(!empty($user_code) ){
			$user_id = $this->getUserId($user_code);
			if(!$user_id['success']) return $user_id;
			$userId = $user_id['data'];
		}

		$transaction = $this->get([ 'user_id'=>$userId,'code'=>$code ])['data'];
		if(empty($transaction)) return $this->error('Nessuna transazione trovata');

		$result = $this->edit( array_merge(
			[
				'user_id'=>$userId,
				'code'=>$code,
			],
			$params
		));
		if(!empty($transaction['source_wallet_id']))
			$this->triggerUpdateForecast($userId,$transaction['source_wallet_id']);
		if(!empty($transaction['dest_wallet_id']))
			$this->triggerUpdateForecast($userId,$transaction['dest_wallet_id']);
		if(!empty($params['source_wallet_id']) && $params['source_wallet_id']!=$transaction['source_wallet_id'])
			$this->triggerUpdateForecast($userId,$params['source_wallet_id']);
		if(!empty($params['dest_wallet_id']) && $params['dest_wallet_id']!=$transaction['dest_wallet_id'])
			$this->triggerUpdateForecast($userId,$params['dest_wallet_id']);
		return $result;
	}

	public function deleteTransaction(string $user_code, string $code){
		if(empty($user_code)) return $this->error("Identificativo dell'utente assente");
		if(empty($code)) return $this->error("Identificativo dell'utente assente");

		$userId = null;
		if(!empty($user_code) ){
			$user_id = $this->getUserId($user_code);
			if(!$user_id['success']) return $user_id;
			$userId = $user_id['data'];
		}

		$transaction = $this->get([ 'user_id'=>$userId,'code'=>$code ])['data'];
		if(empty($transaction)) return $this->error('Nessuna transazione trovata');

		new WrapperClass(['mngPayment']);
		$mngPayment = new mngPayment($this->conn);
		$payments = $mngPayment->gets([ 'transaction_code'=>$code,'user_id'=>$userId ])['data'];
		if(!empty($payments)) return $this->error('Ci sono dei pagamenti collegati a questa transazione');

		return $this->edit([
			'user_id'=>$userId,
			'code'=>$code,
			'deleted'=>1,
			'cancellation_dt'=>date('Y-m-d H:i:s')
		]);
	}

	public function cronProcessRecurrentPayments(){
		$intentTransactions = $this->gets([ 'is_recurry'=>true,'valid_recurry'=>true ])['data'];
		if(empty($intentTransactions)) return $this->error('Nessuna transazione ricorrente');
		new WrapperClass(['mngPayment']);
		$mngPayment = new mngPayment($this->conn);

		foreach($intentTransactions as $intentTransaction){
			if(empty($intentTransaction['repeat_each']) || !in_array($intentTransaction['repeat_each'],['month','year']) ) continue;
			$last_executed = !empty($intentTransaction['last_executed'])?$intentTransaction['last_executed']:'1970-01-01';
			if($intentTransaction['repeat_each'] == 'month' && !empty($intentTransaction['date']) && date('d') != date('d',strtotime($intentTransaction['date'])) ) continue;
			if($intentTransaction['repeat_each'] == 'year' && !empty($intentTransaction['date']) && date('m-d') != date('m-d',strtotime($intentTransaction['date'])) ) continue;
			
			if( ( $intentTransaction['repeat_each'] == 'month' && date('Y-m')!=date('Y-m',strtotime($last_executed)) ) || ( $intentTransaction['repeat_each'] == 'year' && date('Y')!=date('Y',strtotime($last_executed)) ) ){
				$mngPayment->new([
					'description'=>!empty($intentTransaction['description'])?$intentTransaction['description']:null,
					'user_id'=>$intentTransaction['user_id'],
					'transaction_id'=>$intentTransaction['id'],
					'wallet_id'=>$intentTransaction['dest_wallet_id'],
					'ext_code'=>!empty($intentTransaction['ext_code'])?$intentTransaction['ext_code']:null,
					'tag_id'=>!empty($intentTransaction['tag_id'])?$intentTransaction['tag_id']:null,
					'amount'=>$intentTransaction['amount'],
					'currency_code'=>!empty($intentTransaction['currency_code'])?$intentTransaction['currency_code']:null,
					'date'=>date('Y-m-d'),
				]);
			}else continue;
			$intentTransaction['last_executed'] = date('Y-m-d');
			$this->edit($intentTransaction);
		}
	}
	public function cronProcessPostdatedPayments(){
		$intentTransactions = $this->gets([ 'postdated'=>true,'valid_postdated'=>true ])['data'];
		if(empty($intentTransactions)) return $this->error('Nessuna transazione ricorrente');
		new WrapperClass(['mngPayment']);
		$mngPayment = new mngPayment($this->conn);

		foreach($intentTransactions as $intentTransaction){
			if(empty($intentTransaction['date']) || date('Y-m-d') != date('Y-m-d',strtotime($intentTransaction['date'])) ) continue;
			$mngPayment->new([
				'description'=>!empty($intentTransaction['description'])?$intentTransaction['description']:null,
				'user_id'=>$intentTransaction['user_id'],
				'transaction_id'=>$intentTransaction['id'],
				'wallet_id'=>$intentTransaction['dest_wallet_id'],
				'ext_code'=>!empty($intentTransaction['ext_code'])?$intentTransaction['ext_code']:null,
				'tag_id'=>!empty($intentTransaction['tag_id'])?$intentTransaction['tag_id']:null,
				'amount'=>$intentTransaction['amount'],
				'currency_code'=>!empty($intentTransaction['currency_code'])?$intentTransaction['currency_code']:null,
				'date'=>date('Y-m-d'),
			]);
			$intentTransaction['status'] = 2;
			$intentTransaction['postdated'] = 0;
			$this->edit($intentTransaction);
			if($intentTransaction['type'] == 1 && !empty($intentTransaction['source_wallet_id'])){
				$mngPayment->new([
					'type'=>$intentTransaction['type'],
					'description'=>!empty($intentTransaction['description'])?$intentTransaction['description']:null,
					'user_id'=>$intentTransaction['user_id'],
					'transaction_id'=>$intentTransaction['id'],
					'wallet_id'=>$intentTransaction['source_wallet_id'],
					'ext_code'=>!empty($intentTransaction['ext_code'])?$intentTransaction['ext_code']:null,
					'tag_id'=>!empty($intentTransaction['tag_id'])?$intentTransaction['tag_id']:null,
					'amount'=>-1*abs($intentTransaction['amount']),
					'currency_code'=>!empty($intentTransaction['currency_code'])?$intentTransaction['currency_code']:null,
					'date'=>date('Y-m-d')
				]);
				if(isset($intentTransaction['ancillary_expenses']) && empty($intentTransaction['ancillary_expenses'])) unset($intentTransaction['ancillary_expenses']);
				if(isset($intentTransaction['ancillary_expenses'])){
					$mngPayment->new([
						'type'=>0,
						'description'=>getTranslationLanguages(['it'=>'Spese accessorie','en'=>'Ancillary expenses'], !empty($intentTransaction['language']) && in_array($intentTransaction['language'],['it','en'])?$intentTransaction['language']:'en'),
						'user_id'=>$intentTransaction['user_id'],
						'transaction_id'=>$intentTransaction['id'],
						'wallet_id'=>$intentTransaction['source_wallet_id'],
						'ext_code'=>!empty($intentTransaction['ext_code'])?$intentTransaction['ext_code']:null,
						'tag_id'=>!empty($intentTransaction['tag_id'])?$intentTransaction['tag_id']:null,
						'amount'=>-1*abs( $intentTransaction['ancillary_expenses']),
						'currency_code'=>!empty($intentTransaction['currency_code'])?$intentTransaction['currency_code']:null,
						'date'=>date('Y-m-d')
					]);
				}
			}
		}
	}

	public function deactivateRecurrentTransaction(string $user_code, string $code){
		$userId = null;
		if(!empty($user_code) ){
			$user_id = $this->getUserId($user_code);
			if(!$user_id['success']) return $user_id;
			$userId = $user_id['data'];
		}

		$transaction = $this->get([ 'user_id'=>$userId,'code'=>$code,'is_recurry'=>true ])['data'];
		if(empty($transaction)) return $this->error('Nessuna transazione trovata');

		$result =  $this->edit([
			'user_id'=>$userId,
			'code'=>$code,
			'deleted'=>1,
			'cancellation_dt'=>date('Y-m-d H:i:s')
		]);
		if(!empty($transaction['source_wallet_id']))
			$this->triggerUpdateForecast($userId,$transaction['source_wallet_id']);
		if(!empty($transaction['dest_wallet_id']))
			$this->triggerUpdateForecast($userId,$transaction['dest_wallet_id']);
		return $result;
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


	private function triggerUpdateForecast($user_id,$wallet_id){
		$url = 'https://smooney.it/api/v1/cron/update-forecast/'.$user_id.'/'.$wallet_id;
		$headers = ['--header="Api-Key: '.$this->api_key.'"','--header="Token: '.$this->token.'"'];
		$command = 'wget -qO- --no-check-certificate --timeout=100 '.implode(' ',$headers).' '.$url;
		exec( $command . ' > /dev/null 2>&1 &');
	}

}