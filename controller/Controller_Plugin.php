<?php

d('Loaded: controller/Controller_Plugin.php');

class Controller_Plugin extends Controller {
	
	public static function list_plugins() {
		// Load any new plugins
		if ($handle = opendir('./plugin/')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					// For each file we need to load the plugin.ini
					$path = './plugin/'.$file.'/plugin.ini';
					if(file_exists($path)) {
						$ini = parse_ini_file($path, true);
						$plugin = new Plugin_Manager();
						$plugin->register($ini);
					}
				}
			}
			closedir($handle);
		}
		
		// Get a list of all plugins
		$plugins = R::getAll('select id,name,version,description,is_active,dependencies from plugin order by name asc');
	
		$dg = new OPCDataGrid($plugins);
		$dg->fields(array(
			'version' => 'Version',
			'name' => 'Name',
			'id' => 'Action'
		));

		$dg->modify('name', '__modify_name');
		
		$dg->modify('id', '__toggle_act');
		
		set('page_title','Plugin Manager');
		set('breadcrumbs','');
		
		// Button to rebuil dependency list
		$tmp = '<p><a href="'.url_for('___settings','plugin','rebuild').'" class="btn pull-right">Rebuild Dependency List</a><br><br></p>';

		return html($dg->build().$tmp);
	}
	
	public static function rebuild_dependency_list() {
		$plugin_manager = new Plugin_Manager();
		$plugin_manager->rebuild();
	
		return self::list_plugins();
	}
	
	public static function activate($id) {
		$plugin = new Plugin_Manager();
		
		if($plugin->activate($id)) {
			set('page_title','Plugin Manager');
			set('breadcrumbs','');
			
			$p = R::findOne('plugin','id = ? and is_active = 1',array($id));
			
			if(empty($p)) {
				alert('block-message error','There was a problem activating this plugin.');
			}
			else {
				alert('block-message success','The plugin <b>'.$p->name.'</b> has been activated!');
			}
		}
		
		return self::list_plugins();
	}
	
	public static function deactivate($id) {
		$plugin = new Plugin_Manager();

		if($plugin->deactivate($id)) {
			
			set('page_title','Plugin Manager');
			set('breadcrumbs','');
				
			$p = R::findOne('plugin','id = ? and is_active = 0',array($id));
				
			if(empty($p)) {
				alert('block-message error','There was a problem deactivating this plugin.');
			}
			else {
				alert('block-message success','The plugin <b>'.$p->name.'</b> has been deactivated!');
			}
		}
		else {
			alert('block-message error','There was a problem deactivating this plugin.');
		}
		
		return self::list_plugins();
	}
}

function __modify_name($val,$row) {
		return '<b>'.$val.'</b><span class="help-block">'.$row['description'].'</span>';
	}


function __toggle_act($val,$row) {
		if($row['is_active'] == 1) {
			$tmp = '<a href="'.url_for('___settings','plugin','deactivate',$val).'" class="btn danger">Deactivate</a>';
		}
		else {
			$temp = R::dispense('temp');
			$temp->import($row);
			$manager = new Plugin_Manager();
			if($manager->can_activate($temp)) {
				$tmp = '<a href="'.url_for('___settings','plugin','activate',$val).'" class="btn success">Activate</a>';
			}
			else {
				$tmp = '<a href="#" class="btn disabled">Activate</a>';
			}
		}
		if(!empty($row['dependencies'])) {
			$tmp .= '<span class="help-block"><b>Requires: </b>'.$row['dependencies'];
		}
			
		return $tmp;
	}