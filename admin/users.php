<?php
require('../common.php');

$user = new Crud("User");
$user->setListingFields('name','email','phone','status');
$user->addListDataField('parent_user_id', 'User', 'POC', '');
$user->fields['parent_user_id']['data']['0'] = 'None';
$user->render();

