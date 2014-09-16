<div class="<?=($comment->is_child()) ? 'comment child' : 'comment'?>">

	<a name="comment<?=$comment->id?>"></a>
	<div class="comment_content">
		<?php if ($comment->is_by_post_author()) :?>
		<?=$comment->post->author->get_avatar_img_tag(array('class' => 'img_left'))?>
		<?php endif; ?>
		<?=$comment->content_formatted?>
	</div>


	<div class="comment_meta">
		<cite><?=$comment->author_and_link?>, <?=$comment->get_date_formatted('h:iA / M d, Y')?></cite>
	</div>
</div>