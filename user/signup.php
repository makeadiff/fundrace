<?php
include("../common.php");
$html = new HTML;

$current_action = 'register';

if(isset($QUERY['username'])) {
	if(i($QUERY,'action') == 'Register') {
//		// Uncomment this if you want to captcha protect the signup form.
//		if($_SESSION['captcha_key'] != $_REQUEST['captcha']) {
// 			$QUERY['error'] = 'The captcha key you entered is incorrect';
// 		} else
			if($user->register($QUERY['username'], $QUERY['password'], $QUERY['name'], $QUERY['email'], $QUERY['url'])) {
				showMessage("Welcome to $config[site_title], $_SESSION[user_name]!", "index.php");
			}
	}
}

render();
