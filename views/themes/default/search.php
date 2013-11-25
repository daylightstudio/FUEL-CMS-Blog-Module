<?php if ($searched) : ?>
	
	<h1><?=count($posts)?> Search <?=pluralize(count($posts), 'Result')?> Returned for &ldquo;<?=$q?>&rdquo;</h1>
	<?php if (!empty($posts)) : ?>

		<?php foreach($posts as $post) : ?>
			<h2><a href="<?=$post->url?>"><?=highlight_phrase($post->title, $q, '<span class="search_highlight">', '</span>')?></a></h2>
			<?=highlight_phrase(($post->get_excerpt_formatted(50, '', TRUE)), $q, '<span class="search_highlight">', '</span>')?>
		<?php endforeach; ?>

	<?php else : ?>
		<p>There were no search results returned.</p>
	<?php endif; ?>
	
<?php else : ?>
	
	<h1>Search</h1>
	<p>Input your search below:</p>
	<?=$search_input?>
	
<?php endif; ?>
