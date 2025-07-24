<?php
    require_once(dirname(__FILE__).'/../../components/auth.php');
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');

		$income= !empty($_REQUEST["income"]);

    new WrapperClass(['mngWallet']);
    $mngWallet = new mngWallet($ioConn);
    $wallets = $mngWallet->gets([ 'user_code'=>$USERDATA['UserId'],'no-child'=>true ])['data'];
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
<div class="modal fade" id="list-wallets-modal" tabindex="-1" aria-labelledby="list-wallets-modalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content">
						<div class="modal-header py-16 px-24">
								<h5 class="modal-title" id="list-wallets-modalLabel"> </h5>
								<button type="button" class="btn-close hp-bg-none d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Close">
										<i class="ri-close-line hp-text-color-dark-0 lh-1" style="font-size: 24px;"></i>
								</button>
						</div>

						<div class="divider m-0"></div>

								<div class="modal-body">
										<div class="row gx-8">
											<div class="col-12 text-center">
													<div class="mb-10">
															<h3 class="form-label">
																	<?=io::_('For which Wallet?')?>
															</h3>
													</div>
											</div>
										</div>
										<div class="row gx-8">
												<?php if(!empty($wallets)) foreach($wallets as $wallet){ ?>
													<div class="col-12 col-md-6 wallet-card my-10" data-id="<?=$wallet['id']?>">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row g-16">
                                            <div class="col-6 hp-flex-none w-auto">
                                                <div class="avatar-item d-flex align-items-center justify-content-center avatar-lg bg-primary-4 hp-bg-color-dark-primary rounded-circle">
                                                    <i class="<?=!empty($wallet['icon'])?$wallet['icon']:'ri-bank-fill'?> text-primary hp-text-color-dark-primary-2" style="font-size: 24px;"></i>
                                                </div>
                                            </div>

                                            <div class="col-12 text-truncate">
                                                <h3 class="my-8"><?=$wallet['name']?></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
												<?php } ?>
										</div>
								</div>

						</form>
				</div>
		</div>
</div>
<script>
	$('#list-wallets-modal').ready(()=>{
		$('#list-wallets-modal').on('click','.wallet-card',function(){
			let walletId = $(this).data('id');
			$.ajax({
					method: 'get', 
					url: `<?= SITEDOMAIN ?>/modal-transaction/${walletId}<?=$income?'?income=1':''?>`,
					dataType: 'html'
			})
			.then(html => {
				$('#list-wallets-modal').modal('hide');
				setTimeout(()=>{
					$('.container-modal').html(html);
					$('#addNewTransaction').modal('show');
					hideLoader();
					if(!!driver_page) driver_page.moveNext();
				},250);
			})
			.catch(err => {
					hideLoader();
					console.error('Error processing request', err);
					showErrorMessage("<?= io::_('Error processing request') ?>");
			});
		});
	});
</script>
<?php io::w(); ?>