<?php
require_once(MODULES_PATH.'/blog/libraries/Blog_base_controller.php');
class Archives extends Blog_base_controller {
	
	function __construct()
	{
		parent::__construct();
	}
	
	function _remap($category = NULL)
	{
		$cache_id = fuel_cache_id();
		$vars = $this->_common_vars();
		if ($cache = $this->fuel->blog->get_cache($cache_id))
		{
			$output =& $cache;
		}
		else
		{
			$where = array();
			if (!empty($category) AND $category != 'index')
			{
				$tables = $this->config->item('tables');
				$where[$tables['blog_categories'].'.slug'] = $category;
			}
			$vars['archives_by_month'] = $this->fuel->blog->get_post_archives($where);
			$vars['page_title'] = lang('blog_archives_page_title');
			$output = $this->_render('archives', $vars, TRUE);
			$this->fuel->blog->save_cache($cache_id, $output);
		}
		
		$this->output->set_output($output);
	}
}