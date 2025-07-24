<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once(dirname(__FILE__).'/../../../components/auth.php');
	require_once(dirname(__FILE__).'/../../../components/_global_variables.php');

	new WrapperClass(['mngPayment']);
	
	$to = !empty($_REQUEST['to']) ? $_REQUEST['to'] : date('Y-m-d',strtotime(date('Y-m-d').' +1month ')); 
	$from = !empty($_REQUEST['from']) ? $_REQUEST['from'] : date('Y-m-d',strtotime(date('Y-m-d').' -1month'));
	$walletIds = !empty($_REQUEST['walletIds'])?$_REQUEST['walletIds']:[];

	$mngPayment = new mngPayment($ioConn);
	$payments = $mngPayment->gets([
		'user_code'=>$USERDATA['UserId'],
		'in_range'=>[ 'from'=>$from,'to'=>$to ],
		'wallet_ids'=>$walletIds,
		'not_hidden' => true,
		'data'=>[
			[ 'column'=>'id' ],
			[ 'column'=>'code' ],
			[ 'column'=>'wallet_id' ],
			[ 'column'=>'tag_id' ],
			[ 'column'=>'type' ],
			[ 'column'=>'sign' ],
			[ 'column'=>'description' ],
			[ 'column'=>'amount' ],
			[ 'column'=>'date' ],
			[ 'table'=>'`wallet`','column'=>'name','alias'=>'wallet_name' ],
			[ 'table'=>'`tag`','column'=>'name','alias'=>'tag_name' ],
			[ 'table'=>'`tag`','column'=>'color','alias'=>'tag_color' ],
			[ 'table'=>'`tag`','column'=>'type','alias'=>'tag_type' ],
		]
	])['data'];

	$totalIncome = 0.00;
	$totalExpense = 0.00;
	$tagExpenses = []; // Array per tracciare le spese per tag
	$dailyExpenses = []; // Array per tracciare le spese giornaliere
	$expensesByType = [
		1 => 0.00, // First necessity
		2 => 0.00, // Investment / Saving
		3 => 0.00, // Wishes
		null => 0.00 // Other
	];

	foreach ($payments as $payment) {
			$amount = round($payment['amount'],2);
			// Calcola entrate e uscite in base al segno
			if ($payment['sign'] > 0 && empty($payment['type'])) $totalIncome += round($amount,2);
			elseif ($payment['sign'] < 0 && empty($payment['type'])) {
				$totalExpense += round($amount,2);

				// Traccia la spesa per tag
				$tag = $payment['tag_name'];
				if (!isset($tagExpenses[$tag])) {
						$tagExpenses[$tag] = 0;
				}
				$tagExpenses[$tag] += round(abs($amount),2);

				// Traccia la spesa giornaliera
				$date = $payment['date'];
				if (!isset($dailyExpenses[$date])) {
						$dailyExpenses[$date] = 0;
				}
				$dailyExpenses[$date] += round($amount,2);

				// Aggiungi spesa per tipo di tag
        $tagType = $payment['tag_type']; // Assicurati che questo valore venga recuperato dal database
        if (array_key_exists($tagType, $expensesByType)) {
            $expensesByType[$tagType] += round(abs($amount),2);
        }
			}
	}
	
	// Trova il tag con la spesa maggiore
	$topExpenseTag = null;
	$maxExpense = 0;

	foreach ($tagExpenses as $tag => $expense) {
			if ($expense > $maxExpense) {
					$maxExpense = $expense;
					$topExpenseTag = $tag;
			}
	}

	// Calcola la media giornaliera delle spese
	$daysCount = dateStrDiffDays($from,$to);
	$avgDailyExpense = $daysCount > 0 ? $totalExpense / $daysCount : 0.00;
	$avgDailyExpense = abs($avgDailyExpense);
	$totalExpense = abs($totalExpense);

	new WrapperClass(['mngWallet']);
	$mngWallet = new mngWallet($ioConn);
	$wallets = $mngWallet->gets([ 'user_code'=>$USERDATA['UserId'],'ids'=>$walletIds ])['data'];

	$mngWalletHistory = new mngWalletHistory($ioConn);
	$histories = $mngWalletHistory->gets([ 
		'user_code'=>$USERDATA['UserId'],
		'from'=>$from,
		'wallet_ids'=>$walletIds,
	])['data'];

	$mngWalletForecast = new mngWalletForecast($ioConn);
	$all_forecast = $mngWalletForecast->gets([ 
		'user_code'=>$USERDATA['UserId'],
		'from'=>$from,
		'wallet_ids'=>$walletIds,
	])['data'];

	$dateRange =createPeriod( $from , $to,'P1M',true);
	$chartTrend = [];
	foreach ($dateRange as $date) {
		$Ym = $date->format('Y-m');
		if($Ym == date('Y-m')) continue;
		$histories_ym = array_filter($histories,function($r) use($Ym){
			$row_ym = $r['year'].'-'.str_pad($r['month'], 2, "0", STR_PAD_LEFT);
			return $row_ym == $Ym;
		});
		$all_forecast_ym = array_filter($all_forecast,function($r) use($Ym){
			$row_ym = $r['year'].'-'.str_pad($r['month'], 2, "0", STR_PAD_LEFT);
			return $row_ym == $Ym;
		});
		$chartTrend[] = [
			'date'=>ucfirst(formatLanguage(date_create($date->format('Y-m-d')),'M',$language)),
			'month'=>$date->format('m'),
			'year'=>$date->format('Y'),
			'amount_expenses'=>!empty($histories_ym)?round( abs(array_sum( array_column($histories_ym,'amount_expenses') )),2):0.00,
			'amount_income'=>!empty($histories_ym)?round( array_sum( array_column($histories_ym,'amount_income') ),2):0.00,
			'amount_balance'=>!empty($histories_ym)?round( array_sum( array_column($histories_ym,'amount_balance') ),2):0.00,

			'forecast_amount_expenses'=>!empty($all_forecast_ym)?round( abs(array_sum( array_column($all_forecast_ym,'amount_expenses') )),2):0.00,
			'forecast_amount_income'=>!empty($all_forecast_ym)?round( array_sum( array_column($all_forecast_ym,'amount_income') ),2):0.00,
			'forecast_amount_balance'=>!empty($all_forecast_ym)?round( array_sum( array_column($all_forecast_ym,'amount_balance') ),2):0.00,
		];
	}
	if(date('Y-m-d') >= $from && date('Y-m-d') <= $to){
		$all_forecast_ym = array_filter($all_forecast,function($r){
			$row_ym = $r['year'].'-'.str_pad($r['month'], 2, "0", STR_PAD_LEFT);
			return $row_ym == date('Y-m');
		});
		$chartTrend[] = [
			'date'=>ucfirst(formatLanguage(date_create(date('Y-m-d')),'M',$language)),
			'month'=>date('m'),
			'year'=>date('Y'),
			'amount_expenses'=>!empty($wallets)?round( abs(array_sum( array_column($wallets,'amount_expenses') )),2):0.00,
			'amount_income'=>!empty($wallets)?round( array_sum( array_column($wallets,'amount_income') ),2):0.00,
			'amount_balance'=>!empty($wallets)?round( array_sum( array_column($wallets,'amount_balance') ),2):0.00,
	
			'forecast_amount_expenses'=>!empty($all_forecast_ym)?round( abs(array_sum( array_column($all_forecast_ym,'amount_expenses') )),2):0.00,
			'forecast_amount_income'=>!empty($all_forecast_ym)?round( array_sum( array_column($all_forecast_ym,'amount_income') ),2):0.00,
			'forecast_amount_balance'=>!empty($all_forecast_ym)?round( array_sum( array_column($all_forecast_ym,'amount_balance') ),2):0.00,
		];
	}
	map_array_field($chartTrend,'ym',function($r){ return $r['year'].'-'.str_pad($r['month'], 2, "0", STR_PAD_LEFT); });
	$chartTrend = array_column($chartTrend,null,'ym');
	uksort($chartTrend, function($a, $b) { return strcmp($a, $b); });

	$maxValueIncome = !empty($chartTrend)? max(array_column($chartTrend,'amount_income')) : 0;
	$maxValueIncome += ($maxValueIncome*50)/100;
	$maxValueIncome = round(roundUpToTenOrHundred($maxValueIncome),2);

	$maxValueExpenses = !empty($chartTrend)? max(array_column($chartTrend,'amount_expenses')) : 0;
	$maxValueExpenses += ($maxValueExpenses*50)/100;
	$maxValueExpenses = round(roundUpToTenOrHundred($maxValueExpenses),2);

	if( $maxValueExpenses >= $maxValueIncome )
		$maxValue = $maxValueExpenses;
	else $maxValue = $maxValueIncome;

