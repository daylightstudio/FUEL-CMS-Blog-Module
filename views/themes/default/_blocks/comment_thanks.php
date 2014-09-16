<h3><?=lang('blog_comment_thanks')?></h3>
<?php if ($this->fuel->blog->config('monitor_comments')){ ?>
	<p class="success"><?=lang('blog_comments_monitored')?></p>
<?php } ?>
