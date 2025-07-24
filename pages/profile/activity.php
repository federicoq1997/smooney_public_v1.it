<?php
    require_once(dirname(__FILE__).'/../../components/auth.php');
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');
    $pageTitle = 'Smooney - '.io::_('Login Activity');

    new WrapperClass(['mngUser','mngActivityLog']);
    $mngUser = new mngUser($ioConn);
    $user = $mngUser->get(['code'=> $USERDATA['UserId']])['data'];
    if(empty($user)) redirect('/logout');

    $mngActivityLog = new mngActivityLog($ioConn);
    $activityLogs = $mngActivityLog->gets([
        'user_id'=>$user['id'],
        'order_by'=>[ ["`activity_log`.`status`","ASC"],["`activity_log`.`last_update_dt`","DESC"] ]
    ])['data'];
?>
<!DOCTYPE html>
<html dir="ltr">

<head>
    <?php require_once(dirname(__FILE__).'/../../components/_headcontent.php'); ?>
    <!-- Pages -->
    <link rel="stylesheet" type="text/css" href="<?=SITEDOMAIN?>/assets/app-assets/css/pages/page-profile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.5/dist/bootstrap-table.min.css" rel="stylesheet">
    <style>
        .fixed-table-container{
            border-top-left-radius: 7px !important;
            border-top-right-radius: 7px !important;
            border-color: #dfe6e9 !important;
            border-left: 1px solid #dfe6e9 !important;
            border-right: 1px solid #dfe6e9 !important;
            border-top: 1px solid #dfe6e9 !important;
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
                                        <h2><?=io::_('Login Activity')?></h2>
                                        <p class="hp-p1-body mb-0"><?=io::_('Here is the log of the latest access activities.')?></p>
                                    </div>

                                    <div class="divider border-black-40 hp-border-color-dark-80"></div>

                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table mb-0" data-toggle="table" data-pagination="true" data-page-size="10">
                                                <thead>
                                                    <tr>
                                                        <th><?=io::_('Name')?></th>
                                                        <th><?=io::_('IP')?></th>
                                                        <th><?=io::_('Time')?></th>
                                                        <th class="text-end">#</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php if(!empty($activityLogs)) foreach($activityLogs as $activityLog){ ?>
                                                    <tr>
                                                        <td class="align-middle" style="min-width: 200px; width: 200px;">
                                                            <span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter"><?=$mngActivityLog->getBrowserAndOS(!empty($activityLog['user_agent'])?$activityLog['user_agent']:'')?></span>
                                                            <?php if(!empty($activityLog['status'])){ ?>
                                                            <span class="badge text-danger bg-danger-4 hp-bg-dark-danger border-danger"><?=io::_('IP blocked')?></span>
                                                            <?php } ?>
                                                        </td>

                                                        <td class="align-middle" style="min-width: 200px; width: 200px;">
                                                            <span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter"><?=$activityLog['ip']?></span>
                                                        </td>

                                                        <td class="align-middle" style="min-width: 200px; width: 200px;">
                                                            <span class="hp-p1-body text-black-100 hp-text-color-dark-0 fw-lighter"><?=formatLanguage(date_create(!empty($activityLog['deactivate_dt'])?$activityLog['deactivate_dt']:(!empty($activityLog['last_update_dt'])?$activityLog['last_update_dt']:$activityLog['creation_dt'])),'d/m/Y H:i',$language)?></span>
                                                        </td>

                                                        <td class="align-middle text-end">
                                                            <button type="button" class="btn btn-text p-0 hp-p1-body text-black-100 hp-text-color-dark-0 fw-medium <?=empty($activityLog['status'])?'lock-ip':'unlock-ip'?>" data-code="<?=$activityLog['id'].'|'.$activityLog['ip']?>">
                                                                <span><?=empty($activityLog['status'])?io::_('Lock'):io::_('Unlock')?></span>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php } ?>

                                                </tbody>
                                            </table>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.5/dist/bootstrap-table.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.5/dist/extensions/mobile/bootstrap-table-mobile.min.js"></script>

</body>
<script>
function toggleIP(json) {
    showLoader();
    $.ajax({
        method: 'post', url: '<?= SITEACTION ?>/toggle-ip/'+json.id,
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
    $('body').on('click','.lock-ip',function(){
        let id_ip = $(this).data('code');
        let [id,ip] = id_ip.split('|');
        toggleIP({
            id:id,
            ip:ip,
            status:'deactivate',
        });
    });
    $('body').on('click','.unlock-ip',function(){
        let id_ip = $(this).data('code');
        let [id,ip] = id_ip.split('|');
        toggleIP({
            id:id,
            ip:ip,
            status:'active',
        });
    });
});
</script>
</html>
<?php io::w(); ?>