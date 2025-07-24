<?php
    require_once(dirname(__FILE__).'/../../components/auth.php');
    require_once(dirname(__FILE__).'/../../components/_global_variables.php');
    $pageTitle = 'Smooney - '.io::_('Tags');

    $openEditTagId = !empty($_REQUEST['id'])?$_REQUEST['id']:null;
?>
<!DOCTYPE html>
<html dir="ltr">

<head>
    <?php require_once(dirname(__FILE__).'/../../components/_headcontent.php'); ?>
    <link rel="stylesheet" type="text/css" href="/assets/app-assets/css/pages/app-contact.css">
    <link rel="stylesheet" type="text/css" href="/assets/src/js/plugin/color-picker/colorPick.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.5/dist/bootstrap-table.min.css" rel="stylesheet">
    <style>
        .fixed-table-container{

            /* position: relative; */
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid #dfe6e9;
            border-radius: 24px;
        }
        .table thead th:first-child{
            border-top-left-radius:24px;
        }
        .table thead th:last-child{
            border-top-right-radius:24px;
        }
        .table-hover > tbody > tr:last-child:hover > td:first-child{
            border-bottom-left-radius: 24px;
        }
        .table-hover > tbody > tr:last-child:hover > td:last-child{
            border-bottom-right-radius: 24px;
        }
        .fixed-table-container table{
            border-color: transparent;
        }
    </style>
</head>

<body>
    <main class="hp-bg-color-dark-90 d-flex min-vh-100">
        <!-- sidebar -->
        <?php require_once(dirname(__FILE__).'/../../components/sidebar/_sidebar.php'); ?>

        <div class="hp-main-layout">
            
            <?php require_once(dirname(__FILE__).'/../../components/_header.php'); ?>

            <div class="hp-main-layout-content">
                <div class="row mb-32 gy-32">
                    <div id="exp-toolbar" class="row justify-content-start">
                        
                        <div class="col hp-flex-none w-auto">
                            <button type="button" class="btn smooney-primary w-100 btn-new-tag" >
                                <span><?=io::_('Add New Tag')?></span>
                            </button>
                        </div>
                            
                        
                    </div>
                    <table class="table align-middle table-hover table-borderless" id="tag-list" >
                        
                    </table>
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
var currentUrl = window.location.href;
// Rimuovere il parametro 'callback' dall'URL
var updatedUrl = removeUrlParameter(currentUrl, 'id');

// Aggiornare l'URL senza il parametro 'callback'
history.replaceState(null, null, updatedUrl);

// Funzione per rimuovere un parametro dall'URL
function removeUrlParameter(url, parameter) {
    var urlParts = url.split('?');
    if (urlParts.length >= 2) {
        var prefix = encodeURIComponent(parameter) + '=';
        var params = urlParts[1].split(/[&;]/g);

        // Trova e rimuove il parametro
        for (var i = params.length; i-- > 0;) {
            if (params[i].lastIndexOf(prefix, 0) !== -1) {
                params.splice(i, 1);
            }
        }

        // Ricostruire l'URL senza il parametro
        return urlParts[0] + (params.length > 0 ? '?' + params.join('&') : '');
    }
    return url;
}
function openModalTag(id=null){
    showLoader();
    $.ajax({
        method: 'get', url: '<?= SITEDOMAIN ?>/modal-tag',
        data: { id:id },
        dataType: 'html'
    })
    .then(html => {
        $('.container-modal').html(html);
        $('#addNewTag').modal('show');
        hideLoader();
    })
    .catch(err => {
        hideLoader();
        console.error('Error processing request', err);
        showErrorMessage("<?= io::_('Error processing request') ?>");
    });
}
function reloadTable(){
    $('#tag-list').bootstrapTable('refresh');
}
$(document).ready(function(){
    $('body').on('click','.btn-new-tag',function(){
        openModalTag();
    });
    $('body').on('click','.btn-edit-tag',function(){
        openModalTag($(this).data('code'));
    });
    $('body').on('click','.btn-delete-tag',function(){
        $.ajax({
            method: 'delete', url: '<?= SITEACTION ?>/tag/'+$(this).data('code'),
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
            reloadTable();
        })
        .catch(err => {
            hideLoader();
            console.error('Error processing request', err);
            showErrorMessage("<?= io::_('Error processing request') ?>");
        });
    });
    $('#tag-list').bootstrapTable({
        url: '<?=SITEDOMAIN?>/table-tag-list',
        pagination: true,
        pageSize: 10,
        toggle: "table",
        mobileResponsive: true,
        toolbar: "#exp-toolbar",
        locale: "<?=$language?>",
        search: true,
        columns: [
            {
                "field": "name",
                "title": "<?=io::_('Tag')?>",
                "align": "center",
            },
            {
                "field": "type",
                "title": "<?=io::_('Type')?>",
                "align": "center",
            },
            {
                "field": "color",
                "title": "<?=io::_('Color')?>",
                "align": "center",
            },
            {
                "field": "buttons",
                "title": "",
                "align": "center",
            }
        ]
    });
    <?php if(!empty($openEditTagId)){ ?> openModalTag('<?=$openEditTagId?>'); <?php } ?>
    
});
</script>
<div class="container-modal"></div>
</html>
<?php io::w(); ?>