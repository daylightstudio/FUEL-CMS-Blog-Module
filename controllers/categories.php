<?php
require_once(MODULES_PATH.'/blog/libraries/Blog_base_controller.php');

class Categories extends Blog_base_controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->module_helper('blog', 'blog');
	}
	
	function _remap($method = NULL)
	{
		// get the category this way in case there is a language parameter
		$category = uri_segment(3, FALSE, TRUE, TRUE);

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
			if (uri_segment(3, FALSE, TRUE, TRUE) == 'feed')
			{
				
				$type = (uri_segment(4, FALSE, TRUE, TRUE) == 'atom') ? 'atom' : 'rss';
				
				// set the header type
				$this->fuel->blog->feed_header();
				
				// set the output
				$output = $this->fuel->blog->feed_output($type, $category);
			}
			else if (!empty($category) AND $category != 'index')
			{

				$year = (int) uri_segment(4, FALSE, TRUE, TRUE);
				$month = (int) uri_segment(5, FALSE, TRUE, TRUE);
				$day = (int) uri_segment(6, FALSE, TRUE, TRUE);

				$category_obj = $this->fuel->blog->get_category($category);
				if (!isset($category_obj->id)) show_404();

				// run before_posts_by_date hook
				$hook_params = array('category' => $category_obj, 'category_slug' => $category);
				$this->fuel->blog->run_hook('before_posts_by_category', $hook_params);
				
				$vars = array_merge($vars, $hook_params);
				$vars['posts'] = $this->fuel->blog->get_category_posts_by_date($category, $year, $month, $day);

				$vars['page_title'] = array($category_obj->name, lang('blog_categories_page_title'));
				$output = $this->_render('category', $vars, TRUE);
			}
			else
			{
				$vars['categories'] = $this->fuel->blog->get_categories();
				$vars['page_title'] = lang('blog_categories_page_title');
				$output = $this->_render('categories', $vars, TRUE);
			}
			$this->fuel->blog->save_cache($cache_id, $output);
		}
		$this->output->set_output($output);
	}

}