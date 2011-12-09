<?php session_start();

/**
 * The application mode defines the stage that the application currently is in. 
 * The following modes apply: 
 * 
 * 0 - Debug
 * 1 - Staging
 * 2 - Production
 * 
 * You can define whatever other modes you want and the debug mechanism will 
 * treat it as "production". It will be up to you to display any errors that 
 * such that you want.
 */
define('APP_MODE',config('app.mode'));	

date_default_timezone_set(config('env.timezone'));

include('lib/rb.php');
include('lib/limonade.php');
include(config('app.path.controller').'Controller.php');
include('lib/Date_Difference.php');

// plugin manager
include('core/plugin.php');

// hook manager
include('core/hooks.php');

// menu helper
include('layout/Menu_Helper.php');

// layout helper
include('layout/Layout_Helper.php');


/**
 * 
 * Our autoload function for loading models and controllers
 * @param string $classname
 */
function __autoload($classname) {
	if(strpos($classname,'Model_') === 0) {
		
		$path = config('app.path.model').$classname.'.php';
		if(file_exists($path)) {
			require_once($path);
			d('Loaded: '.$path);
		}
	}
	else if(strpos($classname,'Controller_') === 0) {
		$path = config('app.path.controller').$classname.'.php';
		if(file_exists($path)) {
			require_once($path);
			d('Loaded: '.$path);
		}
	}
}

/**
 * 
 * Loads all configuration options from config.php
 * 
 * All paths are passed as . delimited. 
 * 
 * @param string $key
 * @return mixed
 */
function config($key) {
    static $store;

    if(!isset($store)) {
        $store = include('config.php');
    }
		if(is_array($key)) {
			$store['site'] = array('title' => $key['site_title'], 'version' => $key['version']);
		}
		else {
	    $path = explode('.',$key);      
	    $root = $store;
	    while(count($path) > 0) {
	        $key = array_shift($path);
	        if(array_key_exists($key,$root)) {
	            $root = $root[$key];
	        }
	    }
	    return $root;
		}
}

/**
 * 
 * The process log allows us to track what is happening when in debug mode.
 * @param string $msg
 */
function d($msg = '',$suppress = false) {
	static $messages;
	static $s;
	
	if(!isset($s) || !$s) {
		$s = $suppress;
	}
		
	if(!isset($messages)) {
		$messages = array(); 
	}
		
	if(!empty($msg)) {
		$messages[] = $msg;
	}
	else {
		if(!$s) {
			return $messages;
		}
	}
}

/**
 * 
 * Displays error messages on the home page of the website.
 * 
 * @param string $type
 * @param string $message
 * @return array An array of all the alerts.
 */
function alert($type = '',$message = '') {
	static $errors;
	
	if(!isset($errors)) {
		$errors = array();
	}
	
	if(empty($type)) {
		return $errors;
	}
	
	if(!array_key_exists($type,$errors)) {
		$errors[$type] = array();
	}
	
	if(empty($message)) {
		return $errors[$type];
	}
	
	$errors[$type][] = $message;
}


/**
 * 
 * Configure our Limonade instance
 * 
 * - Configure RedBean 
 */
function configure() {	
	// Check if this is the first install
	$settings = R::findOne('settings','1');
	if(!$settings) {
		d('Dispatching Installer');
		___install();
	}
	else {
		config($settings->export());
	}
}

/**
 *
 * Sets up the default user information. If the user is not logged in
 * then we load up the website. If they are logged in, we can point
 * them to the application theme.
 */
function before($route) {

	option('views_dir',config('app.path.view').config('app.theme'));
	option('controllers_dir',config('app.path.controller'));

	set('THEME',config('app.theme'));
	set('THEMEDIR',config('app.path.view').config('app.theme'));
	
	layout('layout.html.php');
	d('View path set to: '.config('app.path.view').config('app.theme'));
}

/**
 * 
 * Sets any objects that might be necessary for the rest of the site
 * 
 * 1. Sets the User object
 */
