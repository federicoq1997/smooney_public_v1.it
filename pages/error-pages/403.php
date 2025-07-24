<?php
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');
    $pageTitle = 'Smooney - Error 403';
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
                    <h1 class="hp-error-content-title fw-light hp-text-color-black-bg hp-text-color-dark-0 mb-0"> 403 </h1>

                    <h2 class="h1 mb-16">Forbidden</h2>

                    <p class="mb-32 hp-p1-body hp-text-color-black-100 hp-text-color-dark-0"> You don’t have an access to this page. </p>

                    <a href="<?=SITEDOMAIN?>/" class="btn router-link-active smooney-primary">
                        <span>Back to Home</span>
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