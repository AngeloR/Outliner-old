<?php
/**
 * 
 * The abstract plugin class denotes some methods that already exist. 
 * @author SupportCon
 *
 */
abstract class Abstract_Plugin {
	
}

/**
 * 
 * Always implement the plugin interface to ensure that all the necessary hooks 
 * are accounted for. 
 * 
 * @author Angelo R.
 *
 */
interface Plugin_Interface {
	
	/**
	 * 
	 * This method is called whenever a plugin is activated. This method should 
	 * set up the plugin to run every time. Hooks like route/main-menu should 
	 * be configured in this section.
	 */
	public function register();
	
	/**
	 * 
	 * In order to keep things speedy, as soon as you "deactivate" a plugin, the 
	 * unregister function is called. This allows us to remove any hooks that we 
	 * previously configured in the register method. 
	 * 
	 * Please ensure that you clean up EVERYTHING set up in register as the system 
	 * will eventually get bogged down with unnecessary hooks being latched on to. 
	 */
	public function unregister();
}


d('Loaded: core/plugin.php');
class Plugin_Manager {
	
	public function __construct() {
		
	}
	
	/**
	 * 
	 * Will return the path to a plugin if you spply the safename. It's a better 
	 * idea to use this instead of directly linking to a plugin just incase we 
	 * change how plugins are organized at a later date.
	 * 
	 * @param string $safename
	 */
	public static function load($safename) {
		$path = 'plugin/'.$safename.'/plugin.php';
		if(file_exists($path)) {
			require_once($path);
			return true;
		}
		return false;
	}
	
	public function can_activate($plugin) {
		if(!empty($plugin->dependencies)) {
			$dependencies = explode('|',$plugin->dependencies);
			foreach($dependencies as $i => $dependency) {
				$d = R::findOne('plugin','safename = ? and is_active = 1',array($dependency));
				if(empty($d)) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	public function rebuild() {
		$plugins = R::find('plugin','1 order by safename asc');
		
		foreach($plugins as $id => $plugin) {
			$info = parse_ini_file('plugin/'.$plugin->safename.'/plugin.ini',true);
			if(!empty($info)) {

				$plugin->name = $info['plugin']['name'];
				$plugin->safename = $info['plugin']['safename'];
				$plugin->description = $info['plugin']['description'];
				$plugin->version = $info['plugin']['version'];
				$plugin->author = $info['author']['name'];
				$plugin->website = $info['author']['website'];
				
				if(array_key_exists('dependency',$info['plugin'])) {
					$plugin->dependencies = implode('|',$info['plugin']['dependency']);				
				}
				else {
					$plugin->dependencies = '';
				}
					
				
				R::store($plugin);
			}
		}
	}
	
	public function register(array $info) {
		$plugin = R::findOne('plugin','safename = ?', array($info['plugin']['safename']));
		
		if(empty($plugin)) {
			
			$plugin = R::dispense('plugin');
			$plugin->name = $info['plugin']['name'];
			$plugin->safename = $info['plugin']['safename'];
			$plugin->description = $info['plugin']['description'];
			$plugin->version = $info['plugin']['version'];
			$plugin->author = $info['author']['name'];
			$plugin->website = $info['author']['website'];
			
			if(array_key_exists('dependency',$info['plugin'])) {
				$plugin->dependencies = implode('|',$info['plugin']['dependency']);				
			}
			else {
				$plugin->dependencies = '';
			}
			$plugin->is_active = false;
			
			
			
			R::store($plugin);
		}
	}
	
	
	/**
	 * 
	 * When you deactivate a plugin the unregister hook is called to ensure that 
	 * proper plugin clean takes place. This function will automatically remove 
	 * any hooks that the plugin is watching, but it will leave any hooks 
	 * that other plugins are waiting for.
	 * 
	 * @param string $plugin_safename
	 */
	public function unregister($plugin_safename) {
		d('Plugin_'.$plugin_safename.': unregister');
		$delicious_hand = new Captain_Hook;
		$delicious_hand->unregister($plugin_safename);
		
		// Run the unregister function
		if(self::load($plugin_safename)) {
			$plugin = self::plugin_instance($plugin_safename);
			$plugin->unregister();
		}
	}
	
	public static function plugin_instance($plugin_safename) {
		$plugin = 'Plugin_'.$plugin_safename;
		return new $plugin();
	}
	
	public function activate($plugin_id) {
		$plugin = R::findOne('plugin','id = ?',array($plugin_id));
		
		if(!empty($plugin)) {
			
			// Lets check to make sure that the dependencies are available.
			$info = parse_ini_file('plugin/'.$plugin->safename.'/plugin.ini',true);
			if(array_key_exists('dependency', $info['plugin'])) {
				foreach($info['plugin']['dependency'] as $i => $dependency) {
					$d = R::findOne('plugin','safename = ? and is_active = 1',array($dependency));
					if(empty($d)) {
						alert('block-message error','The plugin '.$info['plugin']['name'].' could not be activated because of its dependence on '.$dependency.' which is not present or active.');
						return false;
					}
				}
			}
			
			d('activating plugin: '.$plugin->name);
			
			if(!$plugin->is_active) {
				$plugin->is_active = 1;
				R::store($plugin);
				
				// Initialize the plugin and call the unregister method now that it has
				// been activated. 
				if(self::load($plugin->safename)) {
					$plugin = self::plugin_instance($plugin->safename);
					$plugin->register();
				}
			}
			
			return true;
		}
		else {
			alert('block-message error','That plugin does not exist.');
			return false;
		}
	}
	
	public function deactivate($plugin_id) {
		$plugin = R::findOne('plugin','id = ?',array($plugin_id));
		if(!empty($plugin)) {
			if($plugin->is_active) {
				d('deactivating plugin: '.$plugin->name);
				$plugin->is_active = 0;
				$safename = $plugin->safename;
				R::store($plugin);

				return $this->unregister($safename);
			}
				
			return true;
		}
		alert('block-message error','That plugin does not exist.');
		return false;
	}
}