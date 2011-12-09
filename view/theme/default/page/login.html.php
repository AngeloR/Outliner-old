<form action="<?php echo url_for('/auth/login'); ?>" method="post">

	<label for="username">Username: </label>
	<div class="input">
		<input type="text" id="username" name="username">
		<span class="help-block">&nbsp;</span>
	</div>
	
	<label for="password">Password: </label>
	<div class="input">
		<input type="password" id="password" name="password">
		<span class="help-block">&nbsp;</span>
	</div>
	
	<div class="actions">
		<button type="submit" class="btn primary">Login</button> 
		<a href="<?php echo url_for('auth','pwreset'); ?>" class="pull-right">Forgot Password?</a>
	</div>

</form>