?>

<!-- index -->
<div class="row g-32 mb-32">
		<div class="col-12 col-md-6 col-xl-3">
				<div class="card">
						<div class="card-body">
								<div class="row g-16">
										<div class="col-6 hp-flex-none w-auto">
												<div class="avatar-item d-flex align-items-center justify-content-center avatar-lg bg-primary-4 hp-bg-color-dark-primary rounded-circle">
														<svg class="hp-text-color-black-bg hp-text-color-dark-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
															<path d="m11.94 2.212-2.41 5.61H7.12c-.4 0-.79.03-1.17.11l1-2.4.04-.09.06-.16c.03-.07.05-.13.08-.18 1.16-2.69 2.46-3.53 4.81-2.89ZM18.731 8.09l-.02-.01c-.6-.17-1.21-.26-1.83-.26h-6.26l2.25-5.23.03-.07c.14.05.29.12.44.17l2.21.93c1.23.51 2.09 1.04 2.62 1.68.09.12.17.23.25.36.09.14.16.28.2.43.04.09.07.17.09.26.15.51.16 1.09.02 1.74ZM18.288 9.52c-.45-.13-.92-.2-1.41-.2h-9.76c-.68 0-1.32.13-1.92.39a4.894 4.894 0 0 0-2.96 4.49v1.95c0 .24.02.47.05.71.22 3.18 1.92 4.88 5.1 5.09.23.03.46.05.71.05h7.8c3.7 0 5.65-1.76 5.84-5.26.01-.19.02-.39.02-.59V14.2a4.9 4.9 0 0 0-3.47-4.68Zm-3.79 7.23h-5c-.41 0-.75-.34-.75-.75s.34-.75.75-.75h5c.41 0 .75.34.75.75s-.34.75-.75.75Z" fill="#00435F"></path>
														</svg>
												</div>
										</div>

										<div class="col">
												<h3 class="mb-4 mt-8"> € <?=number_format($totalIncome,$totalIncome>99999?0:2,',','.')?> </h3>
												<p class="hp-p1-body mb-0 text-black-80 hp-text-color-dark-30"><?=io::_('Total Income')?></p>
										</div>
								</div>
						</div>
				</div>
		</div>

		<div class="col-12 col-md-6 col-xl-3">
				<div class="card">
						<div class="card-body">
								<div class="row g-16">
										<div class="col-6 hp-flex-none w-auto">
												<div class="avatar-item d-flex align-items-center justify-content-center avatar-lg bg-secondary-4 hp-bg-color-dark-secondary rounded-circle">
														<i class="iconly-Light-Buy text-secondary" style="font-size: 24px;"></i>
												</div>
										</div>

										<div class="col">
												<h3 class="mb-4 mt-8"> € <?=number_format($totalExpense,$totalExpense>99999?0:2,',','.')?> </h3>
												<p class="hp-p1-body mb-0 text-black-80 hp-text-color-dark-30"><?=io::_('Total Expenses')?></p>
										</div>
								</div>
						</div>
				</div>
		</div>

		<div class="col-12 col-md-6 col-xl-3">
				<div class="card">
						<div class="card-body">
								<div class="row g-16">
										<div class="col-6 hp-flex-none w-auto">
												<div class="avatar-item d-flex align-items-center justify-content-center avatar-lg bg-warning-4 hp-bg-color-dark-warning rounded-circle">
														<i class="iconly-Broken-Category text-warning" style="font-size: 24px;"></i>
												</div>
										</div>

										<div class="col">
												<h3 class="mb-4 mt-8"> <?= !empty($topExpenseTag)?$topExpenseTag:'-' ?></h3>
												<p class="hp-p1-body mb-0 text-black-80 hp-text-color-dark-30"><?=io::_('Top Expense Tag')?></p>
										</div>
								</div>
						</div>
				</div>
		</div>

		<div class="col-12 col-md-6 col-xl-3">
				<div class="card">
						<div class="card-body">
								<div class="row g-16">
										<div class="col-6 hp-flex-none w-auto">
												<div class="avatar-item d-flex align-items-center justify-content-center avatar-lg bg-danger-4 hp-bg-color-dark-danger rounded-circle">
														<i class="iconly-Light-Discount text-danger" style="font-size: 24px;"></i>
												</div>
										</div>

										<div class="col">
												<h3 class="mb-4 mt-8"> € <?=number_format($avgDailyExpense,$avgDailyExpense>99999?0:2,',','.')?> </h3>
												<p class="hp-p1-body mb-0 text-black-80 hp-text-color-dark-30"><?=io::_('Avg Daily Expense')?></p>
										</div>
								</div>
						</div>
				</div>
		</div>
