<?php
	new WrapperClass(['mngSidebar']);
	$mngSidebar = new mngSidebar($ioConn);
	$menuProfile = $mngSidebar->gets(['alias'=>'profile'])['data'];
?>
<div class="col hp-profile-menu py-24" style="flex: 0 0 240px;">
	<div class="w-100">
			<div class="hp-profile-menu-header mt-16 mt-lg-0 text-center">

					<div class="d-flex justify-content-center">
							<div class="d-inline-block position-relative">
									<div class="avatar-item d-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px;">
											<img src="<?=SITEDOMAIN?>/assets/app-assets/img/users/<?=!empty($user['gender'])?'user-3.svg':'user-8.svg'?>">
									</div>

							</div>
					</div>

					<h3 class="mt-24 mb-4"><?=$user['firstname']?> <?=$user['lastname']?></h3>
					<a href="mailto:<?=$user['email']?>" class="hp-p1-body"><?=$user['email']?></a>
			</div>
	</div>

	<div class="hp-profile-menu-body w-100 text-start mt-48 mt-lg-0">
			<ul class="me-n1 mx-lg-n12">
				<?php if(!empty($menuProfile)) foreach($menuProfile as $item){ ?>
					<li class="mt-4 mb-16">
							<a href="<?=SITEDOMAIN?><?=$item['url']?>" class="<?=!empty($router_match['params']['page']) && $router_match['params']['page'] == substr($item['url'], 1)?'active':'' ?> position-relative text-black-80 hp-text-color-dark-30 hp-hover-text-color-primary-1 hp-hover-text-color-dark-0 py-12 px-24 d-flex align-items-center">
									<?php $fontSize = null; ?>
									<?php if (isset($item['icon']) && preg_match('/fa-/', $item['icon']) ) $fontSize = 'font-size:1.25em'; ?>
									<i class="<?=$item['icon']?> me-16" style="<?=$fontSize?>"></i>
									<span><?=getTranslationLanguages($item['name'],$language)?></span>
									<span class="hp-menu-item-line position-absolute opacity-0 h-100 top-0 end-0 bg-primary hp-bg-dark-0" style="width: 2px;"></span>
							</a>
					</li>
					<?php } ?>
			</ul>
	</div>

	<div class="hp-profile-menu-footer">
			<img src="<?=SITEDOMAIN?>/assets/app-assets/img/pages/profile/menu-img.svg" alt="Profile Image">
	</div>
</div>

<div class="hp-profile-mobile-menu offcanvas offcanvas-start" tabindex="-1" id="profileMobileMenu" aria-labelledby="profileMobileMenuLabel">
	<div class="offcanvas-header">

			<div class="d-inline-block" id="profile-menu-dropdown" data-bs-dismiss="offcanvas" aria-label="Close">
					<button type="button" class="btn btn-text btn-icon-only">
							<i class="ri-close-fill text-black-80 lh-1" style="font-size: 24px;"></i>
					</button>
			</div>
	</div>

	<div class="offcanvas-body p-0">

			<div class="col hp-profile-menu py-24" style="flex: 0 0 240px;">
					<div class="w-100">
							<div class="hp-profile-menu-header mt-16 mt-lg-0 text-center">

									<div class="d-flex justify-content-center">
											<div class="d-inline-block position-relative">
													<div class="avatar-item d-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px;">
															<img src="<?=SITEDOMAIN?>/assets/app-assets/img/users/<?=!empty($user['gender'])?'user-3.svg':'user-8.svg'?>">
													</div>

													<span class="position-absolute translate-middle badge rounded-pill bg-primary text-white border-none"></span>
											</div>
									</div>

									<h3 class="mt-24 mb-4"><?=$user['firstname']?> <?=$user['lastname']?></h3>
									<a href="mailto:<?=$user['email']?>" class="hp-p1-body"><?=$user['email']?></a>
							</div>
					</div>

					<div class="hp-profile-menu-body w-100 text-start mt-48 mt-lg-0">
							<ul class="me-n1 mx-lg-n12">
									<?php if(!empty($menuProfile)) foreach($menuProfile as $item){ ?>
									<li class="mt-4 mb-16">
											<a href="<?=SITEDOMAIN?><?=$item['url']?>" class="<?=!empty($router_match['params']['page']) && $router_match['params']['page'] == substr($item['url'], 1)?'active':'' ?> position-relative text-black-80 hp-text-color-dark-30 hp-hover-text-color-primary-1 hp-hover-text-color-dark-0 py-12 px-24 d-flex align-items-center">
													<i class="<?=$item['icon']?> me-16"></i>
													<span><?=getTranslationLanguages($item['name'],$language)?></span>
													<span class="hp-menu-item-line position-absolute opacity-0 h-100 top-0 end-0 bg-primary hp-bg-dark-0" style="width: 2px;"></span>
											</a>
									</li>
									<?php } ?>

							</ul>
					</div>

					<div class="hp-profile-menu-footer">
							<img src="<?=SITEDOMAIN?>/assets/app-assets/img/pages/profile/menu-img.svg" alt="Profile Image">
					</div>
			</div>
	</div>
</div>
<?php io::w(); ?>