<?php
include("../common.php");

checkUser();
extract($user->getDetails());


render();
