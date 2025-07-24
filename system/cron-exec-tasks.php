<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Rome');

require_once(dirname(__FILE__) .  '/../api/_wrapper.php');
$wrapper = new WrapperClass(['ioCronJobs']);

$ioConn = new ioConn();
$ioConn->open();
$ioCronJobs = new ioCronJobs($ioConn);



$loop_expiry_time = time() + 55;
while (time() < $loop_expiry_time) {
  $pending_tasks = $ioCronJobs->getPendingCronTasks()['data'];
  // var_dump($pending_tasks);
  if(!empty($pending_tasks)) foreach($pending_tasks as $task){
    // echo '<pre>';
    // print_r($task);
    // echo '</pre>';
    $ioCronJobs->processTask($task);
  }
  // break;
  usleep(200000);
}

// echo '<script>setTimeout(function(){window.location.reload()}, 1000)</script>';
