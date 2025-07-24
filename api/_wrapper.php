<?php

date_default_timezone_set('Europe/Rome');
setlocale(LC_TIME, 'it_IT.utf8');

require_once(dirname(__FILE__) .  '/classes/ioException.Class.php');
require_once(dirname(__FILE__) .  '/classes/ioConnection.Class.php');
require_once(dirname(__FILE__) .  '/classes/ioQuery.Class.php');
require_once(dirname(__FILE__) .  '/classes/ioRequest.Class.php');
require_once(dirname(__FILE__) .  '/classes/ioUtils.Functions.php');
require_once(dirname(__FILE__) . '/classes/vendor/autoload.php');

include(dirname(__FILE__) . '/classes/Default_service/_wrapper.php');
include(dirname(__FILE__) . '/classes/Communication_service/_wrapper.php');
include(dirname(__FILE__) . '/classes/Auth_service/_wrapper.php');
include(dirname(__FILE__) . '/classes/Financial_service/_wrapper.php');
include(dirname(__FILE__) . '/classes/EncryptionManager/_wrapper.php');

$ioConn = new ioConn();
$ioConn->open();

$USERDATA = array();
if (session_status() == PHP_SESSION_NONE)
	session_start();
if(!empty($_SESSION) && !empty($_SESSION['user']))
	$USERDATA = $_SESSION['user'];
session_write_close();

class WrapperClass{
	private $version = 'v1';
	public function __construct($classes = array()) {
		$phpVersion = intval(phpversion());
		foreach($classes as $class)
		{
			switch($class){

				case 'ioRouter':
					require_once(dirname(__FILE__) .  '/classes/ioRouter.Class.php');
				break;
				case 'io':
					require_once(dirname(__FILE__) .  '/classes/io.Class.php');
				break;

				default: break;
			}
		}
		new Default_service\wrapperClass($classes);
		new Communication_service\wrapperClass($classes);
		new Auth_service\wrapperClass($classes);
		new Financial_service\wrapperClass($classes);
		new EncryptionManager\wrapperClass($classes);
	}
}
new WrapperClass(['EncryptionManager']);
new EncryptionManager();
