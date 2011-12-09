<form action="<?php echo url_for('___settings'); ?>" method="post">

	<fieldset>
		<legend>General Settings</legend>
		
		<label>Site Name:</label>
		<div class="input">
			<input type="text" name="name" id="name" value="<?php echo config('site.title'); ?>">
			<span class="help-block">The name of your Outliner install</span>
		</div>
	</fieldset>
	
	
	<fieldset>
		<legend>Admin Account</legend>
		
		<label>Username:</label>
		<div class="input">
			<input type="text" name="username" id="username" value="<?php echo $user->username; ?>">
			<span class="help-block">The name of the administrative account.</span>
		</div>
		
		<label>Password: </label>
		<div class="input">
			<input type="password" name="password" id="password">
			<span class="help-block">&nbsp;</span>
		</div>
		
		<label>Confirm Password:</label>
		<div class="input">
			<input type="password" name="confirm_password" id="confirm_password">
			<span class="help-block">&nbsp;</span>
		</div>
		
		<label>Email:</label>
		<div class="input">
			<input type="email" name="email" id="email" value="<?php echo $user->email; ?>">
			<span class="help-block">There really is no need to enter your email, but IF you do, 
			when you check for updates your email address is passed along to our update server.
			<a href="http://outliner.xangelo.ca/?/preview/a0ccb6c01d0f282c1da2d1b8db3a6481" target="_blank">Why?</a></span>
		</div>
	</fieldset>
	
	<fieldset>
		<legend>Theme</legend>
		
		<label>Theme</label>
		<div class="input">
			<select name="theme">
				<?php ?>
			</select>
			<span class="help-block">Setting a theme will immediately change how the site looks. </span>
		</div>
	</fieldset>
	
	<div class="actions">
		<button type="submit" class="btn primary">Save</button>
	</div>
</form>