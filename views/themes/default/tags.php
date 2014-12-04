<h1>Tags</h1>
<?php foreach($tags as $tag) :
		$posts = $tag->posts;
		if (!empty($posts)) :
	 ?>
<h2><?=fuel_edit($tag->id, 'Edit Category', 'blog/tags')?><?=anchor($tag->url, $tag->name)?></h2>
	<ul class="bullets">
	<?php foreach($posts as $post) : ?>
		<li><?=fuel_edit($post->id, 'Edit Post', 'blog/posts')?><?=anchor($this->fuel_blog->url('id/'.$post->id), $post->title)?></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>
<?php endforeach; ?>