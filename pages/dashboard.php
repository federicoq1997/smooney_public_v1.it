<?php
    require_once(dirname(__FILE__).'/../components/auth.php');
    require_once(dirname(__FILE__).'/../components/_global_variables.php');
    $pageTitle = 'Smooney - Dashboard';

    new WrapperClass(['mngWallet']);
    $mngWallet = new mngWallet($ioConn);
    $wallets = $mngWallet->gets([ 'user_code'=>$USERDATA['UserId'] ])['data'];

    $mngWalletHistory = new mngWalletHistory($ioConn);
    $histories = $mngWalletHistory->gets([
        'user_code'=>$USERDATA['UserId'],
        'from'=>date('Y-m',strtotime(date('Y-m-d').' -12 months'))
    ])['data'];
    $history = [];
    foreach($histories as $_history){
        $key = $_history['year'].'-'.str_pad($_history['month'], 2, "0", STR_PAD_LEFT);
        if(empty($history[$key])) $history[$key] = $_history;
        else{
            $history[$key]['amount_expenses']+= !empty($_history['amount_expenses'])?round($_history['amount_expenses'],2):0.00;
            $history[$key]['amount_income']+= !empty($_history['amount_income'])?round($_history['amount_income'],2):0.00;
            $history[$key]['amount_balance']+= !empty($_history['amount_balance'])?round($_history['amount_balance'],2):0.00;
        }
    }
    $key = date('Y').'-'.date('m');
    $history[$key] = [
        'month'=>date('m'),
        'year'=>date('Y'),
        'amount_expenses'=>0.00,
        'amount_income'=>0.00,
        'amount_balance'=>0.00,
    ];
    foreach($wallets as $wallet){
        $history[$key]['amount_expenses']+= !empty($wallet['amount_expenses'])?round($wallet['amount_expenses'],2):0.00;
        $history[$key]['amount_income']+= !empty($wallet['amount_income'])?round($wallet['amount_income'],2):0.00;
        $history[$key]['amount_balance']+= !empty($wallet['amount_balance'])?round($wallet['amount_balance'],2):0.00;
    }

    $series = [];
    $dateRange =createPeriod(date('Y-m-d',strtotime(date('Y-m-d').' -12 months')),date('Y-m-d'),'P1M',true);
    $series['other'] = [ 
        'name'=>io::_('Balance'),
        'data'=>[],
        'color'=>'var(--smooney)',
        'group'=>"apexcharts-axis-0",
    ];
    foreach ($dateRange as $date) {
		$Ym = $date->format('Y-m');
        $history_date = !empty($history[$Ym])?$history[$Ym]:null;
        $balance = !empty($history_date['amount_balance'])?round($history_date['amount_balance'],2):0.00;
        // $sum_tag = !empty($history_date['info_tag'])? array_sum(array_values($history_date['info_tag'])) :0.00;
        $series['other']['data'][] = ['x'=>ucfirst(formatLanguage(date_create($date->format('Y-m-d')),'M',$language)),'y'=>round($balance,2) ];
    }
    // die(json_encode($series));
    $maxBalance = !empty($history)? max(array_column($history,'amount_balance')) : 0;
    $maxBalance += ($maxBalance*50)/100;
    $maxBalance = round(roundUpToTenOrHundred($maxBalance),2);

    new WrapperClass(['mngPayment']);
    $mngPayment = new mngPayment($ioConn);
    $last_payments = $mngPayment->gets([
        'user_code'=>$USERDATA['UserId'],
        'limit'=>7,
        'sort'=>'DESC',
        'not_hidden'=>true,
        'data'=>[
            [ 'column'=>'description' ],
            [ 'column'=>'sign' ],
            [ 'column'=>'amount' ],
            [ 'column'=>'date' ],
            [ 'table'=>'wallet','column'=>'name','alias'=>'wallet_name' ],
            [ 'table'=>'wallet','column'=>'icon','alias'=>'wallet_icon' ],
            [ 'table'=>'tag','column'=>'name','alias'=>'tag_name' ]
        ]
    ])['data'];

?>
<!DOCTYPE html>
<html dir="ltr">

