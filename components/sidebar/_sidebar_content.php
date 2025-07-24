<?php
	new WrapperClass(['mngSidebar']);
	$mngSidebar = new mngSidebar($ioConn);
	$menu = $mngSidebar->gets()['data'];
?>
<ul>
	<li>

			<ul>
				<?php if(!empty($menu)) foreach($menu as $item){ ?>
					<?php if(empty($item['child'])){ ?>
					<?php if(!empty($item['status']) && $item['status'] == 1) $item['url'] = '/coming-soon'; ?>
					<?php if(!empty($item['status']) && $item['status'] == 2) $item['url'] = '/maintenance'; ?>
					<?php $fontSize = 1.5; ?>
					<?php if (isset($item['icon']) && preg_match('/fa-/', $item['icon']) ) $fontSize = 1.25; ?>
						<li>
							<a href="<?=SITEDOMAIN?><?=$item['url']?>">
									<div class="tooltip-item in-active" data-bs-toggle="tooltip" data-bs-placement="right" title="" data-bs-original-title="<?=getTranslationLanguages($item['name'],$language)?>" aria-label="<?=getTranslationLanguages($item['name'],$language)?>"></div>

									<span>
											<span class="submenu-item-icon">
													<i class="hp-text-color-dark-0 <?=$item['icon']?>" style="font-size: <?=$fontSize?>em;"></i>
											</span>

											<span><?=getTranslationLanguages($item['name'],$language)?></span>
									</span>
									<?php if(!empty($item['status']) && $item['status'] == 1){ ?>
										<span class="badge hp-text-color-black-100 hp-bg-success-3 rounded-pill px-8 border-0 badge-none"><?=io::_('Coming soon')?></span>
									<?php }else if(!empty($item['status']) && $item['status'] == 2){ ?>
										<span class="badge hp-text-color-black-100 hp-bg-danger-3 rounded-pill px-8 border-0 badge-none"><?=io::_('Maintenance')?></span>
									<?php } ?>
							</a>
						</li>
					<?php }else{ ?>
						<li>
							<a href="javascript:;" class="submenu-item">
									<span>
											<span class="submenu-item-icon">
												<i class="hp-text-color-dark-0 <?=$item['icon']?>" style="font-size: 1.5em;"></i>
											</span>

											<span><?=getTranslationLanguages($item['name'],$language)?></span>
									</span>
									<div class="menu-arrow"></div>
							</a>
							<ul class="submenu-children" data-level="1">
								<?php foreach($item['child'] as $child){ ?>

									<?php if(!empty($child['status']) && $child['status'] == 1) $child['url'] = '/coming-soon'; ?>
									<?php if(!empty($child['status']) && $child['status'] == 2) $child['url'] = '/maintenance'; ?>
									<li>
										<a href="<?=SITEDOMAIN?><?=$child['url']?>">
												<span><?=getTranslationLanguages($child['name'],$language)?></span>
										</a>
										<?php if(!empty($child['status']) && $child['status'] == 1){ ?>
											<span class="badge hp-text-color-black-100 hp-bg-success-3 rounded-pill px-8 border-0 badge-none"><?=io::_('Coming soon')?></span>
										<?php }else if(!empty($child['status']) && $child['status'] == 2){ ?>
											<span class="badge hp-text-color-black-100 hp-bg-danger-3 rounded-pill px-8 border-0 badge-none"><?=io::_('Maintenance')?></span>
										<?php } ?>
									</li>
								<?php } ?>
							</ul>
						</li>
					<?php } ?>
				<?php } ?>
			</ul>
	</li>

</ul>
<?php io::w(); ?>