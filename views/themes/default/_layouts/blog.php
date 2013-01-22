<?php 
fuel_set_var('body_class', 'blog');
$current_post = $this->fuel->blog->current_post();
if (isset($current_post) AND !$is_home)
{
	fuel_set_var('canonical', $post->url);	
}
?>
<?php $this->load->view('_blocks/header')?>
	
	<div id="right">
		<?php echo $this->fuel->blog->sidemenu(array('search', 'authors', 'categories', 'links', 'archives'))?>
	</div>

	<div id="main_inner">
		<?php echo fuel_var('body', ''); ?>
	</div>
	
	<div class="clear"></div>
	
<?php $this->load->view('_blocks/footer')?>
