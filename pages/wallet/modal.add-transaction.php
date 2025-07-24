<?php
    require_once(dirname(__FILE__).'/../../components/auth.php');
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');

		$walletId= !empty($router_match["params"]["walletId"])? $router_match["params"]["walletId"]: die();
		$transactionCode= !empty($router_match["params"]["transactionCode"])? $router_match["params"]["transactionCode"]: null;

		new WrapperClass(['mngTag','mngWallet']);
		$mngTag = new mngTag($ioConn);
		$tags = $mngTag->gets([ 'user_code'=>$USERDATA['UserId'] ])['data'];

    $mngWallet = new mngWallet($ioConn);
    $wallets = $mngWallet->gets([ 'user_code'=>$USERDATA['UserId'] ])['data'];
		$wallet = array_filter($wallets,function($r) use($walletId){ return $walletId==$r['id']; });
		$wallet = reset($wallet);
		$wallets = array_filter($wallets,function($r) use($walletId){ return $walletId!=$r['id']; });
		$child_wallets = $mngWallet->gets([ 'user_code'=>$USERDATA['UserId'],'parent_wallet_id'=>$walletId ])['data'];

		$income = !empty($_REQUEST['income']);
		if(!empty($transactionCode)){
			new WrapperClass(['mngIntentTransaction']);
			$mngIntentTransaction = new mngIntentTransaction($ioConn);
			$transaction = $mngIntentTransaction->get([
					'user_code'=>$USERDATA['UserId'],
					'code'=>$transactionCode,
			])['data'];
			if(isset($transaction['amount'])) $income = $transaction['amount'] >=0;
			if(!empty($transaction['source_wallet_id']) && $transaction['source_wallet_id'] == $walletId) $income = false;
		}
?>
<style>
	.colorPickSelector {
		border-radius: 5px;
		width: 100%;
		height: 40px;
		cursor: pointer;
		-webkit-transition: all linear .2s;
		-moz-transition: all linear .2s;
		-ms-transition: all linear .2s;
		-o-transition: all linear .2s;
		transition: all linear .2s;
	}
	#colorPick {
		background: rgba(255, 255, 255, .98) !important;
	}
