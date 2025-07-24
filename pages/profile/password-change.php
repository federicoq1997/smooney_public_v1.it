<?php
    require_once(dirname(__FILE__).'/../../components/auth.php');
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');
    $pageTitle = 'Smooney - '.io::_('Change Password');

    new WrapperClass(['mngUser']);
    $mngUser = new mngUser($ioConn);
    $user = $mngUser->get(['code'=> $USERDATA['UserId']])['data'];
    if(empty($user)) redirect('/logout');

    $languages = io::getAvailableLanguages(true);
    $languagesISO = [];
    foreach ($languages as $el) {
        $languagesISO[$el[0]] = $el;
    }
?>
<!DOCTYPE html>
<html dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

<!DOCTYPE html>
<html dir="ltr">

<head>
    <?php require_once(dirname(__FILE__).'/../../components/_headcontent.php'); ?>
    <!-- Pages -->
    <link rel="stylesheet" type="text/css" href="<?=SITEDOMAIN?>/assets/app-assets/css/pages/page-profile.css">
</head>

<body>
    <main class="hp-bg-color-dark-90 d-flex min-vh-100">
        <?php require_once(dirname(__FILE__).'/../../components/sidebar/_sidebar.php'); ?>

        <div class="hp-main-layout">
            <?php require_once(dirname(__FILE__).'/../../components/_header.php'); ?>

            <div class="hp-main-layout-content">
                <div class="row mb-32 gy-32">

                    <div class="col-12 mt-1">
                        <div class="row hp-profile-mobile-menu-btn bg-black-0 hp-bg-color-dark-100 rounded py-12 px-8 px-sm-12 mb-16 mx-0">
                            <div class="d-inline-block" data-bs-toggle="offcanvas" data-bs-target="#profileMobileMenu" aria-controls="profileMobileMenu">
                                <button type="button" class="btn btn-text btn-icon-only">
                                    <i class="ri-menu-fill hp-text-color-black-80 hp-text-color-dark-30 lh-1" style="font-size: 24px;"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row bg-black-0 hp-bg-color-dark-100 rounded pe-16 pe-sm-32 mx-0">

                            <?php require_once(dirname(__FILE__).'/_sidebar.php'); ?>

                            <div class="col ps-16 ps-sm-32 py-24 py-sm-32 overflow-hidden">
                                <div class="row">
                                    <div class="col-12">
                                        <h2><?=io::_('Change Password')?></h2>
                                        <p class="hp-p1-body mb-0"><?=io::_('Set a unique password to protect your account.')?></p>
                                    </div>

                                    <div class="divider border-black-40 hp-border-color-dark-80"></div>

                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-12 col-sm-8 col-md-7 col-xl-5 col-xxxl-3">
                                                <form id="form-change-password" autocomplete="_off_">
                                                    <div class="mb-24">
                                                        <label for="profileOldPassword" class="form-label"><?=io::_('Old Password')?> :</label>
                                                        <input type="password" class="form-control" name="old-password" id="profileOldPassword" placeholder="Password">
                                                    </div>

                                                    <div class="mb-24">
                                                        <label for="profileNewPassword" class="form-label"><?=io::_('New Password')?> :</label>
                                                        <input type="password" class="form-control" name="new-password" id="profileNewPassword" placeholder="Password">
                                                    </div>

                                                    <div class="mb-24">
                                                        <label for="profileConfirmPassword" class="form-label"><?=io::_('Confirm New Password')?> :</label>
                                                        <input type="password" class="form-control" name="confirm-new-password" id="profileConfirmPassword" placeholder="Password">
                                                    </div>

                                                    <button type="submit" class="btn smooney-primary w-100"><?=io::_('Change Password')?></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php require_once(dirname(__FILE__).'/../../components/_footer.php'); ?>
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

    <?php require_once(dirname(__FILE__).'/../../components/_script.php'); ?>

</body>
<script>
$(document).ready(function(){
    $('body').on('submit','#form-change-password',function(e){
        e.preventDefault();
        e.stopPropagation();
        let json = $(this).serializeObject();
        if(json['confirm-new-password']!=json['new-password']){
            showErrorMessage("<?=io::_('The password just entered does not match its verification')?>");
            return;
        }
        showLoader();
        $.ajax({
            method:'post',url:'<?= SITEACTION ?>/change-password',
            data:{jdata:JSON.stringify(json)},
            dataType:'json'
        })
        .then(res=>{
            if(!res.success){
                hideLoader();
                showErrorMessage(res.error);
                return;
            }
            showSuccessMessage('<?=io::_('Change saved')?>');
            setTimeout(()=>{ window.location.reload(); },1500);
        })
        .catch(err=>{
            hideLoader();
            console.error('Errore durante l\'elaborazione della richiesta',err);
            showErrorMessage("<?=io::_('Errore durante l\'elaborazione della richiesta')?>");
        });
    });
});
</script>
</html>
<?php io::w(); ?>