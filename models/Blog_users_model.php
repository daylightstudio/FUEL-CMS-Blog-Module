<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/'.(( version_compare(FUEL_VERSION, '1.4', '<') ) ? 'b' : 'B').'ase_module_model.php');

class Blog_users_model extends Base_module_model {

	public $unique_fields = array('fuel_user_id');
	public $filter_fields = array('about');
	public $serialized_fields = array('social_media_links');

	function __construct()
	{
		parent::__construct('fuel_blog_users', BLOG_FOLDER); // table name
		$this->add_validation('email', 'valid_email', 'Please enter in a valid email');
	}

	// used for the FUEL admin
	function list_items($limit = NULL, $offset = NULL, $col = 'name', $order = 'asc', $just_count = FALSE)
	{
		$this->db->select('fuel_blog_users.id, CONCAT(first_name, " ", last_name) as name, display_name, fuel_blog_users.active', FALSE);
		//$this->db->join('fuel_users', 'fuel_users.id = fuel_blog_users.fuel_user_id', 'left');
		$data = parent::list_items($limit, $offset, $col, $order, $just_count);
		return $data;
	}

	function options_list($key = 'fuel_user_id', $val = 'name', $where = array(), $order = 'first_name')
	{
		if (empty($key) OR $key == 'id')
		{
			//$key = $this->table_name.'.fuel_user_id';
			$key = $this->table_name.'.id';
		}
		if (empty($key) OR empty($val) OR $val == 'name')
		{
			//$val = 'IF(display_name = "", fuel_users.email, display_name) AS name';
			$val = 'CONCAT(first_name, " ", last_name) as name';
			$order = 'first_name';
		}

		//$this->db->join('fuel_users', 'fuel_users.id = fuel_blog_users.fuel_user_id', 'left');
		$return = parent::options_list($key, $val, $where, $order);
		return $return;
	}
	
	function form_fields($values = array(), $related_fields = array())
	{
		$fields = parent::form_fields($values, $related_fields);
		$CI =& get_instance();
		$CI->load->module_model(FUEL_FOLDER, 'fuel_users_model');
		$CI->load->module_library(BLOG_FOLDER, 'fuel_blog');
		
		$fields['avatar_image']['folder'] = $CI->fuel->blog->config('asset_upload_path');
		//use only fuel users not already chosen
		$where = (!empty($values['fuel_user_id'])) ? array('fuel_user_id !=' => $values['fuel_user_id']) : array();
		$already_used = array_keys($this->options_list('fuel_user_id', 'display_name', $where));
		if (!empty($already_used))
		{
			$CI->fuel_users_model->db()->where_not_in('id', $already_used);	
		}
		

		$options = $CI->fuel_users_model->options_list();
		$upload_image_path = assets_server_path($CI->fuel->blog->settings('asset_upload_path'));
		$fields['fuel_user_id'] = array('label' => 'User', 'type' => 'select', 'options' => $options,  'module' => 'users', 'required' => TRUE);

		$socialfields = $this->fuel->blog->config('social_media');
		$fields['social_media_links'] = array('ignore_representative' => TRUE, 
			'type' => 'template', 
			'repeatable' => TRUE, 
			'fields' => array(
				'link' => array(),
				'type' => array('type' => 'select', 'options' => $socialfields),
		));
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
	
	function _common_query($display_unpublished_if_logged_in = NULL)
	{
		parent::_common_query($display_unpublished_if_logged_in);
		$this->db->select($this->_tables['blog_users'].'.*, CONCAT(first_name, " ", last_name) as name, '.$this->_tables['fuel_users'].'.first_name, '.$this->_tables['fuel_users'].'.last_name, '.$this->_tables['fuel_users'].'.email, '.$this->_tables['fuel_users'].'.user_name, '.$this->_tables['fuel_users'].'.active as users_active', FALSE);
		$this->db->select('posts_count'); // for temp table to get posts count
		$this->db->group_by($this->_tables['fuel_users'].'.id');
	}

	function _common_joins()
	{
		$this->db->join($this->_tables['fuel_users'], $this->_tables['fuel_users'].'.id = '.$this->_tables['blog_users'].'.fuel_user_id', 'left');
		//$this->db->join('fuel_blog_posts', $this->_tables['blog_posts'].'.author_id = '.$this->_tables['fuel_users'].'.id', 'left'); // left or inner????
		$this->db->join('(SELECT COUNT(*) AS posts_count, '.$this->_tables['blog_posts'].'.author_id FROM '.$this->_tables['blog_posts'].' GROUP BY '.$this->_tables['blog_posts'].'.author_id) AS temp', 'temp.author_id= fuel_users.id', 'left'); 
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
		$params['order_by'] ='publish_date desc';
		return $this->lazy_load(array($this->_parent_model->tables('blog_posts').'.author_id' => $this->fuel_user_id, $this->_parent_model->tables('blog_posts').'.published' => 'yes'), array(BLOG_FOLDER => 'blog_posts_model'), TRUE, $params);
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
		$path = $this->_CI->fuel->blog->config('asset_upload_path').$this->avatar_image;
		return assets_path($path);
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

	function get_about_excerpt()
	{
		$content = (empty($this->_fields['about_excerpt'])) ? $this->_fields['about'] : $this->_fields['about_excerpt'];
		$content = $this->_parse($content);
		return $content;
	}

	function get_about_excerpt_formatted()
	{
		$this->_CI->load->helper('typography');
		return auto_typography($this->get_about_excerpt());
	}
}