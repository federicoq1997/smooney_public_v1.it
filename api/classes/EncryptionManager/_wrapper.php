<?php
namespace EncryptionManager{


	class wrapperClass{
		private $version = 'v1';
		public function __construct($classes = array()) {
			$phpVersion = intval(phpversion());
			foreach($classes as $class)
			{
				switch($class){

					default: 
						if(file_exists(dirname(__FILE__) .  '/'.$this->version.'/crud/'.$class.'.Class.php'))
							require_once(dirname(__FILE__) .  '/'.$this->version.'/crud/'.$class.'.Class.php');
				}
			}
		}

	}
}


