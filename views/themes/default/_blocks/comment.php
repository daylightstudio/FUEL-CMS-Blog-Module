<div class="<?=($comment->is_child()) ? 'comment child' : 'comment'?>" id="comment<?=$comment->id?>">

	<div class="comment_content">
		<?php if ($comment->is_by_post_author()) :?>
		<?=$comment->post->author->get_avatar_img_tag(array('class' => 'img_left'))?>
		<?php endif; ?>
		<?=$comment->content_formatted?>
	</div>

	<div class="comment_meta">
		<cite><?=$comment->author_and_link?>, <?=$comment->get_date_formatted('h:iA / M d, Y')?></cite>
	</div>

	<?php /* ?> For displaying the reply form <?php */ ?>
	<div class="comment_form comment_reply_form" id="comment_form<?=$comment->id?>"></div>
	<?php if (!$comment->depth <= 3 AND $post->allow_comments AND $post->is_within_comment_time_limit()) : ?>
		<div class="reply_btn"><a href="<?=$this->fuel->blog->url('comment_reply/'.$comment->id)?>" class="comment_reply">Reply</a></div>
	<?php endif; ?>

</div>