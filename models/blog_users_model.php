<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/base_module_model.php');

class Blog_users_model extends Base_module_model {

	public $unique_fields = array('fuel_user_id');
	public $filter_fields = array('about');

	function __construct()
	{
		parent::__construct('fuel_blog_users', BLOG_FOLDER); // table name
		$this->add_validation('email', 'valid_email', 'Please enter in a valid email');
	}

	// used for the FUEL admin
	function list_items($limit = NULL, $offset = NULL, $col = 'name', $order = 'asc', $just_count = FALSE)
	{
		$this->db->select('fuel_blog_users.id, CONCAT(first_name, " ", last_name) as name, display_name, fuel_blog_users.active', FALSE);
		$this->db->join('fuel_users', 'fuel_users.id = fuel_blog_users.fuel_user_id', 'left');
		$data = parent::list_items($limit, $offset, $col, $order, $just_count);
		return $data;
	}

	function options_list($key = 'fuel_user_id', $val = 'display_name', $where = array(), $order = 'display_name')
	{
		if (empty($key) OR $key == 'id')
		{
			$key = $this->table_name.'.fuel_user_id';
		}
		if (empty($key) OR $val == 'display_name')
		{
			$val = 'IF(display_name = "", fuel_users.email, display_name) AS name';
			$order = 'display_name';
		}

		$this->db->join('fuel_users', 'fuel_users.id = fuel_blog_users.fuel_user_id', 'left');
		$return = parent::options_list($key, $val, $where, $order);
		return $return;
	}
	
	function form_fields($values = array(), $related_fields = array())
	{
		$fields = parent::form_fields($values, $related_fields);
		$CI =& get_instance();
		$CI->load->module_model(FUEL_FOLDER, 'fuel_users_model');
		$CI->load->module_library(BLOG_FOLDER, 'fuel_blog');
		
		//use only fuel users not already chosen
		$where = (!empty($values['fuel_user_id'])) ? array('fuel_user_id !=' => $values['fuel_user_id']) : array();
		$already_used = array_keys($this->options_list('fuel_user_id', 'display_name', $where));
		if (!empty($already_used))
		{
			$CI->fuel_users_model->db()->where_not_in('id', $already_used);	
		}
		

		$options = $CI->fuel_users_model->options_list();
		$upload_image_path = assets_server_path($CI->fuel->blog->settings('asset_upload_path'));
		$fields['fuel_user_id'] = array('label' => 'User', 'type' => 'select', 'options' => $options,  'module' => 'users');
		return $fields;
	}

	function on_before_clean($values = array())
	{
		if (!(int) $values['date_added'])
		{
			$values['date_added'] = datetime_now();
		}
		return $values;
	}
	
	function _common_query()
	{
		$this->db->select('fuel_blog_users.*, CONCAT(first_name, " ", last_name) as name, fuel_users.first_name, fuel_users.last_name, fuel_users.email, fuel_users.user_name, fuel_users.active as users_active', FALSE);
		$this->db->select('posts_count'); // for temp table to get posts count
		$this->db->join('fuel_users', 'fuel_users.id = fuel_blog_users.fuel_user_id', 'left');
		$this->db->join('fuel_blog_posts', 'fuel_blog_posts.author_id = fuel_users.id', 'left'); // left or inner????
		$this->db->join('(SELECT COUNT(*) AS posts_count, fuel_blog_posts.author_id FROM fuel_blog_posts GROUP BY fuel_blog_posts.author_id) AS temp', 'temp.author_id= fuel_users.id', 'left'); 
		$this->db->group_by('fuel_users.id');
	}

}

class Blog_user_model extends Base_module_record {
	
	public $user_id;
	public $first_name;
	public $last_name;
	public $name;
	public $email;
	public $user_name;
	public $active;
	public $posts_count;
	
	protected $_parsed_fields = array('about');
	
	function get_url()
	{
		return $this->_CI->fuel_blog->url('authors/'.$this->fuel_user_id);
	}

	function get_website_link()
	{
		return '<a href="'.prep_url($this->website).'">'.$this->website.'</a>';
	}
	
	function get_posts()
	{
		$params['order_by'] ='post_date desc';
		return $this->lazy_load(array('fuel_blog_posts.author_id' => $this->fuel_user_id, 'fuel_blog_posts.published' => 'yes'), array(BLOG_FOLDER => 'blog_posts_model'), TRUE, $params);
	}

	function get_posts_url($full_path = TRUE)
	{
		$url = 'authors/posts/'.$this->fuel_user_id;
		if ($full_path)
		{
			return $this->_CI->fuel_blog->url($url);
		}
		return $url;
	}
	
	function get_avatar_image_path()
	{
		return img_path($this->avatar_image);
	}

	function get_avatar_img_tag($attrs = array())
	{
		$CI =& get_instance();
		$CI->load->helper('html');
		$src = $this->get_avatar_image_path();
		$attrs = html_attrs($attrs);
		if (!empty($this->avatar_image))
		{
			return '<img src="'.$src.'"'.$attrs.' />';
		}
		return '';
	}
	
}