<head>
    <?php require_once(dirname(__FILE__).'/../components/_headcontent.php'); ?>
    <!-- Charts -->
    <link rel="stylesheet" type="text/css" href="<?=SITEDOMAIN?>/assets/app-assets/css/plugin/apex-charts.css">
    <!-- Pages -->
    <link rel="stylesheet" type="text/css" href="<?=SITEDOMAIN?>/assets/app-assets/css/pages/dashboard-analytics.css">
</head>

<body>
    <main class="hp-bg-color-dark-90 d-flex min-vh-100">
        <!-- sidebar -->
        <?php require_once(dirname(__FILE__).'/../components/sidebar/_sidebar.php'); ?>

        <div class="hp-main-layout">

            <?php require_once(dirname(__FILE__).'/../components/_header.php'); ?>

            <div class="hp-main-layout-content">
                <div class="row mb-32 g-32">

                    <?php if( !empty($USERDATA['User']) && in_array($USERDATA['User'],[0])){ ?>
                        <a class="col-12" href="/love">
                            <div class="py-10 px-36 position-relative overflow-hidden hp-page-content" style="border-radius: .75rem;background: #004360 !important;">
                                

                                <div class="row">
                                    <div class="col">
                                        <div class="row">
                                            <div class="col-12">
                                                <h1 class="mb-0 hp-text-color-black-0">Abbiamo qualcosa di speciale per te. Scoprilo qui!</h1>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php } ?>

                    <div class="col-lg-6 col-12 flex-grow-1 overflow-hidden">
                        <div class="row g-32">
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h1 class="hp-mb-0 my-10"><?=io::_('Dashboard')?></h1>
                                    <button type="button" class="btn smooney-primary btn-new-transaction my-10" style="color:var(--smooney);cursor:pointer;"><?=io::_('New Transaction')?></button>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-32">
                                    <div class="col" id="box-income">
                                        <div class="card hp-dashboard-feature-card hp-border-color-black-0 hp-border-color-dark-80 hp-cursor-pointer">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-center hp-dashboard-feature-card-icon rounded-3 hp-bg-black-20 hp-bg-dark-80" style="width: 48px; height: 48px;">
                                                    <svg class="hp-text-color-black-bg hp-text-color-dark-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <path d="M18.809 6.25h1.36c-.19-.27-.39-.52-.6-.77l-.76.77ZM18.52 4.42c-.25-.21-.5-.41-.77-.6v1.36l.77-.76ZM19.58 5.481l2.95-2.95c.29-.29.29-.77 0-1.06a.754.754 0 0 0-1.06 0l-2.95 2.95c.38.33.73.69 1.06 1.06ZM17.752 3c0-.41-.34-.75-.75-.75-.4 0-.72.32-.74.71.52.25 1.02.53 1.49.86V3ZM21.752 7c0-.41-.34-.75-.75-.75h-.83c.33.47.62.97.86 1.49.4-.02.72-.34.72-.74ZM12.75 14.75h.3c.39 0 .7-.35.7-.78 0-.54-.15-.62-.49-.74l-.51-.18v1.7Z" fill="currentColor"></path>
                                                        <path d="M21.04 7.74c-.01 0-.02.01-.04.01h-4c-.1 0-.19-.02-.29-.06a.782.782 0 0 1-.41-.41.868.868 0 0 1-.05-.28V3c0-.01.01-.02.01-.04C14.96 2.35 13.52 2 12 2 6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10c0-1.52-.35-2.96-.96-4.26Zm-7.29 4.08c.64.22 1.5.69 1.5 2.16 0 1.25-.99 2.28-2.2 2.28h-.3v.25c0 .41-.34.75-.75.75s-.75-.34-.75-.75v-.25h-.08c-1.33 0-2.42-1.12-2.42-2.5 0-.42.34-.76.75-.76s.75.34.75.75c0 .55.41 1 .92 1h.08v-2.22l-1-.35c-.64-.22-1.5-.69-1.5-2.16 0-1.25.99-2.28 2.2-2.28h.3V7.5c0-.41.34-.75.75-.75s.75.34.75.75v.25h.08c1.33 0 2.42 1.12 2.42 2.5 0 .41-.34.75-.75.75s-.75-.34-.75-.75c0-.55-.41-1-.92-1h-.08v2.22l1 .35Z" fill="currentColor"></path>
                                                        <path d="M10.25 10.03c0 .54.15.62.49.74l.51.18v-1.7h-.3c-.38 0-.7.35-.7.78Z" fill="currentColor"></path>
                                                    </svg>
                                                </div>

                                                <div class="d-flex mt-12">
                                                    <span class="h4 mb-0 d-block hp-text-color-black-bg hp-text-color-dark-0 fw-medium me-4"> <?=io::_('Income')?> </span>
                                                    <div>
                                                        <svg class="hp-text-color-success-1" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                            <path fill="currentColor" d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81v8.37C2 19.83 4.17 22 7.81 22h8.37c3.64 0 5.81-2.17 5.81-5.81V7.81C22 4.17 19.83 2 16.19 2zm1.06 10.33c0 .41-.34.75-.75.75s-.75-.34-.75-.75V9.31l-7.72 7.72c-.15.15-.34.22-.53.22s-.38-.07-.53-.22a.754.754 0 010-1.06l7.72-7.72h-3.02c-.41 0-.75-.34-.75-.75s.34-.75.75-.75h4.83c.41 0 .75.34.75.75v4.83z"></path>
                                                        </svg>
                                                    </div>
                                                </div>

                                                <span class="hp-caption mt-4 d-block fw-normal hp-text-color-black-60">  <?=ucfirst(formatLanguage(date_create(date('Y-m-d')),'F Y',$language))?> </span>
                                                <span class="d-block mt-12 mb-8 h3"> € <?=number_format($history[date('Y').'-'.date('m')]['amount_income'],$history[date('Y').'-'.date('m')]['amount_income']>99999?0:2,',','.')?> </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col" id="box-expenses">
                                        <div class="card hp-dashboard-feature-card hp-border-color-black-0 hp-border-color-dark-80 hp-cursor-pointer">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-center hp-dashboard-feature-card-icon rounded-3 hp-bg-black-20 hp-bg-dark-80" style="width: 48px; height: 48px;">
                                                    <svg class="hp-text-color-black-bg hp-text-color-dark-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <path d="M10.25 10.03c0 .54.15.62.49.74l.51.18v-1.7h-.3c-.38 0-.7.35-.7.78ZM12.75 14.75h.3c.39 0 .7-.35.7-.78 0-.54-.15-.62-.49-.74l-.51-.18v1.7Z" fill="currentColor"></path>
                                                        <path d="m19.58 5.48-2.05 2.05c-.15.15-.34.22-.53.22s-.38-.07-.53-.22a.754.754 0 0 1 0-1.06l2.05-2.05C16.76 2.92 14.49 2 12 2 6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10c0-2.49-.92-4.76-2.42-6.52Zm-5.83 6.34c.64.23 1.5.69 1.5 2.16 0 1.25-.99 2.28-2.2 2.28h-.3v.25c0 .41-.34.75-.75.75s-.75-.34-.75-.75v-.25h-.08c-1.33 0-2.42-1.12-2.42-2.5 0-.42.34-.76.75-.76s.75.34.75.75c0 .55.41 1 .92 1h.08v-2.22l-1-.35c-.64-.23-1.5-.69-1.5-2.16 0-1.25.99-2.28 2.2-2.28h.3V7.5c0-.41.34-.75.75-.75s.75.34.75.75v.25h.08c1.33 0 2.42 1.12 2.42 2.5 0 .41-.34.75-.75.75s-.75-.34-.75-.75c0-.55-.41-1-.92-1h-.08v2.22l1 .35ZM22.69 1.71a.782.782 0 0 0-.41-.41.868.868 0 0 0-.28-.05h-4c-.41 0-.75.34-.75.75s.34.75.75.75h2.19l-1.67 1.67c.38.33.73.68 1.06 1.06l1.67-1.67V6c0 .41.34.75.75.75s.75-.34.75-.75V2c0-.1-.02-.19-.06-.29Z" fill="currentColor"></path>
                                                    </svg>
                                                </div>

                                                <div class="d-flex mt-12">
                                                    <span class="h4 mb-0 d-block hp-text-color-black-bg hp-text-color-dark-0 fw-medium me-4"> <?=io::_('Expenses')?> </span>
                                                    <div>
                                                        <svg class="hp-text-color-danger-1" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none">
                                                            <path fill="currentColor" d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81v8.37C2 19.83 4.17 22 7.81 22h8.37c3.64 0 5.81-2.17 5.81-5.81V7.81C22 4.17 19.83 2 16.19 2zm1.34 5.53l-7.72 7.72h3.02c.41 0 .75.34.75.75s-.34.75-.75.75H8c-.41 0-.75-.34-.75-.75v-4.83c0-.41.34-.75.75-.75s.75.34.75.75v3.02l7.72-7.72c.15-.15.34-.22.53-.22s.38.07.53.22c.29.29.29.77 0 1.06z"></path>
                                                        </svg>
                                                    </div>
                                                </div>

                                                <span class="hp-caption mt-4 d-block fw-normal hp-text-color-black-60">  <?=ucfirst(formatLanguage(date_create(date('Y-m-d')),'F Y',$language))?> </span>
                                                <span class="d-block mt-12 mb-8 h3"> € <?=number_format($history[date('Y').'-'.date('m')]['amount_expenses'],$history[date('Y').'-'.date('m')]['amount_expenses']>99999?0:2,',','.')?> </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col" id="box-balance">
                                        <div class="card hp-dashboard-feature-card hp-border-color-black-0 hp-border-color-dark-80 hp-cursor-pointer">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-center hp-dashboard-feature-card-icon rounded-3 hp-bg-black-20 hp-bg-dark-80" style="width: 48px; height: 48px;">
                                                    <svg class="hp-text-color-black-bg hp-text-color-dark-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                        <path d="m11.94 2.212-2.41 5.61H7.12c-.4 0-.79.03-1.17.11l1-2.4.04-.09.06-.16c.03-.07.05-.13.08-.18 1.16-2.69 2.46-3.53 4.81-2.89ZM18.731 8.09l-.02-.01c-.6-.17-1.21-.26-1.83-.26h-6.26l2.25-5.23.03-.07c.14.05.29.12.44.17l2.21.93c1.23.51 2.09 1.04 2.62 1.68.09.12.17.23.25.36.09.14.16.28.2.43.04.09.07.17.09.26.15.51.16 1.09.02 1.74ZM18.288 9.52c-.45-.13-.92-.2-1.41-.2h-9.76c-.68 0-1.32.13-1.92.39a4.894 4.894 0 0 0-2.96 4.49v1.95c0 .24.02.47.05.71.22 3.18 1.92 4.88 5.1 5.09.23.03.46.05.71.05h7.8c3.7 0 5.65-1.76 5.84-5.26.01-.19.02-.39.02-.59V14.2a4.9 4.9 0 0 0-3.47-4.68Zm-3.79 7.23h-5c-.41 0-.75-.34-.75-.75s.34-.75.75-.75h5c.41 0 .75.34.75.75s-.34.75-.75.75Z" fill="currentColor"></path>
                                                    </svg>
                                                </div>

                                                <div class="d-flex mt-12">
                                                    <span class="h4 mb-0 d-block hp-text-color-black-bg hp-text-color-dark-0 fw-medium me-4"> <?=io::_('Balance')?> </span>
                                                    <div></div>
                                                </div>

                                                <span class="hp-caption mt-4 d-block fw-normal hp-text-color-black-60"> <?=ucfirst(formatLanguage(date_create(date('Y-m-d')),'F Y',$language))?> </span>
                                                <span class="d-block mt-12 mb-8 h3"> € <?=number_format($history[date('Y').'-'.date('m')]['amount_balance'],$history[date('Y').'-'.date('m')]['amount_balance']>99999?0:2,',','.')?> </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row">
                                    <div class="mb-18 col-12">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="hp-flex-none w-auto col">
                                                <span class="d-block hp-p1-body"><?=io::_('Balance of the last 12 months')?></span>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="overflow-hidden col-12 mb-n24">
                                        <div id="dashboard-analytics-balance-chart" class="overflow-hidden"></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="hp-flex-none w-auto hp-dashboard-line px-0 col">
                        <div class="hp-bg-black-40 hp-bg-dark-80 h-100 mx-24" style="width: 1px;"></div>
                    </div>

                    <div class="col-lg-5 col-12 hp-analytics-col-2">
                        <div class="row g-32">

                            <div class="col-12">
                                <span class="h3 d-block fw-semibold hp-text-color-black-bg hp-text-color-dark-0 mb-0"> <?=io::_('Recent Activities')?> </span>
                                <?php if(!empty($last_payments)){ ?>
                                    <span class="hp-p1-body d-block mt-4"> <?=capitalizeFirstLetter(formatLanguage(date_create(reset($last_payments)['date']),'d M Y',$language))?> </span>
                                <?php } ?>

                                <div class="row mt-24" id="container-recent-activities">
                                    <?php if(!empty($last_payments)) foreach($last_payments as $last_payment){ ?>
                                    <div class="hp-cursor-pointer hp-transition hp-hover-bg-dark-100 hp-hover-bg-black-10 rounded py-8 mb-16 col-12 " data-code="<?=$last_payment['code']?>" <?=$last_payment['sign']>0?'data-income="1"':''?> 
                                        data-bs-toggle="popover" data-bs-trigger="hover focus"
                                        title="<?=$last_payment['wallet_name'].(!empty($last_payment['description'])?' | '.$last_payment['description']:'¿?')?>"  
                                        data-bs-content="<div class='row g-16 align-items-center'>
                                            <div class='col-6 hp-flex-none w-auto mt-0'>
                                                <div class='avatar-item d-flex align-items-center justify-content-center avatar-lg <?= $last_payment['sign']>0?'bg-success-4 hp-bg-color-dark-success':'bg-danger-4 hp-bg-color-dark-danger'?>  rounded-circle'>
                                                    <i class='iconly-Light-<?= $last_payment['sign']>0?'ArrowUpSquare text-success-3':'ArrowDownSquare text-danger-3'?>' style='font-size: 24px;'></i>
                                                </div>
                                            </div>

                                            <div class='col mt-0'>
                                                <h5 class='mb-4 mt-8'>€ <?= numberFormatReduceAccuracyThousands($last_payment['amount']) ?></h5>
                                                <small class='hp-p1-body mb-0 text-black-80 hp-text-color-dark-30'><?=(!empty($last_payment['tag_name'])?$last_payment['tag_name']:'')?></small>
                                            </div>
                                        </div>"
                                        >
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col">
                                                <div class="row align-items-center">
                                                    <div class="hp-flex-none w-auto pe-0 col">
                                                        <div class="me-16 border hp-border-color-black-10 hp-bg-black-0 rounded-3 d-flex align-items-center justify-content-center" style="min-width: 48px; height: 48px;">
                                                            <i class="<?=$last_payment['wallet_icon']?> text-primary lh-1" style="font-size: 30px;color:var(--smooney) !important"></i>
                                                            <i class="hp-text-color-dark-0 iconly-Bold-<?= $last_payment['sign']>0?'ArrowUpSquare text-success-3':'ArrowDownSquare text-danger-3'?> " style="font-size: 14px;position: absolute;transform: translate(10px, 10px);background: #fff;border-radius: 100%;"></i>
                                                        </div>
                                                    </div>

                                                    <div class="hp-flex-none w-auto ps-0 col">
                                                        <span class="d-block hp-p1-body fw-medium hp-text-color-black-bg hp-text-color-dark-0"> <?=(!empty($last_payment['description'])?truncateString($last_payment['description']):'¿?')?> </span>
                                                        <span class="d-block hp-caption fw-normal hp-text-color-black-60"> <?=(!empty($last_payment['wallet_name'])? substr($last_payment['wallet_name'], 0, 6).(strlen($last_payment['wallet_name'])>6?'...':'').' -':'')?> <?=capitalizeFirstLetter(formatLanguage(date_create($last_payment['date']),'d M Y',$language))?> </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="hp-flex-none w-auto col">
                                                <span class="h5 hp-text-color-black-bg hp-text-color-dark-0"> € <?= numberFormatReduceAccuracyThousands($last_payment['amount']) ?> </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php }?>
                                </div>
                            </div>

                        </div>
                    </div>


                </div>

            </div>

            <?php require_once(dirname(__FILE__).'/../components/_footer.php'); ?>
        </div>
    </main>

    <div class="scroll-to-top">
        <button type="button" class="btn smooney-primary btn-icon-only rounded-circle hp-primary-shadow">
            <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="16px" width="16px" xmlns="http://www.w3.org/2000/svg">
                <g>
                    <path fill="none" d="M0 0h24v24H0z"></path>
                    <path d="M13 7.828V20h-2V7.828l-5.364 5.364-1.414-1.414L12 4l7.778 7.778-1.414 1.414L13 7.828z"></path>
                </g>
            </svg>
        </button>
    </div>

    <?php require_once(dirname(__FILE__).'/../components/_script.php'); ?>
    <!-- Charts -->
    <script src="<?=SITEDOMAIN?>/assets/app-assets/js/plugin/apexcharts.min.js"></script>
    <script>
        const optionsAnalyticsBalanceChart = {
            series: <?=json_encode(array_values($series))?>,
            fill: {
                opacity: 1,
                colors: [
                    document.body.classList.contains("dark") ? "#ffffff" : "#2D3436"
                ],
            },
            chart: {
                fontFamily: "Manrope, sans-serif",
                type: "bar",
                height: "250",
                "stacked": true,
                "stackOnlyBar": true,
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
                borderColor: "#B2BEC3",
                opacity: 1,
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 2,
                    columnWidth: "60%",
                    colors: {
                        backgroundBarColors: ["#B2BEC3"],
                        backgroundBarOpacity: 0.2,
                    },
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
                    height: 6,
                    offsetX: 0,
                    offsetY: 0,
                },
                tickPlacement: "between",
                labels: {
                    style: {
                        colors: [
                            "#B2BEC3",
                            "#B2BEC3",
                            "#B2BEC3",
                            "#B2BEC3",
                            "#B2BEC3",
                            "#B2BEC3",
                            "#B2BEC3",
                            "#B2BEC3",
                            "#B2BEC3",
                            "#B2BEC3",
                            "#B2BEC3",
                            "#B2BEC3",
                        ],
                        fontSize: "12px",
                    },
                },
                categories: <?=json_encode(array_column($series['other']['data'],'x'))?>,
            },
            legend: {
                horizontalAlign: "right",
                offsetX: 40,
                position: "top",
                markers: {
                    radius: 12,
                },
                show: false,
            },
            yaxis: {
                labels: {
                    style: {
                        colors: ["#636E72"],
                        fontSize: "14px",
                    },
                    formatter: (value) => {
                        let n = Number(value);
                        return n < 1000 ? number_format(n,2,',','.') : number_format((n / 1000).toFixed(2),2,',','.') + "K";
                    },
                },
                min: 0,
                max: <?=$maxBalance?>,
                tickAmount: 4,
            }
        };

        if (document.querySelector("#dashboard-analytics-balance-chart")) {
            let chart = new ApexCharts(document.querySelector("#dashboard-analytics-balance-chart"), optionsAnalyticsBalanceChart);
            chart.render();
        }
    </script>

    <!-- Cards -->
    <script src="<?=SITEDOMAIN?>/assets/app-assets/js/cards/card-analytic.js"></script>
    <script src="<?=SITEDOMAIN?>/assets/app-assets/js/cards/card-advance.js"></script>
    <script src="<?=SITEDOMAIN?>/assets/app-assets/js/cards/card-statistic.js"></script>
    <script>
        $(document).ready(function(){
            $('body').on('click','.btn-new-transaction',function(){
                $.ajax({
                    method:'get',url:'<?= SITEDOMAIN ?>/modal-types-transaction',
                    data: { },
                    dataType: 'html'
                })
                .then(html => {
                    $('.container-modal').html(html);
                    $('#transaction-actions-modal').modal('show');
                    hideLoader();
                    if(!!driver_page) driver_page.moveNext();
                })
                .catch(err => {
                    hideLoader();
                    console.error('Error processing request', err);
                    showErrorMessage("<?= io::_('Error processing request') ?>");
                });
            });
        })
    </script>
</body>
<div class="container-modal"></div>

</html>
<?php io::w(); ?>