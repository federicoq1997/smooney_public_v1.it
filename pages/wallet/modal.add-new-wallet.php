<?php
    require_once(dirname(__FILE__).'/../../components/auth.php');
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');
		$walletId= !empty($router_match["params"]["walletId"])? $router_match["params"]["walletId"]: null;
		$parentWalletId = !empty($_REQUEST['parentWalletId'])?$_REQUEST['parentWalletId']:null;
		$type = !empty($_REQUEST['type'])?$_REQUEST['type']:null;
		if(!empty($type) && $type == 'new-card-walet') $icon = 'fa-duotone fa-solid fa-credit-card';
		if(!empty($type) && $type == 'new-saving-walet') $icon = 'fa-duotone fa-solid fa-piggy-bank';

		if(!empty($walletId)){
			new WrapperClass(['mngWallet']);
			$mngWallet = new mngWallet($ioConn);
			$wallet = $mngWallet->get([ 'user_code'=>$USERDATA['UserId'],'code'=>$walletId ])['data'];	
			if(!empty($wallet['parent_wallet_id'])){
				$wallet['name'] = explode(' | ',$wallet['name']);
				$wallet['name'] = end($wallet['name']);
				$parentWalletId = $wallet['parent_wallet_id'];
				$icon = $wallet['icon'];
			}
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
<div class="modal fade" id="addNewWallet" tabindex="-1" aria-labelledby="addNewWalletLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
						<div class="modal-header py-16 px-24">
								<h5 class="modal-title" id="addNewWalletLabel"><?=!empty($wallet['code'])?io::_('Edit Wallet'):io::_('Add Wallet')?></h5>
								<button type="button" class="btn-close hp-bg-none d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Close">
										<i class="ri-close-line hp-text-color-dark-0 lh-1" style="font-size: 24px;"></i>
								</button>
						</div>

						<div class="divider m-0"></div>

						<form id="form-new-wallet" autocomplete="_off_">
								<div class="modal-body">
										<div class="row gx-8">
												<div class="col-12 col-md-6">
														<div class="mb-10">
																<label for="name" class="form-label">
																		<span class="text-danger me-4">*</span>
																		<?=io::_('Name')?>
																</label>
																<input type="text" class="form-control" id="name" name="name" value="<?=!empty($wallet['name'])?$wallet['name']:''?>">
														</div>
												</div>

												<?php if(empty($wallet['code'])){ ?>
													<div class="col-12 col-md-6">
															<div class="mb-10">
																	<label for="amount_income" class="form-label">
																			<span class="text-danger me-4">*</span>
																			<?=io::_('Current balance')?>
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
																					<input class="input-number-input" type="number" min="0" step="0.01" placeholder="0" value="" name="amount_balance">
																			</div>
																	</div>
																</div>
															</div>
													</div>
												<?php } ?>

												<?php if(empty($icon)){ ?>
													<div class="col-12 col-md-6">
															<div class="mb-10">
																	<label for="selectpicker-type" class="form-label">
																			<span class="text-danger me-4">*</span>
																			<?=io::_('Type')?>
																	</label>
																	<select class="form-select" name="icon" id="selectpicker-type" class="selectpicker" data-container="#form-new-wallet">
																		<option value="ri-bank-fill" <?= empty($wallet['icon']) || $wallet['icon'] == 'ri-bank-fill'?'selected':'' ?>  ><?=io::_('Bank')?></option>
																		<option value="ri-visa-line" <?= !empty($wallet['icon']) && $wallet['icon'] == 'ri-visa-line'?'selected':'' ?>><?=io::_('Visa circuit')?></option>
																		<option value="ri-mastercard-fill" <?= !empty($wallet['icon']) && $wallet['icon'] == 'ri-mastercard-fill'?'selected':'' ?>><?=io::_('Mastercard circuit')?></option>
																		<option value="ri-paypal-fill" <?= !empty($wallet['icon']) && $wallet['icon'] == 'ri-paypal-fill'?'selected':'' ?>><?=io::_('Paypal')?></option>
																		<option value="ri-bank-card-fill" <?= !empty($wallet['icon']) && $wallet['icon'] == 'ri-bank-card-fill'?'selected':'' ?>><?=io::_('Credit card')?></option>
																		<option value="iconly-Bold-Wallet" <?= !empty($wallet['icon']) && $wallet['icon'] == 'iconly-Bold-Wallet'?'selected':'' ?>><?=io::_('Wallet')?></option>
																	</select>
															</div>
													</div>
												<?php } ?>

												<?php if(!empty($wallet['code'])){ ?>
													<div class="col-12 col-md-6">
															<div class="mb-10">
																	<label for="amount_income" class="form-label">
																			<span class="text-danger me-4">*</span>
																			<?=io::_('Current income')?>
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
																					<input class="input-number-input" type="number" min="0" step="0.01" placeholder="0" value="<?=isset($wallet['amount_income'])?round($wallet['amount_income'],2):''?>" name="amount_income">
																			</div>
																	</div>
																</div>
															</div>
													</div>
													<div class="col-12 col-md-6">
															<div class="mb-10">
																	<label for="amount_expenses" class="form-label">
																			<span class="text-danger me-4">*</span>
																			<?=io::_('Current expenses')?>
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
																					<input class="input-number-input" type="number" min="0" step="0.01" placeholder="0" value="<?=isset($wallet['amount_expenses'])?abs(round($wallet['amount_expenses'],2)):''?>" name="amount_expenses">
																			</div>
																	</div>
																</div>
															</div>
													</div>
													<div class="col-12 col-md-6">
															<div class="mb-10">
																	<label for="amount_balance" class="form-label">
																			<span class="text-danger me-4">*</span>
																			<?=io::_('Current balance')?>
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
																					<input class="input-number-input" type="number" min="0" step="0.01" placeholder="0" value="<?=isset($wallet['amount_balance'])?round($wallet['amount_balance'],2):''?>" name="amount_balance">
																			</div>
																	</div>
																</div>
															</div>
													</div>
												<?php } ?>

												
										</div>
								</div>

								<div class="modal-footer pt-0 px-24 pb-24">
										<div class="divider mt-0"></div>

										<button type="submit" class="m-0 btn smooney-primary w-100"><?=io::_('Save')?></button>
								</div>
						</form>
				</div>
		</div>
</div>
<script>
	$('#addNewWallet').ready(()=>{
		$('#form-new-wallet #selectpicker-type').bsSelectDrop({
			btnClass:'btn',search:false
		});
		$('#form-new-wallet').on('submit',function(e){
			e.preventDefault();
			e.stopPropagation();
			showLoader();
			let json = $(this).serializeObject();
			json.amount_balance = Number((json.amount_balance).replaceAll(',','.'));
			<?php if(!empty($wallet['code'])){ ?>
				if(!!json.amount_expenses) json.amount_expenses =-1* Math.abs(Number((json.amount_expenses).replaceAll(',','.')));
			<?php } ?>
			<?php if(!empty($parentWalletId)){ ?> json.parent_wallet_id = '<?=$parentWalletId?>'; <?php } ?>
			<?php if(!empty($icon)){ ?> json.icon = '<?=$icon?>'; <?php } ?>
			$.ajax({
					method: 'post', url: '<?= SITEACTION ?>/wallet-info<?=!empty($wallet['code'])?'/'.$wallet['code']:''?>',
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
					$('#addNewWallet').modal('hide');
					setTimeout(()=>{ window.location.reload(); },250);
			})
			.catch(err => {
					hideLoader();
					console.error('Error processing request', err);
					showErrorMessage("<?= io::_('Error processing request') ?>");
			});
		});
		<?php if(!empty($wallet['code'])){ ?>
			// $('#form-new-wallet').on('change','input[name="amount_balance"]',function(){
			// 	let amount_balance = Number($('#form-new-wallet input[name="amount_balance"]').val());
			// 	let amount_income = Number($('#form-new-wallet input[name="amount_income"]').val());
			// 	let amount_expenses = Number($('#form-new-wallet input[name="amount_expenses"]').val());
			// 	if(amount_balance < amount_income){
			// 		$('#form-new-wallet input[name="amount_expenses"]').val( Math.abs( amount_balance - amount_income ).toFixed(2) );
			// 	}
			// 	else{
			// 		if( amount_income  )
			// 		$('#form-new-wallet input[name="amount_income"]').val( Math.abs( amount_balance + Math.abs(amount_expenses) ).toFixed(2) );
			// 	}
			// });
			$('#form-new-wallet').on('change','input[name="amount_income"]',function(){
				let amount_income = Number($('#form-new-wallet input[name="amount_income"]').val());
				let amount_expenses = Number($('#form-new-wallet input[name="amount_expenses"]').val());
				let amount_balance = amount_income - Math.abs(amount_expenses) ;
				$('#form-new-wallet input[name="amount_balance"]').val(amount_balance.toFixed(2))
			});
			$('#form-new-wallet').on('change','input[name="amount_expenses"]',function(){
				let amount_income = Number($('#form-new-wallet input[name="amount_income"]').val());
				let amount_expenses = Number($('#form-new-wallet input[name="amount_expenses"]').val());
				let amount_balance = amount_income - Math.abs(amount_expenses) ;
				$('#form-new-wallet input[name="amount_balance"]').val(amount_balance.toFixed(2))
			});
		<?php } ?>
	});
</script>
<?php io::w(); ?>