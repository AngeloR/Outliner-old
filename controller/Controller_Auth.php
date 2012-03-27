<?php

class Controller_Auth extends Controller {
	
	public static function is_logged_in() {
		static $logged;
	
		if(!isset($logged)) {
			$logged = (array_key_exists(config('env.session'),$_SESSION) && !empty($_SESSION[config('env.session')]));
		}
	
		return $logged;
	}
	
	public static function user() {
		if(self::is_logged_in()) {
			return unserialize($_SESSION[config('env.session')]);
		}
	}
	
	public static function login_handler() {
		$user = R::findOne('user','username = ? and password = ?',array($_POST['username'],Model_User::hash($_POST['password'])));
		if(!empty($user)) {
			$now = time();
			$user->last_login = $now;
			R::store($user);
			
			$_SESSION[config('env.session')] = serialize($user);
			redirect_to('/');
		}
		else {
			set('page_title','Login');
			set('breadcrumbs','');
			alert('alert-error','Please enter a valid username and password to sign up.');
			return render('page/login.html.php');
		}
	}
	
	public static function logout_handler() {
		d('Calling logout handler');
		$session = config('env.session');
		if(array_key_exists($session,$_SESSION)) {
			unset($_SESSION[$session]);
		}
		redirect_to('/');
	}
}