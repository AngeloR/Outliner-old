<?php

class Layout_Helper {
	
	/**
	 * 
	 * This is a helper function so that plugins can bypass limonades default 
	 * "partial" function. Since that function can't be used by plugins due to 
	 * their package location. 
	 * 
	 * 
	 * 
	 * @param unknown_type $filename
	 */
	public static function partial() {
		$args = func_get_args();
		$filename = array_shift($args);
		
		if (is_file($filename)) {
			ob_start();
			extract($args[0]);
			include $filename;
			return ob_get_clean();
		}
		return false;
	}
	
	public static function scripts(array $scripts) {
		$tmp = array();
		foreach($scripts as $i => $script) {
			$tmp[] = '<script src="'.$script.'"></script>';
		}
		
		return implode("\r\n",$tmp);
	}
	
	public static function css(array $css) {
		$tmp = array();
		foreach($css as $i => $sheet) {
			$tmp[] = '<link rel="stylesheet" href="'.$sheet.'">';
		}
		
		return implode("\r\n",$tmp);
	}
	
	public static function file($path) {
		d('',true);
		return render_file($path);
	}
}