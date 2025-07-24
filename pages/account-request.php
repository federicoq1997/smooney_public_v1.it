<?php
    require_once(dirname(__FILE__).'/../components/_global_variables.php');
    $pageTitle = 'Login - Account Request';
?>
<!DOCTYPE html>
<html dir="ltr">

<head>
    <?php require_once(dirname(__FILE__).'/../components/_headcontent.php'); ?>
    <!-- Pages -->
    <link rel="stylesheet" type="text/css" href="/assets/app-assets/css/pages/authentication.css">
</head>

<body>
    <div class="row hp-authentication-page">
        <div class="hp-bg-black-20 hp-bg-color-dark-90 col-lg-6 col-12" style="background:var(--smooney) !important;">
            <div class="row hp-image-row h-100 px-8 px-sm-16 px-md-0 pb-32 pb-sm-0 pt-32 pt-md-0">
                <div class="hp-logo-item m-16 m-sm-32 m-md-64 w-auto px-0 col-12">
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

                <div class="col-12 px-0">
                    <div class="row h-100 w-100 mx-0 align-items-center justify-content-center">
                        <div class="hp-bg-item text-center mb-32 mb-md-0 px-0 col-12">
                            <img class="hp-dark-none m-auto w-100" src="<?=SITEDOMAIN?>/assets/app-assets/img/pages/authentication/authentication-bg.svg" alt="Background Image">
                            <img class="hp-dark-block m-auto w-100" src="<?=SITEDOMAIN?>/assets/app-assets/img/pages/authentication/authentication-bg-dark.svg" alt="Background Image">
                        </div>

                        <div class="hp-text-item text-center col-xl-9 col-12">
                            <h2 class="hp-text-color-black-0 hp-text-color-dark-0 mx-16 mx-lg-0 mb-16"> Manage your finances with ease: one click for change! </h2>
                            <p class="h4 mb-0 fw-normal hp-text-color-black-0 hp-text-color-dark-30"> Get total control of your financial life with just one click! Sign up now and start monitoring and optimizing your spending in a simple and intuitive way. </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 py-sm-64 py-lg-0">
            <div class="row align-items-center justify-content-center h-100 mx-4 mx-sm-n32" id="container-request">
                <div class="col-12 col-md-9 col-xl-7 col-xxxl-5 px-8 px-sm-0 pt-24 pb-48">
                    <span class="d-block hp-p1-body hp-text-color-dark-0 hp-text-color-black-100 fw-medium mb-6"> SIGN UP FOR FREE </span>
                    <h1 class="mb-0 mb-sm-24">Request an account</h1>

                    <form class="mt-16 mt-sm-32 mb-8" autocomplete="_off_" id="form-request">
                        <div class="mb-16">
                            <label for="registerUsername" class="form-label">Username :</label>
                            <input type="text" class="form-control" id="registerUsername" name="user">
                        </div>

                        <div class="mb-16">
                            <label for="registerEmail" class="form-label">E-mail :</label>
                            <input type="email" class="form-control" id="registerEmail" name="email">
                        </div>

                        <button type="submit" class="btn smooney-primary w-100">
                            Send
                        </button>
                    </form>

                    <div class="col-12 hp-form-info text-center">
                        <span class="text-black-80 hp-text-color-dark-40 hp-caption me-4">Already have an account?</span>
                        <a class="text-primary-1 hp-text-color-dark-primary-2 hp-caption" href="<?=SITEDOMAIN?>/">Login</a>
                    </div>

                    <div class="mt-48 mt-sm-96 col-12">
                        <p class="hp-p1-body text-center hp-text-color-black-60 mb-8"> Copyright <?=date('Y')?> Smooney. </p>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once(dirname(__FILE__).'/../components/_script.php'); ?>
</body>
<script>

$(document).ready(function(){
    $('body').on('submit','#form-request',function(e){
        e.preventDefault();
        e.stopPropagation();
        let json = $('#form-request').serializeObject();
        $('input.otp').each(function() {  json.otp += $(this).val(); });
        $.ajax({
            method:'post',url:'<?= SITEACTION ?>/auth/account-request',
            data:{jdata:JSON.stringify(json)},
            dataType:'json'
        })
        .then(res=>{
            if(!res.success){
                showErrorMessage(res.error);
                return;
            }
            $('#container-request').html(`<div class="col-12 col-md-9 col-xl-7 col-xxxl-5 px-8 px-sm-0 pt-24 pb-48">
                <h4 class="mt-sm-8 mt-sm-0 text-black-60 hp-text-color-black-100">We are processing your request for a new account.<br>You will be contacted by an administrator as soon as possible.</h4>
                <div class="mt-48 mt-sm-96 col-12">
                    <p class="hp-p1-body text-center hp-text-color-black-60 mb-8"> Copyright <?=date('Y')?> Smooney. </p>
                </div>
            </div>`);
        })
        .catch(err=>{
            console.error('Errore durante l\'elaborazione della richiesta',err);
            showErrorMessage('Errore durante l\'elaborazione della richiesta');
        });
    });
});
</script>
</html>
<?php io::w(); ?>