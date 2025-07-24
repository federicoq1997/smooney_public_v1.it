<link rel="stylesheet" href="/assets/app-assets/css/layouts/customizer.min.css">
<div class="hp-theme-customizer">
	<div class="hp-theme-customizer-button">
			<div class="hp-theme-customizer-button-bg">
					<svg width="48" height="121" viewBox="0 0 48 121" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M38.6313 21.7613C46.5046 11.6029 47.6987 2.40985 48 0V61H0C1.03187 53.7789 1.67112 44.3597 13.2122 37.7607C22.0261 32.721 32.4115 29.7862 38.6313 21.7613Z" fill="white"></path>
							<path d="M38.6058 99.5632C46.502 109.568 47.6984 118.627 48 121V61H0C1.03532 68.1265 1.67539 77.4295 13.3283 83.9234C22.1048 88.8143 32.3812 91.6764 38.6058 99.5632Z" fill="white"></path>
					</svg>
			</div>

			<div class="hp-theme-customizer-button-icon">
				<i class="fa-solid fa-sliders text-light" style="font-size: 1.1rem;"></i>
			</div>
	</div>

	<div class="hp-theme-customizer-container bg-black-0 hp-bg-dark-90">
			<div class="hp-theme-customizer-container-header bg-black-10 hp-bg-dark-85 py-16 px-24">
					<div class="d-flex justify-content-between align-items-center">
							<div>
									<span class="h5 mb-0 d-block text-black-80 hp-text-color-dark-0"><?=io::_('Advanced Search')?></span>
							</div>

							<div>
									<button type="button" class="btn btn-text hp-bg-dark-85">
											<i class="fa-solid fa-circle-xmark text-black-80" style="font-size: 1.2rem;"></i>
									</button>
							</div>
					</div>
			</div>

			<div class="hp-theme-customizer-container-body pb-md-96 pb-mb-0 py-24 px-24">
					<form id="form-filter-analytics">
						<div class="row mx-0 hp-theme-customizer-container-body-item">
							<div class="col-12">
									<div class="mb-10">
											<label for="walletIds" class="form-label">
													<?=io::_('Wallets')?>
											</label>
											<select class="form-select selectpicker w-100" name="walletIds[]" id="walletIds" multiple  data-container="#form-filter-analytics">
												<?php if(!empty($wallets)) foreach($wallets as $wallet){ ?>
													<?php $s= ''; ?>
													<option value="<?=$wallet['id']?>" $s ><?=$wallet['name']?></option>
												<?php } ?>
											</select>
									</div>
							</div>
						</div>
					</form>

			</div>
	</div>
</div>
