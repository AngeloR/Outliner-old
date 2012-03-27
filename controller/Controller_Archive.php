<?php

class Controller_Archive extends Controller {
	
	
	public static function display($path = '') {
		d('Executing outliner for '.(empty($path)?'root':$path));
		
		// strip trailing slash
		if(strpos($path,'/')===strlen($path)-1) {
			$path = substr($path,0,strlen($path)-1);
		}
		
		$node = R::dispense('node');
		
		$parent = $node->current_node($path);
		if(empty($parent)) {
			return Controller_Site::error404('That node doesn\'t exist.');
		}
		
		set('page_title',$parent->title);
		
		set('breadcrumbs',self::breadcrumbs($path));
		
		// check logged in	
		if(Controller_Auth::is_logged_in()) {
			$nodes = R::find('node','parent_id = ? and archived != 1 order by node_type, title asc, post_date asc',array($parent->id));
			$archived_nodes = self::get_archived_nodes($parent->id);
			set('archived_nodes',$archived_nodes);
		}
		else {
			$nodes = R::find('node','parent_id = ? and archived != 1 and is_public = 1 order by node_type, title asc, post_date asc',array($parent->id));
		}
		set('nodes',$nodes);
		
		// set the current node so that everything can pop in and use it :)
		Model_Node::current($parent);
		
		
		$user = Controller_Auth::user();
		set('user',$user);
		return render('page/outliner.html.php');
	}
	
	public static function breadcrumbs($path,$preview_mode = false) {
		if(empty($path)) {
			$tmp = '<ul class="breadcrumb"><li class="active"><a href="'.url_for('/').'">Root</a></li></ul>';
		}
		else {
			
			$path = explode('/',$path);
			$path_size = count($path);
			
			$tmp = '<ul class="breadcrumb">';
			$tmp .= '<li><a href="'.url_for('/').'">Root</a><span class="divider">/</span></li>';
			
			$front = array();
			
			foreach($path as $i => $p) {
				$front[] = $p;
				
				if($i == ($path_size-1) && !$preview_mode) {
					$tmp .= '<li class="active">'.urldecode($p).'<span class="divider">/</span></li>';
				}
				else {
					$tmp .= '<li><a href="'.url_for(implode('/',$front)).'/">'.urldecode($p).'</a><span class="divider">/</span></li>';
				}
			}

			$tmp .= '</ul>';

			
		}
		return $tmp;
	}
	
	public static function preview($title) {
		$node = R::findOne('node','node_type != "branch" and shareurl = ?',array($title));
		if(empty($node)) {
			return Controller_Site::error404('That url doesn\'t exist.');
		}
		else {
			// Set these up for using the pre-process hooks 
			$title = $node->title;
			$text = $node->text;
			$res = array($title,$text);
			Captain_Hook::execute('pre-process', $res);
			$title = $res[0];
			$text = $res[1];
			
			$title = trim(markdown($title));
			
			$path = explode('/',$node->path);
			array_shift($path);
			$path = join('/',$path);
			
			set('page_title',substr($title,3,strlen($title)-7));
			set('breadcrumbs',self::breadcrumbs($path, true));
			
			
			$text = markdown($text);
			
			$res = array($title,$text);
			Captain_Hook::execute('post-process', $res);
			$title = $res[0];
			$text = $res[1];
			
			return html($text);
		}
	}
	
	public static function load($id) {
		if(Controller_Auth::is_logged_in()) {
			$node = R::findOne('node','id = ?',array($id));
		}
		else {
			$node = R::findOne('node','id = ? and is_public = 1',array($id));
		}
		
		d('',true);
		$a = array(
			'id' => $node->id,
			'title' => $node->title,
			'text' => $node->text,
			'share' => $node->shareurl
		);
		return json($a);
	}
	
	public static function delete($id) {
		d('',true);
		if(Controller_Auth::is_logged_in()) {
			$node = R::findOne('node','id = ?',array($id));
		
		
			$children = R::find('node','parent_id = ?',array($node->id));
				
			foreach($children as $id=>$child) {
				self::delete($id);
				R::trash($child);
			}
			
			R::trash($node);

			return json((bool)true);
		}
		return json((bool)false);
	}
	
	public static function update($id) {
		$node = R::findOne('node','id = ?',array($id));
		if(!empty($node)) {
			$node->import($_POST,'title,text');
			$node->last_updated = date('U');
			R::store($node);
		
			d('',true);
			return json(array('status' => 'success'));
		}
	}
	
	public static function add_new($path, $type = null) {
		
		$title = trim($_POST['title']);
		$text = (array_key_exists('text',$_POST))?$_POST['text']:'';
		
		$node = R::dispense('node');
		
		$parent = $node->new_node($path,$title,$text);
		$id = R::store($node);
		
		if($parent->title !== 'Root') {
			$parent->child_id = $id;
			R::store($parent);
		}
		
		d('',true);
		return json(array('status' => 'success', 'data' => array('id' => $id, 'title' => $node->title)));
	}
	
	public static function locker($id) {
		d('',true);
		$node = R::findOne('node','id = ?',array($id));
		if(!empty($node)) {
			$node->lock();
			R::store($node);
			
			return json(array('id'=>(int)$node->id));
		}
	}
	
	public static function get_archived_nodes($id) {
		return R::find('node','parent_id = ? and archived = 1 order by node_type, title asc, post_date asc',array($id));
		
	}
	
	public static function archive_node($id) {
		d('',true);
		$node = R::findOne('node','id = ?',array($id));
		if(!empty($node)) {
			$node->archived = 1;
			R::store($node);
			return json(true);
		}
		return json(false);
	}
	
	public static function unarchive_node($id) {
		d('',true);
		$node = R::findOne('node','id = ?',array($id));
		if(!empty($node)) {
			$node->archived = 0;
			R::store($node);
			return json(true);
		}
		return json(false);
	}
}