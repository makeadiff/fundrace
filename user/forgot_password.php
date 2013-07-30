<?php
include("../common.php");

if(isset($_REQUEST['action']) and $_REQUEST['action'] == 'Get Password') {
	if($user->passwordRetrival($QUERY)) {
		showMessage("An email containing your login details has been sent to your email address", "login.php");
	}
}
render();
