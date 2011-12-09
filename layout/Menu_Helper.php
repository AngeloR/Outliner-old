<?php

class Menu_Helper {
	
	private $instance;
	
	private $menu;
	
	/**
	 * 
	 * Privatize our constructor to ensure that there is only ever one menu 
	 * entry present.
	 */
	public function __construct() {
		$this->menu = array('/' => array());
	}
	
	/**
	 * 
	 * Add an item to the menu
	 * @param array $menu 
	 * @param string $key The location of the menu in the toolbar
	 */
	public function add(array $menu, $key) {
		$path = explode('/', $key);
		
		$root =& $this->menu;
		foreach($path as $i => $uri) {
			if(array_key_exists('/'.$uri,$root)) {
				$root =& $this->menu['/'.$uri];
			}
		}
		
		$this->menu = array_merge($root,$menu);
	}
	
	/**
	 * 
	 * Parse the menu and prepare it for display.
	 */
	public function parse() {
		$root = $this->menu; 
		$path = array();
		$menu = '';
		
		foreach($root as $path => $item) {
			if(array_key_exists('children',$item)) {
				// This menu item has children!
				$menu .= '<li class="dropdown" data-dropdown="dropdown"><a href="#" class="dropdown-toggle">'.$item['display'].'</a>';
				
				$tmp = '<ul class="dropdown-menu">';
				foreach($item['children'] as $p => $i) {
					$tmp .= '<li '.$this->is_active($p).'><a href="'.url_for($p).'">'.$i.'</a></li>';
				}
				$tmp .= '</ul>';
				
				$menu .= $tmp.'</li>';
			}
			else {
				if(array_key_exists('display',$item)) {
					$menu .= '<li '.$this->is_active($path).'><a href="'.url_for($path).'">'.$item['display'].'</a></li>';
				}
			}
		} 
		
		echo $menu; 
	}
	
	public function is_active($path) {
		if(request_uri() == $path) {
			return 'class="active"';
		}
	}
}