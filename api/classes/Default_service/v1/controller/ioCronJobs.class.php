<?php
date_default_timezone_set('Europe/Rome');

if(!class_exists('mngCronJobs')) require_once(dirname(__FILE__).'/../crud/mngCronJobs.php');

class ioCronJobs{

  public $cn;
  public $crom_list=array();
  public $mngCronJobs;
  protected $api_key= '018cefe0-96e3-7fcc-8d79-796139604819';
  protected $token= '018cefe0-c00c-78dc-970b-6945dd450044';

  public function __construct($dbConn){
    $this->cn = $dbConn;
    $this->mngCronJobs = new mngCronJobs($this->cn);
  }


  public function getPendingCronTasks(){
    $pending_tasks = $this->mngCronJobs->getPendingTasks()['data'];
    var_dump($pending_tasks);
    if(!empty($pending_tasks)) foreach($pending_tasks as $k=>$task)
    {
        // Se il task è stato sospeso -> Lo disattivo
        if(!empty($task['suspension_time']) && strtotime($task['suspension_time'])<time()){
          $this->mngCronJobs->stopTask($task['id']);
          unset($pending_tasks[$k]);
          continue;
        }
    }

    return array("success"=>true, "data"=>$pending_tasks);
  }

  public function processTask($task){
    // $least_next_execution_time = !empty($task['next_execution_time']) ? $task['next_execution_time'] : date('Y-m-d H:i:s');
    $least_next_execution_time = date('Y-m-d H:i:s');

    // Imposto l'ora di ultima esecuzione
    $this->mngCronJobs->setTaskLastExecutionTime($task['id'], date('Y-m-d H:i:s'));


    // Se è stato già eseguito e definito il tempo di prossima esecuzione -> Lo eseguo
    if(!empty($task['next_execution_time']))
    {
      // Se il task è di tipo HTTP -> eseguo una get senza attendere il risultato
      if(!empty($task['http_url'])){
        $this->async_get($task['http_url'],[],$task['timeout']);
      }
    }
    // Altrimenti verrà eseguito al prossimo ciclo secondo il tempo impostato


    // Imposto il datetime di prossima esecuzione
    if(!empty($task['repeat_each']) && !empty($task['repeat_each_value']))
    {
      $next_execution_time = date('Y-m-d H:i:s', strtotime(
        ((intval($task['repeat_each_value'])>=0?'+':'-') . intval($task['repeat_each_value']) . ' '.($task['repeat_each'])), strtotime($least_next_execution_time)
      ));

      // Se è specificata un'ora o un minuto/secondo specifico -> Imposto l'orario
      if($task['H']!=''){
        $next_execution_time = date('Y-m-d '.intval($task['H']).':i:s', strtotime($next_execution_time));
      }
      if($task['I']!=''){
        $next_execution_time = date('Y-m-d H:'.intval($task['I']).':s', strtotime($next_execution_time));
      }
      if($task['S']!=''){
        $next_execution_time = date('Y-m-d H:i:'.intval($task['S']).'', strtotime($next_execution_time));
      }

      $this->mngCronJobs->setTaskNextExecutionTime($task['id'], $next_execution_time);
    }

    return array("success"=>true, "data"=>$task);
  }



  public function get($url, $data=array(), $async=true){
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $url.'?'.http_build_query($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 80,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
  }

  public function async_get($url, $data=[],$timeout=100){
    $check_running_process = shell_exec('ps -C wget -f');
		$headers = ['--header="Api-Key: '.$this->api_key.'"','--header="Token: '.$this->token.'"'];
    $command = 'wget -qO- --no-check-certificate --timeout='.$timeout.' '.implode(' ',$headers).' '.$url.'?'.http_build_query($data);
    preg_match('/wget (.*) '.str_replace('/','\/',$url.'?'.http_build_query($data)).'/', $check_running_process, $matches , 0, 0);
    if(empty($matches)){
      exec( $command . ' > /dev/null 2>&1 &');
    }
  }

  function async_post($url, $data=[],$header=[]){
    $check_running_process = shell_exec('ps -C wget -f');
		$headers = [];
		foreach($header as $k=>$h)
			$headers[]='--header="'.$k.': '.$h.'"';
    $command = 'wget -qO- --no-check-certificate --timeout=100 '.implode(' ',$headers).' --post-data="'.json_encode($data).'" '.$url.'';

    if(strpos($check_running_process, $command)===false){
      exec( $command . ' > /dev/null 2>&1 &');
    }
  }


}

?>