</div>

<div class="row g-32">
		<div class="col-12 col-xl-8">
				<div class="row g-32">
						<!-- Revenue -->
						<div class="col-12">
								<div class="card hp-card-6 hp-chart-text-color">
										<div class="card-body">
												<div class="row justify-content-between mb-16">
														<div class="col-6">
																<h4 class="me-8"><?=io::_('Trend')?></h4>
														</div>
												</div>

												<div id="analytics-trend-chart"></div>
										</div>
								</div>
						</div>

				</div>
		</div>
		<!-- Earnings -->
		<div class="col-12 col-xl-4">
				<div class="row g-32">
						<div class="col-12">
								<div class="card hp-card-6">
										<div class="card-body">
												<div class="row">
														<div class="col-12">
															<div class="d-flex align-items-center justify-content-between mb-32">
																<h5 class="mb-0"><?=io::_('Distribution of Expenses')?></h5>
																<div class="hp-cursor-pointer hp-transition hp-hover-bg-dark-100 hp-hover-bg-black-10"
																	data-bs-toggle="popover" data-bs-trigger="hover focus"
																	title="<?=io::_('Rule of 20/30/50')?>"  
																	data-bs-content="<?=io::_('The 20/30/50 rule suggests allocating 50 percent of your income to necessary expenses, 30 percent to wants, and 20 percent to savings and investments. This circle chart illustrates how you are currently distributing your expenses, helping you maintain a financial balance')?>"
																	>
																	<i class="hp-text-color-dark-0 iconly-Light-InfoSquare"></i>
																</div>
															</div>
														</div>

														<div class="col-12">
																<div id="distribution-expenses-donut-card" class="hp-donut-chart"></div>
														</div>
												</div>
										</div>
								</div>
						</div>


				</div>
		</div>
