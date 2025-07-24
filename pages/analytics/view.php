<?php
    require_once(dirname(__FILE__).'/../../components/auth.php');
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');
    $pageTitle = 'Smooney - '.io::_('Analytics');

    new WrapperClass(['mngWallet']);
    $mngWallet = new mngWallet($ioConn);
    $wallets = $mngWallet->gets([ 'user_code'=>$USERDATA['UserId'] ])['data'];
?>
<!DOCTYPE html>
<html dir="ltr">
<head>
    <?php require_once(dirname(__FILE__).'/../../components/_headcontent.php'); ?>
    <link rel="stylesheet" type="text/css" href="/assets/app-assets/css/pages/app-contact.css">
    <link rel="stylesheet" type="text/css" href="/assets/app-assets/css/plugin/apex-charts.css">
    <link rel="stylesheet" type="text/css" href="/assets/app-assets/css/pages/dashboard-ecommerce.css">
		<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
		<style>
			.daterangepicker td.active, .daterangepicker td.active:hover {
				background-color: var(--smooney);
			}
			.apexcharts-legend-series {
				margin-top: 12px !important;
				margin-bottom: 12px !important;
			}
			.btn-soft-border{
				border-radius: .75rem !important;
			}

			.btn-secondary{
				background:#6c757d !important;
				border-color: #6c757d !important;
			}
			.btn-outline-secondary{
				color: #6c757d !important;
    		border-color: #6c757d !important;
			}
			.btn-outline-secondary:hover {
				color: #ffffff !important;
				background-color: #6c757d !important;
				border-color: #6c757d;
			}
			.btn-check:checked + .btn-outline-secondary, .btn-check:active + .btn-outline-secondary, .btn-outline-secondary:active, .btn-outline-secondary.active, .btn-outline-secondary.dropdown-toggle.show {
				color: #ffffff !important;
				background-color: #6c757d !important;
				border-color: #6c757d;
			}

			.btn.btn-secondary:hover {
				background: #9da2a5 !important;
				border-color: #9da2a5 !important;
			}
			.btn-check:focus + .btn-secondary, .btn-secondary:focus{
				box-shadow: 0 0 0 0.05rem rgb(162 168 171);
			}
		</style>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.5/dist/bootstrap-table.min.css" rel="stylesheet">
</head>

