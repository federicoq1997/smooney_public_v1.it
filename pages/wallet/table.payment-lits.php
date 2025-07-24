<?php
	require_once(dirname(__FILE__).'/../../components/auth.php');
	require_once(dirname(__FILE__).'/../../components/_global_variables.php');

	$search_text = !empty($_REQUEST['search_text']) ? $_REQUEST['search_text'] : null;
	$results_per_page = !empty($_REQUEST['results_per_page']) ? $_REQUEST['results_per_page'] : null;
	$paging_start = !empty($_REQUEST['paging_start']) ? $_REQUEST['paging_start'] : null;
	$sort = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : null;
	$order = !empty($_REQUEST['order']) ? $_REQUEST['order'] : null;
	$walletId = !empty($_REQUEST['walletId']) ? $_REQUEST['walletId'] : null;

	new WrapperClass(['mngWallet']);
	$mngWallet = new mngWallet($ioConn);
	$wallet = $mngWallet->get([ 'user_code'=>$USERDATA['UserId'],'code'=>$walletId ])['data'];
	if(empty($wallet)) die(json_encode([ 'total'=>1,'rows'=>[] ]));
	$child_wallets = $mngWallet->gets([ 'user_code'=>$USERDATA['UserId'],'parent_wallet_id'=>$wallet['id'] ])['data'];

	new WrapperClass(['mngPayment']);
	$mngPayment = new mngPayment($ioConn);
	$payments = $mngPayment->getsWithPaging([ 
		'user_code'=>$USERDATA['UserId'],
		'wallet_ids'=>array_merge([$wallet['id']],array_column($child_wallets,'id')),
		'search_text'=>$search_text,
		'not_hidden'=>true,

		'data'=>[
			[ 'column'=>'type' ],
			[ 'column'=>'sign' ],
			[ 'column'=>'amount' ],
			[ 'column'=>'description' ],
			[ 'column'=>'date' ],
			[ 'table'=>'tag','column'=>'name','alias'=>'type_name' ],
			[ 'table'=>'tag','column'=>'color','alias'=>'type_color' ],
			[ 'table'=>'intent_transaction','column'=>'code','alias'=>'intent_transaction_code' ],
		],

		'results_per_page'=>$results_per_page,
		'paging_start'=>$paging_start,
		'sort'=>$sort,
		'order'=>$order,
	]);
	$paginationInfo = !empty($payments['paging'])?$payments['paging']:null;
	$payments = $payments['data'];

	$json = [
		'columns'=>[],
		'total' => !empty($paginationInfo) ? $paginationInfo['Count'] : (!empty($payments) ? count($payments) : 1),
		'rows' => [],
	];

	if(empty($payments)) die(json_encode($json));
	foreach($payments as $payment){
		$res = [];
		$res['id'] = '<span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter">#'.$payment['id'].'</span>';
		$res['description'] = '<span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter">'.($payment['description']).'</span>';
		$res['type']='<span class="badge hp-text-color-black-100 rounded-pill px-8 border-0 badge-none " style="background:'.$payment['type_color'].' !important;color:'.(getBestTextColor('#181818',!empty($payment['type_color'])?$payment['type_color']:'#12a3af')).' !important;">'.$payment['type_name'].'</span>';
		$res['amount']='
		<div class="d-flex align-content-center justify-content-start">
			<i class="hp-text-color-dark-0  me-10 '.($payment['amount']>=0?'text-success-3 iconly-Light-ArrowUp':'iconly-Light-ArrowDown text-danger-3').' "></i>
			<span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter">€ '.number_format($payment['amount'],2,',','.').'</span>
		</div>
		';
		$res['date'] = '<span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter">'.formatLanguage(date_create($payment['date']),'D d F Y',$language).'</span>';
		$res['buttons'] = '
			<i class="iconly-Light-Delete hp-cursor-pointer hp-transition hp-hover-text-color-danger-1 text-black-80 btn-delete-payment mx-1" data-code="'.$payment['code'].'" style="font-size: 24px;"></i>
		';
		$json['rows'][] = $res;
	}
	die(json_encode($json));
?>