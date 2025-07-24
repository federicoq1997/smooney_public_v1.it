<?php
	require_once(dirname(__FILE__).'/../../components/auth.php');
	require_once(dirname(__FILE__).'/../../components/_global_variables.php');
	new WrapperClass(['mngTag']);
	$mngTag = new mngTag($ioConn);
	$tags = $mngTag->gets([ 'user_code'=>$USERDATA['UserId'] ])['data'];

	$json = [
		'columns'=>[],
		'total' => 1,
		'totalNotFiltered' => 1,
		'rows' => [],
	];

	if(empty($tags)) die(json_encode($json));
	foreach($tags as $tag){
		$res = [];
		$res['name'] = '<span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter">'.$tag['name'].'</span>';
		if(!empty($tag['type']) && $tag['type'] == 1)
			$res['type']='<span class="badge hp-text-color-black-100 hp-bg-success-3 rounded-pill px-8 border-0 badge-none ">Prima necessità</span>';
		if(!empty($tag['type']) && $tag['type'] == 2)
			$res['type']='<span class="badge hp-text-color-black-100 hp-bg-info-3 rounded-pill px-8 border-0 badge-none ">Investimenti / Risparmi</span>';
		if(!empty($tag['type']) && $tag['type'] == 3)
			$res['type']='<span class="badge hp-text-color-black-100 hp-bg-warning-3 rounded-pill px-8 border-0 badge-none ">Desideri</span>';
		$res['color'] = '<div class="d-flex align-items-center justify-content-center">
			<span class="bg-danger-4 border  p-10 rounded-circle start-100" style="background: '.$tag['color'].' !important;"></span>
			</span>
		</div>';
		$res['buttons'] = '
			<i class="h iconly-Broken-EditSquare hp-cursor-pointer hp-transition hp-hover-text-color-success-1 text-black-80 btn-edit-tag mx-1" data-code="'.$tag['id'].'" style="font-size: 24px;"></i>
			<i class="iconly-Light-Delete hp-cursor-pointer hp-transition hp-hover-text-color-danger-1 text-black-80 btn-delete-tag mx-1" data-code="'.$tag['id'].'" style="font-size: 24px;"></i>
		';
		$json['rows'][] = $res;
		$json['total']++;
		$json['totalNotFiltered']++;
	}
	die(json_encode($json));
?>