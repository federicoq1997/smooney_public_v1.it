function loadCSS(href) {
	var cssLink = document.createElement("link");
	cssLink.rel = "stylesheet";
	cssLink.href = href;
	document.head.appendChild(cssLink);
}
function checkClass(className){
	try {
		var existClass = eval(className) instanceof Function;
		if (existClass) return true;
		return false;
	} catch (error) {
		return false;
	}
}
function isScriptLoaded(src,className=null) {
	const scriptLoaded = Array.from(document.getElementsByTagName('script')).some(script => script.src === src);
	const classExists = className ? window[className] !== undefined : true;
	return (scriptLoaded && classExists) || (className && checkClass(className));
}

function loadScript(src, callback,className=null) {
	if (isScriptLoaded(src,className)) {
		callback();
	}
	else {
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.onload = function() {
				callback();
		};
		script.src = src;
		document.head.appendChild(script);
	}
}
const toMilliseconds = (hrs,min,sec) => (hrs*60*60+min*60+sec)*1000;
function hideLoader(){
	$('.smooney-loading .smooney-loading-message .text').html(``);
	$('.smooney-loading').removeAttr("style");
	$('.smooney-loading').addClass('d-none');
}
function showLoader(){
	$('.smooney-loading').removeClass('d-none');
}
Number.prototype.pad = function(n) {
	return new Array(n + 1 - (this + '').length).join('0') + this;
}
function copyToClipboard(data) {
	var $temp = $("<input>");
	$("body").append($temp);
	$temp.val(''+data.target+'').select();
	document.execCommand("copy");
	$temp.remove();
}
function arrayColumn(array, columnName, columnIndex) {
	if(columnIndex) var arr_col={};
	else var arr_col=[];
	$.each(array,function(index,value) {
		if(columnIndex){
			if(columnName!=null)
				arr_col[value[columnIndex]]=value[columnName];
			else arr_col[value[columnIndex]]=value;
		}else{
			arr_col.push(value[columnName]);
		}
	});
	return arr_col;
}
async function asyncForEach(array, callback) {
	for (var key in array) {
		await callback(array[key], key, array);
	}
}
function initToastSwal(position='top-end',timer=3000, url = null){
	return Swal.mixin({
		toast: true,
		position: position,
		showConfirmButton: false,
		timer: timer,
		timerProgressBar: true,
		didOpen: (toast) => {
			toast.addEventListener('mouseenter', Swal.stopTimer)
			toast.addEventListener('mouseleave', Swal.resumeTimer)
			if (url) {
				toast.addEventListener('click', () => {
					window.location.href = url; 
				});
			}
		}
	});
}
function showSuccessMessage(message,timer=3000,position='top-end'){
	const Toast =  initToastSwal(position,timer);
	Toast.fire({
		icon: 'success',
		title: message,
	});
};
function showErrorMessage(message,timer=3000,position='top-end'){
	const Toast =  initToastSwal(position,timer);
	Toast.fire({
		icon: 'error',
		title: message,
	});
};
function showNotyMessage(message,sound=true,timer=5000,url=null){
	const Toast =  initToastSwal('bottom-start',timer,url);
	Toast.fire({
		icon: 'info',
		title: message,
	});
};
function number_format(number, decimals, dec_point, thousands_sep) {
	// Strip all characters but numerical ones.
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s = '',
		toFixedFix = function(n, prec) {
			var k = Math.pow(10, prec);
			return '' + Math.round(n * k) / k;
		};
	// Fix for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	if (s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if ((s[1] || '').length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}
function getCookie(user) {
	var cookieArr = document.cookie.split(";");
	for(var i = 0; i < cookieArr.length; i++) {
		var cookiePair = cookieArr[i].split("=");
		if(user == cookiePair[0].trim()) {
			return decodeURIComponent(cookiePair[1]);
		}
	}
	return null;
}
function checkCookie(cookieName) {
	var cookies = document.cookie.split(';');
	for (var i = 0; i < cookies.length; i++) {
		var cookie = cookies[i].trim();
		if (cookie.indexOf(cookieName + '=') === 0) {
			var cookieValue = cookie.substring(cookieName.length + 1);
			return cookieValue;
		}
	}
	return null;
}
function getBestTextColor(textColorHex, bgColorHex) {
	// Funzione per convertire un colore esadecimale in RGB
	function hexToRgb(hex) {
			hex = hex.replace(/^#/, '');
			const bigint = parseInt(hex, 16);
			const r = (bigint >> 16) & 255;
			const g = (bigint >> 8) & 255;
			const b = bigint & 255;
			return [r, g, b];
	}

	// Funzione per calcolare la luminosità relativa di un colore
	function getRelativeLuminance(color) {
		const [r, g, b] = color.map(value => {
				value /= 255;
				return value <= 0.04045 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
		});

		return 0.2126 * r + 0.7152 * g + 0.0722 * b;
	}

	// Funzione per calcolare il rapporto di contrasto tra due colori
	function getContrastRatio(color1, color2) {
			const luminance1 = getRelativeLuminance(color1);
			const luminance2 = getRelativeLuminance(color2);
			const contrastRatio = (Math.max(luminance1, luminance2) + 0.05) / (Math.min(luminance1, luminance2) + 0.05);
			return contrastRatio;
	}

	// Converti i colori da esadecimale a RGB
	const rgbColor1 = hexToRgb(textColorHex);
	const rgbColor2 = hexToRgb('#fff'); 
	const contrastRatio1 = getContrastRatio(rgbColor1, hexToRgb(bgColorHex));
	const contrastRatio2 = getContrastRatio(rgbColor2, hexToRgb(bgColorHex));
	const contrastThreshold = 4.5; 
	if (contrastRatio1 >= contrastThreshold && contrastRatio1 >= contrastRatio2) return textColorHex;
	return '#fff';
}
$(document).ready(function(){
	$('body').on('click','.copy-txt',function () {
		canDelete = false;
		copyToClipboard({ target:$(this).data('target') });
	});
});