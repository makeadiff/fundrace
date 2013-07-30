<h1>Login</h1>

<form action="login.php" method="post" class="form-area">
<label for="username">User name</label>
<input type="text" name="username" id="username" value="<?=isset($PARAM['username']) ? $PARAM['username']:''?>" /><br />

<label for="password">Password</label>
<input type="password" name="password" id="password" value="" /><br />

<label for="remember">Remember Me</label>
<input type="checkbox" name="remember" id="remember" value="1" checked="checked" /><br />

<input type="submit" name="action" value="Login" />
</form>

<a href="forgot_password.php">Forgot Password?</a>
