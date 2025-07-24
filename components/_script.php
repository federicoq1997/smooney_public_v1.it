<script src="/assets/app-assets/js/plugin/jquery.min.js"></script>
<script src="/assets/app-assets/js/plugin/bootstrap.bundle.min.js"></script>
<script src="/assets/app-assets/js/plugin/swiper-bundle.min.js"></script>
<script src="/assets/app-assets/js/plugin/jquery.mask.min.js"></script>
<script src="/assets/app-assets/js/plugin/autocomplete.min.js"></script>
<script src="/assets/app-assets/js/plugin/moment.min.js"></script>
<script src="/assets/app-assets/js/daterangepicker/datepicker-full.js"></script>

<!-- Layouts -->
<script src="/assets/app-assets/js/layouts/header-search.js"></script>
<script src="/assets/app-assets/js/layouts/sider.js"></script>
<script src="/assets/app-assets/js/components/input-number.js"></script>

<!-- Base -->
<script src="/assets/app-assets/js/base/index.js"></script>
<script src="/assets/app-assets/js/components/popover.min.js"></script>

<!-- Pages -->
<script src="/assets/src/js/core/UidGenerator.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/assets/src/js/core/utils.js"></script>
<script type="text/javascript" src="/assets/src/js/core/jquery.serialize-object.js?v=1"></script>
<script>
$.extend(FormSerializer.patterns, {
	validate: /^[a-z][a-z0-9_-]*(?:\[(?:\d*|[a-z0-9_-]+)\])*$/i,
	key:      /[a-z0-9_-]+|(?=\[\])/gi,
	named:    /^[a-z0-9_-]+$/i
});
</script>
<script src="/assets/src/js/plugin/jquery.ui.min.js"></script>
<script src="/assets/src/js/plugin/handlebars.js"></script>
<script src="/assets/src/js/plugin/typeahead.jquery/bloodhound.min.js"></script>
<script src="/assets/src/js/plugin/typeahead.jquery/script.min.js"></script>
<script src="/assets/app-assets/js/bsSelectDrop/jquery.bsSelectDrop.min.js"></script>

<script>
	function copyToClipboard(string) {
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val(''+string+'').select();
		document.execCommand("copy");
		$temp.remove();
	}
	async function downloadFile(ulr){
		return fetch(ulr)
		.then(resp => resp.blob())
		.then(blob => {
			const url_ = window.URL.createObjectURL(blob);
			var link = document.createElement('a');
			link.href =  url_;
			link.click();
			link.delete;
			window.URL.revokeObjectURL(url_);
		})
		.catch(() => showErrorMessage("Qualcosa è andato storto durante il download."));
	}
	var driver_page;
	$(document).ready(function(){
		$('body').on('click','.btn-copy-text',function(){
			const target = $(this).data('target');
			if(!target) return;
			const val = $(target).html();
			copyToClipboard(val);
			$(this).addClass('text-success');
			setTimeout(()=>{ $(this).removeClass('text-success'); },1000);
		});
		var searchItems = new Bloodhound({
			datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: '<?=SITEACTION?>/search-page?search_text=%QUERY&not-collapsed=1',
				wildcard: '%QUERY',
				prepare: function (query, settings) {
					settings.url = settings.url.replace('%QUERY', (query));
					return settings;
				},
				filter: function(response) {
					return response.data;
				}
			}
		});
		Handlebars.registerHelper('translate', function(val) {
			return new Handlebars.SafeString(val['<?=$language?>']);
		});
		$('#header-search').typeahead({
			minLength: 1,
			hint: false,
			highlight: true,
		}, {
			name: 'search-page',
			display: (item)=>{return '';},
			limit: 1000,
			source: searchItems,
			templates: {
				empty: [
					'<div class="empty-message">',
						'<?=io::_('No page found')?>',
					'</div>'
				].join('\n'),
				suggestion: Handlebars.compile('<span class="text-dark">{{translate name}}</span>')
			}
		})
		.on('typeahead:select', function(event, item) {
			showLoader();
			window.location.href = item.url;
		});
		$('body').on('click','.help-driverjs',function(){
			loadCSS('https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css');
			loadScript('https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js', function() {
				var driver = window.driver.js.driver;
				try{
					<?php
						try{
							// Ottieni il percorso del file chiamante
							$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
							$callerFile = $backtrace[0]['file'] ?? __FILE__;
							$callerDir = dirname($callerFile);
							$callerFilename = pathinfo($callerFile, PATHINFO_FILENAME);
							$filePath = $callerDir.'/driverjs/'.$callerFilename.'.php';
							if(!file_exists($filePath)) throw new Exception('File di configurazione assente');
							$configDriverJS = include $filePath;
						}catch(Exception $e){
							$configDriverJS = [];
						}
					?>
					const config = <?=json_encode($configDriverJS)?>;
					if(!!config.steps) config.steps.forEach(function(step, i) {
						if (step['popover'].hasOwnProperty('onPrevClick')) {
							config.steps[i]['popover']['onPrevClick'] = new Function( step['popover']['onPrevClick']);
						}
						if (step['popover'].hasOwnProperty('onNextClick')) {
							config.steps[i]['popover']['onNextClick'] = new Function(step['popover']['onNextClick']);
						}
						if (step['popover'].hasOwnProperty('onCloseClick')) {
							config.steps[i]['popover']['onCloseClick'] = new Function(step['popover']['onCloseClick']);
						}
        	});
					// Converti il callback in funzione se esiste
					if (config.hasOwnProperty('onHighlightStarted')) {
						config['onHighlightStarted'] = new Function(
							'return ' + config['onHighlightStarted']
						)();
					}
					driver_page = new driver(config);
					driver_page.drive();
				}catch(err){
					console.error(`Error durante il caricamento del tutorial`,err);
				}
			});
		});
	});
</script>