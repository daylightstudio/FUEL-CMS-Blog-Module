<div class="post">
	<?=fuel_edit($post)?>
	
	<?=blog_block('post_unpublished', array('post' => $post))?>
	
	<h1><?=$post->title?> </h1>
	<div class="post_author_date">
		<?=lang('blog_post_published')?> 
		<span class="post_content_date"><?=$post->get_date_formatted(lang('blog_post_date_format'))?></span> 
		<?=lang('blog_post_published_by')?> 
		<span class="post_author_name"><?=$post->author_name?></span>
	</div>
	
	<div class="post_content">
		<?=$post->content_formatted?>
	</div>
	
</div>


<?php if ($post->comments_count > 0) : ?>
	<h3><?=lang('blog_comment_heading')?></h3>
	<div class="comments" id="comments">

		<?=$post->comments_formatted?>
		<?=js('comment_reply', BLOG_FOLDER)?>
		
		<?php /* Another example without the nesting... ?>
		<?php foreach($post->comments as $comment) : ?>

			<div class="<?=($comment->is_child()) ? 'comment child' : 'comment'?>">

				<div class="comment_content" id="comment<?=$comment->id?>">
					<?=$comment->content_formatted?>
				</div>

				<div class="comment_meta">
					<cite><?=$comment->author_and_link?>, <?=$comment->get_date_formatted('h:iA / M d, Y')?></cite>
				</div>
			</div>
		<?php endforeach; ?>

		<?php */ ?>

	</div>
<?php endif; ?>

<?php if ($post->allow_comments) : ?>
	<div class="comment_form" id="comments_form">

	<?php if ($post->is_within_comment_time_limit()) : ?>
		<?php if (empty($thanks)) : ?>
		<h3><?=lang('blog_leave_comment_heading')?></h3>
		<?php else: ?>
		<?=$thanks?>
		<?php endif;
		 ?>
		<?=$comment_form?>
	<?php else: ?>
		<p><?=lang('blog_comments_off')?></p>
	<?php endif; ?>
	</div>

<?php else: ?>
	<p><?=lang('blog_comments_off')?></p>
<?php endif; ?>
