<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/'.(( version_compare(FUEL_VERSION, '1.4', '<') ) ? 'b' : 'B').'ase_module_model.php');

class Blog_links_model extends Base_module_model {

	public $required = array('url');
	
	function __construct()
	{
		parent::__construct('fuel_blog_links', BLOG_FOLDER); // table name
	}

	// used for the FUEL admin
	function list_items($limit = NULL, $offset = NULL, $col = 'name', $order = 'asc', $just_count = FALSE)
	{
		// set language field
		if ($this->fuel->language->has_multiple())
		{
			$this->db->select('id, name, url, language, published');
		}
		else
		{
			$this->db->select('id, name, url, published');
		}
		$data = parent::list_items($limit, $offset, $col, $order, $just_count);
		return $data;
	}

	function form_fields($values = array(), $related = array())
	{
		$fields = parent::form_fields($values, $related);
	
		// set language field
		$fields['language'] = array('type' => 'select', 'options' => $this->fuel->language->options(), 'value' => $this->fuel->language->default_option(), 'hide_if_one' => TRUE);

		$fields['url']['label'] = 'URL';
		return $fields;
	}
	
	function _common_query($display_unpublished_if_logged_in = NULL)
	{
		parent::_common_query($display_unpublished_if_logged_in);

		if (!defined('FUEL_ADMIN') AND $this->fuel->language->has_multiple())
		{
			$language = $this->fuel->language->detect();
			$this->db->where($this->_tables['blog_links'].'.language', $language);
		}
	}

}

class Blog_link_model extends Base_module_record {
	
	function get_link()
	{
		$url = $this->url;
		if (preg_match('#^www\..+#', $url) OR (preg_match('#(\.com|\.net)/?$#', $url) AND !is_http_path($url))) 
		{
			$url = prep_url($url);	
		}
		else
		{
			$url = site_url($url);	
		}
		$label = (!empty($this->name)) ? $this->name : $this->url;
		$attrs = (!empty($this->target)) ? 'target="_'.$this->target.'"' : '';
		return anchor($url, $label, $attrs);
	}
}
?>