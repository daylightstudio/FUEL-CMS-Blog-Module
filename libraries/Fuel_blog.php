<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FUEL CMS
 * http://www.getfuelcms.com
 *
 * An open source Content Management System based on the 
 * Codeigniter framework (http://codeigniter.com)
 *
 * @package		FUEL CMS
 * @author		David McReynolds @ Daylight Studio
 * @copyright	Copyright (c) 2013, Run for Daylight LLC.
 * @license		http://docs.getfuelcms.com/general/license
 * @link		http://www.getfuelcms.com
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * The Main Library class used in the blog
 *
 * @package		FUEL BLOG
 * @subpackage	Libraries
 * @category	Libraries
 * @author		David McReynolds @ Daylight Studio
 * @link		http://docs.getfuelcms.com/modules/blog/fuel_blog
 */

class Fuel_blog extends Fuel_advanced_module {
	
	protected $_settings = NULL;
	protected $_current_post = NULL;

	/**
	 * Constructor
	 *
	 * The constructor can be passed an array of config values
	 */
	public function __construct($params = array())
	{
		parent::__construct();
		
		if (empty($params))
		{
			$params['name'] = 'blog';
		}
		$this->initialize($params);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns the title of the blog specified in the settings
	 *
	 * @access	public
	 * @return	string
	 */
	public function title()
	{
		return $this->config('title');
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the descripton of the blog specified in the settings
	 *
	 * @access	public
	 * @return	string
	 */
	public function description()
	{
		return $this->config('description');
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the language abbreviation currently used in CodeIgniter
	 *
	 * @access	public
	 * @param	boolean
	 * @return	string
	 */
	public function language($code = FALSE)
	{
		// static $language;
		// static $language_code;

		// set the value to TRUE
		if ($this->fuel->language->has_multiple())
		{
			$language = $this->fuel->language->detect();
		}
		else
		{
			$language = $this->CI->config->item('language');	
		}
		
		if ($code)
		{
			$this->CI->config->module_load(BLOG_FOLDER, 'language_codes');
			$codes = $this->CI->config->item('lang_codes');
			$flipped_codes = array_flip($codes);
			if (isset($flipped_codes[$language]))
			{
				return $flipped_codes[$language];
			}
			return FALSE;
		}
		else
		{
			return $language;
		}
	}

	/**
	 * Returns the domain to be used for the blog based on the FUEL configuration. 
	 * If empty it will return whatever $_SERVER['SERVER_NAME']. Needed for Atom feeds
	 *
	 * @access	public
	 * @param	boolean
	 * @return	string
	 */
	public function domain()
	{
		if ($this->CI->config->item('domain', 'fuel'))
		{
			return $this->CI->config->item('domain', 'fuel');
		}
		else
		{
			return $_SERVER['SERVER_NAME'];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the blog specific URL
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function url($uri = '')
	{
		$uri = trim($uri, '/');
		$base_uri = trim($this->config('uri'), '/');
		
		return site_url($base_uri.'/'.$uri);
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the blog specific URI
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function uri($uri = '')
	{
		$uri = trim($uri, '/');
		$base_uri = trim($this->config('uri'), '/');
		return $base_uri.'/'.$uri;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the blog specific URI
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function uri_segment($n, $default = FALSE, $rerouted = TRUE, $strip_lang = TRUE)
	{
		$segs = explode('/', $this->config('uri'));
		$index = count($segs) - 1 + $n;
		return uri_segment($index, $default, $rerouted, $strip_lang);
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the blog specific RSS feed URL
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public function feed($type = 'rss', $slug = '')
	{
		if (empty($slug))
		{
			if (($this->CI->uri->rsegment(1) == 'categories' OR $this->CI->uri->rsegment(1) == 'tags') AND $this->CI->uri->rsegment(2))
			{
				$uri = $this->CI->uri->rsegment(1).'/feed/'.$this->CI->uri->rsegment(2).'/';
				return $this->url($uri.$type);
			}
		}
		$uri = (!empty($slug)) ? $slug.'/feed/' : 'feed/';
		return $this->url($uri.$type);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Sets the HTTP headers needed for the RSS feed
	 *
	 * @access	public
	 * @return	string
	 */
	public function feed_header()
	{
		header('Content-Type: application/xml; charset=UTF-8');
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns the output for the RSS feed
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public function feed_output($type = 'rss', $slug = NULL)
	{
		$this->CI->load->helper('xml');
		$this->CI->load->helper('date');
		$this->CI->load->helper('text');
		
		$vars = $this->feed_data($slug);
		if ($type == 'atom')
		{
			$output = $this->CI->load->module_view(BLOG_FOLDER, 'feed/atom_posts', $vars, TRUE);
		}
		else
		{
			$output = $this->CI->load->module_view(BLOG_FOLDER, 'feed/rss_posts', $vars, TRUE);
		}
		return $output;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the data need for the blog feed
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function feed_data($slug = NULL, $type = 'categories', $limit = 10)
	{
		$data['title'] = $this->title();
		$data['link'] = $this->url();
		$data['description'] = $this->description();
		$data['last_updated'] = $this->last_updated();
		$data['language'] = $this->language();
		
		if (!empty($slug))
		{
			if ($type == 'tags')
			{
				$data['posts'] = $this->get_tag_posts($slug, 'sticky, publish_date desc', $limit);	
			}
			else
			{
				$data['posts'] = $this->get_category_posts($slug, 'sticky, publish_date desc', $limit);		
			}
			
		}
		else
		{
			$data['posts'] = $this->get_posts(array(), 'sticky, publish_date desc', $limit);
		}
		return $data;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns last updated blog post
	 *
	 * @access	public
	 * @return	string
	 */
	public function last_updated()
	{
		$post = $this->get_posts(array(), 'publish_date desc', 1);
		if (!empty($post[0])) return $post[0]->atom_date;
		return FALSE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns the path to the theme view files
	 *
	 * @access	public
	 * @return	string
	 */
	public function theme_path()
	{
		$theme_path = trim($this->config('theme_path'), '/').'/';
		return $theme_path;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns name of the theme layout file to use
	 *
	 * @access	public
	 * @return	string
	 */
	public function layout()
	{
		return '_layouts/'.$this->config('theme_layout');
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns an image based on the assets upload path
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	boolean
	 * @return	string
	 */
	public function image_path($image, $variable = NULL, $is_server = FALSE)
	{
		$base_path = $this->fuel->blog->config('asset_upload_path');
		$base_path = preg_replace('#(\{.+\})#U', $variable, $base_path);

		if ($is_server)
		{
			$folder = assets_server_path($base_path);
		}
		else
		{
			$folder = assets_path($base_path);
		}
		return $folder.$image;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns a boolean value whether it is the home page
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function is_home()
	{
		if (uri_path(FALSE) == trim($this->config('uri'), '/'))
		{
			return TRUE;
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns header of the blog
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function header($vars = array(), $return = TRUE)
	{
		return $this->view('_blocks/header', $vars, $return);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns a view for the blog
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	boolean
	 * @return	string
	 */
	public function view($view, $vars = array(), $return = TRUE)
	{
		$view_folder = $this->theme_path();
		$block = $this->CI->load->module_view($this->config('theme_module'), $view_folder.$view, $vars, TRUE);
		if ($return)
		{
			return $block;
		}
		echo $block;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a block view file for the blog
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	boolean
	 * @return	string
	 */
	public function block($block, $vars = array(), $return = TRUE)
	{
		$view = '_blocks/'.$block;
		return $this->view($view, $vars, $return);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns a the specified blog model object. Options are posts, categories, comments, settings and links
	 *
	 * @access	public
	 * @param	string
	 * @return	object
	 */
	public function &model($model = NULL)
	{
		if (strncmp('blog_', $model, 5) !== 0)
		{
			$model = 'blog_'.strtolower($model);
		}

		if ($sub = $this->submodules($model))
		{
			return $sub->model();
		}

		return parent::model($model);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns the sidemenu for the blog
	 *
	 * @access	public
	 * @param	array
	 * @return	string
	 */
	public function sidemenu($blocks = array('search', 'categories'))
	{
		return $this->block('sidemenu', array('blocks' => $blocks));
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * Returns the current post for the page. Only set when viewing a single post.
	 *
	 * @access	public
	 * @return	object
	 */
	public function current_post()
	{
		return $this->_current_post;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the most recent posts
	 *
	 * @access	public
	 * @param	int
	 * @return	array
	 */
	public function get_recent_posts($limit = 5, $where = array())
	{
		$posts = $this->get_posts($where, 'publish_date desc', $limit);
		return $posts;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns the most popular posts
	 *
	 * @access	public
	 * @param	int
	 * @return	array
	 */
	public function get_popular_posts($limit = 5, $where = array())
	{
		$model = $this->model('blog_posts');
		$tables = $this->_tables();
		$model->db()->select('(SELECT COUNT(*) FROM '.$tables['blog_comments'].' WHERE '.$tables['blog_posts'].'.id = '.$tables['blog_comments'].'.post_id GROUP BY fuel_blog_comments.post_id) AS num_comments', FALSE);
		$model->db()->limit($limit);
		$model->db()->order_by('num_comments desc');
		$query = $model->get();
		$posts = $query->result();
		return $posts;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns the most recent posts for a given category
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_category_posts($category = '', $order_by = 'publish_date desc', $limit = NULL, $offset = NULL, $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_posts');
		$model->readonly = TRUE;
		$tables = $this->_tables();
		
		if (is_numeric($category))
		{
			$where[$tables['blog_categories'].'.id'] = $category;
		}
		else
		{
			$where[$tables['blog_categories'].'.slug'] = $category;
		}
		$posts = $model->find_all($where, $order_by, $limit, $offset, $return_method, $assoc_key);
		return $posts;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns posts by providing a given date
	 *
	 * @access	public
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_category_posts_by_date($category, $year = NULL, $month = NULL, $day = NULL, $limit = NULL, $offset = NULL, $order_by = 'sticky, publish_date desc', $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_posts');
		$tables = $this->_tables();
		$model->db()->where($tables['blog_categories'].'.slug', $category);
		$posts = $this->get_posts_by_date($year, $month, $day, NULL, $limit, $offset, $order_by, $return_method);
		return $posts;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the most recent posts for a given category
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_tag_posts($tag = '', $order_by = 'publish_date desc', $limit = NULL, $offset = NULL, $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_posts');
		$model->readonly = TRUE;
		$tables = $this->_tables();
		
		if (is_numeric($tag))
		{
			$where[$tables['blog_tags'].'.id'] = $tag;
		}
		else
		{
			$where[$tables['blog_tags'].'.slug'] = $tag;
		}
		$posts = $model->find_all($where, $order_by, $limit, $offset, $return_method, $assoc_key);
		return $posts;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns posts by providing a given date
	 *
	 * @access	public
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_tag_posts_by_date($tag, $year = NULL, $month = NULL, $day = NULL, $limit = NULL, $offset = NULL, $order_by = 'sticky, publish_date desc', $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_posts');
		$tables = $this->_tables();
		$model->db()->where($tables['blog_tags'].'.slug', $tag);
		$posts = $this->get_posts_by_date($year, $month, $day, NULL, $limit, $offset, $order_by, $return_method);
		return $posts;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns posts by providing a given date
	 *
	 * @access	public
	 * @param	int
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_posts_by_date($year = NULL, $month = NULL, $day = NULL, $slug = NULL, $limit = NULL, $offset = NULL, $order_by = 'sticky, publish_date desc', $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->_get_posts_by_date($year, $month, $day, $slug, $limit, $offset, $order_by, $return_method, $assoc_key);
		$return_arr = (!empty($slug)) ? FALSE : TRUE;
		$posts = $model->get($return_arr)->result();
		return $posts;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns posts count by providing a given date
	 *
	 * @access	public
	 * @param	int
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	int
	 */
	public function get_posts_by_date_count($year = NULL, $month = NULL, $day = NULL, $slug = NULL, $limit = NULL, $offset = NULL, $order_by = 'sticky, publish_date desc', $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->_get_posts_by_date($year, $month, $day, $slug, $limit, $offset, $order_by, $return_method, $assoc_key);
		$model->_common_query();
		$count = $model->record_count();
		return $count;
	}

	// --------------------------------------------------------------------

	/**
	 * Helper function for getting posts by date
	 *
	 * @access	public
	 * @param	int
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	protected function _get_posts_by_date($year = NULL, $month = NULL, $day = NULL, $slug = NULL, $limit = NULL, $offset = NULL, $order_by = 'sticky, publish_date desc', $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_posts');
		$model->readonly = TRUE;
		$tables = $this->_tables();
		if (!empty($year)) $model->db()->where('YEAR('.$tables['blog_posts'].'.publish_date) = '.$year);
		if (!empty($month)) $model->db()->where('MONTH('.$tables['blog_posts'].'.publish_date) = '.$month);
		if (!empty($day)) $model->db()->where('DAY('.$tables['blog_posts'].'.publish_date) = '.$day);
		if (!empty($slug)) $model->db()->where($tables['blog_posts'].'.slug = "'.$slug.'"');
		if (!empty($limit))
		{
			$model->db()->limit($limit);
		}
		$model->db()->offset($offset);
		$model->db()->order_by($order_by);
		return $model;
	}
	// --------------------------------------------------------------------

	/**
	 * Returns posts based on specific query parameters
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_posts($where = array(), $order_by = 'sticky, publish_date desc', $limit = NULL, $offset = NULL, $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_posts');
		$model->readonly = TRUE;
		$posts = $model->find_all($where, $order_by, $limit, $offset, $return_method, $assoc_key);
		return $posts;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the number of posts
	 *
	 * @access	public
	 * @param	mixed
	 * @return	array
	 */
	public function get_posts_count($where = array())
	{
		$model = $this->model('blog_posts');

		if ($this->fuel->language->has_multiple())
		{
			$tables = $this->_tables();
			$language = $this->language();
			$model->db()->where($tables['blog_posts'].'.language', $language);
		}

		$count = $model->record_count($where);
		return $count;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns posts to be displayed for a specific page. Used for pagination mostly
	 *
	 * @access	public
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_posts_by_page($limit = NULL, $offset = NULL, $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_posts');
		$model->readonly = TRUE;
		$posts = $this->get_posts('', 'sticky, publish_date desc', $limit, $offset, $return_method, $assoc_key);
		return $posts;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns posts grouped by the year/month
	 *
	 * @access	public
	 * @param	array
	 * @param	int
	 * @param	int
	 * @return	array
	 */
	public function get_post_archives($where = array(), $limit = NULL, $offset = NULL)
	{
		$posts = $this->get_posts($where, 'publish_date desc');
		$return = array();
		foreach($posts as $post)
		{
			$key = date('Y/m', strtotime($post->publish_date));
			// if ($key != date('Y/m', time()))
			// {
				if (!isset($return[$key]))
				{
					$return[$key] = array();
				}
				$return[$key][] = $post;
			//}
		}
		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a single post
	 *
	 * @access	public
	 * @param	mixed	can be id or slug
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	public function get_post_by_date_slug($slug, $year = NULL, $month = NULL, $day = NULL, $order_by = NULL, $return_method = NULL)
	{
		$model = $this->model('blog_posts');
		$model->readonly = TRUE;
		$tables = $this->_tables();
		if (is_int($slug))
		{
			$where[$tables['blog_posts'].'.id'] = $slug;
		}
		else
		{
			$where[$tables['blog_posts'].'.slug'] = $slug;
		}
		if (!empty($year)) $model->db()->where('YEAR('.$tables['blog_posts'].'.publish_date) = '.$year);
		if (!empty($month)) $model->db()->where('MONTH('.$tables['blog_posts'].'.publish_date) = '.$month);
		if (!empty($day)) $model->db()->where('DAY('.$tables['blog_posts'].'.publish_date) = '.$day);

		$post = $model->find_one($where, $order_by, $return_method);
		$this->_current_post = $post;
		return $post;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a single post
	 *
	 * @access	public
	 * @param	mixed	can be id or slug
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	public function get_post($slug, $order_by = NULL, $return_method = NULL)
	{
		$model = $this->model('blog_posts');
		$model->readonly = TRUE;
		$tables = $this->_tables();
		if (is_int($slug))
		{
			$where[$tables['blog_posts'].'.id'] = $slug;
		}
		else
		{
			$where[$tables['blog_posts'].'.slug'] = $slug;
		}

		$post = $model->find_one($where, $order_by, $return_method);
		$this->_current_post = $post;
		return $post;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns the next post (if any) from a given date
	 *
	 * @access	public
	 * @param	object	The current post
	 * @param	string	The return type of the object (array or object)
	 * @return	object
	 */
	public function get_next_post($current_post, $return_method = NULL)
	{
		$tables = $this->_tables();
		$posts = $this->get_posts(array('publish_date >=' => $current_post->publish_date, "{$tables['blog_posts']}.id !=" => $current_post->id), 'publish_date asc, id asc', 1, NULL, $return_method);
		if (!empty($posts))
		{
			return $posts[0];
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the previous post (if any) from a given date
	 *
	 * @access	public
	 * @param	object	The current post
	 * @param	string	The return type of the object (array or object)
	 * @return	object
	 */
	public function get_prev_post($current_post, $return_method = NULL)
	{
		$tables = $this->_tables();
		$posts = $this->get_posts(array('publish_date <=' => $current_post->publish_date, "{$tables['blog_posts']}.id !=" => $current_post->id), 'publish_date desc, id desc', 1, NULL, $return_method);
		if (!empty($posts))
		{
			return $posts[0];
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a list of blog categories
	 *
	 * @access	public
	 * @param	mixed
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_categories($where = array(), $order_by = NULL, $limit = NULL, $offset = NULL, $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_categories');
		$model->readonly = TRUE;
		$tables = $this->_tables();
		$where[$tables['blog_categories'].'.published'] = 'yes';
		$categories = $model->find_all($where, $order_by, $limit, $offset, $return_method, $assoc_key);
		return $categories;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a single blog category
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	public function get_category($category, $order_by = NULL, $return_method = NULL)
	{
		$model = $this->model('blog_categories');
		$model->readonly = TRUE;
		$tables = $this->_tables();
		$where = $tables['blog_categories'].'.slug = "'.$category.'" OR '.$tables['blog_categories'].'.name = "'.$category.'" AND '.$tables['blog_categories'].'.published = "yes"';
		$categories = $model->find_one($where, $order_by, $return_method);
		return $categories;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a list of published categories
	 *
	 * @access	public
	 * @param	string
	 * @return	object
	 */
	public function get_published_categories($language = NULL)
	{
		$model = $this->model('blog_categories');
		return $model->get_published_categories($language);
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a list of blog tags
	 *
	 * @access	public
	 * @param	mixed
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_tags($where = array(), $order_by = NULL, $limit = NULL, $offset = NULL, $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_tags');
		$model->readonly = TRUE;
		$tags = $model->find_all($where, $order_by, $limit, $offset, $return_method, $assoc_key);
		return $tags;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a single blog tag
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_tag($category, $order_by = NULL, $return_method = NULL)
	{
		$model = $this->model('blog_tags');
		$model->readonly = TRUE;
		$tables = $this->_tables();
		$where = $tables['blog_tags'].'.slug = "'.$category.'" OR '.$tables['blog_tags'].'.name = "'.$category.'"';
		$tags = $model->find_one($where, $order_by, $return_method);
		return $tags;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a list of published tags
	 *
	 * @access	public
	 * @param	string
	 * @return	array
	 */
	public function get_published_tags($language = NULL)
	{
		$model = $this->model('blog_tags');
		return $model->get_published_tags($language);
	}

	// --------------------------------------------------------------------

	/**
	 * Searches posts for a specific term
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	int
	 * @param	int
	 * @return	array
	 */
	public function search_posts($term, $order_by = 'publish_date desc', $limit = NULL, $offset = NULL)
	{
		$model = $this->model('blog_posts');
		$model->readonly = TRUE;
		
		$tables = $this->_tables();

		// can't use this because of the need to group with parenthesis'
		// $model->db()->like('title', $t);
		// $model->db()->or_like('content', $t);
		
		$terms = explode(' ', $term);
		$where = '(';
		$cnt = count($terms);
		$i = 0;
		foreach($terms as $t)
		{
			$t = $this->CI->db->escape_str($t);
			$where .= "(".$tables['blog_posts'].".title LIKE '%".$t."%' OR ".$tables['blog_posts'].".content LIKE '%".$t."%' OR ".$tables['blog_posts'].".content_filtered LIKE '%".$t."%')";
			if ($i < $cnt - 1) $where .= " AND ";
			$i++;
		}
		$where .= ") AND ".$tables['blog_posts'].".published = 'yes' AND ";
		$where .= $tables['blog_posts'].'.publish_date <= "'.datetime_now().'"';
		$posts = $model->find_all($where, $order_by, $limit, $offset);
		return $posts;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns comments. Usually specify a post in the where parameter
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_comments($where = array(), $order_by = 'date_added desc', $limit = NULL, $offset = NULL, $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_comments');
		$model->readonly = TRUE;
		$tables = $this->_tables();
		$where[$tables['blog_comments'].'.published'] = 'yes';
		$where[$tables['blog_posts'].'.published'] = 'yes';
		$comments = $model->find_all($where, $order_by, $limit, $offset, $return_method, $assoc_key);
		return $comments;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a single comment
	 *
	 * @access	public
	 * @param	int
	 * @return	array
	 */
	public function get_comment($id)
	{
		$model = $this->model('blog_comments');
		$model->readonly = TRUE;
		$comment = $model->find_by_key($id);
		return $comment;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns links
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_links($where = array(), $order_by = 'precedence desc', $limit = NULL, $offset = NULL, $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_links');
		$model->readonly = TRUE;
		$tables = $this->_tables();
		$where[$tables['blog_links'].'.published'] = 'yes';
		$links = $model->find_all($where, $order_by, $limit, $offset, $return_method, $assoc_key);
		return $links;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a FUEL author/user
	 *
	 * @access	public
	 * @param	int
	 * @return	object
	 */
	public function get_user($id)
	{
		$model = $this->model('blog_users');
		$model->readonly = TRUE;
		$where['active'] = 'yes';
		$where['fuel_user_id'] = $id;
		$user = $model->find_one($where);
		return $user;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns FUEL users/authors
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	int
	 * @param	int
	 * @param	string
	 * @param	string
	 * @return	array
	 */
	public function get_users($where = array(), $order_by = NULL, $limit = NULL, $offset = NULL, $return_method = NULL, $assoc_key = NULL)
	{
		$model = $this->model('blog_users');
		$model->readonly = TRUE;
		$where['active'] = 'yes';
		$users = $model->find_all($where, $order_by, $limit, $offset, $return_method, $assoc_key);
		return $users;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns the logged in information array of the currently logged in FUEL user
	 *
	 * @access	public
	 * @return	mixed
	 */
	public function logged_in_user()
	{
		$this->CI->load->module_library(FUEL_FOLDER, 'fuel_auth');
		$valid_user = $this->CI->fuel->auth->valid_user();
		if (!empty($valid_user))
		{
			return $valid_user;
		}
		return NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns whether you are logged into FUEL or not
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function is_logged_in()
	{
		$this->CI->load->module_library(FUEL_FOLDER, 'fuel_auth');
		return $this->CI->fuel->auth->is_logged_in();
	}

	// --------------------------------------------------------------------

	/**
	 * Returns pagination stuff
	 *
	 * @access	public
	 * @return	string
	 */
	public function pagination($post_count, $base_url = '')
	{
		$limit = $this->config('per_page');
		$this->CI->load->library('pagination');

		$config = $this->config('pagination');
		$offset = (((int)$this->CI->input->get('page') - 1) * $limit);
		$offset = ($offset < 0 ? 0 : $offset);

		$config['base_url'] = $this->url($base_url.'?');
		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = 'page';
		$config['per_page'] = $limit;
		$config['num_links'] = 2;
		$config['use_page_numbers'] = TRUE;
		$config['total_rows'] = $post_count;
		
		// create pagination
		$this->CI->pagination->initialize($config); 
		return $this->CI->pagination->create_links();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns whether cache should be used based on the blog settings
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function use_cache()
	{
		$use_cache = (int) $this->config('use_cache');
		return !(empty($use_cache));
	}

	// --------------------------------------------------------------------

	/**
	 * Returns a cached file if it exists
	 *
	 * @access	public
	 * @param	string
	 * @return	mixed
	 */
	public function get_cache($cache_id, $cache_group = NULL, $skip_checking = FALSE)
	{
		if ($this->use_cache())
		{
			$cache_options =  array('default_ttl' => $this->config('cache_ttl'));
			$this->CI->load->library('cache', $cache_options);
			if (empty($cache_group))
			{
				$cache_group = $this->CI->config->item('blog_cache_group');
			}

			if ($this->use_cache() AND $this->CI->cache->get($cache_id, $cache_group, FALSE))
			{
				$output = parent::get_cache($cache_id, $cache_group, $skip_checking);
				return $output;
			}
		}
		return FALSE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Saves output to the cache
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	public function save_cache($cache_id, $data, $cache_group = NULL, $ttl = NULL)
	{
		if ($this->use_cache() AND !is_fuelified())
		{
			$cache_options =  array('default_ttl' => $this->config('cache_ttl'));
			$this->CI->load->library('cache', $cache_options);

			if (empty($cache_group))
			{
				$cache_group = $this->CI->config->item('blog_cache_group');
			}

			if (empty($ttl))
			{
				$cache_group = $this->CI->config->item('cache_ttl');
			}

			// save to cache
			$this->CI->cache->save($cache_id, $data, $cache_group, $ttl);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Removes page from cache
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function remove_cache($cache_id = NULL)
	{
		if ($this->use_cache())
		{
			$this->CI->load->library('cache');

			$cache_group = $this->CI->config->item('blog_cache_group');

			// save to cache
			if (!empty($cache_id))
			{
				$this->CI->cache->remove($cache_id, $cache_group);
			}
			else
			{
				$this->CI->cache->remove_group($cache_group);
			}
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Returns the page title
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public function page_title($title = '', $sep = NULL, $order = 'right')
	{
		$title_arr = array();
		if (!isset($sep))
		{
			$sep = $this->config('page_title_separator');
		}
		if ($order == 'left') $title_arr[] = $this->config('title');
		if (is_array($title))
		{
			foreach($title as $val)
			{
				$title_arr[] = $val;
			}
		}
		else if (!empty($title))
		{
			array_push($title_arr, $title);
		}
		if ($order == 'right') $title_arr[] = $this->config('title');
		return implode($sep, $title_arr);
	}

	/**
	 * Returns TRUE/FALSE as to whether the passed parameters get through Akismet. Used during validation.
	 * 
	 * @access	public
	 * @param	string	A blog_comment_model object 
	 * @param	boolean	Determines whether to log errors or not  (optional)
	 * @return	boolean
	 */
 	public function is_spam($comment, $log = TRUE)
	{
		$is_spam = FALSE;
		if ($this->config('akismet_api_key'))
		{
			$is_spam = $this->process_akismet($comment, $log);
		}
		elseif ($this->config('stopforumspam'))
		{
			$is_spam = $this->process_stopforumspam($comment);
		}
		return $is_spam;
	}
	
	/**
	 * Returns TRUE/FALSE as to whether the passed parameters get through Akismet. Used during validation.
	 *
	 * Credit goes to stopforumspam plugin used for Vanilla (http://vanillaforums.org/addon/stopforumspam-plugin)
	 * 
	 * @access	public
	 * @param	string	A blog_comment_model object or the name of the person submitting the form. Will pull from post
	 * @param	string	The email address of the person submitting the comment (optional)
	 * @param	string	The IP address of the author submitting the comment (optional)
	 * @param	boolean	Determines whether to log errors or not (optional)
	 * @return	boolean
	 */
 	public function process_stopforumspam($name, $email = NULL, $ip = NULL, $log = TRUE)
	{
		if ($this->config('stopforumspam'))
		{
			$this->CI->load->module_library(BLOG_FOLDER, 'stopforumspam');

			if ($name instanceof Blog_comment_model)
			{
				$comment = $name;
				$to_check = array(
					'username'	=> $comment->author_name,
					'email'		=> $comment->author_email,
					'ip'		=> $comment->author_ip
				);
				$log = $email;
			}
			else
			{
				if (is_array($name))
				{
					extract($name);
				}
				if (empty($ip))
				{
					$ip = $_SERVER['REMOTE_ADDR'];
				}
				$to_check = array(
					'username'	=> $name,
					'email'		=> $email,
					'ip'		=> $ip
				);
			}

			// test
			// $to_check['username'] = 'JHannam';
			// $to_check['email'] = 'cooneyursula4916@yahoo.com';
			// $to_check['ip'] = '23.95.105.75';

			$is_spam = $this->CI->stopforumspam->check($to_check);
			if ($this->CI->stopforumspam->has_errors())
			{
				if ($log)
				{
					log_message('error', 'STOPFORUMSPAM :: '.$this->CI->stopforumspam->last_error());	
				}
			}
			else
			{
				return $is_spam;
			}
		}

		// if no stopforumspam config return FALSE and pass through
		return FALSE;
	}

	/**
	 * Returns TRUE/FALSE as to whether the passed parameters get through Akismet. Used during validation.
	 * 
	 * @access	public
	 * @param	string	A blog_comment_model object  or the name of the person submitting the form. Will pull from post
	 * @param	string	The email address of the person submitting the comment (optional)
	 * @param	string	The comment being submitted (optional)
	 * @param	boolean	Determines whether to log errors or not (optional)
	 * @return	boolean
	 */
 	public function process_akismet($name, $email = NULL, $msg = '', $log = TRUE)
	{
		if ($this->config('akismet_api_key'))
		{
			$this->CI->load->module_library(BLOG_FOLDER, 'akismet');

			if ($name instanceof Blog_comment_model)
			{
				$comment = $name;
				$akisment_comment = array(
					'author'	=> $comment->author_name,
					'email'		=> $comment->author_email,
					'body'		=> $comment->content
				);
				$log = $email;
			}
			else
			{
				if (is_array($name))
				{
					extract($name);
				}
				$akisment_comment = array(
					'author'	=> $name,
					'email'		=> $email,
					'body'		=> $content
				);
			}

			$config = array(
				'blog_url' => $this->url(),
				'api_key' => $this->config('akismet_api_key'),
				'comment' => $akisment_comment
			);

			$this->CI->akismet->init($config);

			if ( $this->CI->akismet->errors_exist())
			{
				if ($log)
				{
					if ( $this->CI->akismet->is_error('AKISMET_INVALID_KEY') )
					{
						log_message('error', 'AKISMET :: Theres a problem with the api key');
					}
					elseif ( $this->CI->akismet->is_error('AKISMET_RESPONSE_FAILED') )
					{
						log_message('error', 'AKISMET :: Looks like the server\'s not responding');
					}
					elseif ( $this->CI->akismet->is_error('AKISMET_SERVER_NOT_FOUND') )
					{
						log_message('error', 'AKISMET :: Wheres the server gone?');
					}
				}
			}
			else
			{
				return $this->akismet->is_spam();
			}
		}

		// if no AKISMET key then return FALSE and pass through
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns either an array of fields for the comment form or the HTML
	 *
	 * @access	public
	 * @param	object
	 * @param	object
	 * @param	mixed
	 * @return	mixed
	 */
	public function comment_form($post, $parent_comment = NULL, $values = array(), $form_params = array())
	{
		$this->CI->load->library('session');
		$this->CI->load->library('form');
		$blog_config = $this->fuel->blog->config();

		if (is_true_val($this->config('use_captchas')))
		{
			$captcha = $this->captcha();
			$vars['captcha'] = $captcha;
		}

		$antispam = md5(random_string('unique'));
		$this->CI->session->set_userdata('antispam', $antispam);

		$form = '';
		if ($post->allow_comments)
		{
			$this->CI->load->module_model(BLOG_FOLDER, 'blog_comments_model');

			if (empty($form_params))
			{
				$form_params = $blog_config['comment_form'];
			}

			$this->CI->load->library('form_builder', $form_params);
			
			$fields['author_name'] = array('label' => 'Name', 'required' => TRUE);
			$fields['author_email'] = array('label' => 'Email', 'required' => TRUE);
			$fields['author_website'] = array('label' => 'Website');
			$fields['new_comment'] = array('label' => 'Comment', 'type' => 'textarea', 'required' => TRUE);
			$fields['post_id'] = array('type' => 'hidden', 'value' => $post->id);
			$fields['antispam'] = array('type' => 'hidden', 'value' => $antispam);
			
			if (isset($parent_comment->id))
			{
				$fields['parent_id'] = array('type' => 'hidden', 'value' => $parent_comment->id); 	
			}

			if (!empty($vars['captcha']))
			{
				$fields['captcha'] = array('required' => TRUE, 'label' => lang('form_label_security_text'), 'value' => '', 'after_html' => ' <span class="captcha">'.$vars['captcha']['image'].'</span><br /><span class="captcha_text">'.lang('blog_captcha_text').'</span>');
			}
			
			// now merge with config... can't do array_merge_recursive'
			foreach($blog_config['comment_form']['fields'] as $key => $field)
			{
				if (isset($fields[$key])) $fields[$key] = array_merge($fields[$key], $field);
			}

			if (!isset($blog_config['comment_form']['label_layout'])) $this->CI->form_builder->label_layout = 'left';
			if (!isset($blog_config['comment_form']['submit_value'])) $this->CI->form_builder->submit_value = 'Submit Comment';
			if (!isset($blog_config['comment_form']['use_form_tag'])) $this->CI->form_builder->use_form_tag = TRUE;
			if (!isset($blog_config['comment_form']['display_errors'])) $this->CI->form_builder->display_errors = TRUE;
			$this->CI->form_builder->form_attrs = 'method="post" action="'.site_url($this->CI->uri->uri_string()).'#comments_form"';
			$this->CI->form_builder->set_fields($fields);
			$this->CI->form_builder->set_field_values($values);
			$this->CI->form_builder->set_validator($this->CI->blog_comments_model->get_validation());
		

			// setup comment fields as a variable to be used in a view file 
			$this->CI->load->vars(array('comment_fields' => $fields));

			if ($form_params !== FALSE)
			{
				$this->CI->form_builder->set_fields($fields);
				$this->CI->form_builder->set_field_values($values);
				$this->CI->form_builder->set_validator($this->CI->blog_comments_model->get_validation());
				$vars['form'] = $this->CI->form_builder->render();
				$form = $this->block('comment_form', $vars, TRUE);
				return $form;
			}
			else
			{
				return $fields;
			}

		}

		return $form;
	}

	public function captcha()
	{
		$this->CI->load->library('session');
		$this->CI->load->library('captcha');

		$assets_folders = $this->CI->config->item('assets_folders');
		$blog_folder = MODULES_PATH.BLOG_FOLDER.'/';
		$captcha_path = $blog_folder.'assets/captchas/';
		$word = strtoupper(random_string('alnum', 5));
		
		$captcha_options = array(
						'word'		 => $word,
						'img_path'	 => $captcha_path, // system path to the image
						'img_url'	 => captcha_path('', BLOG_FOLDER), // web path to the image
						'font_path'	 => $blog_folder.'fonts/',
					);
		$captcha_options = array_merge($captcha_options, $this->config('captcha'));
		if (!empty($_POST['captcha']) AND $this->CI->session->userdata('comment_captcha') == $this->CI->input->post('captcha'))
		{
			$captcha_options['word'] = $this->input->post('captcha');
		}
		$captcha = $this->CI->captcha->get_captcha_image($captcha_options);
		$captcha_md5 = $this->get_encryption($captcha['word']);
		$this->CI->session->set_userdata('comment_captcha', $captcha_md5);
		
		return $captcha;
	}
	
	public function get_encryption($word)
	{
		$captcha_md5 = md5(strtoupper($word).$this->CI->config->item('encryption_key'));
		return $captcha_md5;
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the table aliases
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	protected function _tables($table = NULL)
	{
		//$tables = $this->CI->config->item('tables');
		$tables = Base_module_model::$tables;
		if (isset($table))
		{
			if (!empty($tables[$table]))
			{
				return $tables[$table];
			}
		}
		return $tables;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Runs a specific blog hook
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	string
	 */
	public function run_hook($hook, $params = array())
	{
		// call module specific hook
		$hook_name = 'blog_'.$hook;
		$GLOBALS['EXT']->_call_hook($hook_name, $params);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Convenience magic method if you want to drop the "get" from the method
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	string
	 */
	public function __call($name, $args)
	{
		$method = 'get_'.$name;
		if (method_exists($this, $method))
		{
			return call_user_func_array(array($this, $method), $args);
		}
	}

}

/* End of file Fuel_blog.php */
/* Location: ./modules/blog/libraries/Fuel_blog.php */