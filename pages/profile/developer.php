<?php
    require_once(dirname(__FILE__).'/../../components/auth.php');
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');
    $pageTitle = 'Smooney - '.io::_('Contact');

    new WrapperClass(['mngUser']);
    $mngUser = new mngUser($ioConn);
    $user = $mngUser->get(['code'=> $USERDATA['UserId']])['data'];
    if(empty($user)) redirect('/logout');

    $languages = io::getAvailableLanguages(true);
    $languagesISO = [];
    foreach ($languages as $el) {
        $languagesISO[$el[0]] = $el;
    }
    $crypt_key = $user['crypt_key'];
    $crypt_key = EncryptionManager::decryptUserKey($crypt_key);
    $crypt_key = oscurareStringa($crypt_key,25);
?>
<!DOCTYPE html>
<html dir="ltr">

<head>
    <?php require_once(dirname(__FILE__).'/../../components/_headcontent.php'); ?>
    <!-- Pages -->
    <link rel="stylesheet" type="text/css" href="<?=SITEDOMAIN?>/assets/app-assets/css/pages/page-profile.css">
    <style>
        .pointer{
            cursor: pointer;
        }
    </style>
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
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-12 col-md-6">
                                                <h3><?=io::_('Encryption key')?></h3>
                                            </div>

                                            <div class="col-12 col-md-6 hp-profile-action-btn text-end">
                                                <button class="btn btn-ghost smooney-primary smooney-text-primary" data-bs-toggle="modal" data-bs-target="#profileVerifyCryptKeyModal"><?=io::_('Verify')?></button>
                                            </div>

                                            <div class="col-12 hp-profile-content-list mt-8 pb-0 pb-sm-10">
                                                
                                                <div class="col-12 col-md-6">
                                                    <h5 class="text-black-100"><?=$crypt_key?></h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="divider border-black-40 hp-border-color-dark-80"></div>

                                    <div class="col-12">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-12 col-md-6">
                                                <h3><?=io::_('Authentication Codes')?></h3>
                                            </div>

                                            <div class="col-12 col-md-6 hp-profile-action-btn text-end">
                                                <button class="btn btn-ghost smooney-primary smooney-text-primary  btn-new-api-key" ><?=io::_('Generate new codes')?></button>
                                            </div>

                                            <div class="col-12 hp-profile-content-list mt-8">
                                                <ul>
                                                    <li class="mt-18">
                                                        <span class="hp-p1-body"><?=io::_('Code')?>&nbsp;<i class="fa-solid fa-copy pointer btn-copy-text" data-target="#text-code"></i></span>
                                                        <span class="mt-0 mt-sm-4 hp-p1-body text-black-100 hp-text-color-dark-0" id="text-code"><?=$user['code']?></span>
                                                    </li>
                                                    <li class="mt-18">
                                                        <span class="hp-p1-body"><?=io::_('Api Key')?>&nbsp;<i class="fa-solid fa-copy pointer btn-copy-text" data-target="#text-api_key"></i></span>
                                                        <span class="mt-0 mt-sm-4 hp-p1-body text-black-100 hp-text-color-dark-0" id="text-api_key"><?=$user['api_key']?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="divider border-black-40 hp-border-color-dark-80"></div>

                                    <div class="col-12">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-12">
                                                <h3><?=io::_('Apps')?></h3>
                                            </div>


                                            <div class="col-12 hp-profile-content-list mt-8">
                                                <div class="col-12 col-md-6">
                                                    <h5 class="text-black-100"><?=io::_('iOS')?></h5>
                                                </div>
                                                <div class="row mx-0 align-items-top justify-content-start">
                                                    <div class="w-100">
                                                        <div class="row mx-0 align-items-top justify-content-start">
                                                            <span class="hp-p1-body text-black-100 hp-text-color-dark-0col-auto pointer btn-download-shortcut"><?=io::_('Create a payment with Shortcuts')?>&nbsp;<i class="fa-duotone fa-solid fa-cloud-arrow-down"></i></span>
                                                            <span class="mt-0 hp-p1-body col-auto" > <?=io::_("Download the shortcut to create payments quickly and easily via the iOS Shortcuts app. Just set it up with your account and you're ready to go!")?> </span>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="profileVerifyCryptKeyModal" tabindex="-1" aria-labelledby="profileVerifyCryptKeyModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width: 416px;">
                        <div class="modal-content">
                            <div class="modal-header py-16">
                                <h5 class="modal-title" id="profileVerifyCryptKeyModalLabel"><?=io::_('Verify your encryption key')?></h5>
                                <button type="button" class="btn-close hp-bg-none d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="ri-close-line hp-text-color-dark-0 lh-1" style="font-size: 24px;"></i>
                                </button>
                            </div>

                            <div class="divider my-0"></div>

                            <div class="modal-body py-48">
                                <form id="form-crypt-key" autocomplete="_off_">
                                    <div class="row g-24">
                                        <div class="col-12">
                                            <label for="crypt-key" class="form-label"><?=io::_('Encryption key')?></label>
                                            <input type="text" class="form-control" id="crypt-key" name="crypt-key" value="">
                                        </div>

                                        
                                        <div class="col-12">
                                            <button type="submit" class="btn smooney-primary w-100"><?=io::_('Verify')?></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="guideShortcutStepModal" tabindex="-1" aria-labelledby="guideShortcutStepModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" >
                        <div class="modal-content">
                            <div class="modal-header py-16">
                                <h5 class="modal-title" id="guideShortcutStepModalLabel"><?=io::_('Guide to configure shortcut on iPhone')?></h5>
                                <button type="button" class="btn-close hp-bg-none d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="ri-close-line hp-text-color-dark-0 lh-1" style="font-size: 24px;"></i>
                                </button>
                            </div>

                            <div class="divider my-0"></div>

                            <div class="modal-body py-48">
                                
                                    <div class="row g-24">
                                        <div class="col-12">
                                            <ol class="list-group list-group-numbered">
                                                <li class="list-group-item">
                                                    <h5><?=io::_("Download the shortcut")?></h5>
                                                    <p>
                                                        <?=io::_("Click on the link provided in the <strong>Apps</strong> section and download the quick command.")?>
                                                    </p>
                                                </li>
                                                
                                                <li class="list-group-item">
                                                    <h5><?=io::_('Add shortcut to “Quick Commands”')?></h5>
                                                    <p>
                                                        <?=io::_("After downloading the file, the quick command will be opened automatically in the <strong>Quick Commands</strong> app.")?>
                                                        <?=io::_("Press on <strong>Add Quick Command</strong> to save it to your library.")?>
                                                    </p>
                                                </li>
                                                
                                                <li class="list-group-item">
                                                    <h5><?=io::_("Open the edit page (if it does not appear automatically)")?></h5>
                                                    <ul>
                                                        <li><?=io::_("Go to the <strong>Quick Commands</strong> app.")?></li>
                                                        <li><?=io::_("Find the command you just added to your library.")?></li>
                                                        <li><?=io::_("Tap the <strong>three dots</strong> (<code>•••</code>) next to the command name to open the edit screen.")?></li>
                                                    </ul>
                                                </li>
                                                
                                                <li class="list-group-item">
                                                    <h5><?=io::_("Edit the “Code” and “ApiKey” fields.")?></h5>
                                                    <p>
                                                        <?=io::_("On the edit screen, locate the <strong>first action</strong> of the quick command.")?><br>
                                                        <?=io::_("Click on the <strong>arrow</strong> or <strong>“Show More”</strong> to expand the details of the action.")?>
                                                    </p>
                                                    <p>
                                                        <?=io::_(" You will see two fields that are not valued:")?>
                                                    </p>
                                                    <ul>
                                                        <li><strong>Code</strong>&nbsp;<i class="fa-solid fa-copy pointer btn-copy-text" data-target="#text-code"></i></li>
                                                        <li><strong>ApiKey</strong>&nbsp;<i class="fa-solid fa-copy pointer btn-copy-text" data-target="#text-api_key"></i></li>
                                                    </ul>
                                                    <p><?=io::_("Enter in the respective fields your <strong>authentication codes</strong> available on the main screen.")?></p>
                                                </li>
                                                
                                                <li class="list-group-item">
                                                    <h5><?=io::_("Save changes")?></h5>
                                                    <p>
                                                        <?=io::_("After entering the codes, press <strong>Fine</strong> in the upper right corner to save the quick command.")?>
                                                    </p>
                                                </li>
                                                
                                                <li class="list-group-item">
                                                    <h5><?=io::_("The quick command is ready!")?></h5>
                                                    <p>
                                                        <?=io::_("Now you can use the quick command to create payments quickly and easily.")?>
                                                    </p>
                                                </li>
                                            </ol>
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
    
    $('body').on('submit','#form-crypt-key',function(event){
        event.preventDefault();
        event.stopPropagation();
        showLoader();
        let json = $('#form-crypt-key').serializeObject();
        $.ajax({
            method: 'post', url: '<?= SITEACTION ?>/verify-crypt-key',
            data: { jdata: JSON.stringify(json) },
            dataType: 'json'
        })
        .then(res => {
            $('#profileVerifyCryptKeyModal').modal('hide');
            hideLoader();
            $('input[name="crypt-key"]').val(``);
            if (!res.success) {
                showErrorMessage(res.error);
                return;
            }
            showSuccessMessage('<?= io::_('Valid encryption key') ?>');
        })
        .catch(err => {
            hideLoader();
            console.error('Error processing request', err);
            showErrorMessage("<?= io::_('Error processing request') ?>");
        });
    });
    $('body').on('click','.btn-new-api-key',function(){
        showLoader();
        $.ajax({
            method: 'post', url: '<?= SITEACTION ?>/update-api-key',
            data: { jdata: JSON.stringify(json) },
            dataType: 'json'
        })
        .then(res => {
            if (!res.success) {
                hideLoader();
                showErrorMessage(res.error);
                return;
            }
            showSuccessMessage('<?= io::_('Change saved') ?>');
            setTimeout(() => { window.location.reload(); }, 1500);
        })
        .catch(err => {
            hideLoader();
            console.error('Error processing request', err);
            showErrorMessage("<?= io::_('Error processing request') ?>");
        });
    });
    $('body').on('click','.btn-download-shortcut',function(){
        window.open('/app-ios/download/shortcut','_blank');
        $('#guideShortcutStepModal').modal('show');
    });
});
</script>
</html>
<?php io::w(); ?>