function before_render($content_or_func, $layout, $locals, $view_path) {
	// Always set the user object
	$user = isset($_SESSION[config('env.session')])?unserialize($_SESSION[config('env.session')]):null;
	set('user',$user);
	
	return array($content_or_func, $layout, $locals, $view_path);
}

/**
 * 
 * After runs our cleanup system. There is a variety of things this method will 
 * do.
 * 
 * 1. If debug mode is turned on, this system will dump the process log.
 * 2. It will set the user object so that it can be used throughout the site
 */
function after($output) {
	$tmp = '';
	if(defined('APP_MODE') && APP_MODE == 0) {
		$d = d();
		if(!empty($d)) {
			$tmp .= '<div class="__eitdebug">';
			$tmp .= '<h2>Process Log</h2>';
				ob_start();
				var_dump($d);
			$tmp .= ob_get_clean();
			$tmp .= '</div>';
		}
	}

	return $output.$tmp;
}

function ___install() {
	// New install, set up the default info page that lets the user know that the
	// system has been configured and set up for the latest version.
	d('Installing Outliner');
	$settings = R::dispense('settings');
	$settings->site_title = 'Outliner';
	$settings->version = config('app.version');
	R::store($settings);
	
	$user = R::dispense('user');
	$user->new_user_setup('admin','admin');
	R::store($user);
	d('Outliner Installed');
}

/**
* Bootstrap Process. Due to the structure of limonade the boostrap process
* needs to be the first thing that runs. It will set everything up for our
* system to continue.
*/
function bootstrap() {
	$db = config(config('db.active'));

	R::setup('mysql:host='.$db['host'].';dbname='.$db['name'],$db['user'],$db['pass']);
	d('Connected to database');
	//R::debug(true);
}


bootstrap();



/**
 * The Routes below are organized by level. First level are the immediate pages 
 * like about/contact/home/Services
 * 
 * All levels after that will have to contain one of the other pages in their 
 * path. 
 * 
 * We wrap it in a try/catch so that any errors we hit can be resolved.
 */
try {
	$james = new Captain_Hook();
	dispatch_get('/auth/login',array('Controller_Site','login'));
	dispatch_post('/auth/login',array('Controller_Auth','login_handler'));
	dispatch_get('/auth/logout',array('Controller_Auth','logout_handler'));

	
	if(Controller_Auth::is_logged_in()) {
	
		dispatch_get('/___node/:id', array('Controller_Archive','load'));
		dispatch_post('/___node/:id', array('Controller_Archive','update'));
		dispatch_delete('/___node/:id', array('Controller_Archive','delete'));
		dispatch_post('/___locker/:id', array('Controller_Archive','locker'));
		
		dispatch_get('/___update', array('Controller_Patch','exec'));
		dispatch_get('/___settings', array('Controller_Site','settings'));
		dispatch_post('/___settings', array('Controller_Site','settings_handler'));
		
		dispatch_get('/___archive/:id',array('Controller_Archive','get_archived_nodes'));
		dispatch_post('/___archive/:id', array('Controller_Archive','archive_node'));
		dispatch_delete('/___archive/:id', array('Controller_Archive','unarchive_node'));
		
		
		dispatch_get('/___settings/plugin', array('Controller_Plugin','list_plugins'));
		dispatch_get('/___settings/plugin/rebuild', array('Controller_Plugin','rebuild_dependency_list'));
		dispatch_get('/___settings/plugin/activate/:id',array('Controller_Plugin','activate'));
		dispatch_get('/___settings/plugin/deactivate/:id', array('Controller_Plugin','deactivate'));

		$james->execute('private-routes');
	}
	
	$james->execute('public-routes');
	
	dispatch_get('/preview/:md5', array('Controller_Archive','preview'));
	
	dispatch_get('/**',array('Controller_Archive','display'));
	dispatch_put('/**',array('Controller_Archive','add_new'));
	
	d('Initializing core');
	run();
}
catch(Exception $e) {
	if(defined('APP_MODE') && APP_MODE == 0) {
		echo '<div class="__eitdebug">';
		var_dump(d());
		echo '</div>';
		
		echo '<div class="__eiterror">'.print_r($e->getTrace(),true).'</div>';
	}
}