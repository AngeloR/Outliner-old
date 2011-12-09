<form action="<?php echo url_for('/auth/signup'); ?>" method="post">

	<label for="username">Requested Username: </label>
	<div class="input">
		<input type="text" id="username" name="username">
		<span class="help-block">&nbsp;</span>
	</div>
	
	<label for="email">Email: </label>
	<div class="input">
		<input type="text" id="email" name="email">
		<span class="help-block">&nbsp;</span>
	</div>
	
	<label for="password">Password: </label>
	<div class="input">
		<input type="password" id="password" name="password">
		<span class="help-block">&nbsp;</span>
	</div>
	
	<label for="confirm_password">Confirm Password: </label>
	<div class="input">
		<input type="password" id="confirm_password" name="confirm_password">
		<span class="help-block">&nbsp;</span>
	</div>
	
	<div class="actions">
		<button type="submit" class="btn primary">Signup</button> 
		<a href="<?php echo url_for('auth','pwreset'); ?>" class="pull-right">Forgot Password?</a>
	</div>

</form>