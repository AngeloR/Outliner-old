<form action="<?php echo url_for('___settings'); ?>" method="post" class="form-horizontal">

	<fieldset>
		<legend>General Settings</legend>
		<div class="control-group">
			<label class="control-label">Site Name:</label>
			<div class="controls">
				<input type="text" name="name" id="name" value="<?php echo config('site.title'); ?>">
				<p class="help-block">The name of your Outliner install</p>
			</div>
		</div>
	</fieldset>
	
	
	<fieldset>
		<legend>Admin Account</legend>
		<div class="control-group">
			<label class="control-label">Username:</label>
			<div class="controls">
				<input type="text" name="username" id="username" value="<?php echo $user->username; ?>">
				<p class="help-block">The name of the administrative account.</p>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Password: </label>
			<div class="controls">
				<input type="password" name="password" id="password">
				<p class="help-block">&nbsp;</p>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label">Confirm Password:</label>
			<div class="controls">
				<input type="password" name="confirm_password" id="confirm_password">
				<p class="help-block">&nbsp;</p>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label">Email:</label>
			<div class="controls">
				<input type="email" name="email" id="email" value="<?php echo $user->email; ?>">
				<p class="help-block">There really is no need to enter your email, but IF you do, 
				when you check for updates your email address is passed along to our update server.
				<a href="http://outliner.xangelo.ca/?/preview/a0ccb6c01d0f282c1da2d1b8db3a6481" target="_blank">Why?</a></p>
			</div>
		</div>
	</fieldset>
	
	<fieldset>
		<legend>Theme</legend>
		<div class="control-group">
			<label class="control-label">Theme</label>
			<div class="controls">
				<select name="theme">
					<?php ?>
				</select>
				<p class="help-block">Setting a theme will immediately change how the site looks. </p>
			</div>
		</div>

	</fieldset>
	
	<div class="form-actions">
		<button type="submit" class="btn btn-primary">Save</button>
	</div>
</form>