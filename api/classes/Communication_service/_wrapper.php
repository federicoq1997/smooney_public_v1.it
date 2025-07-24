<?php
namespace Communication_service{
	// date_default_timezone_set('Europe/Rome');
	// setlocale(LC_TIME, 'it_IT.utf8');

	require_once(dirname(__FILE__) .  '/php-mailer/class.phpmailer.php');
	require_once(dirname(__FILE__) .  '/php-mailer/class.smtp.php');


	class wrapperClass{
		private $version = 'v1';
		public function __construct($classes = array()) {
			$phpVersion = intval(phpversion());
			foreach($classes as $class)
			{
				switch($class){

					case 'systemMailer':
						require_once(dirname(__FILE__) .  '/'.$this->version.'/system.Mailer.php');
					break;
					case 'Communications':
						require_once(dirname(__FILE__) .  '/'.$this->version.'/Communications.Class.php');
					break;
					case 'smsSkebby':
						require_once(dirname(__FILE__) .  '/'.$this->version.'/sms.Skebby.php');
					break;
					case 'Telegram':
						require_once(dirname(__FILE__) .  '/'.$this->version.'/Telegram.Class.php');
					break;


					default: break;
				}
			}
		}

	}
}


