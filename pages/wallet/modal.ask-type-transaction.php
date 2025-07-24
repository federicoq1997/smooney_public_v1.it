<?php
	require_once(dirname(__FILE__).'/../../components/_global_variables.php');
?>
<style>
	.btn-action-transaction{
		min-height: 180px;
    min-width: 300px;
    font-size: 1.2rem;
	}
	.btn-action-transaction[data-income="0"]{
		color: #fff;
    background: linear-gradient( 135deg, rgb(202 85 48) 0%, rgb(165 58 25) 100%), #422020;
    border-color: #e65d32;
	}
	.btn-action-transaction[data-income="1"]{
		color: #fff;
    background: linear-gradient(135deg, #115d7d 0%, var(--smooney) 100%), var(--smooney);
    border-color: var(--smooney);
	}
	#transaction-actions-modal .btn:hover:not(.btn-link):not(.btn-text) {
    color: #fff;
	}
</style>
<div class="modal fade" id="transaction-actions-modal" tabindex="-1" role="dialog" aria-labelledby="other-actions-label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document" >
		<div class="modal-content"  style="background: none;">
			<div class="modal-body">
				<div class="row mx-0 justify-content-center">
					<div class="col p-2 my-10 text-center">
						<button type="button" class="btn btn-squared-action btn-soft-border  btn-action-transaction" data-income="1">
							<p><br>
								<?=io::_('Income')?>?
							</p>
						</button>
					</div>
					<div class="col p-2 my-10 text-center">
						<button type="button" class="btn btn-squared-action btn-soft-border  btn-action-transaction" data-income="0">
							<p><br>
								<?=io::_('Outcome')?>?
							</p>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	$('#transaction-actions-modal').on('click','.btn-action-transaction',function(){
		let income = $(this).data('income');
		$('#transaction-actions-modal').modal('hide');
		$.ajax({
			method:'get',url:`<?= SITEDOMAIN ?>/modal-list-wallets?income=${income}`,
			data: { },
			dataType: 'html'
		})
		.then(html =>{
			$('.container-modal').html(html);
			$('#list-wallets-modal').modal('show');
			hideLoader();
			if(!!driver_page) driver_page.moveNext();
		})
		.catch(err =>{
			hideLoader();
			console.error('Error processing request', err);
			showErrorMessage("<?= io::_('Error processing request') ?>");
		});
	});
</script>
<?php io::w(); ?>