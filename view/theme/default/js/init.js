sandbox.register_module('keymaster', util.extend({
	title: 'Keymaster'
	, description: 'Sets navigation keys'
	, up: function() {
		var new_node = $('#node-list .active').prev();
		
		if($(new_node).attr('class') == '__hidden') {
			new_node = $(new_node).prev();
		}
		
		if($(new_node).hasClass('spacer')) {
			new_node = $(new_node).prev();
		}
		
		if(new_node.length > 0) {
			$('#node-list .active').removeClass('active');
			$(new_node).addClass('active');
		}
		
		return false;
	}
	, down: function() {
		var new_node = $('#node-list .active').next();
		
		if($(new_node).attr('class') == '__hidden') {
			new_node = $(new_node).next();
		}
		
		if($(new_node).hasClass('spacer')) {
			new_node = $(new_node).next();
		}
		
		if(new_node.length > 0) {
			$('#node-list .active').removeClass('active');
			$(new_node).addClass('active');
		}
		
		return false;
	}
	, escape: function() {
		$('#__editor').hide();
		$('#title').blur().val('');
		$('#text').blur().val('');	
	}
	, show_editor: function() {
		$('#__editor').show();
		$('#title').focus();
	}
	, enter: function() {
		// select the current node and pass it to node
		var node = $('#node-list .active')
			, url = window.location.href;
		
		// new node? 
		if($(node).attr('id') == '__new-node') {
			sandbox.request_module('keymaster').show_editor();
			
			return false;
		}
		else {
			// branch or leaf? Leafs will always have text associated with them
			var bol = $(node).attr('id').split('node-')[1]
				, id;
			
			bol = bol.split('-');
			id = bol[0];
			bol.shift();
			
			if($(node).hasClass('branch')) {
				// BRANCH!
				sandbox.request_module('keymaster').update();
			}
			else {
				// LEAF!
				sandbox.request_module('node').load(id);
			}
		}
		
		
	}
	, up_one_level: function() {
		
		// First do a check to see if we're on a fullscreen preview page.
		var loc = window.location.search.split('/');
		if(loc[1] == 'preview') {
			// grab the last element from the breadcrumbs list and redirect the 
			// user to that
			window.location = $('.breadcrumb a').last().attr('href')
		}
		else {
			// we are in a regular branch node... 
			// only do stuff if this isn't the root element 
			if(window.location.search !== '?/') {
				var url = window.location.href
					, s = url.split('/');
				
				// get rid of the last element (something and /);
				while(s[s.length-1] === undefined || s[s.length-1] === '') {
					s.pop();
					s.pop();
				}		
				window.location.href = s.join('/')+'/';
			}
		}
	}
	, save_prep: function() {
		var title = $('#title').val()
		, text = $('#text').val()
		, id = $('#id').val();
	
		if(title !== text && title !== '') {
			if(id === '') {
				// new node
				sandbox.request_module('node').save({title: title, text: text});
			}
			else {
				// old node that was edited
				sandbox.request_module('node').save({id: id, title: title, text: text});
			}
		}
	}
	, key_delete: function() {
		sandbox.request_module('node').destroy();
	}
	, drill_down: function() {
		var node = $('#node-list .active')
			, bol = $(node).attr('id').split('node-')[1]
			, id;
		
		bol = bol.split('-');
		id = bol[0];
		bol.shift();
		
		if($(node).hasClass('branch')) {
			// BRANCH!
			window.location += encodeURIComponent(bol.join('/'))+'/';
		}
		else if($(node).hasClass('leaf')) {
			// LEAF
			// drilling down into a leaf opens it in fullscreen mode.
			window.location = $($(node)[0].children[0]).attr('href');
		}
	}
	, update: function() {
		if($('#node-list .active').hasClass('branch')) {
			var item = {
				title: $($('#node-list .active').children()[0]).html()
				, id: $('#node-list .active').attr('id').split('-')[1]
			}
			$('#title').val(item.title);
			$('#id').val(item.id);
			$('#__editor').show();
			$('#title').focus();
		}
	}
	, preview: function() {
		if($('#node-list .active').hasClass('leaf')) {
			var id = $('#node-list .active').attr('id').split('-')[1];
			sandbox.request_module('node').preview(id);
		}
	}
	, full_preview: function() {
		if($('#node-list .active').hasClass('leaf')) {
			var url = $('#node-list .active a').attr('href');
			window.location = url;
		}
	}
	, toggle_privacy: function() {
		var id = $('#node-list .active').attr('id').split('-')[1];
		
		sandbox.request_module('node').locker(id);
	}
	, archive_toggle: function() {
		var node = $('.active[id^=node]')
			, id = node.attr('id').split('-')[1];
		
		if(node.hasClass('archived')) {
			sandbox.request_module('node').unarchive(id);
		}
		else {
			sandbox.request_module('node').archive(id);
		}
	}
	, initialize: function() {
		shortcut.add('up', sandbox.request_module('keymaster').up, {'disable_in_input': true});
		shortcut.add('down', sandbox.request_module('keymaster').down, {'disable_in_input': true});
		shortcut.add('enter', sandbox.request_module('keymaster').enter, {'disable_in_input': true});
		shortcut.add('escape', sandbox.request_module('keymaster').escape);
		shortcut.add('ctrl+enter', sandbox.request_module('keymaster').save_prep);
		shortcut.add('left', sandbox.request_module('keymaster').up_one_level, {'disable_in_input': true});
		shortcut.add('right', sandbox.request_module('keymaster').drill_down, {'disable_in_input': true});
		shortcut.add('ctrl+d', sandbox.request_module('keymaster').key_delete, {'disable_in_input': true});
		shortcut.add('shift+a', sandbox.request_module('keymaster').archive_toggle, {'disable_in_input': true});
		
		shortcut.add('p', sandbox.request_module('keymaster').preview, {'disable_in_input': true});
		shortcut.add('shift+p', sandbox.request_module('keymaster').full_preview, {'disable_in_input': true});
		shortcut.add('shift+l', sandbox.request_module('keymaster').toggle_privacy, {'disable_in_input': true});
		
		$('#__new-node-toggler').click(function(e){
			e.preventDefault();
			e.stopPropagation(); 
			
			sandbox.request_module('keymaster').show_editor();
		});
		
		$('.leaf').click(function(e){
			// Make sure you are clicking on the actual link!
			if($(e.target).is('a')) {
				// only a single left click triggers this mode. Middle clicking will 
				// open it in a new window.
				if(event.which == 1) {
					e.preventDefault();
					e.stopPropagation();
					sandbox.request_module('node').preview($(this).attr('id').split('-')[1]);
				}
			}
		});
		
		// catch form submit!
		$('#__new-node-form').submit(function(){
			
			return false;
		});
		
		$('.disable-form').submit(function(){
			return false;
		});
	}
}, sandbox.module));


