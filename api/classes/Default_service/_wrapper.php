<?php
namespace Default_service{

	class wrapperClass{
		private $version = 'v1';
		public function __construct($classes = array()) {
			$phpVersion = intval(phpversion());
			foreach($classes as $class)
			{
				switch($class){

					case 'ioCronJobs':
						require_once(dirname(__FILE__) .  '/'.$this->version.'/controller/ioCronJobs.class.php');
					break;

					default: 
						if(file_exists(dirname(__FILE__) .  '/'.$this->version.'/crud/'.$class.'.Class.php'))
							require_once(dirname(__FILE__) .  '/'.$this->version.'/crud/'.$class.'.Class.php');

				}
			}
		}

	}

}

