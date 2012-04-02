<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(FUEL_PATH.'models/base_module_model.php');

class Blog_categories_model extends Base_module_model {

	public $required = array('name');
	public $record_class = 'Blog_category';
	public $unique_fields = array('slug', 'name');
	public $linked_fields = array('slug' => array('name' => 'url_title'));

	public $belongs_to = array('posts' => array('module' => 'blog', 'model' => 'blog_posts'));
	
	function __construct()
	{
		parent::__construct('blog_categories', BLOG_FOLDER); // table name
	}

	// used for the FUEL admin
	function list_items($limit = NULL, $offset = NULL, $col = 'name', $order = 'asc')
	{
		$this->db->where(array('id !=' => 1)); // Uncategorized category
		$this->db->select('id, name, precedence, published');
		$data = parent::list_items($limit, $offset, $col, $order);
		return $data;
	}
	
	function on_before_clean($values)
	{
		if (empty($values['slug']) && !empty($values['name'])) $values['slug'] = url_title($values['name'], 'dash', TRUE);
		return $values;
	}
	
	// check if it is the "Uncategorized" category so we don't delete it'
	function on_before_delete($where)
	{
		$CI =& get_instance();
		
		$CI->load->module_model('blog', 'blog_posts_to_categories_model');
		$CI->load->module_language('blog', 'blog');
		if (is_array($where) && isset($where['id']))
		{
			if ($where['id'] == 1)
			{
				$this->add_error(lang('blog_error_delete_uncategorized'));
				$CI->session->set_flashdata('error', lang('blog_error_delete_uncategorized'));
				return;
			}
		}
	}
	
	
	// cleanup category to posts
	function on_after_delete($where)
	{
		$CI =& get_instance();
		$CI->load->module_model('blog', 'blog_posts_to_categories_model');
		if (is_array($where) && isset($where['id']))
		{
			$where = array('category_id' => $where['id']);
			$CI->blog_posts_to_categories_model->delete($where);
		}
	}

	function form_fields($values = array())
	{
		$fields = parent::form_fields($values);
		return $fields;
	}
	
	function _common_query()
	{
		parent::_common_query();
		$this->db->order_by('precedence, name asc');
	}

	function get_published_categories()
	{
		$published_categories = $this->get_related_keys(array(), $this->belongs_to['posts'], 'belongs_to', 'fuel_blog_categories');
		$categories_query_params = array('where_in' => array('id' => $published_categories));
		$categories_query = $this->query($categories_query_params);
		return $categories_query->result();
	}
}

class Blog_category_model extends Base_module_record {

	private $_tables;
	private $_category_posts;

	function on_init()
	{
		$this->_tables = $this->_CI->config->item('tables');
	}

	private function _get_category_posts()
	{
		if (empty($this->_category_posts)) {
			$this->_category_posts = $this->_parent_model->get_related_keys(array('id' => $this->id), $this->_parent_model->belongs_to['posts'], 'belongs_to', $this->_parent_model->table_name());
		}
		return $this->_category_posts;
	}

	function get_posts()
	{
		$this->_CI->load->module_model('blog', 'blog_posts_model');
		$category_posts_query_params = array('where_in' => array($this->_tables['blog_posts'].'.id' => $this->_get_category_posts()));
		$posts = $this->_CI->blog_posts_model->query($category_posts_query_params);
		return $posts->result();
	}

	function get_posts_count()
	{
		return sizeof($this->_get_category_posts());
	}

	function get_url($full_path = TRUE)
	{
		$url = 'categories/'.$this->slug;
		if ($full_path)
		{
			return $this->_CI->fuel_blog->url($url);
		}
		return $url;
	}

}
