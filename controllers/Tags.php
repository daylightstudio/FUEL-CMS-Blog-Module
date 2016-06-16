<?php
require_once(MODULES_PATH.'/blog/libraries/Blog_base_controller.php');

class Tags extends Blog_base_controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->module_helper('blog', 'blog');
	}
	
	function _remap($method = NULL)
	{
		// get the tag this way in case there is a language parameter
		$tag = $this->fuel->blog->uri_segment(3);

		$cache_id = fuel_cache_id();
		if ($cache = $this->fuel->blog->get_cache($cache_id))
		{
			$output =& $cache;
		}
		else
		{
			$vars = $this->_common_vars();
			$vars['pagination'] = '';
			
			// check if RSS feed
			if ($this->fuel->blog->uri_segment(3) == 'feed')
			{
				
				$type = ($this->fuel->blog->uri_segment(4) == 'atom') ? 'atom' : 'rss';
				
				// set the header type
				$this->fuel->blog->feed_header();
				
				// set the output
				$output = $this->fuel->blog->feed_output($type, $tag);
			}
			else if (!empty($tag) AND $tag != 'index')
			{

				$year = (int) $this->fuel->blog->uri_segment(4);
				$month = (int) $this->fuel->blog->uri_segment(5);
				$day = (int) $this->fuel->blog->uri_segment(6);

				$tag_obj = $this->fuel->blog->get_tag($tag);
				if (!isset($tag_obj->id)) show_404();

				// run before_posts_by_date hook
				$hook_params = array('tag' => $tag_obj, 'tag_slug' => $tag);
				$this->fuel->blog->run_hook('before_posts_by_tag', $hook_params);
				
				$vars = array_merge($vars, $hook_params);

				$limit = $this->fuel->blog->config('per_page');
				$offset = (((int)$this->input->get('page') - 1) * $limit);
				$offset = ($offset < 0 ? 0 : $offset);
				
				if (!empty($offset))
				{
					$vars['page_title'] = array($tag_obj->name, lang('blog_tags_num_title', $offset, $offset + $limit));
				}
				else
				{
					$vars['page_title'] = array($tag_obj->name, lang('blog_tags_page_title'));
				}

				$vars['offset'] = $offset;
				$vars['limit'] = $limit;
				$vars['posts'] = $this->fuel->blog->get_tag_posts_by_date($tag, $year, $month, $day, $limit, $offset);
				$vars['post_count'] = count($this->fuel->blog->get_tag_posts_by_date($tag, $year, $month, $day));

				// create pagination
				$url_segs = array();
				if (!empty($year)) $url_segs[] = $year;
				if (!empty($month)) $url_segs[] = sprintf("%02d", $month);
				if (!empty($day)) $url_segs[] = sprintf("%02d", $day);
				$base_url = 'tags/' . $tag . '/'.implode('/', $url_segs);

				$vars['pagination'] = $this->fuel->blog->pagination($vars['post_count'], $base_url);
				$output = $this->_render('tag', $vars, TRUE);
			}
			else
			{
				$vars['tags'] = $this->fuel->blog->get_tags();
				$vars['page_title'] = lang('blog_tags_page_title');
				$output = $this->_render('tags', $vars, TRUE);
			}
			$this->fuel->blog->save_cache($cache_id, $output);
		}
		$this->output->set_output($output);
	}

}