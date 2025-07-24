<?php
    require_once(dirname(__FILE__).'/../../components/auth.php');
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');
    $pageTitle = 'Smooney - '.io::_('Notification Settings');

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
                                    <form id="edit-notification" autocomplete="_off_">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-12 col-md-6">
                                                <h2><?=io::_('Notification Settings')?></h2>
                                            </div>

                                            <div class="col-12 col-md-6 hp-profile-action-btn text-end">
                                                <button type="submit" class="btn btn-ghost smooney-primary smooney-text-primary" ><?=io::_('Save')?></button>
                                            </div>
                                        </div>

                                        <div class="divider border-black-40 hp-border-color-dark-80"></div>

                                        <div class="col-12">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-12 col-md-6">
                                                    <h3><?=io::_('Telegram')?></h3>
                                                </div>

                                                <div class="col-12 col-md-6 hp-profile-action-btn text-end">
                                                    <button type="button" class="btn btn-ghost smooney-primary smooney-text-primary" data-bs-toggle="modal" data-bs-target="#connectTelegramModal"><?=io::_('Connect')?></button>
                                                </div>
                                            </div>
                                            <input type="hidden" name="notification_settings[telegram][actions][]" value="otp">
                                            <input type="hidden" name="notification_settings[telegram][actions][]" value="change-password">
                                            
                                            <ul class="hp-profile-notifications mt-24">
                                                <li class="d-flex align-items-center justify-content-between mb-18">
                                                    <span class="hp-caption text-black-80 hp-text-color-dark-30 pr-8"><?=io::_('Send me a notification for each new login')?></span>

                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="telegram-new_ip"  name="notification_settings[telegram][actions][]" value="new_ip" <?=!empty($user['notification_settings']['telegram']['actions']) && in_array('new_ip',$user['notification_settings']['telegram']['actions'])?'checked':''?> >
                                                        <label class="form-check-label" for="telegram-new_ip"></label>
                                                    </div>
                                                </li>
                                                <li class="d-flex align-items-center justify-content-between mb-18">
                                                    <span class="hp-caption text-black-80 hp-text-color-dark-30 pr-8"><?=io::_('I would like to receive a summary of my financial progress at the end of each week')?></span>

                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="telegram-recap_weekend"  name="notification_settings[telegram][actions][]" value="recap_weekend" <?=!empty($user['notification_settings']['telegram']['actions']) && in_array('recap_weekend',$user['notification_settings']['telegram']['actions'])?'checked':''?> >
                                                        <label class="form-check-label" for="telegram-recap_weekend"></label>
                                                    </div>
                                                </li>
                                                <li class="d-flex align-items-center justify-content-between mb-18">
                                                    <span class="hp-caption text-black-80 hp-text-color-dark-30 pr-8"><?=io::_('I would like to receive a summary of my financial progress at the end of each month')?></span>

                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="telegram-recap_month"  name="notification_settings[telegram][actions][]" value="recap_month" <?=!empty($user['notification_settings']['telegram']['actions']) && in_array('recap_month',$user['notification_settings']['telegram']['actions'])?'checked':''?> >
                                                        <label class="form-check-label" for="telegram-recap_month"></label>
                                                    </div>
                                                </li>

                                                
                                            </ul>
                                        </div>
                                        <div class="col-12">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-12 col-md-6">
                                                    <h3><?=io::_('Email')?></h3>
                                                </div>

                                            </div>
                                            <input type="hidden" name="notification_settings[email][actions][]" value="otp">
                                            <input type="hidden" name="notification_settings[email][actions][]" value="change-password">
                                            
                                            <ul class="hp-profile-notifications mt-24">
                                                <li class="d-flex align-items-center justify-content-between mb-18">
                                                    <span class="hp-caption text-black-80 hp-text-color-dark-30 pr-8"><?=io::_('Send me a notification for each new login')?></span>

                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="email-new_ip"  name="notification_settings[email][actions][]" value="new_ip" <?=!empty($user['notification_settings']['email']['actions']) && in_array('new_ip',$user['notification_settings']['email']['actions'])?'checked':''?> >
                                                        <label class="form-check-label" for="email-new_ip"></label>
                                                    </div>
                                                </li>
                                                <!-- <li class="d-flex align-items-center justify-content-between mb-18">
                                                    <span class="hp-caption text-black-80 hp-text-color-dark-30 pr-8"><?=io::_('I would like to receive a summary of my financial progress at the end of each week')?></span>

                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="email-recap_weekend"  name="notification_settings[email][actions][]" value="recap_weekend" <?=!empty($user['notification_settings']['email']['actions']) && in_array('recap_weekend',$user['notification_settings']['email']['actions'])?'checked':''?> >
                                                        <label class="form-check-label" for="email-recap_weekend"></label>
                                                    </div>
                                                </li> -->

                                                
                                            </ul>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal fade" id="connectTelegramModal" tabindex="-1" aria-labelledby="connectTelegramModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" style="max-width: 416px;">
                                        <div class="modal-content">
                                            <div class="modal-header py-16">
                                                <h5 class="modal-title" id="connectTelegramModalLabel"><?=io::_('Connect Telegram')?></h5>
                                                <button type="button" class="btn-close hp-bg-none d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Close">
                                                    <i class="ri-close-line hp-text-color-dark-0 lh-1" style="font-size: 24px;"></i>
                                                </button>
                                            </div>

                                            <div class="divider my-0"></div>

                                            <div class="modal-body pt-1 pb-48">
                                                <p class="hp-p1-body mb-10"><?=io::_('Before you begin, log into Telegram, create a new group, and add the user @smooney_bot. Once the group is created, provide me with your Telegram ID and the name of the group you just created.')?></p>
                                                <form id="connect-telegram" autocomplete="_off_">
                                                    <div class="row g-24">
                                                        <div class="col-12">
                                                            <label for="group_name" class="form-label"><?=io::_('Group name')?></label>
                                                            <input type="text" class="form-control" id="group_name" name="group_name" >
                                                        </div>

                                                        <div class="col-12">
                                                            <label for="telegram_id" class="form-label"><?=io::_('ID Telegram')?></label>
                                                            <input type="text" class="form-control" name="telegram_id" id="telegram_id" >
                                                        </div>

                                                        <div class="col-6">
                                                            <div class="btn w-100" data-bs-dismiss="modal"><?=io::_('Cancel')?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <button type="submit" class="btn smooney-primary w-100"><?=io::_('Connect')?></button>
                                                        </div>
                                                    </div>
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
    $('body').on('submit','#connect-telegram',function(e){
        e.preventDefault();
        e.stopPropagation();
        showLoader();
        let json = $(this).serializeObject();
        $.ajax({
            method:'post',url:'<?= SITEACTION ?>/connect-telegram',
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
    $('body').on('submit','#edit-notification',function(e){
        e.preventDefault();
        e.stopPropagation();
        showLoader();
        let json = $(this).serializeObject();
        $.ajax({
            method:'post',url:'<?= SITEACTION ?>/update-profile',
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