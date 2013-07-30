<?php
require_once(joinPath($config['site_folder'] , 'models/User.php'));
$user = new User;
$user_id = $user->id;

if(		$config['current_page'] != '/user/login.php' and
		$config['current_page'] != '/user/logout.php' and
		$config['current_page'] != '/user/signup.php' and
		$config['current_page'] != '/user/forgot_password.php' and
		dirname($config['current_page']) != '/admin'
	) {
	checkUser();
}

function checkUser($check_admin = false) {
	global $config;
	
	if((!isset($_SESSION['user_id']) or !$_SESSION['user_id']))
		showMessage("Please login to use this feature", $config['site_url'] . 'user/login.php', "error");
}


function getTaskProgress($user_id) {
	global $sql;
	$aim_task = 100;
	$task_done = $sql->getOne("SELECT SUM(Task.points) FROM TaskUser INNER JOIN Task ON Task.id=TaskUser.task_id 
													WHERE TaskUser.user_id=$user_id");
	$task_done = $task_done ? $task_done : 0;

	$task_progress_percentage = intval(($task_done / $aim_task) * 100);
	
	return $task_progress_percentage;
}