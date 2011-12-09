<?php

class Model_Node extends RedBean_SimpleModel {
	public function update() {
		$this->last_updated = date('U');
		
		if(!isset($this->safetitle)) {
			$this->safetitle = urlencode($this->title);
		}
		
		if(!isset($this->shareurl)) {
			$this->shareurl =  md5($this->path.date('U'));
		}
		
		if(empty($this->node_type) || in_array($this->node_type, array('leaf','branch'))) {
			if(empty($this->text)) {
				$this->node_type = 'branch';
			}
			else {
				$this->node_type = 'leaf';
			}
		}
		
		if(!isset($this->archived)) {
			$this->archived = 0;
		}
	}
	
	public static function current($val = '') {
		if(empty($val)) {
			return unserialize($_SESSION['outliner-data']);
		}
		else {
			$_SESSION['outliner-data'] = serialize($val);
		}
	}
	
	public function after_update() {
		$this->cascade_update_time();
	}
	
	/**
	 * 
	 * When an update occurs, the update time cascades up the list so that all 
	 * branches will show the new last-updated time. 
	 */
	public function cascade_update_time() {
		// Only cascade the update time if we are not at the root element
		if($this->path != '/') {
			// Split the path into sections
			$path = explode('/',$this->path);
			// the first element is blank
			array_shift($path);
			
			$sql = 'update node set last_updated = '.$this->last_updated.' where ';	
			$tmp = array();
			foreach($path as $i => $p) {
				$safetitle = array_pop($path);
				$tmp[] = '(safetitle = "'.$safetitle.'" and path = "/'.implode('/',$path).'")';
			}
			
			$sql .= implode(' or ',$tmp);
			
			R::exec($sql);
		}
	}
	
	public function parent_node($path) {
		if(empty($path) || $path == '/') {
			// root path
			$node = new stdClass();
			$node->id = 0;
			$node->title = 'Root';
			$node->safetitle = '/';
			$node->is_public = 1;
			$node->path = '';
		}
		else {
			$split = explode('/',$path);
		
			$title = array_pop($split);
			$path = '/'.implode('/',$split);
		
			d('Loading node: '.$title.' ('.$path.'/'.$title.')');
		
				
			if(Controller_Auth::is_logged_in()) {
				$node = R::findOne('node','safetitle = ? and path = ? ',array($title,$path));
			}else {
				$node = R::findOne('node','safetitle = ? and path = ? and is_public = 1',array($title,$path));
			}
		
		}
		
		return $node;
	}
	
	/**
	 * 
	 * This toggles the lock mode on a node
	 */
	public function lock() {
		if($this->is_public) {
			$this->is_public = 0;
		}
		else {
			$this->is_public = 1;
		}
	}
	
	/**
	 * 
	 * Allows you to create a new node. The details here aren't saved, only the 
	 * defaults for each are set up. It is up to you to save them. 
	 * 
	 * Having it this way allows you to set defaults, and then alter them as 
	 * necessary. 
	 * 
	 * @param string $path
	 * @param string $title
	 * @param string $text
	 * @param string $node_type
	 */
	public function new_node($path,$title,$text,$node_type = '') {
		$parent = $this->parent_node($path);
		$time = date('U');

		$this->title = trim($title);
		$this->text = $text;
		$this->parent_id = $parent->id;
		$this->post_date = $this->last_updated = $time;
		$this->archived = 0;
		if($parent->path == '') {
			$this->path = '/';
		}
		else {
			if($parent->path == '/') {
				$this->path = $parent->path.$parent->safetitle;
			}
			else {
				$this->path = $parent->path.'/'.$parent->safetitle;
			}
		}
		
		$this->is_public = $parent->is_public;
		
		if(!empty($node_type)) {
			$this->node_type = strtolower(trim($node_type));
		}
		
		return $parent;
	}
	
}