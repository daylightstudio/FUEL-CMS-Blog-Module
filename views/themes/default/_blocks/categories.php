<?php $categories = $CI->fuel->blog->get_published_categories(); ?>
<?php if ( ! empty($categories)) : ?>
<div class="blog_block">
	<h3>Categories</h3>
	<ul>
		<?php foreach ($categories as $category) : 
		$cat_cnt = $category->posts_count;
		?>
		<?php if (!empty($cat_cnt)) : ?>
		<li>
			<?=fuel_edit($category)?>
			<a href="<?=$category->url?>"><?=$category->name?></a> (<?=$cat_cnt?>)
		</li>
		<?php endif; ?>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>