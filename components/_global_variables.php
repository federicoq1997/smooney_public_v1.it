<?php

	define("SITEDOMAIN", 'https://'.$_SERVER['HTTP_HOST']);
	define("SITEACTION", SITEDOMAIN."/action");

	new WrapperClass(['io']);
	$_webroot=dirname(__FILE__) . '/../';

	// // Enabled Languages
	// $languages_iso = io::getAvailableLanguages();
	// $_enabled_languages = array();
	// foreach($languages_iso as $iso)
	//     $_enabled_languages[$iso] = strtoupper($iso);
	
	// // Caricamento della lingua
	$language = isset($_COOKIE["sm_lang"]) ? $_COOKIE["sm_lang"] : 'it';
	if(empty($_enabled_languages[$language])) $language='it';
	define("SITELANG" , $language);

	header('Content-language: '.$language);
	setlocale(LC_TIME, "it_IT.utf8");
	if($language=='en') setlocale(LC_TIME, "en_US.utf8");
	if($language=='fr') setlocale(LC_TIME, "fr_FR.utf8");
	if($language=='es') setlocale(LC_TIME, "es_ES.utf8");
	if($language=='de') setlocale(LC_TIME, "de_DE.utf8");
	if($language=='cz') setlocale(LC_TIME, "cz_CZ.utf8");

	if (class_exists('io')) {$language_page = new io($language, $_webroot);}

?>
