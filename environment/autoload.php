<?php
require_once( dirname(__FILE__).'/EnvironmentType.Class.php' );
try{
	EnvironmentType::init();
}
catch(Exception $e){
	$str = '';
	$str = '<h1>Errore 686549fe-04dd-43e4-9748-464fd3a4b626! </h1><p>Si e\' verificato un fantomatico errore </p>';
	$str .= '<ul><li> Errore: <b>'.$e->getMessage().'</b></li>';
	die($str);
}
?>