<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/'.(( version_compare(FUEL_VERSION, '1.4', '<') ) ? 'b' : 'B').'ase_module_model.php');

class Blog_tags_model extends Base_module_model {

	public $required = array('name');
	public $record_class = 'Blog_tag';
	public $unique_fields = array(array('slug', 'language'), array('name', 'language'));
	public $linked_fields = array('slug' => array('name' => 'url_title'));

	public $belongs_to = array(
		'posts' => array('model' => array(BLOG_FOLDER => 'blog_posts_model'), 'where' => 'language = "{language}" OR language = ""')
	);
	
	function __construct()
	{
		parent::__construct('blog_tags', BLOG_FOLDER); // table name
	}

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

	function form_fields($values = array(), $related = array())
	{
		$fields = parent::form_fields($values, $related);

		// set language field
		$fields['language'] = array('type' => 'select', 'options' => $this->fuel->language->options(), 'value' => $this->fuel->language->default_option(), 'hide_if_one' => TRUE);
		$fields['description']['editor'] = FALSE;
		return $fields;
	}
	
	function _common_query($display_unpublished_if_logged_in = NULL)
	{
		parent::_common_query();
		$this->db->join($this->_tables['fuel_categories'], $this->_tables['fuel_categories'].'.id = '.$this->_tables['fuel_tags'].'.category_id', 'LEFT');
		$this->db->order_by('precedence, name asc');
	}

	function get_published_tags($language = NULL)
	{
		$CI =& get_instance();
		$published_tags = $CI->fuel->blog->model('blog_posts')->get_related_keys('tags', array(), $CI->fuel->blog->model('posts')->has_many['tags'], 'has_many');

		//$published_categories = $this->get_related_keys(array(), $this->belongs_to['posts'], 'belongs_to');
		$tags_query_params = array();
		if (!empty($published_tags))
		{
			$tags_query_params = array('where_in' => array($this->table_name.'.id' => $published_tags), 'where' => $this->table_name.'.published = "yes"');
			if (!empty($language))
			{
				$tags_query_params['where'] .= ' AND '.$this->table_name.'.language="'.$language.'" OR '.$this->table_name.'.language=""';
			}
			$tags_query = $this->query($tags_query_params);
			return $tags_query->result();
		}
		return array();
	}

	function options_list($key = NULL, $val = NULL, $where = array(), $order = TRUE)
	{
		$this->db->join($this->_tables['fuel_categories'], $this->_tables['fuel_categories'].'.id = '.$this->_tables['fuel_tags'].'.category_id', 'LEFT');

		// if (empty($key)) $key = $this->_tables['fuel_categories'].'.id';
		// if (empty($val)) $val = $this->_tables['fuel_categories'].'.name';
		if (empty($key)) $key = $this->_tables['fuel_tags'].'.id';
		if (empty($val)) $val = $this->_tables['fuel_tags'].'.name';

		// needed to prevent ambiguity
		if (strpos($key, '.') === FALSE AND strpos($key, '(') === FALSE)
		{
			$key = $this->_tables['fuel_tags'].'.'.$key;
		}

		// needed to prevent ambiguity
		if (strpos($val, '.') === FALSE AND strpos($val, '(') === FALSE)
		{
			$val = $this->_tables['fuel_tags'].'.'.$val;
		}
	
		$options = parent::options_list($key, $val, $where, $order);
		return $options;
	}
	
	function ajax_options($where = array())
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

class Blog_tag_model extends Base_module_record {

	protected $_tables;
	protected $_tag_posts;

	function on_init()
	{
		$this->_tables = $this->_CI->config->item('tables');
	}

	protected function _get_tag_posts()
	{
		if (empty($this->_tag_posts))
		{
			$this->_tag_posts = $this->_parent_model->get_related_keys('posts', array('id' => $this->id), $this->_parent_model->belongs_to['posts'], 'belongs_to', $this->_parent_model->table_name());
		}
		return $this->_tag_posts;
	}

	function get_posts()
	{
		$this->_CI->load->module_model(BLOG_FOLDER, 'blog_posts_model');
		$cat_posts = $this->_get_tag_posts();
		if (empty($cat_posts)) return array();
		
		$category_posts_query_params = array('where_in' => array($this->_tables['blog_posts'].'.id' => $cat_posts));
		$posts = $this->_CI->blog_posts_model->query($category_posts_query_params);
		return $posts->result();
	}

	function get_posts_count()
	{
		//return sizeof($this->_get_tag_posts());
		$blog_posts_model = $this->_get_relationship('posts', TRUE, 'belongs_to');
		if ($blog_posts_model)
		{
			$where = array('published' => 'yes');
			$count = $blog_posts_model->record_count($where);
			return $count;
		}
		return 0;
	}

	function get_url($full_path = TRUE)
	{
		$url = 'tags/'.$this->slug;
		if ($full_path)
		{
			return $this->_CI->fuel_blog->url($url);
		}
		return $url;
	}

}
