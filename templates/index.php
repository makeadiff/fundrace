<table id="index-layout">
<tr>
<td>
<div id="impact-box" class="box">
<h1>Impact Progress Bar</h1>
<div id="progress-bar">
<div id="progress" style="width:<?php echo $progress_precentage; ?>%;"><?php echo $progress_precentage; ?>%</div>
</div>
</div>
</td>

<td rowspan="2">
<div id="leaderboard-box" class="box">
<h1>Leaderboard</h1>
<table id="leaderboard">
<tr><td>#</td><td>Name</td><td class="leaderboard-raised">Amount</td></tr>
<?php $count=1; foreach($leaderboard as $user) { ?>
<tr>
<td><?php echo $count++ ?></td>
<td><?php echo $user['name'] ?></td>
<td class="leaderboard-raised"><?php echo $user['raised'] ?></td>
</tr>
<?php } ?>
</table>
</div>
</td>
</tr>

<tr><td>
<div id="task-box" class="box">
<h1>Task List</h1>

<div id="task-progress-bar">
<div id="task-progress" style="width:<?php echo $task_progress_percentage; ?>%;"><?php echo $task_progress_percentage; ?>%</div>
</div>

<form id="tasks" action="" method="post">
<ul>
<?php foreach($task_list as $task) { ?>
<li><input type="checkbox" value="<?php echo $task['id'] ?>" name="task[<?php echo $task['id'] ?>]" 
	id="task-<?php echo $task['id'] ?>" <?php if(in_array($task['id'], $tasks_done)) echo 'checked'; ?> /> 
<label for="task-<?php echo $task['id'] ?>"><?php echo $task['name'] ?></label></li>
<?php } ?>
</ul>
<input type="hidden" name="action" value="save" />
</form>
</div>
</td></tr>
</table>

