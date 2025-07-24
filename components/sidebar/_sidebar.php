<div class="hp-sidebar hp-bg-color-black-20 hp-bg-color-dark-90 border-end border-black-40 hp-border-color-dark-80">
	<div class="hp-sidebar-container">
			<div class="hp-sidebar-header-menu">
					<div class="row justify-content-between align-items-center mx-0">
							<div class="w-auto px-0 hp-sidebar-collapse-button hp-sidebar-visible">
									<div class="hp-cursor-pointer">
											<svg width="8" height="15" viewBox="0 0 8 15" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path d="M3.91102 1.73796L0.868979 4.78L0 3.91102L3.91102 0L7.82204 3.91102L6.95306 4.78L3.91102 1.73796Z" fill="#fff"></path>
													<path d="M3.91125 12.0433L6.95329 9.00125L7.82227 9.87023L3.91125 13.7812L0.000224113 9.87023L0.869203 9.00125L3.91125 12.0433Z" fill="#fff"></path>
											</svg>
									</div>
							</div>

							<div class="w-auto px-0">
									<div class="hp-header-logo d-flex align-items-center">
											<a href="<?=SITEDOMAIN?>/dashboard" class="position-relative">

													<img class="hp-logo hp-sidebar-visible hp-dark-none" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
													<img class="hp-logo hp-sidebar-visible hp-dark-block" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
													<img class="hp-logo hp-sidebar-hidden hp-dir-none hp-dark-none" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
													<img class="hp-logo hp-sidebar-hidden hp-dir-none hp-dark-block" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
													<img class="hp-logo hp-sidebar-hidden hp-dir-block hp-dark-none" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
													<img class="hp-logo hp-sidebar-hidden hp-dir-block hp-dark-block" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
											</a>

									</div>
							</div>

							<div class="w-auto px-0 hp-sidebar-collapse-button hp-sidebar-hidden">
									<div class="hp-cursor-pointer mb-4">
											<svg width="8" height="15" viewBox="0 0 8 15" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path d="M3.91102 1.73796L0.868979 4.78L0 3.91102L3.91102 0L7.82204 3.91102L6.95306 4.78L3.91102 1.73796Z" fill="#fff"></path>
													<path d="M3.91125 12.0433L6.95329 9.00125L7.82227 9.87023L3.91125 13.7812L0.000224113 9.87023L0.869203 9.00125L3.91125 12.0433Z" fill="#fff"></path>
											</svg>
									</div>
							</div>
					</div>

					<?php require(dirname(__FILE__).'/_sidebar_content.php'); ?>

			</div>

	</div>
</div>
<?php io::w(); ?>