</div>
<style>
	.fixed-table-container{

			/* position: relative; */
			display: -webkit-box;
			display: -ms-flexbox;
			display: flex;
			-webkit-box-orient: vertical;
			-webkit-box-direction: normal;
			-ms-flex-direction: column;
			flex-direction: column;
			min-width: 0;
			word-wrap: break-word;
			background-color: #fff;
			background-clip: border-box;
			border: 1px solid #dfe6e9;
			border-radius: 24px;
	}
	.table thead th:first-child{
			border-top-left-radius:24px;
	}
	.table thead th:last-child{
			border-top-right-radius:24px;
	}
	.table-hover > tbody > tr:last-child:hover > td:first-child{
			border-bottom-left-radius: 24px;
	}
	.table-hover > tbody > tr:last-child:hover > td:last-child{
			border-bottom-right-radius: 24px;
	}
	.fixed-table-container table{
			border-color: transparent;
	}
</style>
<div class="row g-32 mt-10">
	<div class="col-12">
			<div class="card ">
					<div class="card-body container-payment-list">
							<div class="row mx-0">
								<div id="exp-toolbar" class="row justify-content-start">
									<div class="col hp-flex-none w-auto d-flex">
										<h4 class="mb-0"><?=io::_('Processed Transactions')?></h4>
									</div>
								</div>
								<table class="table align-middle table-hover table-borderless" id="payment-list" ></table>
									
							</div>
					</div>
			</div>
	</div>