<body>
    <main class="hp-bg-color-dark-90 d-flex min-vh-100">
				<?php require_once(dirname(__FILE__).'/../../components/sidebar/_sidebar.php'); ?>


        <div class="hp-main-layout">
						<?php require_once(dirname(__FILE__).'/../../components/_header.php'); ?>


            <div class="hp-main-layout-content">
                <div class="row mb-32 gy-32">
                    <div class="col-12">
                        <div class="row align-items-center justify-content-between g-24">
                            <div class="col-12 col-md-6">
                                <h1 class="mb-0"><?=io::_('Analytics')?></h1>
                            </div>

                            <div class="col hp-flex-none w-auto d-flex">
															<?php
																$_REQUEST['to'] 	= date('Y-m-d',strtotime(date('Y-m-d').' +1month ')); 
																$_REQUEST['from']	= date('Y-m-d',strtotime(date('Y-m-d').' -1month'));
															?>
															<div class="input-group mb-3" style="min-width: 250px;">
																<span class="input-group-text " style="background:var(--smooney);border-radius: .75rem 0 0 .75rem;">
																	<i class="text-white iconly-Broken-Calendar"></i>
																</span>
																<input type="hidden" name="date[from]" value="<?=$_REQUEST['from']?>" >
																<input type="hidden" name="date[to]" value="<?=$_REQUEST['to']?>" >
																<input type="text" class="form-control bg-white" style="border-radius: 0 .75rem .75rem 0;" readonly id="calendar" value="<?=date('d/m/Y',strtotime($_REQUEST['from']))?> - <?=date('d/m/Y',strtotime($_REQUEST['to']))?>" />

															</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 content-page">
                    </div>

                    
                </div>
            </div>

						<?php require_once(dirname(__FILE__).'/../../components/_footer.php'); ?>

        </div>
    </main>
		<?php require_once(dirname(__FILE__).'/components/sidebar.filter.php'); ?>

    <div class="scroll-to-top">
        <button type="button" class="btn btn-primary btn-icon-only rounded-circle hp-primary-shadow">
            <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="16px" width="16px" xmlns="http://www.w3.org/2000/svg">
                <g>
                    <path fill="none" d="M0 0h24v24H0z"></path>
                    <path d="M13 7.828V20h-2V7.828l-5.364 5.364-1.414-1.414L12 4l7.778 7.778-1.414 1.414L13 7.828z"></path>
                </g>
            </svg>
        </button>
    </div>
    <?php require_once(dirname(__FILE__).'/../../components/_script.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.5/dist/bootstrap-table.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.5/dist/extensions/mobile/bootstrap-table-mobile.min.js"></script>
    <script src="/assets/app-assets/js/plugin/apexcharts.min.js"></script>
    <script src="/assets/app-assets/js/charts/apex-chart.js"></script>

    <!-- Cards -->
    <script src="/assets/app-assets/js/cards/card-advance.js"></script>
    <script src="/assets/app-assets/js/cards/card-analytic.js"></script>
    <script src="/assets/app-assets/js/cards/card-statistic.js"></script>
		<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <!-- Pages -->
    <script src="/assets/app-assets/js/pages/dashboard-ecommerce.js"></script>
		<script>
		function initTable(){
			function reloadTable(){
				$('#payment-list').bootstrapTable('refresh');
			}
			<?php
				$tableColumn = include __DIR__.'/ajax/table-headers.php';;
			?>
			$('#payment-list').bootstrapTable({
					url: '<?=SITEDOMAIN?>/analytics/table-payment-list',
					pagination: true,
					pageSize: 10,
					toggle: "table",
					mobileResponsive: true,
					serverSort: true,
					sidePagination: 'server',
					queryParams:(table_params)=>{
							var params = {}
							params.search_text = $(".search-input").val();
							params.from = $('input[name="date[from]"]').val();
							params.to = $('input[name="date[to]"]').val();
							params.results_per_page = !!(table_params.limit)?table_params.limit:1000000;
							params.paging_start = !!(table_params.offset)?table_params.offset:0;
							params.sort = !!(table_params.sort)?table_params.sort:null;
							params.order = !!(table_params.order)?table_params.order:null;
							let advance_search = $('#form-filter-analytics').serializeObject();
							$.extend(params, advance_search);
							return params;
					},
					toolbar: "#exp-toolbar",
					locale: "<?=$language?>",
					search: true,
					columns: <?= json_encode($tableColumn) ?>
			});

			$('#payment-list').on('load-success.bs.table', function (e, data) {
				if($(".container-payment-list .export.btn-export").length==0)
					$('.container-payment-list .fixed-table-toolbar .float-right').append(`
					<div class="export btn-export ms-10" data-type="csv">
						<button class="btn btn-default mr-2 soft-border" aria-label="Export" type="button" title="Esporta dati">
							<i class="fa fa-download" aria-hidden="true"></i>
						</button>
					</div>`);
			});
		}
		$(document).ready(function(){
			
			$('#calendar').daterangepicker({
				"autoApply": true,
				"locale": {
					"format": "DD/MM/YYYY",
    			"drops": "auto",
					"daysOfWeek": [
						"<?=io::_('Sun')?>", "<?=io::_('Mon')?>", "<?=io::_('Tue')?>", "<?=io::_('Wed')?>", "<?=io::_('Thu')?>", "<?=io::_('Fri')?>", "<?=io::_('Sat')?>"
					],
					"monthNames": [
						"<?=io::_('January')?>", "<?=io::_('February')?>", "<?=io::_('March')?>", "<?=io::_('April')?>", "<?=io::_('May')?>", "<?=io::_('June')?>", "<?=io::_('July')?>", "<?=io::_('August')?>", "<?=io::_('September')?>", "<?=io::_('October')?>", "<?=io::_('November')?>", "<?=io::_('December')?>"
					],
					"firstDay": 1
				},
			}, function(start, end, label) {
				$('input[name="date[from]"]').val( start.format('YYYY-MM-DD') );
				$('input[name="date[to]"]').val( end.format('YYYY-MM-DD') );
				reloadContent();
			});

			async function reloadContent(){
				$('.content-page').html(``);
				showLoader();
				$.ajax({
					method:'get',
					url:'/pages/analytics/components/content-page.php',
					data:{
						from:$('input[name="date[from]"]').val(),
						to:$('input[name="date[to]"]').val(),
					},
					dataType:'html'
				})
				.then(html=>{
					hideLoader();
					$('.content-page').html(html);
					var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
					var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
							return new bootstrap.Popover(popoverTriggerEl,{html:true})
					})
					initTable();
				})
				.catch(err=>{
					hideLoader();
					$('.content-page').html(``);
				});
			}
			reloadContent();

			$('body').on('change','#form-filter-analytics',function(){
				reloadContent();
			});
			$('body').on('click','.container-payment-list .btn[aria-label="Export"]',async function(){
				var params = {}
				params.search_text = $(".search-input").val();
				params.from = $('input[name="date[from]"]').val();
				params.to = $('input[name="date[to]"]').val();
				params.results_per_page = 1000000;
				params.paging_start = 0;
				params.sort = null;
				params.order = null;
				let advance_search = $('#form-filter-analytics').serializeObject();
				$.extend(params, advance_search);
				params['download_csv'] = true;
				showLoader();
				await downloadFile("<?=SITEDOMAIN?>/analytics/table-payment-list?"+$.param( params ));
				hideLoader();
			})

		});
	</script>
	<script>
		$(".hp-theme-customizer-button").click(function () {
			$(".hp-theme-customizer").toggleClass("active");
			$('select').bsSelectDrop({
				btnEmptyText:'<?=io::_('All selected')?>'
			});
			$('select').bsSelectDrop('refresh');
		})

		$(".hp-theme-customizer-container-header button").click(function () {
			$(".hp-theme-customizer").removeClass("active");
		})
	</script>
</body>

</html>
<?php io::w(); ?>