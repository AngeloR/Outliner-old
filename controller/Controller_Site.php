<?php

class Controller_Site extends Controller {
	
	public static function login() {
		set('breadcrumbs','');
		set('page_title','Login');
		return render('page/login.html.php');
	}
	
	public static function about() {
		set('breadcrumbs','');
		set('page_title','About');
		return html(markdown(partial('page/about.md')));
	}
	
	public static function signup() {
		set('breadcrumbs','');
		set('page_title','Sign Up');
		return render('page/signup.html.php');
	}
	
	public static function error404($message) {
		set('breadcrumbs','');
		set('page_title','404 - Something\'s missing');
		return html($message);
	}
	
	public static function settings() {
		set('breadcrumbs','');
		set('page_title','Settings');
		return render('page/settings.html.php');
	}
	
	public static function settings_handler() {
		$settings = R::findOne('settings','1');
		$settings->site_title = $_POST['name'];
		R::store($settings);
		
		config($settings->export());
		
		
		$user = unserialize($_SESSION[config('env.session')]); 
		$user->username = trim($_POST['username']); 
		$user->email = trim($_POST['email']);
		if(!empty($_POST['password']) && !empty($_POST['confirm_password'])) {
			if($_POST['password'] == $_POST['confirm_password']) {
				// Passwords match
				$user->password = $user->hash(trim($_POST['password']));
			}
			else {
				// Passwords don't match
				alert('error','The passwords entered did not match.');
			}
		}
		else if((empty($_POST['password']) && !empty($_POST['confirm_password'])) || (empty($_POST['confirm_password']) && !empty($_POST['password']))) {
			alert('error','Please enter the password and confirm it in order to change your password.');
		}
		
		if($user->getMeta('tainted')) {
			R::store($user);
			$_SESSION[config('env.session')] = serialize($user);
		}
		
		return self::settings();
	}
	
	public static function export_handler() {
		$nodes = R::find('node','1 order by parent_id asc');

		die();
	}
	
	public static function log($page = 0) {
		$logs = R::getAll('select id,details,type,timestamp from log order by timestamp desc');
		$dg = new OPCDataGrid($logs);
		$dg->fields(array(
			'details' => 'Details',
			'type' => 'Type',
			'timestamp' => 'Timestamp'
		));
		
		$dg->modify('timestamp', function($val, $row){
			return date('l jS \of F Y h:i:s A',$val);
		});
		
		return html($dg->build());
	}
}