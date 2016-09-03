<?php
class Blog_base_controller extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		if (!$this->fuel->auth->accessible_module('blog'))
		{
			show_404();
		}
		$this->load->module_helper(BLOG_FOLDER, 'blog');
	}
	
	function _common_vars()
	{
		$vars['blog'] =& $this->fuel->blog;
		$vars['is_blog'] = TRUE;
		$vars['page_title'] = '';
		$vars['is_home'] = $this->fuel->blog->is_home();
		//$this->load->vars($vars);
		return $vars;
	}
	
	function _render($view, $vars = array(), $return = FALSE, $layout = '')
	{
		if (empty($layout)) $layout = $this->fuel->blog->layout();

		// get any global variables for the headers and footers
		$uri_path = trim($this->fuel->blog->config('uri'), '/');
		$_vars = $this->fuel->pagevars->retrieve($uri_path);
		
		if (is_array($_vars))
		{
			$vars = array_merge($_vars, $vars);
		}
		
		$view_folder = $this->fuel->blog->theme_path();
		$view_layout = $this->fuel->blog->layout();

		$vars['CI'] =& get_instance();

		$page = $this->fuel->pages->create();

		$theme_module = $this->fuel->blog->config('theme_module');
		$layout_theme_module = $theme_module;

		$in_app_folder = ($theme_module == 'app' OR $theme_module == 'application');
		$view_path = ($in_app_folder) ? 
								APPPATH.'views/'.$view_folder.$view.'.php' 
								: MODULES_PATH.$this->fuel->blog->config('theme_module').'/views/'.$view_folder.$view.'.php';

		// check that a view file exists and if not, do one last check that it exists in the default theme folder and if not, redirect it
		if (!file_exists($view_path))
		{
			// if the view file isn't found in the main theme folder, then look in the default theme area
			$view_path = BLOG_PATH.'views/themes/default/'.$view.'.php';
			if (file_exists($view_path))
			{
				$theme_module = BLOG_FOLDER;
				$view_folder = 'themes/default/';
			}
			else
			{
				redirect_404();
			}
		}
		
		if (!empty($layout))
		{
			$vars['body'] = $this->load->module_view($theme_module, $view_folder.$view, $vars, TRUE);
			$view = $this->fuel->blog->theme_path().$this->fuel->blog->layout();
		}
		else
		{
			$view = $view_folder.$view;
		}

		$vars = array_merge($vars, $this->load->get_vars());

		$output = $this->load->module_view($layout_theme_module, $view, $vars, TRUE);
		$output = $page->fuelify($output);

		if ($return)
		{
			return $output;
		}
		else
		{
			$this->output->set_output($output);
		}
	}
}