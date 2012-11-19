<?php foreach($blocks as $block){ ?>
<div id="blog_<?=$block?>">
	<?=$this->fuel->blog->block($block)?>
</div>
<?php } ?>