</div>
<script>

		if (document.querySelector("#distribution-expenses-donut-card")) {
			let chart = new ApexCharts(document.querySelector("#distribution-expenses-donut-card"), {
					series: <?= json_encode(array_values($expensesByType)) ?>,
					chart: {
							id: "distribution-expenses-donut-card",
							fontFamily: "Manrope, sans-serif",
							type: "donut",
							height: 350,
							toolbar: {
									show: false,
							},
							zoom: {
									enabled: false,
							},
					},
					colors: ["#00435F","#0063F7", "#98FFE0", "#1BE7FF"],
					labels: ["<?=io::_('First necessity')?>","<?= io::_('Investment / Saving') ?>", "<?= io::_('Wishes') ?>", "<?= io::_('Other') ?>"],

					dataLabels: {
							enabled: false,
					},

					plotOptions: {
							pie: {
									donut: {
											size: "90%",
											labels: {
													show: true,
													name: {
															fontSize: "2rem",
													},
													value: {
															fontSize: "24px",
															fontWeight: "regular",
															color: "B2BEC3",
															formatter(val) {
																const total = Number('<?=array_sum(array_values($expensesByType))?>');
																let perc = total > 0 ?  (val / total)*100 : 0;
																perc = perc.toFixed(1);
																return `${perc}%`;
															},
													},

													total: {
															show: true,
															fontSize: "24px",
															fontWeight: "regular",
															label: "<?=io::_('Total')?>",
															color: "#636E72",

															formatter: function (w) {
																	return `€ ${w.globals.initialSeries.reduce((a, b) => {
																			return Number((a + b).toFixed(2));
																	}, 0)}`;
															},
													},
											},
									},
							},
					},
					responsive: [
							{
									breakpoint: 426,
									options: {
											legend: {
													itemMargin: {
															horizontal: 16,
															vertical: 8,
													},
											},
									},
							},
					],

					legend: {
							itemMargin: {
									horizontal: 12,
									vertical: 24,
							},
							horizontalAlign: "center",
							position: "bottom",
							fontSize: "12px",
							inverseOrder: true,
							markers: {
									radius: 12,
							},
					},
			});
			chart.render();
		}
		if (document.querySelector("#analytics-trend-chart")) {
			let chartTrend = new ApexCharts(document.querySelector("#analytics-trend-chart"), {
					series: [
							{
									name: "<?=io::_('Income')?>",
									data: <?= json_encode(array_values( array_column($chartTrend,'amount_income') )) ?>,
							},
							{
									name: "<?=io::_('Expense')?>",
									data: <?= json_encode(array_values( array_column($chartTrend,'amount_expenses') )) ?>,
							},
							{
									name: "<?=io::_('Forecast Income')?>",
									data: <?= json_encode(array_values( array_column($chartTrend,'forecast_amount_income') )) ?>,
							},
							{
									name: "<?=io::_('Forecast  Expense')?>",
									data: <?= json_encode(array_values( array_column($chartTrend,'forecast_amount_expenses') )) ?>,
							},
					],
					chart: {
							id: "analytics-trend-chart",
							fontFamily: "Manrope, sans-serif",
							type: "bar",
							height: 300,
							toolbar: {
									show: false,
							},
							zoom: {
									enabled: false,
							},
					},
					labels: {
							style: {
									fontSize: "14px",
							},
					},

					dataLabels: {
							enabled: false,
					},

					grid: {
							borderColor: "#DFE6E9",
							row: {
									opacity: 0.5,
							},
					},
					plotOptions: {
							bar: {
									horizontal: false,
									borderRadius: 2,
									columnWidth: "45%",
									endingShape: "rounded",
							},
							colors: {
									backgroundBarColors: ["#0063F7", "#ff0022","#ebfafa","#ffe7ea"],
							},
					},

					stroke: {
							show: true,
							width: 4,
							colors: ["transparent"],
					},
					xaxis: {
							axisTicks: {
									show: false,
									borderType: "solid",
									color: "#78909C",
									height: 6,
									offsetX: 0,
									offsetY: 0,
							},

							tickPlacement: "between",
							labels: {
									style: {
											colors: ["636E72"],
											fontSize: "14px",
									},
							},
							categories: <?=json_encode(array_column($chartTrend,'date'))?>,
					},
					legend: {
							horizontalAlign: "right",
							offsetX: 40,
							position: "top",
							markers: {
									radius: 12,
							},
					},
					yaxis: {
							labels: {
									style: {
											colors: ["636E72"],
											fontSize: "14px",
									},
									formatter: (value) => {
											let n = Number(value);
											return n < 1000 ? number_format(n,2,',','.') : number_format((n / 1000).toFixed(2),2,',','.') + "K";
									},
							},

							min: 0,
							max: <?=$maxValue?>,
							tickAmount: 4,
					},
			});
			chartTrend.render();
		}
</script>

<?php io::w(); ?>