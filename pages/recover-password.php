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

<body>
    <div class="row hp-authentication-page d-flex flex-column">

        <div class="px-32 flex-shrink-1 col d-flex">
            <div class="row h-100 m-auto w-100 align-items-center" style="max-width: 410px;">
                <div class="col-12">
                    <h1 class="mb-0 mb-sm-24">Recover Password</h1>

                    <form class="mt-16 mt-sm-32 mb-8" autocomplete="_off_" id="recover-password" >
                        <div class="mb-24">
                            <label for="recoverEmail" class="form-label">E-mail :</label>
                            <input type="email" class="form-control" id="recoverEmail" name="email" placeholder="you@example.com">
                        </div>
                        <div class="d-none mb-16" data-step="login-step-1">
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

                        <button type="button" data-step="login-step-0" data-button-step="login-step-1" class="btn smooney-primary w-100 btn-send-otp">
                            Continue
                        </button>
                        <button type="submit" data-step="login-step-1" class="d-none btn smooney-primary w-100">
                            Reset Password
                        </button>
                    </form>

                    <div class="col-12 hp-form-info text-center">
                        <span class="text-black-80 hp-text-color-dark-40 hp-caption me-4">Go back to</span>
                        <a class="text-primary-1 hp-text-color-dark-primary-2 hp-caption" href="<?=SITEDOMAIN?>/">Login</a>
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
    $('body').on('submit','#recover-password',function(e){
        e.preventDefault();
        e.stopPropagation();
        let json = $('#recover-password').serializeObject();
        json.otp = ''; 
        $('input.otp').each(function() {  json.otp += $(this).val(); });
        $.ajax({
            method:'post',url:'<?= SITEACTION ?>/auth/recover-password',
            data:{jdata:JSON.stringify(json)},
            dataType:'json'
        })
        .then(res=>{
            if(!res.success){
                showErrorMessage(res.error);
                return;
            }
            window.location.href = '<?=SITEDOMAIN?>/';
        })
        .catch(err=>{
            console.error('Errore durante l\'elaborazione della richiesta',err);
            showErrorMessage('Errore durante l\'elaborazione della richiesta');
        });
    });
    $('body').on('click','.btn-send-otp',function(e){
        e.preventDefault();
        e.stopPropagation();
        $.ajax({
            method:'post',url:'<?= SITEACTION ?>/auth/send-otp',
            data:{jdata:JSON.stringify({email:$('#recover-password input[name="email"]').val()})},
            dataType:'json'
        })
        .then(res=>{
            if(!res.success){
                $('[data-step="login-step-1"]').addClass('d-none');
                $('[data-step="login-step-0"]').removeClass('d-none');
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