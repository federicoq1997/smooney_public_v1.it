<?php
    require_once(dirname(__FILE__).'/../../components/auth.php');
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');
    $pageTitle = 'Smooney - '.io::_('Wallet');

    new WrapperClass(['mngWallet']);
    $mngWallet = new mngWallet($ioConn);
    $wallets = $mngWallet->gets([ 'user_code'=>$USERDATA['UserId'],'no-child'=>true ])['data'];
?>
<!DOCTYPE html>
<html dir="ltr">

<head>
    <?php require_once(dirname(__FILE__).'/../../components/_headcontent.php'); ?>
    <link rel="stylesheet" type="text/css" href="/assets/app-assets/css/pages/app-contact.css">
</head>

<body>
    <main class="hp-bg-color-dark-90 d-flex min-vh-100">
        <!-- sidebar -->
        <?php require_once(dirname(__FILE__).'/../../components/sidebar/_sidebar.php'); ?>

        <div class="hp-main-layout">
            
            <?php require_once(dirname(__FILE__).'/../../components/_header.php'); ?>

            <div class="hp-main-layout-content">
                <div class="row mb-32 gy-32">
                    <div class="col-12">
                        <div class="row mx-0 align-items-center justify-content-between">
                            <div class="col hp-flex-none w-auto">
                                <button type="button" class="btn smooney-primary w-100 btn-new" >
                                    <span><?=io::_('Add New Wallet')?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-10">
                        <div class="row mx-0 align-items-center justify-content-between">
                            <?php if($wallets) foreach($wallets as $wallet){ ?>
                                <?php
                                    $child_wallets = $mngWallet->gets([ 'user_code'=>$USERDATA['UserId'],'parent_wallet_id'=>$wallet['id'] ])['data'];
                                    if(!empty($child_wallets)){
                                        $wallet['amount_income'] += array_sum(array_column($child_wallets,'amount_income'));
                                        $wallet['amount_expenses'] += array_sum(array_column($child_wallets,'amount_expenses'));
                                        $wallet['amount_balance'] += array_sum(array_column($child_wallets,'amount_balance'));                                
                                    }
                                ?>
                                <div class="col-12 col-md-6 open-wallet my-10" code="<?=$wallet['code']?>" style="cursor:pointer;">
                                    <div class="card hp-card-2">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="col hp-flex-none w-auto">
                                                    <h5 class="mb-0 text-black-80 hp-text-color-dark-30"><?=io::_('Balance')?></h5>

                                                    <div class="d-flex align-items-center">
                                                        <i class="ri-money-euro-circle-line hp-text-color-dark-0" style="font-size: 24px;"></i>
                                                        <h2 class="mb-0 ms-4"><?=number_format(!isset($wallet['amount_balance'])?0.00:$wallet['amount_balance'],2,',',' ')?></h2>
                                                    </div>
                                                </div>

                                                <i class="<?=!empty($wallet['icon'])?$wallet['icon']:'ri-bank-fill'?> text-primary lh-1" style="font-size: 64px;color:var(--smooney) !important"></i>
                                            </div>

                                            <div class="row mt-32 justify-content-between align-items-center">
                                                <div class="col hp-flex-none w-auto ">
                                                    <p class="mb-0 hp-caption text-black-80 hp-text-color-dark-30"><?=io::_('Name')?></p>
                                                    <h5 class="mb-0"><?=$wallet['name']?></h5>
                                                </div>
                                                <div class="col hp-flex-none w-auto ">
                                                    <a href="<?=SITEDOMAIN?>/wallet/<?=$wallet['code']?>">
                                                        <i class="h iconly-Broken-EditSquare hp-cursor-pointer hp-transition hp-hover-text-color-success-1 text-black-80 btn-edit mx-1" data-code="<?=$wallet['code']?>" style="font-size: 24px;"></i>
                                                    </a>
                                                    <i class="iconly-Light-Delete hp-cursor-pointer hp-transition hp-hover-text-color-danger-1 text-black-80 btn-delete mx-1" data-code="<?=$wallet['code']?>" style="font-size: 24px;"></i>
                                                </div>

                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }?>
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
    <script src="/assets/src/js/plugin/color-picker/colorPick.min.js"></script>

</body>
<script>

$(document).ready(function(){
    $('body').on('click','.open-wallet',function(e){
        let code = $(this).attr('code');
        if($(e.target).hasClass('btn-delete')) return;
        window.location.href =`<?=SITEDOMAIN?>/wallet/${code}`;
    });
    $('body').on('click','.btn-new',function(){
        showLoader();
        $.ajax({
            method: 'get', url: '<?= SITEDOMAIN ?>/modal-wallet',
            data: { },
            dataType: 'html'
        })
        .then(html => {
            $('.container-modal').html(html);
            $('#addNewWallet').modal('show');
            hideLoader();
        })
        .catch(err => {
            hideLoader();
            console.error('Error processing request', err);
            showErrorMessage("<?= io::_('Error processing request') ?>");
        });
    });
    $('body').on('click','.btn-delete',async function(){
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success soft-border mx-10',
                cancelButton: 'btn btn-danger soft-border mx-10',
            },
            buttonsStyling: false
        });
        await swalWithBootstrapButtons.fire({
            title: '<?= io::_('Attention') ?>!',
            text: "<?=io::_('By continuing, you will delete this Wallet. The operation will not be reversible.')?>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<?= io::_('Delete') ?>',
            cancelButtonText: '<?= io::_('Cancel') ?>'
        }).then(async (result) => {
            if(result.isConfirmed){
                showLoader();
                $.ajax({
                    method: 'delete', url: '<?= SITEACTION ?>/wallet/'+$(this).data('code'),
                    data: {  },
                    dataType: 'json'
                })
                .then(res => {
                    if (!res.success) {
                        hideLoader();
                        showErrorMessage(res.error);
                        return;
                    }
                    showSuccessMessage('<?= io::_('Change saved') ?>');
                    setTimeout(()=>{ window.location.reload(); },250);
                })
                .catch(err => {
                    hideLoader();
                    console.error('Error processing request', err);
                    showErrorMessage("<?= io::_('Error processing request') ?>");
                });
            }
        });
        
    });
});
</script>
<div class="container-modal"></div>
</html>
<?php io::w(); ?>