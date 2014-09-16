<div class="blog_block">
	<h3><?=lang('blog_about_author')?></h3>
	<?=$post->author->get_avatar_img_tag(array('class' => 'img_left'))?>
	<?=$post->author->about_formatted?>
</div>