</style>
<div class="modal fade" id="addNewTransaction" tabindex="-1" aria-labelledby="addNewTransactionLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
						<div class="modal-header py-16 px-24">
								<h5 class="modal-title" id="addNewTransactionLabel"><?=!empty($transactionCode)?io::_('Edit '.($income?'Income':'a Payment')):io::_('Add '.($income?'Income':'a Payment'))?></h5>
								<button type="button" class="btn-close hp-bg-none d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Close">
										<i class="ri-close-line hp-text-color-dark-0 lh-1" style="font-size: 24px;"></i>
								</button>
						</div>

						<div class="divider m-0"></div>

						<form id="form-new-transaction" autocomplete="_off_">
								<div class="modal-body">
										<div class="row gx-8">
												<div class="col-12">
														<div class="mb-10">
																<label for="description" class="form-label">
																		<span class="text-danger me-4">*</span>
																		<?=io::_('Description')?>
																</label>
																<input type="text" class="form-control" id="description" name="description" value="<?=!empty($transaction['description'])?$transaction['description']:''?>">
														</div>
												</div>
												<div class="col-12 col-md-6">
														<div class="mb-10">
																<label for="amount_income" class="form-label">
																		<span class="text-danger me-4">*</span>
																		<?=io::_('Amount')?>
																</label>
																<div class="input-group mb-3">
																	<div class="input-group-prepend">
																		<span class="input-group-text" id="euro" style="border-top-right-radius: 0px;border-bottom-right-radius: 0px;"><i class="ri-money-euro-circle-line" style="font-size: 1.2rem;"></i></span>
																	</div>
																	<div class="input-number" style="width: 110px;">
																		<div class="input-number-handler-wrap">
																				<span class="input-number-handler input-number-handler-up">
																						<span class="input-number-handler-up-inner">
																								<svg viewBox="64 64 896 896" width="1em" height="1em" fill="currentColor">
																										<path d="M890.5 755.3L537.9 269.2c-12.8-17.6-39-17.6-51.7 0L133.5 755.3A8 8 0 00140 768h75c5.1 0 9.9-2.5 12.9-6.6L512 369.8l284.1 391.6c3 4.1 7.8 6.6 12.9 6.6h75c6.5 0 10.3-7.4 6.5-12.7z"></path>
																								</svg>
																						</span>
																				</span>

																				<span class="input-number-handler input-number-handler-down input-number-handler-down-disabled">
																						<span class="input-number-handler-down-inner">
																								<svg viewBox="64 64 896 896" width="1em" height="1em" fill="currentColor">
																										<path d="M884 256h-75c-5.1 0-9.9 2.5-12.9 6.6L512 654.2 227.9 262.6c-3-4.1-7.8-6.6-12.9-6.6h-75c-6.5 0-10.3 7.4-6.5 12.7l352.6 486.1c12.8 17.6 39 17.6 51.7 0l352.6-486.1c3.9-5.3.1-12.7-6.4-12.7z"></path>
																								</svg>
																						</span>
																				</span>
																		</div>

																		<div class="input-number-input-wrap">
																				<input class="input-number-input" type="number" min="0" step="0.01" placeholder="0" value="<?=isset($transaction['amount'])?abs(round($transaction['amount'],2)):''?>" name="amount">
																		</div>
																</div>
															</div>
														</div>
												</div>

												<?php if(!empty($tags) && empty($income)){ ?>
													<div class="col-12 col-md-6 container-tags">
															<div class="mb-10">
																	<label for="tag_id" class="form-label">
																			<span class="text-danger me-4">*</span>
																			<?=io::_('Tag')?>
																	</label>
																	<select class="form-select" name="tag_id" id="tag_id" class="selectpicker" data-container="#form-new-transaction">
																		<?php foreach($tags as $tag){ ?>
																			<?php $s= ''; ?>
																			<?php if(!empty($transaction['tag_id']) && $transaction['tag_id'] == $tag['id']) $s= 'selected'; ?>
																			<option value="<?=$tag['id']?>" $s ><?=$tag['name']?></option>
																		<?php } ?>
																	</select>
															</div>
													</div>
												<?php } ?>


												<div class="col-12 col-md-6">
														<div class="mb-10">
																<label for="date" class="form-label">
																		<span class="text-danger me-4">*</span>
																		<?=io::_('Date')?>
																</label>
																<input type="text" class="form-control" readonly id="date" name="date" value="<?=!empty($transaction['date'])? date('d/m/Y',strtotime($transaction['date'])):''?>" />
														</div>
												</div>

												<div class="col-12 mt-10 <?=!empty($child_wallets)?'':'d-none'?>">
													<div class="mb-10">
															<label for="<?=!empty($income)?'dest_wallet_id':'source_wallet_id'?>" class="form-label">
																	<span class="text-danger me-4">*</span>
																	<?=io::_((!empty($income)?'Destination':'Source').' Wallet')?>
															</label>
															<select class="form-select" name="<?=!empty($income)?'dest_wallet_id':'source_wallet_id'?>" id="<?=!empty($income)?'dest_wallet_id':'source_wallet_id'?>"
																class="selectpicker" data-container="#form-new-transaction">
																<option value="<?=$walletId?>" <?=empty($transaction['dest_wallet_id']) || $transaction['dest_wallet_id'] == $walletId?> > <?=$wallet['name']?> </option>
																<?php if(!empty($child_wallets)) foreach($child_wallets as $wallet){ ?>
																	<?php $s= ''; ?>
																	<?php $key= !empty($income)?'dest_wallet_id':'source_wallet_id'; ?>
																	<?php if(!empty($transaction[$key]) && $transaction[$key] == $wallet['id']) $s= 'selected'; ?>
																	<option value="<?=$wallet['id']?>" <?=$s?> ><?=$wallet['name']?></option>
																<?php } ?>
															</select>
													</div>
												</div>

												<?php if(!empty($wallets)){ ?>
													<div class="col-12">
														<div class="form-check">
															<input type="checkbox" class="form-check-input" id="checkbox_type" name="type" value="1" <?=!empty($transaction['source_wallet_id'])?'checked':''?>>
															<label class="form-check-label ps-4" for="checkbox_type"><?=io::_('Bank transfer between own accounts')?></label>
														</div>
													</div>
													<div class="col-12 mt-10 <?=!empty($transaction['source_wallet_id'])?'':'d-none'?> container-dest-wallet">
															<div class="mb-10">
																	<label for="<?=empty($income)?'dest_wallet_id':'source_wallet_id'?>" class="form-label">
																			<span class="text-danger me-4">*</span>
																			<?=io::_((!empty($income)?'Source':'Destination').' Wallet')?>
																	</label>
																	<select class="form-select" name="<?=empty($income)?'dest_wallet_id':'source_wallet_id'?>" id="<?=empty($income)?'dest_wallet_id':'source_wallet_id'?>"
																		class="selectpicker" data-container="#form-new-transaction">
																		<?php foreach($wallets as $wallet){ ?>
																			<?php $s= ''; ?>
																			<?php $key= empty($income)?'dest_wallet_id':'source_wallet_id'; ?>
																			<?php if(!empty($transaction[$key]) && $transaction[$key] == $wallet['id']) $s= 'selected'; ?>
																			<option value="<?=$wallet['id']?>" <?=$s?> ><?=$wallet['name']?></option>
																		<?php } ?>
																	</select>
															</div>
													</div>
													<div class="col-12 col-md-6 <?=!empty($transaction['source_wallet_id'])?'':'d-none'?> container-ancillary_expenses">
														<div class="mb-10">
																<label for="ancillary_expenses" class="form-label">
																		<span class="text-danger me-4">*</span>
																		<?=io::_('Ancillary expenses')?>
																</label>
																<div class="input-group mb-3">
																	<div class="input-group-prepend">
																		<span class="input-group-text" id="euro" style="border-top-right-radius: 0px;border-bottom-right-radius: 0px;"><i class="ri-money-euro-circle-line" style="font-size: 1.2rem;"></i></span>
																	</div>
																	<div class="input-number" style="width: 110px;">
																		<div class="input-number-handler-wrap">
																				<span class="input-number-handler input-number-handler-up">
																						<span class="input-number-handler-up-inner">
																								<svg viewBox="64 64 896 896" width="1em" height="1em" fill="currentColor">
																										<path d="M890.5 755.3L537.9 269.2c-12.8-17.6-39-17.6-51.7 0L133.5 755.3A8 8 0 00140 768h75c5.1 0 9.9-2.5 12.9-6.6L512 369.8l284.1 391.6c3 4.1 7.8 6.6 12.9 6.6h75c6.5 0 10.3-7.4 6.5-12.7z"></path>
																								</svg>
																						</span>
																				</span>

																				<span class="input-number-handler input-number-handler-down input-number-handler-down-disabled">
																						<span class="input-number-handler-down-inner">
																								<svg viewBox="64 64 896 896" width="1em" height="1em" fill="currentColor">
																										<path d="M884 256h-75c-5.1 0-9.9 2.5-12.9 6.6L512 654.2 227.9 262.6c-3-4.1-7.8-6.6-12.9-6.6h-75c-6.5 0-10.3 7.4-6.5 12.7l352.6 486.1c12.8 17.6 39 17.6 51.7 0l352.6-486.1c3.9-5.3.1-12.7-6.4-12.7z"></path>
																								</svg>
																						</span>
																				</span>
																		</div>

																		<div class="input-number-input-wrap">
																				<input class="input-number-input" type="number" min="0" step="0.01" placeholder="0" value="<?=!empty($transaction['ancillary_expenses'])?abs(round($transaction['ancillary_expenses'],2)):''?>" name="ancillary_expenses">
																		</div>
																</div>
															</div>
														</div>
													</div>
												<?php } ?>

												
										</div>
								</div>

								<div class="modal-footer pt-0 px-24 pb-24">
										<div class="divider mt-0 <?=!empty($transactionCode)?'mb-0':'mb-10'?>"></div>
										<div class="w-100 row mx-0 align-items-center justify-content-center">
											<?php if(!empty($transactionCode)){ ?>
												<div class="col-12 col-md-6 my-10"> 
													<button type="button" class="m-0 btn btn-danger-3 w-100 btn-deactivate"><?=io::_('Deactivate')?></button>
												</div>
												<div class="col-12 col-md-6 my-10"> 
											<?php } ?>
												<button type="submit" class="m-0 btn smooney-primary w-100 "><?=io::_('Save')?></button>
											<?php if(!empty($transactionCode)){ ?>
											</div>
											<?php } ?>
										</div>
								</div>
						</form>
				</div>
		</div>
