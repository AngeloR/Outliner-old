<ul id="node-list">
	<?php if(Controller_Auth::is_logged_in()):?>
	<li class="active" id="__new-node"><a href="#" id="__new-node-toggler">New Node</a></li>
	<li class="__hidden" id="__editor">
		<form action="<?php echo url_for('/'); ?>" method="post" id="__new-node-form">
			<input type="hidden" name="id" id="id">
			<label>Title</label>
			<div class="input">
				<input type="text" name="title" id="title" class="xxlarge">
				<span class="help-block">&nbsp;</span>
			</div>
			
			<label>Text</label>
			<div class="input">
				<textarea class="xxlarge" rows="10" name="text" id="text"></textarea>
				<span class="help-block">&nbsp;</span>
			</div>
		</form>
	</li>
	<?php endif; ?>
	<?php $hook = new Captain_Hook(); //Set up the renderer ?>
	<?php foreach($nodes as $node): ?>
		<li id="node-<?php echo $node->id.'-'.$node->safetitle; ?>" class="<?php echo $node->node_type; ?> <?php echo ($node->is_public)?'public':'private'; ?>">
			<?php if($node->node_type == 'branch'): ?>
				<a href="?<?php echo $_SERVER['QUERY_STRING'].((empty($_SERVER['QUERY_STRING']))?'/':'').urlencode($node->safetitle).'/';?>"><?php echo $node->title; ?></a>
			<?php elseif($node->node_type == 'leaf'): ?>
				<a href="<?php echo url_for('preview',$node->shareurl); ?>"><?php echo $node->title; ?></a>
			<?php else: ?>
				<?php $node_array = array($node); $hook->execute('custom-renderer-'.$node->node_type, $node_array); ?>
			<?php endif; ?>
			<span class="pull-right small"><?php echo Date_Difference::getString($node->last_updated); ?> </span>
		</li>
	<?php endforeach; ?>
	<?php if(isset($archived_nodes) && count($archived_nodes)> 0): ?>
		<li class="spacer">&nbsp;</li>
		<?php foreach($archived_nodes as $i => $node): ?>
				<li id="node-<?php echo $node->id.'-'.$node->safetitle; ?>" class="archived <?php echo $node->node_type; ?> <?php echo ($node->is_public)?'public':'private'; ?>">
					<?php if($node->node_type == 'branch'): ?>
						<a href="?<?php echo $_SERVER['QUERY_STRING'].((empty($_SERVER['QUERY_STRING']))?'/':'').urlencode($node->safetitle).'/';?>"><?php echo $node->title; ?></a>
					<?php elseif($node->node_type == 'leaf'): ?>
						<a href="<?php echo url_for('preview',$node->shareurl); ?>"><?php echo $node->title; ?></a>
					<?php else: ?>
						<?php $node_array = array($node); $hook->execute('custom-renderer-'.$node->node_type, $node_array); ?>
					<?php endif; ?>
					<span class="pull-right small"><?php echo Date_Difference::getString($node->last_updated); ?> </span>
				</li>
				<?php endforeach; ?>
	<?php endif; ?>
</ul>