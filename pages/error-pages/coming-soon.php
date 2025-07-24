<?php
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');
    require_once(dirname(__FILE__).'/../../api/_wrapper.php');
    $info = getInfoUser();
    $message = "💬 <b>Smooney | Comming Soon</b>\n";
    $message .= "Un utente ha visitato questa pagina.\n";
    $message .= "<b>IP</b>: ".(isset($info['ip'])?$info['ip']:'')."\n";
    $message .= "<b>User-agent:</b> ".(isset($info['user-agent'])?$info['user-agent']:'')."\n";
    $message .= "\n<b>Data:</b> ".(date('d/m/Y H:i:s')).".\n\n";
    $message = str_replace(['<b>', '</b>'], '', $message);
    $message = str_replace(['<br>', '</br>'], "\n", $message);
    sendTelegramMessage($message,'-4170380050');
    $pageTitle = 'Smooney - Coming Soon';
?>
<!DOCTYPE html>
<html dir="ltr">

<head>
    <?php require_once(dirname(__FILE__).'/../../components/_headcontent.php'); ?>

    <!-- Pages -->
    <link rel="stylesheet" type="text/css" href="/assets/app-assets/css/pages/page-error.css">
</head>

<body>
    <div class="row text-center overflow-hidden">

        <div class="d-flex align-items-center justify-content-center border-bottom hp-border-color-dark-60 py-16 col-12" style="background: var(--smooney);">
            <div class="hp-header-logo d-flex align-items-center">
                <a href="<?=SITEDOMAIN?>/" class="position-relative">

                    <img class="hp-logo hp-sidebar-visible hp-dark-none" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
                    <img class="hp-logo hp-sidebar-visible hp-dark-block" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
                    <img class="hp-logo hp-sidebar-hidden hp-dir-none hp-dark-none" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
                    <img class="hp-logo hp-sidebar-hidden hp-dir-none hp-dark-block" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
                    <img class="hp-logo hp-sidebar-hidden hp-dir-block hp-dark-none" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
                    <img class="hp-logo hp-sidebar-hidden hp-dir-block hp-dark-block" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo-white.svg" alt="logo">
                </a>

            </div>
        </div>

        <div class="hp-error-content py-32 col-12">
            <div class="row h-100 align-items-center justify-content-center">
                <div class="col">
                    <h2 class="h1 mb-16">Coming soon</h2>

                    <div class="d-flex align-items-center justify-content-center data-date-timer" style="gap: 15px;" data-date-timer="Dec 12, 2024">
                        <div class="hp-comingsoon-timer-item">
                            <span class="d-block fw-light text-primary" data-date-timer-day>0</span>
                            <span class="d-block fw-light text-black-80 hp-text-color-dark-30 h4">DAYS</span>
                        </div>

                        <div class="hp-comingsoon-timer-item">
                            <span class="d-block fw-light text-primary" data-date-timer-hours>0</span>
                            <span class="d-block fw-light text-black-80 hp-text-color-dark-30 h4">HOURS</span>
                        </div>

                        <div class="hp-comingsoon-timer-item">
                            <span class="d-block fw-light text-primary" data-date-timer-minutes>0</span>
                            <span class="d-block fw-light text-black-80 hp-text-color-dark-30 h4">MINUTES</span>
                        </div>

                        <div class="hp-comingsoon-timer-item">
                            <span class="d-block fw-light text-primary" data-date-timer-seconds>0</span>
                            <span class="d-block fw-light text-black-80 hp-text-color-dark-30 h4">SECONDS</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-18">
                    <a class="btn smooney-primary " href="/account-request">
                        Request an account
                    </a>
                </div>
            </div>
        </div>

        <div class="py-32 col-12">
            <p class="hp-p1-body text-center hp-text-color-black-60 mb-8"> Copyright <?=date('Y')?> Smooney. </p>
        </div>
    </div>

    <?php require_once(dirname(__FILE__).'/../../components/_script.php'); ?>
</body>

</html>