<?php date_default_timezone_set(config('env.timezone')); $james = new Captain_Hook();?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo config('site.title'); ?></title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Styles -->
    <link href="<?php echo $THEMEDIR; ?>/css/bootstrap.min.css" rel="stylesheet">
		<?php $james->execute('css'); ?>
    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="outliner.png">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
    <?php $james->execute('head-js'); ?>
  </head>

  <body>

    <div class="topbar">
      <div class="fill">
        <div class="container">
          <a class="brand" href="<?php echo url_for('/'); ?>"><img src="outliner.png"> <?php echo config('site.title'); ?></a>
          <ul class="nav">
            <li<?php echo (request_uri() == '/')?' class="active"':''; ?>><a href="<?php echo url_for('/'); ?>">Home</a></li>
            <li<?php echo (request_uri()==='/about')?' class="active"':''; ?>><a href="<?php echo url_for('about'); ?>/">About</a></li>
            
            <?php $james->execute('public-main-menu'); ?>
            
            <?php if(isset($user)): ?>
           	 <li class="dropdown" data-dropdown="dropdown"><a href="#" class="dropdown-toggle<?php echo (strpos(request_uri(),'/___settings') === 0)?' active':''; ?>">Settings</a>
           	 	<ul class="dropdown-menu">
           	 		<li<?php echo (request_uri() == '/___settings')?' class="active"':''; ?>><a href="<?php echo url_for('___settings'); ?>">General Settings</a></li>
           	 		<li<?php echo (request_uri() == '/___settings/plugin')?' class="active"':''; ?>><a href="<?php echo url_for('___settings','plugin'); ?>">Plugin Manager</a></li>
           	 		<?php $james->execute('settings-menu'); ?>
           	 		<li><a href="http://xangelo.ca/?/Outliner/Documentation/" target="_blank">Documentation</a>
           	 	</ul>
           	 </li>
           	 
           	 <?php $james->execute('private-main-menu'); ?>
           	 
            <?php endif; ?>
          </ul>
          
          <ul class="secondary-nav">
          	<?php if(isset($user)): ?>
          	<li><a href="<?php echo url_for('auth','logout'); ?>">Logout</a>
          	<?php else: ?>
          		<li><a href="<?php echo url_for('auth','login'); ?>">Login</a>
          	<?php endif; ?>
          </ul>
          
        </div>
      </div>
    </div>

    <div class="container">

      <div class="content">
	      <div class="modal" style=" display: none" id="public-modal">
		      <div class="modal-header">
			      <a href="#" class="close">&times;</a>
			      <h3 id="public-modal-title"></h3>
		      </div>
		      <div class="modal-body" id="public-modal-content">
		      
		      </div>
		      <div class="modal-footer" id="public-modal-footer">

		      </div>
	      </div>
      	<?php if(Controller_Auth::is_logged_in()) : ?>
      	
	      	<div class="modal" style=" display: none" id="preview-pane">
		      	<div class="modal-header">
			      	<a href="#" class="close">&times;</a>
			      	<h3 id="preview-title"></h3>
		      	</div>
		      	<div class="modal-body" id="preview-content">
		      		
		      	</div>
		      	<div class="modal-footer">
			      	<form class="disable-form">
			      		<label>Share URL:</label>
			      		<div class="input">
			      			<input type="text" id="share-url" class="xlarge">
			      			<span class="help-block">Share this url to have others see this note. This will only work if the note is public.</span>
			      		</div>
			      	</form>
		      	</div>
	      	</div>
      	<?php endif; ?>
        <div class="page-header">
          <h1><?php echo $page_title; ?></h1>
        </div>
        <div class="row">
        	<div class="span14">
        		<?php echo $breadcrumbs; ?>
        	</div>
          <div class="span14 clear">
          	<?php $alerts = alert(); 
          	
          	foreach($alerts as $type => $alert) : 
          		foreach($alert as $i => $message) : ?>
		          	<div class="alert-message <?php echo $type; ?>">
		          		<a class="close" href="#">&times;</a>
		          		<p><?php echo $message; ?></p>
		          	</div>
          		<?php endforeach; ?>
          	<?php endforeach; ?>
            <?php echo $content; ?>
          </div>
        </div>
      </div>

      <footer>
        <p><a href="http://xangelo.ca">Angelo's</a> Outliner - v<?php echo Controller_Patch::current_version(); ?></p>
        <?php $james->execute('footer'); ?>
      </footer>

    </div> <!-- /container -->

  </body>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
  <script src="<?php echo $THEMEDIR; ?>/js/Markdown.Converter.js"></script>
  <script src="<?php echo $THEMEDIR; ?>/js/Markdown.Sanitizer.js"></script>
  <script src="<?php echo $THEMEDIR; ?>/js/bootstrap-modal.js"></script>
  <script src="<?php echo $THEMEDIR; ?>/js/bootstrap-dropdown.js"></script>
  <script src="<?php echo $THEMEDIR; ?>/js/vader.js"></script>
  <?php $james->execute('public-js'); ?>
  <?php if(array_key_exists(config('env.session'),$_SESSION) && !empty($_SESSION[config('env.session')])) :?>
	  <?php $james->execute('private-js'); ?>
	  <script src="<?php echo $THEMEDIR; ?>/js/init.js"></script>
	<?php else: ?>
	<!--  Keyboard navigation is only present if you log in, otherwise you're a clicker -->
  <?php endif; ?>
</html>
