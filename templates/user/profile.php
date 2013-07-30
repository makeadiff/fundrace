<h1>Edit Profile</h1>

<form action="" method="post" class="form-area">
<?php include('_profile_form.php'); ?>

<p>If the password is empty, it will remain unchanged</p>
<input type="hidden" name="user_id" value="<?=$_SESSION['user_id']?>" />
<input type="submit" name="action" value="Save" />
</form>

