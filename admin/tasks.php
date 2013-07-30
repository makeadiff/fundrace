<?php
require('../common.php');

$user = new Crud("Task");

$user->addListDataField('parent_task_id', 'Task', 'Parent Task', 'parent_task_id=0 ORDER BY sort_order');
$user->fields['parent_task_id']['data']['0'] = 'None';
$user->render();

