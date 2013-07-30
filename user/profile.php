<?php
include("../common.php");
$html = new HTML;

checkUser();
$current_action = 'profile';

if(isset($_REQUEST['name'])) {
 	if($user->update($_SESSION['user_id'], $QUERY['password'], $QUERY['name'], $QUERY['email'], $QUERY['url'])) {
 		$QUERY['success'] = "Profile Updated";
 	}
}
$PARAM = $user->find($_SESSION['user_id']);
unset($PARAM['password']);

render();
