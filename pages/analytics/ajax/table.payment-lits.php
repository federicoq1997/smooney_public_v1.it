<?php
	require_once(dirname(__FILE__).'/../../../components/auth.php');
	require_once(dirname(__FILE__).'/../../../components/_global_variables.php');

	$search_text = !empty($_REQUEST['search_text']) ? $_REQUEST['search_text'] : null;
	$results_per_page = !empty($_REQUEST['results_per_page']) ? $_REQUEST['results_per_page'] : (empty($_REQUEST['download_csv'])?false:1000);
	$paging_start = !empty($_REQUEST['paging_start']) ? $_REQUEST['paging_start'] : null;
	$sort = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : null;
	$order = !empty($_REQUEST['order']) ? $_REQUEST['order'] : null;

	$to = !empty($_REQUEST['to']) ? $_REQUEST['to'] : date('Y-m-d'); 
	$from = !empty($_REQUEST['from']) ? $_REQUEST['from'] : date('Y-m-d',strtotime($to.' -30days'));
	$walletIds = !empty($_REQUEST['walletIds'])?$_REQUEST['walletIds']:[];
	$columns = include __DIR__.'/table-headers.php';

	new WrapperClass(['mngPayment']);
	$mngPayment = new mngPayment($ioConn);

	$json = [
		'columns'=>[],
		'total' => 1,
		'rows' => [],
	];

	$has_results = true;
	do{

		$payments = $mngPayment->getsWithPaging([ 
			'user_code'=>$USERDATA['UserId'],
			'wallet_ids'=>$walletIds,
			'in_range'=>[ 'from'=>$from,'to'=>$to ],
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
				[ 'table'=>'wallet','column'=>'name','alias'=>'wallet_name' ],
				[ 'table'=>'parent_wallet','column'=>'name','alias'=>'parent_wallet_name' ],
			],

			'results_per_page'=>$results_per_page,
			'paging_start'=>$paging_start,
			'sort'=>$sort,
			'order'=>$order,
		]);
		$paginationInfo = !empty($payments['paging'])?$payments['paging']:null;
		$payments = $payments['data'];

		$json['total'] = !empty($paginationInfo) ? intval($paginationInfo['Count']) : (!empty($payments) ? count($payments) : 1);

		if(!empty($payments)) {
			$has_results = true;
			foreach($payments as $payment){
				$res = [];
				$res['id'] = '<span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter">#'.$payment['id'].'</span>';
				$res['description'] = '<span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter">'.($payment['description']).'</span>';
				$res['wallet'] = '<span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter">'.((!empty($payment['parent_wallet_name'])?$payment['parent_wallet_name'].' | ':'').$payment['wallet_name']).'</span>';
				$res['type']='<span class="badge hp-text-color-black-100 rounded-pill px-8 border-0 badge-none " style="background:'.$payment['type_color'].' !important;color:'.(getBestTextColor('#181818',!empty($payment['type_color'])?$payment['type_color']:'#12a3af')).' !important;">'.$payment['type_name'].'</span>';
				$res['amount']='
				<div class="d-flex align-content-center justify-content-start">
					<i class="hp-text-color-dark-0  me-10 '.($payment['amount']>=0?'text-success-3 iconly-Light-ArrowUp':'iconly-Light-ArrowDown text-danger-3').' "></i>
					<span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter">€ '.number_format($payment['amount'],2,',','.').'</span>
				</div>
				';
				$res['date'] = '<span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-medium">'.formatLanguage(date_create($payment['date']),'D d',$language).' '.ucfirst( formatLanguage(date_create($payment['date']),'M',$language) ).'</span>';

				$json['rows'][] = $res;
			}

			if(( (!empty($paginationInfo['Current'])?$paginationInfo['Current']:1)+1)>(!empty($paginationInfo['Pages'])?$paginationInfo['Pages']:0))
				$has_results = false;
			else{
				$next_nav = (!empty($paginationInfo['Current'])?$paginationInfo['Current']:1)+1;
				$paging_start = (!empty($paginationInfo['Nav'][$next_nav])?$paginationInfo['Nav'][$next_nav]:1);
			}

		}
		else  $has_results = false;

		usleep(100000);
	}
	while($has_results && !empty($_REQUEST['download_csv']));

	$json['columns']=$columns;
	if(empty($_REQUEST['download_csv']))
		die(json_encode($json));

	$filename = "Export - Processed Transactions - ".date("Ymd-Gis", time());
	header("Content-type: text/csv; charset=UTF-8");
	header("Content-Disposition: attachment; filename={$filename}.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	$footer_data=[];
	if(!empty($columns)) foreach($columns as $header){
		$footer_data[$header['field']] = !empty($header['footerFormatter'])?$header['footerFormatter']:'';
		map_array_field($json['rows'],$header['field'],function($r) use($header){
			return str_replace(["\t"," ","\n","\r"],'',$r[$header['field']]);
		});
	}
	outputCSV($json['rows'], array_column($columns, 'title', 'field'), $footer_data);

?>