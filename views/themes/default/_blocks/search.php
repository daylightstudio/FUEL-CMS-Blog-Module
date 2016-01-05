<div class="blog_search">
	<form method="get" action="<?=$this->fuel->blog->url('search')?>">
		<input type="text" name="q" value="" id="q">
		<input type="button" value="Search" class="search_btn">
		
		<?php
		if ($this->config->item('csrf_protection')) :
		    $this->security->csrf_set_cookie();
		?>
		    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>"/>
		<?php endif;?>
	</form>
</div>