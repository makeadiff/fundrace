<?php
require('common.php');


$amount_raised = 9000; //$sql->getOne("SELECT SUM(amount) FROM Collection");
$aim_amount = 10000;
$progress_precentage = intval(($amount_raised / $aim_amount) * 100);

$task_list = $sql->getAll("SELECT id,name FROM Task WHERE parent_task_id=0 ORDER BY sort_order");
$tasks_done = $sql->getCol("SELECT task_id FROM TaskUser WHERE user_id=$user_id");

$task_progress_percentage = getTaskProgress($user_id);

$leaderboard = $sql->getAll("SELECT U.id,U.name,SUM(amount) AS raised FROM User U INNER JOIN Collection C ON U.id=C.user_id GROUP BY C.user_id ORDER BY raised DESC");

render();
