<?php
    require_once(dirname(__FILE__).'/../../components/auth.php');
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');
		if(!empty($_REQUEST['id'])){
			new WrapperClass(['mngTag']);
			$mngTag = new mngTag($ioConn);
			$tag = $mngTag->get([ 'user_code'=>$USERDATA['UserId'],'id'=>$_REQUEST['id'] ])['data'];
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
<div class="modal fade" id="addNewTag" tabindex="-1" aria-labelledby="addNewTagLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
						<div class="modal-header py-16 px-24">
								<h5 class="modal-title" id="addNewTagLabel"><?=!empty($tag)?io::_('Edit Tag'):io::_('Add Tag')?></h5>
								<button type="button" class="btn-close hp-bg-none d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Close">
										<i class="ri-close-line hp-text-color-dark-0 lh-1" style="font-size: 24px;"></i>
								</button>
						</div>

						<div class="divider m-0"></div>

						<form id="form-new-tag" autocomplete="_off_">
								<?php if(!empty($tag['id'])) echo '<input type="hidden" name="id" value="'.$tag['id'].'">'; ?>
								<div class="modal-body">
										<div class="row gx-8">
												<div class="col-12 col-md-6">
														<div class="mb-10">
																<label for="name" class="form-label">
																		<span class="text-danger me-4">*</span>
																		<?=io::_('Name')?>
																</label>
																<input type="text" class="form-control" id="name" name="name" value="<?=!empty($tag['name'])?$tag['name']:''?>">
														</div>
												</div>

												<div class="col-12 col-md-6">
														<div class="mb-10">
																<label for="colorPickSelector" class="form-label">
																		<span class="text-danger me-4">*</span>
																		<?=io::_('Color')?>
																</label>
																<div class="colorPickSelector"></div>
																<input type="hidden" name="color" value="<?=!empty($tag['color'])?$tag['color']:'#3498db'?>">
														</div>
												</div>
												<div class="col-12">
														<div class="mb-10">
																<label for="tag_type" class="form-label w-100">
																		<?=io::_('Type')?>
																</label>
																<div class="col-10">
																	<select  name="type" id="selectpicker-type" class="selectpicker" data-container="#form-new-tag">
																		<option value="1" <?=(!empty($tag['type']) && $tag['type'] == 1)?'selected':''?>> <?= io::_('First necessity') ?></option>
																		<option value="3" <?=(!empty($tag['type']) && $tag['type'] == 3)?'selected':''?>> <?= io::_('Wishes') ?></option>
																		<option value="2" <?=(!empty($tag['type']) && $tag['type'] == 2)?'selected':''?>> <?= io::_('Investment / Saving') ?></option>
																	</select>
																</div>
														</div>
												</div>

												
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
	$('#addNewTag').ready(()=>{
		$("#form-new-tag .colorPickSelector").colorPick({
			'initialColor': '<?=!empty($tag['color'])?$tag['color']:'#3498db'?>',
			'allowRecent': true,
			'recentMax': 5,
			'allowCustomColor': false,
			'palette': ["#1abc9c", "#16a085", "#2ecc71", "#27ae60", "#3498db", "#2980b9", "#9b59b6", "#8e44ad", "#34495e", "#2c3e50", "#f1c40f", "#f39c12", "#e67e22", "#d35400", "#e74c3c", "#c0392b", "#ecf0f1", "#bdc3c7", "#95a5a6", "#7f8c8d"],
			'onColorSelected': function() {
					this.element.css({'backgroundColor': this.color, 'color': this.color});
					$('#form-new-tag input[name="color"]').val(this.color);
			}
		});
		var substringMatcher = function(strs) {
			return function findMatches(q, cb) {
				var matches, substringRegex;
				matches = [];
				substrRegex = new RegExp(q, 'i');
				$.each(strs, (i, str)=> {
					if (substrRegex.test(str.name)) {
						matches.push({key:str.id,value:str.name});
					}
				});
				cb(matches);
			};
		};
		$('#form-new-tag #selectpicker-type').bsSelectDrop({
			btnClass:'btn',search:false
		});
		$('#form-new-tag').on('submit',function(e){
			e.preventDefault();
			e.stopPropagation();
			showLoader();
			let json = $(this).serializeObject();
			json.text_color = getBestTextColor('#181818',json.color || '#000');
			$.ajax({
					method: 'post', url: '<?= SITEACTION ?>/tag'+(!!json.id?'/'+json.id:''),
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
					hideLoader();
					$('#addNewTag').modal('hide');
					if(typeof reloadTable != 'undefined') reloadTable();
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