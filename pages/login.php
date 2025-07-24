<?php
    require_once(dirname(__FILE__).'/../components/_global_variables.php');
    $pageTitle = 'Login - Smooney';
?>
<!DOCTYPE html>
<html dir="ltr">

<head>
    <?php require_once(dirname(__FILE__).'/../components/_headcontent.php'); ?>
    <!-- Pages -->
    <link rel="stylesheet" type="text/css" href="/assets/app-assets/css/pages/authentication.css">
</head>

<body class="login-page">
    <div class="row hp-authentication-page">
        <div class="col-12">
            <div class="row hp-image-row px-8 px-sm-16 px-md-0 pb-32 pb-sm-0 pt-32 pt-md-0 justify-content-center">
                <div class=" m-16 m-sm-32 m-md-64 w-auto px-0 col-12">
                    <div class="hp-header-logo d-flex align-items-center">
                        <a href="<?=SITEDOMAIN?>/" class="position-relative">

                            <img class="hp-logo hp-sidebar-visible hp-dark-none" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo.svg" alt="logo">
                            <img class="hp-logo hp-sidebar-visible hp-dark-block" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo.svg" alt="logo">
                            <img class="hp-logo hp-sidebar-hidden hp-dir-none hp-dark-none" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo.svg" alt="logo">
                            <img class="hp-logo hp-sidebar-hidden hp-dir-none hp-dark-block" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo.svg" alt="logo">
                            <img class="hp-logo hp-sidebar-hidden hp-dir-block hp-dark-none" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo.svg" alt="logo">
                            <img class="hp-logo hp-sidebar-hidden hp-dir-block hp-dark-block" src="<?=SITEDOMAIN?>/assets/app-assets/img/SmooneyLogo/smooney.logo.svg" alt="logo">
                        </a>

                    </div>
                </div>
            </div>
        </div>
        <div class="px-32 flex-shrink-1 col-12 d-flex">
            <div class="row h-100 m-auto w-100 align-items-center" style="max-width: 390px;">
                <div class="col-12">
                    <h1 class="mb-0 mb-sm-24">Login</h1>

                    <form class="mt-16 mt-sm-32 mb-8" autocomplete="_off_" id="auth-form">
                        <div class="mb-16">
                            <label for="loginUsername" class="form-label">Email :</label>
                            <input type="email" class="form-control" id="loginUsername" name="email">
                        </div>

                        <div class="d-none mb-16" data-step="login-step-1">
                            <label for="loginPassword" class="form-label">Password :</label>
                            <input type="password" class="form-control" id="loginPassword" name="password">
                        </div>

                        <div class="d-none row align-items-center justify-content-between mb-16" data-step="login-step-1">
                            <div class="col hp-flex-none w-auto">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1" name="RememberMe">
                                    <label class="form-check-label ps-4" for="exampleCheck1">Remember me</label>
                                </div>
                            </div>

                            <div class="col hp-flex-none w-auto">
                                <a class="hp-button text-black-80 hp-text-color-dark-40" href="<?=SITEDOMAIN?>/recover-password">Forgot Password?</a>
                            </div>
                        </div>

                        <button type="button" data-button-step="login-step-1" class="btn smooney-primary w-100">
                            Continue
                        </button>

                        <button type="submit" data-step="login-step-1" class="d-none btn smooney-primary w-100">
                            Continue
                        </button>
                    </form>                    
                    <form class="mt-16 mt-sm-32 mb-3 d-none" autocomplete="_off_" id="otp-form">
                        <div class="mb-16">
                            <label class="form-label w-100 text-center">Verify OTP </label>
                            <input type="hidden" name="code" value="">
                            <div class="d-flex align-items-center justify-content-center">
                                <input class="otp" type="text" pattern="\d*"  maxlength=1 >
                                <input class="otp" type="text" pattern="\d*"  maxlength=1 >
                                <input class="otp" type="text" pattern="\d*"  maxlength=1 >
                                <input class="otp" type="text" pattern="\d*"  maxlength=1 >
                                <input class="otp" type="text" pattern="\d*"  maxlength=1 >
                                <input class="otp" type="text" pattern="\d*"  maxlength=1 >
                            </div>
                        </div>

                        <button type="submit" data-step="login-step-1" class="d-none btn smooney-primary w-100">
                            Continue
                        </button>
                        <div class="mt-10 text-center w-100 " >
                            <span class="text-black-80 hp-text-color-dark-40 hp-caption btn-send-again" style="cursor: pointer;">Send again</span>
                        </div>
                    </form>

                    <div class="col-12 hp-form-info text-center">
                        <span class="text-black-80 hp-text-color-dark-40 hp-caption me-4">Don’t you have an account?</span>
                        <a class="text-primary-1 hp-text-color-dark-primary-2 hp-caption" href="<?=SITEDOMAIN?>/account-request">Request an account</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="my-48 px-24 col-12">
            <p class="hp-p1-body text-center hp-text-color-black-60 mb-8"> Copyright <?=date('Y')?> Smooney. </p>
        </div>
    </div>

    <?php require_once(dirname(__FILE__).'/../components/_script.php'); ?>
    <script src="/assets/app-assets/js/pages/auth.js"></script>
</body>
<script>

$(document).ready(function(){
    $('body').on('submit','#auth-form',function(e){
        e.preventDefault();
        e.stopPropagation();
        let json = $('#auth-form').serializeObject();
        $.ajax({
            method:'post',url:'<?= SITEACTION ?>/auth/verify-credentials',
            data:{jdata:JSON.stringify(json)},
            dataType:'json'
        })
        .then(res=>{
            if(!res.success){
                showErrorMessage(res.error);
                return;
            }
            $('#auth-form').addClass('d-none');
            $('#otp-form').removeClass('d-none');
            $('#otp-form input[name="code"]').val(res.data.UserId);
        })
        .catch(err=>{
            console.error('Errore durante l\'elaborazione della richiesta',err);
            showErrorMessage('Errore durante l\'elaborazione della richiesta');
        });
    });    
    $('body').on('submit','#otp-form',function(e){
        e.preventDefault();
        e.stopPropagation();
        let json = $('#otp-form').serializeObject();
        if($('input[name="RememberMe"]').is(':checked')) json['RememberMe'] = true;
        json.otp = ''; 
        $('input.otp').each(function() {  json.otp += $(this).val(); });
        $.ajax({
            method:'post',url:'<?= SITEACTION ?>/auth/login',
            data:{jdata:JSON.stringify(json)},
            dataType:'json'
        })
        .then(res=>{
            if(!res.success){
                showErrorMessage(res.error);
                return;
            }
            window.location.href = '<?=SITEDOMAIN?>/dashboard';
        })
        .catch(err=>{
            console.error('Errore durante l\'elaborazione della richiesta',err);
            showErrorMessage('Errore durante l\'elaborazione della richiesta');
        });
    });
    $('body').on('click','.btn-send-again',function(e){
        e.preventDefault();
        e.stopPropagation();
        $.ajax({
            method:'post',url:'<?= SITEACTION ?>/auth/send-otp',
            data:{jdata:JSON.stringify({code:$('#otp-form input[name="code"]').val()})},
            dataType:'json'
        })
        .then(res=>{
            if(!res.success){
                showErrorMessage(res.error);
                return;
            }
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