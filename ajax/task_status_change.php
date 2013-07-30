<?php
require('../common.php');

$task_id = $QUERY['task_id'];
$status = ($QUERY['status'] == 'true') ? true : false;

$task_exist = $sql->getOne("SELECT task_id FROM TaskUser WHERE user_id=$user_id AND task_id=$task_id");

if(!$status and $task_exist) {
	$sql->execQuery("DELETE FROM TaskUser WHERE user_id=$user_id AND task_id=$task_id");
} elseif($status and !$task_exist) {
	$sql->execQuery("INSERT INTO TaskUser(user_id,task_id,completed_on) VALUES($user_id ,$task_id,NOW())");
}

$task_progress_percentage = getTaskProgress($user_id);

print '{"task_done_percentage":'.$task_progress_percentage.'}';