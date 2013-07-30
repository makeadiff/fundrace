<h1>Signup</h1>

<form action="" method="post" class="form-area">
<?php include('_profile_form.php'); ?>

<!--
<p>Type the characters you see in the picture below. </p>
<img src="captcha.php" /><?php $html->buildInput('captcha','Word Verification'); ?>
<?php $html->buildInput('action', '&nbsp;', 'submit', "Get Another Word"); ?>
-->

<p>We will not give away your email address to anyone. We just need it in case you forgot your password.</p>

<input type="submit" name="action" value="Register" />
</form>
