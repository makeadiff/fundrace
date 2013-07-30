<?php 
$extra = array();
if($current_action == 'profile') $extra = array('readonly'=>'readonly');
$html->buildInput("username", "Username", "text", i($PARAM,'username'), $extra); ?>

<?php $html->buildInput("password", "Password", "password", i($PARAM,'password')); ?>
<?php $html->buildInput("confirm_password", "Confirm Password", "password", i($PARAM,'confirm_password')); ?>

<?php $html->buildInput("name", "Name", "text", i($PARAM,'name')); ?>
<?php $html->buildInput("email", "Email", "text", i($PARAM,'email')); ?>
<?php $html->buildInput("url", "Website", "text", i($PARAM,'url') ? i($PARAM,'url') : 'http://'); ?>
