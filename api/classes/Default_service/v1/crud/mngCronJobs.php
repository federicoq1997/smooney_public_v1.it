<?php


class mngCronJobs{

	private $cn;

	public function __construct( $cn ) {
		$this->cn = $cn;
	}


	public function getTasks($params=array())
	{
		$data = $this->cn->query("
			SELECT `task`.*
			FROM `tasks` AS `task`
			WHERE 1=1
			".(isset($params['id'])?" AND `task`.`id`='".$this->cn->escape($params['id'])."' ":"")."
			".(isset($params['name'])?" AND `task`.`name`='".$this->cn->escape($params['name'])."' ":"")."
			".(isset($params['status'])?" AND `task`.`status`='".$this->cn->escape($params['status'])."' ":"")."
			".(isset($params['toBeExecuted']) && $params['toBeExecuted'] ?" AND (`task`.`next_execution_time` IS NULL OR `task`.`next_execution_time` < '".date('Y-m-d H:i:s')."') ":"")."
			ORDER BY `next_execution_time` ASC
		");

		if($data) return array('success' =>true,  "data"=>$data);
		return array('message' => 'no tasks found', 'success' =>false, 'data'=>array());
	}

  /* Restituisce i task da processare (in ordine di tempo -> prima quello che è in attesa da più tempo) */
	public function getPendingTasks($params=array())
	{
		return $this->getTasks(array("toBeExecuted"=>true, "status"=>1));
	}


	public function getTask($id_task, $params=array()){

		if(!is_numeric($id_task)) return array( 'message' => 'ID TASK NOT VALID', 'success' =>false);

		$data = $this->cn->query("
			SELECT `task`.*
			FROM `tasks` AS `task`
			WHERE `task`.`id`=".$this->cn->escape($id_task)."
		");

		if($data) return array('success' =>true,  "data"=>$data[0]);
		return array('message' => 'no tasks found', 'success' =>false, 'data'=>null);
	}

	public function editTask($id_task, $data=array())
	{
		if(!is_numeric($id_task)) return array( 'message' => 'ID TASK NOT VALID', 'success' =>false);

		$res = $this->cn->update("
			UPDATE `tasks`
			SET
			`name` = ".(isset($data['name'])?"'".$this->cn->escape($data['name'])."'":"`name`").",
			`description` = ".(isset($data['description'])?"'".$this->cn->escape($data['description'])."'":"`description`").",
			`repeat_each_value` = ".(isset($data['repeat_each_value'])?"'".$this->cn->escape($data['repeat_each_value'])."'":"`repeat_each_value`").",
			`repeat_each` = ".(isset($data['repeat_each'])?"'".$this->cn->escape($data['repeat_each'])."'":"`repeat_each`").",
			`H` = ".(isset($data['H'])?"'".$this->cn->escape($data['H'])."'":"`H`").",
			`I` = ".(isset($data['I'])?"'".$this->cn->escape($data['I'])."'":"`I`").",
			`S` = ".(isset($data['S'])?"'".$this->cn->escape($data['S'])."'":"`S`").",
			`shell_command` = ".(isset($data['shell_command'])?"'".$this->cn->escape($data['shell_command'])."'":"`shell_command`").",
			`http_url` = ".(isset($data['http_url'])?"'".$this->cn->escape($data['http_url'])."'":"`http_url`").",
			`config` = ".(!empty($data['config'])?"'".$this->cn->escape(json_encode($data['config']))."'":"`config`").",
      `last_execution_time` = ".(isset($data['last_execution_time'])?"'".$this->cn->escape($data['last_execution_time'])."'":"`last_execution_time`").",
      `next_execution_time` = ".(isset($data['next_execution_time'])?"'".$this->cn->escape($data['next_execution_time'])."'":"`next_execution_time`").",
      `suspension_time` = ".(isset($data['suspension_time'])?"'".$this->cn->escape($data['suspension_time'])."'":"`suspension_time`").",
      `status` = ".(isset($data['status'])?"'".$this->cn->escape($data['status'])."'":"`status`")."

			WHERE `id` = ".$this->cn->escape($id_task)."
		");

		return array('success' =>true,  "data"=>$res);
	}

	public function createTask($data=array())
	{
		if(empty($data['name'])) return array( 'message' => 'NAME NOT VALID', 'success' =>false);

		$id_task = $this->cn->insert("
			INSERT INTO `tasks`
			(`creation_time`)
			VALUES
			( NOW() )
		");

		if(empty($id_task)) return array( 'message' => 'Error inserting TASK', 'success' =>false);
		return $this->editTask($id_task, $data);
	}


	public function startTask($id_task)
	{
		return $this->editTask($id_task, array(
			"status" => '1'
		));
	}

	public function pauseTask($id_task)
	{
		return $this->editTask($id_task, array(
			"status" => '-1'
		));
	}

	public function stopTask($id_task)
	{
		return $this->editTask($id_task, array(
			"status" => '0'
		));
	}


	public function setTaskLastExecutionTime($id_task, $datetime){
		if(!is_numeric($id_task)) return array( 'message' => 'ID TASK NOT VALID', 'success' =>false);

		$res = $this->cn->update("
			UPDATE `tasks`
			SET `last_execution_time` = '".$this->cn->escape($datetime)."'
			WHERE `id` = ".$this->cn->escape($id_task)."
		");

		return array('success' =>true,  "data"=>$res);
	}

	public function setTaskNextExecutionTime($id_task, $datetime){
		if(!is_numeric($id_task)) return array( 'message' => 'ID TASK NOT VALID', 'success' =>false);

		$res = $this->cn->update("
			UPDATE `tasks`
			SET `next_execution_time` = '".$this->cn->escape($datetime)."'
			WHERE id = ".$this->cn->escape($id_task)."
		");

		return array('success' =>true,  "data"=>$res);
	}


}


?>