sandbox.register_module('modal', util.extend({
	title: 'Modal'
	, description: 'Provides standard modal interface'
	, set: function(opts) {
		$('#public-modal-title').html(opts.title);
		$('#public-modal-content').html(opts.text);
		$('#public-modal-footer').html(opts.footer);
		
		return sandbox.request_module('modal');
	}
	, show: function() {
		$('#public-modal').modal({'backdrop': 'static', 'keyboard': true, 'show': true});
	}
	, initialize: function() {
		
	}
}, sandbox.module));


sandbox.register_module('node', util.extend({
	title: 'Node'
	, description: 'Node API'
	, locker: function(id) {
		$.ajax({
			url: 'index.php/?/___locker/'+id
			, dataType: 'json'
			, type: 'post'
			, success: function(res) {
				if(res.id) {
					var item = $('[id^=node-'+res.id+'-]');
					console.log(item);
					if(item) {
						if($(item).hasClass('private')) {
							$(item).removeClass('private').addClass('public');
						}
						else {
							$(item).removeClass('public').addClass('private');
						}
					}
				}
			}
		});
	}
 	, archive: function(id) {
 		$.ajax({
 			url: 'index.php/?/___archive/'+id
 			, type: 'post'
 			, success: function(res) {
 				if(res) {
 					window.location.reload();
 				}
 			}
 		});
 	}
 	, unarchive: function(id) {
 		$.ajax({
 			url: 'index.php/?/___archive/'+id
 			, type :'delete'
 			, success: function(res) {
 				if(res) {
 					window.location.reload();
 				}
 			}
 		});
 	}
	, preview: function(id) {
		
		$.ajax({
			url: 'index.php/?/___node/'+id
			, dataType: 'json'
			, type: 'get'
			, success: function(res) {
				if(res.id) {
					var converter = Markdown.getSanitizingConverter();		// as defined in Pagedown
					$('#preview-content').html(converter.makeHtml(res.text));
					$('#preview-title').html(converter.makeHtml('### '+res.title));
					$('#share-url').val(window.location.href.split('?')[0]+'?/preview/'+res.share);
					
					$('#preview-pane').modal({'backdrop': 'static', 'keyboard': true, 'show': true});
					$('#share-url').focus();
				}
			}
		});
		
	}
	, load: function(id) {

		$.ajax({
			url: 'index.php/?/___node/'+id
			, dataType: 'json'
			, type: 'get'
			, success: function(res) {
				if(res.id) {
					$('#id').val(res.id);
					$('#text').val(res.text);
					$('#title').val(res.title);
					
					$('#__editor').show();
					$('#title').focus();
				}
			}
		});
	}
	, destroy: function() {
		var item = $('#node-list .active');
		id = item.attr('id').split('-')[1];
		if(id) {
			$.ajax({
				url: 'index.php/?/___node/'+id
				, dataType: 'json'
				, type: 'delete'
				, success: function(res) {
					$(item).remove();
					$('#__new-node').addClass('active');
				}
			});
		}
	}
	, save: function(node) {
		if(node.id) {
			node._method = 'post';
			var url = 'index.php/?/___node/'+$('#id').val()
		}
		else {
			node._method = 'put';
			var url = window.location.href;
		}
		$.ajax({
			url: url
			, dataType: 'json'
			, type: 'post'
			, data: node
			, success: function(res) {
				if(res.status == 'success') {
					window.location.reload();
				}
				else {
					
				}
			}
		});
	}
}, sandbox.module));