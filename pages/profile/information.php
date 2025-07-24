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
                                    
                                    <div class="col-12">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-12 col-md-6">
                                                <h3><?=io::_('Contact')?></h3>
                                            </div>

                                            <div class="col-12 col-md-6 hp-profile-action-btn text-end">
                                                <button class="btn btn-ghost smooney-primary smooney-text-primary" data-bs-toggle="modal" data-bs-target="#profileContactEditModal"><?=io::_('Edit')?></button>
                                            </div>

                                            <div class="col-12 hp-profile-content-list mt-8 pb-0 pb-sm-120">
                                                <ul>
                                                    <li>
                                                        <span class="hp-p1-body"><?=io::_('Firstname')?></span>
                                                        <span class="mt-0 mt-sm-4 hp-p1-body text-black-100 hp-text-color-dark-0"><?=$user['firstname']?></span>
                                                    </li>
                                                    <li class="mt-18">
                                                        <span class="hp-p1-body"><?=io::_('Lastname')?></span>
                                                        <span class="mt-0 mt-sm-4 hp-p1-body text-black-100 hp-text-color-dark-0"><?=$user['lastname']?></span>
                                                    </li>
                                                    <li class="mt-18">
                                                        <span class="hp-p1-body"><?=io::_('Email')?></span>
                                                        <span class="mt-0 mt-sm-4 hp-p1-body text-black-100 hp-text-color-dark-0"><?=$user['email']?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="divider border-black-40 hp-border-color-dark-80"></div>

                                    <div class="col-12">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-12 col-md-6">
                                                <h3><?=io::_('Preferance')?></h3>
                                            </div>

                                            <div class="col-12 col-md-6 hp-profile-action-btn text-end">
                                                <button class="btn btn-ghost smooney-primary smooney-text-primary" data-bs-toggle="modal" data-bs-target="#profilePreferanceEditModal"><?=io::_('Edit')?></button>
                                            </div>

                                            <div class="col-12 hp-profile-content-list mt-8">
                                                <ul>
                                                    <li class="mt-18">
                                                        <span class="hp-p1-body"><?=io::_('Language')?></span>
                                                        <span class="mt-0 mt-sm-4 hp-p1-body text-black-100 hp-text-color-dark-0"><?=!empty($languagesISO[$user['language']])?$languagesISO[$user['language']][1]:$user['language']?></span>
                                                    </li>
                                                    <li class="mt-18">
                                                        <span class="hp-p1-body"><?=io::_('Date Format')?></span>
                                                        <span class="mt-0 mt-sm-4 hp-p1-body text-black-100 hp-text-color-dark-0">d/m/Y</span>
                                                    </li>
                                                    <li class="mt-18">
                                                        <span class="hp-p1-body"><?=io::_('Timezone')?></span>
                                                        <span class="mt-0 mt-sm-4 hp-p1-body text-black-100 hp-text-color-dark-0"><?=date_default_timezone_get()?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="profileContactEditModal" tabindex="-1" aria-labelledby="profileContactEditModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width: 416px;">
                        <div class="modal-content">
                            <div class="modal-header py-16">
                                <h5 class="modal-title" id="profileContactEditModalLabel"><?=io::_('Contact Edit')?></h5>
                                <button type="button" class="btn-close hp-bg-none d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="ri-close-line hp-text-color-dark-0 lh-1" style="font-size: 24px;"></i>
                                </button>
                            </div>

                            <div class="divider my-0"></div>

                            <div class="modal-body py-48">
                                <form id="edit-profile" autocomplete="_off_">
                                    <div class="row g-24">
                                        <div class="col-12">
                                            <label for="firstname" class="form-label"><?=io::_('Firstname')?></label>
                                            <input type="text" class="form-control" id="firstname" name="firstname" value="<?=$user['firstname']?>">
                                        </div>

                                        <div class="col-12">
                                            <label for="lastname" class="form-label"><?=io::_('Lastname')?></label>
                                            <input type="text" class="form-control" name="lastname" id="lastname" value="<?=$user['lastname']?>">
                                        </div>

                                        <div class="col-12">
                                            <label for="email" class="form-label"><?=io::_('Email')?></label>
                                            <input type="email" class="form-control" name="email" readonly id="email" value="<?=$user['email']?>">
                                        </div>

                                        <div class="col-6">
                                            <div class="btn w-100" data-bs-dismiss="modal"><?=io::_('Cancel')?></div>
                                        </div>
                                        <div class="col-6">
                                            <button type="submit" class="btn smooney-primary w-100"><?=io::_('Edit')?></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="profilePreferanceEditModal" tabindex="-1" aria-labelledby="profilePreferanceEditModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width: 316px;">
                        <div class="modal-content">
                            <div class="modal-header py-16">
                                <h5 class="modal-title" id="profilePreferanceEditModalLabel"><?=io::_('Preferance Edit')?></h5>
                                <button type="button" class="btn-close hp-bg-none d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="ri-close-line hp-text-color-dark-0 lh-1" style="font-size: 24px;"></i>
                                </button>
                            </div>

                            <div class="divider my-0"></div>

                            <div class="modal-body py-48">
                                <form id="edit-preference" autocomplete="_off_">
                                    <div class="row g-24">
                                        <div class="col-12">
                                            <div class="row mx-0 align-items-center justify-content-start">
                                                <label for="language" class="form-label px-0"><?=io::_('Language')?></label>
                                                <select class="form-select selectpicker w-100" name="language" id="language" data-container="#form-new-transaction">
                                                    <?php foreach($languagesISO as $languageISO){ ?>
                                                        <?php $s= ''; ?>
                                                        <?php if(!empty($user['language']) && $user['language'] == $languageISO[0]) $s= 'selected'; ?>
                                                        <option value="<?=$languageISO[0]?>" <?=$s?> ><?=$languageISO[1]?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="btn w-100" data-bs-dismiss="modal"><?=io::_('Cancel')?></div>
                                        </div>
                                        <div class="col-6">
                                            <button type="submit" class="btn smooney-primary w-100"><?=io::_('Edit')?></button>
                                        </div>
                                    </div>
                                </form>
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
function handleSubmit(event, formId) {
    event.preventDefault();
    event.stopPropagation();
    showLoader();
    let json = $(formId).serializeObject();
    $.ajax({
        method: 'post', url: '<?= SITEACTION ?>/update-profile',
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
}

$(document).ready(function(){
    
    $('body').on('submit','#edit-profile',function(e){
        handleSubmit(e, '#edit-profile');
    });
    $('body').on('submit','#edit-preference',function(e){
        handleSubmit(e, '#edit-preference');
    });
});
</script>
</html>
<?php io::w(); ?>