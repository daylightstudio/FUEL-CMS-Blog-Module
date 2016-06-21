<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/'.(( version_compare(FUEL_VERSION, '1.4', '<') ) ? 'b' : 'B').'ase_module_model.php');

class Blog_categories_model extends Base_module_model {

	public $required = array('name');
	public $record_class = 'Blog_category';
	public $unique_fields = array(array('slug', 'language'), array('name', 'language'));
	public $linked_fields = array('slug' => array('name' => 'url_title'));

	// public $belongs_to = array(
	// 	'posts' => array('model' => array(BLOG_FOLDER => 'blog_posts_model'), 'where' => 'language = "{language}" OR language = ""')
	// );
	
	function __construct()
	{
		parent::__construct('blog_categories', BLOG_FOLDER); // table name
	}

	/* NO LONGER USED BECAUSE fuel_categeries_model IS replacing this functionality. LEFT HERE FOR POSTERITY

	// used for the FUEL admin
	function list_items($limit = NULL, $offset = NULL, $col = 'name', $order = 'asc', $just_count = FALSE)
	{
		$this->db->where(array('id !=' => 1)); // Uncategorized category

		if ($this->fuel->language->has_multiple())
		{
			$this->db->select('id, name, precedence, language, published');
		}
		else
		{
			$this->db->select('id, name, precedence, published');
		}
		
		$data = parent::list_items($limit, $offset, $col, $order, $just_count);
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

	function form_fields($values = array())
	{
		$fields = parent::form_fields($values);

		// set language field
		$fields['language'] = array('type' => 'select', 'options' => $this->fuel->language->options(), 'value' => $this->fuel->language->default_option(), 'hide_if_one' => TRUE);
		$fields['description']['editor'] = FALSE;
		return $fields;
	}*/
	
	function _common_query($display_unpublished_if_logged_in = NULL)
	{
		parent::_common_query($display_unpublished_if_logged_in);
		$this->db->order_by('precedence, name asc');
	}

	function get_published_categories($language = NULL)
	{
		$CI =& get_instance();
		$posts = $CI->fuel->blog->model('blog_posts')->find_all_array_assoc('category_id');
		$published_categories = array_keys($posts);

		//$published_categories = $this->get_related_keys(array(), $this->belongs_to['posts'], 'belongs_to');
		$categories_query_params = array();
		if (!empty($published_categories))
		{
			$categories_query_params = array('where_in' => array('id' => $published_categories), 'where' => 'published = "yes"');
			if (!empty($language))
			{
				$categories_query_params['where'] .= ' AND language="'.$language.'" OR language=""';
			}
			$categories_query = $this->query($categories_query_params);
			return $categories_query->result();
		}
		return array();
	}

	public function ajax_options($where = array())
	{
		if (!empty($where['language']))
		{
			$this->db->where($where);
			$this->db->or_where('language = ""');
			$options = $this->options_list();
		}
		else
		{
			$options = $this->options_list(NULL, NULL, $where);
		}
		$str = '';
		foreach($options as $key => $val)
		{
			$str .= "<option value=\"".$key."\" label=\"".$val."\">".$val."</option>\n";
		}
		return $str;
	}
}

class Blog_category_model extends Base_module_record {

	protected $_tables;

	function on_init()
	{
		$this->_tables = $this->_CI->config->item('tables');
	}

	function get_posts()
	{
		return $this->lazy_load(array($this->_tables['blog_posts'].'.category_id' => $this->id), 'blog_posts_model', TRUE);
	}

	function get_posts_count()
	{
		$this->_CI->load->module_model('blog', 'blog_posts_model');
		$where = array('published' => 'yes', 'category_id' => $this->id);
		$count = $this->_CI->blog_posts_model->record_count($where);
		return $count;
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
