<?php $categories = $CI->fuel->blog->get_published_categories(); ?>
<?php if ( ! empty($categories)) : ?>
<div class="blog_block">
	<h3>Categories</h3>
	<ul>
		<?php foreach ($categories as $category) : ?>
		<li>
			<?=fuel_edit($category)?>
			<a href="<?=$category->url?>"><?=$category->name?></a> (<?=$category->posts_count?>)
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>