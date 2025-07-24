<?php
class mngSidebar extends CRUDBase{
	private $table = 'sidebar';
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
	public function gets(array $params = []) {

		$query = new ioQuery('select');
		$query->from($this->table);

		if(empty($params['data'])) $query->column('`'.$this->table.'`.*');
		else $query->column('`'.$this->table.'`.id')->column('`'.$this->table.'`.code');

		$query->where('AND',' `'.$this->table.'`.deleted = 0 ')
		->group_by(' `'.$this->table.'`.code ')
		->order_by(' `'.$this->table.'`.sort ','ASC');

		if(!empty($params['data']) && is_array($params['data'])) foreach($params['data'] as $key => $value){
			$query->column( (!empty($value['func'])?$value['func']: ( ( !empty($value['table'])?$value['table']:'`'.$this->table.'`' ).'.'.$value['column'])) ,!empty($value['alias'])?$value['alias']:null);
		}
		if(!empty($params['search_text'])) {
			$query->where('AND', "  LOWER(`".$this->table."`.`name`) LIKE LOWER('%".$this->conn->escape($params['search_text'])."%')   ");
		}
		if(!empty($params['alias'])) {
			$query->where('AND', " `".$this->table."`.`alias` = '".$this->conn->escape($params['alias'])."'   ");
		}
		$result = $this->conn->query($query->get());
		if($result){
			if(empty($params['data']) || (is_array($params['data']) && in_array('creation_dt',array_column($params['data'],'column'))) )
				map_array_field($result,'creation_dt',function($row){ return !empty($row['creation_dt'])?date('Y-m-d H:i:s',strtotime($row['creation_dt'])):null; });
			if(empty($params['data']) || (is_array($params['data']) && in_array('last_update_dt',array_column($params['data'],'column'))) )
				map_array_field($result,'last_update_dt',function($row){ return !empty($row['last_update_dt'])?date('Y-m-d H:i:s',strtotime($row['last_update_dt'])):null; });
			if(empty($params['data']) || (is_array($params['data']) && in_array('cancellation_dt',array_column($params['data'],'column'))) )
				map_array_field($result,'cancellation_dt',function($row){ return !empty($row['cancellation_dt'])?date('Y-m-d H:i:s',strtotime($row['cancellation_dt'])):null; });
			if(empty($params['data']) || (is_array($params['data']) && in_array('name',array_column($params['data'],'column'))) )
				map_array_field($result,'name',function($row){ return !empty($row['name'])?_json_decode($row['name'],true):null; });
			
			if(empty($params['not-collapsed'])){
				$result = array_column($result,null,'id');
				foreach($result as $k=>$info){
					if(empty($info['parent_id']) || empty($result[$info['parent_id']])) continue;
					if(empty($result[$info['parent_id']]['child'])) $result[$info['parent_id']]['child'] = [];
					$result[$info['parent_id']]['child'][] = $info;
					unset($result[$k]);
				}
			}

			return parent::success($result);
		}
		return parent::error('Nessun metodo trovato');
	}


	public function searchPage($params){
		$default_page = $this->gets([ 
			'search_text'=>!empty($params['search_text'])?$params['search_text']:null,
			'not-collapsed'=>!empty($params['not-collapsed']),
		])['data'];

		if(!empty($params['user_code'])) $userId = $this->getUserId($params['user_code']);
		if(empty($userId)) return parent::success($default_page);
		if(empty($params['search_text'])) return parent::success($default_page);
		
		$allPages = $this->gets()['data'];
		$result = $default_page;

		$base_path_wallet = array_column($allPages,null,'code')['8045e5e2-f194-11ee-adf2-4551c5eb4bb3'];
		$page_wallet = new ioQuery('select');
		$page_wallet->from('wallet')
		->column('wallet.id')
		->column('wallet.code')
		->column('wallet.name')
		->column('wallet.parent_wallet_id')
		->column('`parent_wallet`.name','parent_wallet_name')
		->column('`parent_wallet`.code','parent_wallet_code')
		->left_join('`wallet`','parent_wallet',' `wallet`.parent_wallet_id IS NOT NULL AND parent_wallet.id = `wallet`.parent_wallet_id AND parent_wallet.deleted = 0 ')
		->where('AND', "  LOWER(`wallet`.`name`) LIKE LOWER('%".$this->conn->escape($params['search_text'])."%')   ")
		->where('AND','wallet.deleted = 0');
		$page_wallet = $this->conn->query($page_wallet->get());
		if(empty($page_wallet)) $page_wallet = [];
		else{ 
			$page_wallet = array_filter($page_wallet,function($r){
				return empty($r['parent_wallet_id']) || ( !empty($r['parent_wallet_id']) && !empty($r['parent_wallet_code']) );
			});
			$page_wallet = array_map(function($row) use($base_path_wallet){
				if(!empty($row['parent_wallet_name']))
					$row['name'] = $row['parent_wallet_name'].' | '.$row['name'];
				if(!empty($row['parent_wallet_code']))
					$row['code'] = $row['parent_wallet_code'];
				
				return [
					'code'=>$row['code'],
					'name'=>['it'=>'Wallet | '.$row['name'],'en'=>'Wallet | '.$row['name']],
					'url'=>$base_path_wallet['url'].'/'.$row['code'],
				];
			},$page_wallet);
		}
		$result = array_merge($result,$page_wallet);

		$base_path_tags = array_column($allPages,null,'code')['3ec9183e-f1ba-11ee-9344-0acad7d5a8c7'];
		$page_tag = new ioQuery('select');
		$page_tag->from('tag')
		->column('tag.id')
		->column('tag.name')
		->where('AND', "  LOWER(`tag`.`name`) LIKE LOWER('%".$this->conn->escape($params['search_text'])."%')   ")
		->where('AND','tag.deleted = 0');
		$page_tag = $this->conn->query($page_tag->get());
		if(empty($page_tag)) $page_tag = [];
		else $page_tag = array_map(function($row) use($base_path_tags){
			return [
				'id'=>$row['id'],
				'name'=>['it'=>'Tag | '.$row['name'],'en'=>'Tag | '.$row['name']],
				'url'=>$base_path_tags['url'].'?id='.$row['id'],
			];
		},$page_tag);
		$result = array_merge($result,$page_tag);

		return parent::success($result);
	}

}