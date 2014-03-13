<h1>Authors</h1>

<?php foreach($authors as $author) : ?>
<div class="author">
	<?php if (!empty($author->avatar_image)) : ?>
	<?=$author->get_avatar_img_tag(array('class' => 'img_left', 'alt' => $author->name))?>
	<?php endif; ?>
	<?=anchor($author->url, $author->name)?>
</div>
<?php endforeach; ?>
