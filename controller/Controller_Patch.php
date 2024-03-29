<?php

class Controller_Patch extends Controller {
	
	
	public static function exec() {
		set('page_title','Application Patcher');
		set('breadcrumbs','');
		return html(self::update());
	}
	
	public static function current_version() {
	    $version = R::findOne('patch','1 order by id desc limit 1');
        if(empty($version)) {
            $version = config('app.version'); 
        }
        else {
            $version = $version->version; 
        }
        return $version;
	}
	
	public static function should_patch($version) {
		$patch = R::findOne('patch','version = ?',array($version));
		return empty($patch);
	}
	
	public static function patched($version) {
		return 'Patch '.$version.' completed successfully.';
	}
	
	
	public static function update() {
		$version = '0.6.0';
		if(self::should_patch($version)) {
			// ftp the directory over to root

			$patch = Model_Patch::new_patch($version);
			$patch->description = 'Updated the entire system base to use the new version of Bootstrap';
			R::store($patch);
			
			return self::patched($version);
		}
		else {
			return 'Patch Unsuccessful:' .self::patched($version );
		}
		
	}
}