</div>
<script>
	$('#addNewTransaction').ready(()=>{
		
		$('#form-new-transaction #<?=empty($income)?'dest_wallet_id':'source_wallet_id'?>').bsSelectDrop({
			btnClass:'btn',search:false
		});
		$('#form-new-transaction #tag_id').bsSelectDrop({
			btnClass:'btn',search:false
		});
		$('#form-new-transaction').on('click','.btn-deactivate',function(){
			$.ajax({
					method: 'delete', url: '<?= SITEACTION ?>/transaction<?=!empty($transactionCode)?'/'.$transactionCode:''?>',
					dataType: 'json'
			})
			.then(res => {
				if (!res.success) {
					hideLoader();
					showErrorMessage(res.error);
					return;
				}
				showSuccessMessage('<?= io::_('Change saved') ?>');
				$('#addNewTransaction').modal('hide');
				setTimeout(()=>{ window.location.reload(); },250);
			})
			.catch(err => {
				hideLoader();
				console.error('Error processing request', err);
				showErrorMessage("<?= io::_('Error processing request') ?>");
			});
		});
		$('#form-new-transaction').on('submit',function(e){
			e.preventDefault();
			e.stopPropagation();
			showLoader();
			let json = $(this).serializeObject();
			if(!json.description || !json.amount || !json.date){
				showErrorMessage('<?=io::_('Fill in all required data')?>');
				return;
			}
			<?php if(!empty($income)){ ?>
				json.amount = Math.abs( Number(json.amount) );
			<?php }else{ ?>
				json.amount = -1* Math.abs( Number(json.amount) );
			<?php } ?>
			json.date = moment(json.date, 'DD/MM/YYYY').format('YYYY-MM-DD');
			if(!!json.type) delete json.tag_id;
			else json.type = 0;
			<?php if(empty($income)){ ?>
				if(!!json.type){
					<?php if(empty($child_wallets)){ ?>
						json.source_wallet_id = '<?=$walletId?>';
					<?php } ?>
					json.amount = Math.abs( Number(json.amount) );
				}
				<?php if(empty($child_wallets)){ ?>
					else json.dest_wallet_id = '<?=$walletId?>';
				<?php } ?>
			<?php }else { ?>
				<?php if(empty($child_wallets)){ ?>
					json.source_wallet_id = json.dest_wallet_id;
					json.dest_wallet_id = '<?=$walletId?>';
				<?php } ?>
				if(!Number(json.type)) delete json.source_wallet_id;
			<?php } ?>
			if(!!json.ancillary_expenses) json.ancillary_expenses = Number(json.ancillary_expenses);
			//if(!!json.type) json.date = moment().format('YYYY-MM-DD');
			$.ajax({
					method: 'post', url: '<?= SITEACTION ?>/transaction<?=!empty($transactionCode)?'/'.$transactionCode:''?>',
					data: { jdata: JSON.stringify(json) },
					dataType: 'json'
			})
			.then(res => {
				if (!res.success) {
					hideLoader();
					showErrorMessage(res.error);
					return;
				}
				showSuccessMessage('<?= io::_('Change saved') ?>');
				$('#addNewTransaction').modal('hide');
				setTimeout(()=>{ window.location.reload(); },250);
			})
			.catch(err => {
				hideLoader();
				console.error('Error processing request', err);
				showErrorMessage("<?= io::_('Error processing request') ?>");
			});
			
		});
		new Datepicker(document.getElementById('date'),{
			buttonClass:'btn',
			format:'dd/mm/yyyy',
			weekStart:1,
			autohide:true,
			defaultViewDate: new Date().setHours(0, 0, 0, 0),
			language:"<?=$language?>",
			nextArrow:'<i class="hp-text-color-dark-0 iconly-Light-ChevronRight"></i>',
			prevArrow:'<i class="hp-text-color-dark-0 iconly-Light-ChevronLeft"></i>'
		});
		$('body').on('change','#checkbox_type',function(){
			if($(this).is(':checked')){
				$('.container-dest-wallet').removeClass('d-none');
				$('.container-ancillary_expenses').removeClass('d-none');
				$('.container-tags').addClass('d-none');
			} 
			else{
				$('.container-dest-wallet').addClass('d-none');
				$('.container-ancillary_expenses').addClass('d-none');
				$('.container-tags').removeClass('d-none');
			} 
		});
	});
</script>
<?php io::w(); ?>