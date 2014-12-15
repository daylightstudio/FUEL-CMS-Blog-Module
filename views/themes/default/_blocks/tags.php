<?php $tags = $CI->fuel->blog->get_published_tags(); ?>
<?php if ( ! empty($tags)) : ?>
<div class="blog_block">
	<h3><?=lang('blog_tags')?></h3>
	<ul>
		<?php foreach ($tags as $tag) : 
		$tag_cnt = $tag->posts_count;
		?>
		<?php if (!empty($tag_cnt)) : ?>
		<li>
			<?=fuel_edit($tag)?>
			<a href="<?=$tag->url?>"><?=$tag->name?></a> (<?=$tag_cnt?>)
		</li>
		<?php endif; ?>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>