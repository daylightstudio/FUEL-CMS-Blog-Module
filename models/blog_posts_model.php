<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/base_module_model.php');
require_once(MODULES_PATH.'/blog/config/blog_constants.php');

class Blog_posts_model extends Base_module_model {

	public $category = null;
	public $required = array('title', 'content');
	public $hidden_fields = array('content_filtered');
	public $filters = array('title', 'content_filtered', 'fuel_users.first_name', 'fuel_users.last_name');
	public $unique_fields = array('slug');
	public $linked_fields = array('slug' => array('title' => 'url_title'));
	public $display_unpublished_if_logged_in = TRUE; // determines whether to display unpublished content on the front end if you are logged in to the CMS

	public $has_many = array(
		'categories' => array(
			'model' => array(BLOG_FOLDER => 'blog_categories')
			),
		'related_posts' => array(
			'model' => array(FUEL_FOLDER => 'blog_posts')
			), 
		'blocks' => array(
			'model' => array(FUEL_FOLDER => 'fuel_blocks')
			)
		);

	function __construct()
	{
		parent::__construct('blog_posts', BLOG_FOLDER); // table name
	}
	
	// used for the FUEL admin
	function list_items($limit = NULL, $offset = NULL, $col = 'post_date', $order = 'desc', $just_count = FALSE)
	{
		// set the filter again here just in case the table names are different
		$this->filters = array('title', 'content_filtered', $this->_tables['fuel_users'].'.first_name', $this->_tables['fuel_users'].'.last_name');
		
		$this->db->select($this->_tables['blog_posts'].'.id, title, CONCAT('.$this->_tables['fuel_users'].'.first_name, " ", '.$this->_tables['fuel_users'].'.last_name) AS author, '.$this->_tables['blog_posts'].'.post_date, '.$this->_tables['blog_posts'].'.published', FALSE);
		$this->db->join($this->_tables['fuel_users'], $this->_tables['fuel_users'].'.id = '.$this->_tables['blog_posts'].'.author_id', 'left');
		$data = parent::list_items($limit, $offset, $col, $order, $just_count);
		return $data;
	}
	
	function tree($just_published = FALSE)
	{
		$CI =& get_instance();
		$CI->load->module_model(BLOG_FOLDER, 'blog_categories_model');
		$CI->load->module_model(FUEL_FOLDER, 'fuel_relationships_model');
		$CI->load->helper('array');

		$return = array();
		
		$where = ($just_published) ? $where = array('published' => 'yes') : array();
		$categories = $CI->blog_categories_model->find_all($where, 'precedence asc');
		$posts_to_categories = $CI->fuel_relationships_model->find_by_candidate($this->_tables['blog_posts'], $this->_tables['blog_categories']);
		if (empty($posts_to_categories)) return array();
		
		foreach($categories as $category)
		{
			$return[$category->id] = array('id' => $category->id, 'parent_id' => 0, 'label' => $category->name, 'location' => fuel_url('blog/categories/edit/'.$category->id), 'precedence' => $category->precedence);
		}
		
		foreach($posts_to_categories as $val)
		{
			$attributes = ($val->candidate_published == 'no') ? array('class' => 'unpublished', 'title' => 'unpublished') : NULL;
			$return['p_'.$val->candidate_id.'_c'.$val->foreign_id] = array('label' => $val->candidate_title, 'parent_id' => $val->foreign_id, 'location' => fuel_url('blog/posts/edit/'.$val->candidate_id), 'attributes' => $attributes, 'precedence' => 100000);
		}
		$return = array_sorter($return, 'precedence', 'asc');
		return $return;
	}
	
	function form_fields($values = array())
	{
		$fields = parent::form_fields($values);
		$CI =& get_instance();
		
		$CI->load->module_model(BLOG_FOLDER, 'blog_users_model');
		
		$blog_config = $CI->fuel->blog->config();
		
		$user_options = $CI->blog_users_model->options_list();
		$user = $this->fuel->auth->user_data();
		
		$user_value = (!empty($values['author_id'])) ? $values['author_id'] : $user['id'];
		$author_comment = $fields['author_id']['comment'];
		$fields['author_id'] = array('label' => 'Author', 'type' => 'select', 'options' => $user_options, 'first_option' => 'Select an author...', 'value' => $user_value, 'comment' => $author_comment);
		if (!isset($values['allow_comments']))
		{
			$fields['allow_comments']['value'] = ($CI->fuel->blog->config('allow_comments')) ? 'yes' : 'no';
		} 
		if (!empty($blog_config['formatting']) )
		{

			$blog_config['formatting'] = (array) $blog_config['formatting'];
			if (count($blog_config['formatting']) == 1)
			{
				$fields['formatting'] = array('type' => 'hidden', 'options' => current($blog_config['formatting']), 'default' => $fields['formatting']['default']);
				if (strtolower($blog_config['formatting'][0]) == 'markdown')
				{
					$fields['content']['markdown'] = TRUE;
					$fields['excerpt']['markdown'] = TRUE;
				}
			}
			else
			{
				$fields['formatting'] = array('type' => 'select', 'options' => $blog_config['formatting'], 'default' => $fields['formatting']['default']);
			}
		}
		
		$fields['content']['style'] = 'width: 680px; height: 400px';
		$fields['excerpt']['style'] = 'width: 680px;';
		$fields['published']['order'] = 10000;
		
		if (!is_true_val($CI->fuel->blog->config('allow_comments')))
		{
			unset($fields['allow_comments']);
		}
		
		unset($fields['content_filtered']);
		$fields['date_added']['type'] = 'hidden'; // so it will auto add
		//$fields['date_added']['type'] = 'datetime'; // so it will auto add
		$fields['last_modified']['type'] = 'hidden'; // so it will auto add
		$fields['slug']['order'] = 2.5; // for older versions where the schema order was different
		
		$fields['main_image']['folder'] = $CI->fuel->blog->config('asset_upload_path');
		$fields['main_image']['img_styles'] = 'float: left; width: 200px;';
		
		$fields['list_image']['folder'] = $CI->fuel->blog->config('asset_upload_path');
		$fields['list_image']['img_styles'] = 'float: left; width: 100px;';

		$fields['thumbnail_image']['folder'] = $CI->fuel->blog->config('asset_upload_path');
		$fields['thumbnail_image']['img_styles'] = 'float: left; width: 60px;';

		if (empty($fields['post_date']['value']))
		{
			$fields['post_date']['value'] = datetime_now();
		}

		$fields['blocks']['sorting'] = TRUE;


		if (!empty($values['id']))
		{
			unset($fields['related_posts']['options'][$values['id']]);
		}
		
		return $fields;
	}
	
	function on_before_clean($values)
	{
		$values['slug'] = (empty($values['slug']) && !empty($values['title'])) ? url_title($values['title'], 'dash', TRUE) : url_title($values['slug'], 'dash');
		
		if (empty($values['post_date']))
		{
			$values['post_date'] = datetime_now();
		}
		
		// create author if it doesn't exists'
		$CI =& get_instance();
		$id = (!empty($values['author_id'])) ? $values['author_id'] : $CI->fuel->auth->user_data('id');
		$CI->load->module_model(BLOG_FOLDER, 'blog_users_model');
		$author = $CI->blog_users_model->find_one(array('fuel_user_id' => $id));
		if (!isset($author->id))
		{
			$author = $CI->blog_users_model->create();
			$author->fuel_user_id = $CI->fuel->auth->user_data('id');

			// determine a display name if one isn't provided'
			if (trim($author->display_name) == '')
			{
				$display_name = $CI->fuel->auth->user_data('first_name').' '.$this->fuel->auth->user_data('last_name');
				if (trim($display_name) == '') $display_name = $CI->fuel->auth->user_data('email');
				if (empty($display_name)) $display_name = $CI->fuel->auth->user_data('user_name');
				$author->display_name = $display_name;
			}

			// save author
			$author->save();
			$values['author_id'] = $author->fuel_user_id;
		}

		return $values;
	}
	
	function on_before_save($values)
	{
		$values['title'] = strip_tags($values['title']);
		$values['content_filtered'] = strip_tags($values['content']);

		return $values;
	}
	
	function on_after_save($values)
	{

		// if no category is selected, then we set it to the Uncategorized
		$saved_data = $this->normalized_save_data;
		if (empty($saved_data['categories']))
		{
			$this->normalized_save_data['categories'] = array(1);
		}

		$values = parent::on_after_save($values);

		$CI =& get_instance();

		// remove cache
		$CI->fuel->blog->remove_cache();

		return $values;
	}

	function ajax_options()
	{
		$options = $this->options_list();
		$str = '';
		foreach($options as $key => $val)
		{
			$str .= "<option value=\"".$key."\" label=\"".$val."\">".$val."</option>\n";
		}
		return $str;
	}
	
	function _common_query()
	{
		parent::_common_query();
		
		$this->db->select($this->_tables['blog_posts'].'.*, '.$this->_tables['blog_users'].'.display_name, CONCAT('.$this->_tables['fuel_users'].'.first_name, " ", '.$this->_tables['fuel_users'].'.last_name) as author_name', FALSE);
		$this->db->select('YEAR('.$this->_tables['blog_posts'].'.post_date) as year, DATE_FORMAT('.$this->_tables['blog_posts'].'.post_date, "%m") as month, DATE_FORMAT('.$this->_tables['blog_posts'].'.post_date, "%d") as day,', FALSE);
		$rel_join = $this->_tables['blog_relationships'].'.candidate_key = '.$this->_tables['blog_posts'].'.id AND ';
		$rel_join .= $this->_tables['blog_relationships'].'.candidate_table = "'.$this->_tables['blog_posts'].'" AND ';
		$rel_join .= $this->_tables['blog_relationships'].'.foreign_table = "'.$this->_tables['blog_categories'].'"';
		$this->db->join($this->_tables['blog_relationships'], $rel_join, 'left');
		$this->db->join($this->_tables['blog_users'], $this->_tables['blog_users'].'.fuel_user_id = '.$this->_tables['blog_posts'].'.author_id', 'left');
		$this->db->join($this->_tables['fuel_users'], $this->_tables['fuel_users'].'.id = '.$this->_tables['blog_posts'].'.author_id', 'left');
		$this->db->join($this->_tables['blog_categories'], $this->_tables['blog_categories'].'.id = '.$this->_tables['blog_relationships'].'.foreign_key', 'left');
		$this->db->group_by($this->_tables['blog_posts'].'.id');
	}

}

class Blog_post_model extends Base_module_record {

	private $_tables;
	public $author_name;
	
	function on_init()
	{
		$this->_tables = $this->_CI->config->item('tables');
	}
	
	function get_content_formatted($strip_images = FALSE)
	{
		$this->_CI->load->module_helper(FUEL_FOLDER, 'fuel');
		$content = $this->content;
		if ($strip_images)
		{
			$CI->load->helper('security');
			$content = strip_image_tags($this->content);
		}
		$content = $this->_format($content);
		$content = $this->_parse($content);
		return $content;
	}

	function get_excerpt_formatted($char_limit = NULL, $readmore = '')
	{
		$this->_CI->load->helper('text');
		$excerpt = (empty($this->excerpt)) ? $this->content : $this->excerpt;

		if (!empty($char_limit))
		{
			// must strip tags to get accruate character count
			$excerpt = strip_tags($excerpt);
			$excerpt = character_limiter($excerpt, $char_limit);
		}
		if (!empty($readmore))
		{
			$excerpt .= ' '.anchor($this->url, $readmore, 'class="readmore"');
		}
		$excerpt = $this->_format($excerpt);
		$excerpt = $this->_parse($excerpt);
		return $excerpt;
	}
	
	function is_future_post()
	{
		return strtotime($this->post_date) > time();
	}
	
	function is_published()
	{
		return ($this->published === 'yes');
	}
	
	function get_comments($order = 'date_added asc', $limit = NULL)
	{
		$this->_CI->load->module_model('blog', 'blog_comments_model');
		$where = array('post_id' => $this->id, $this->_tables['blog_comments'].'.published' => 'yes');
		$order = $this->_tables['blog_comments'].'.'.$order;
		$comments = $this->_CI->blog_comments_model->find_all($where, $order, $limit);
		return $comments;
	}
	
	function get_comments_count($order = 'date_added asc', $limit = NULL)
	{
		$this->_CI->load->module_model('blog', 'blog_comments_model');
		$where = array('post_id' => $this->id, $this->_tables['blog_comments'].'.published' => 'yes');
		$cnt = $this->_CI->blog_comments_model->record_count($where, $order, $limit);
		return $cnt;
	}
	
	function get_comments_formatted($block = 'comment', $parent_id = 0, $container_class = 'child')
	{
		static $comments;
		static $post;
		
		// initialization... grab all comments
		$items = array();
		if (empty($comments))
		{
			$comments = $this->comments;
			$post = $this->post;
		}

		$str = '';
		
		// get child comments
		foreach($comments as $key => $comment)
		{
			
			if ((int)$comment->parent_id === (int)$parent_id)
			{
				$items[] = $comment;
				unset($comments[$key]);
			}
		}

		if (!empty($items))
		{

			// now loop through roots and get any children
			foreach($items as $item)
			{
				$str .= $this->_CI->fuel->blog->block($block, array('comment' => $item, 'post' => $this));
				$children = $this->get_comments_formatted($block, $item->id);
				if (!empty($children))
				{
					$str .= "<div class=\"".$container_class."\">\n\t";
					$str .= $children;
					$str .= "</div>\n";
				}
			}
		}
		return $str;
	}
	
	function belongs_to_category($category)
	{
		$categories = $this->categories;

		if (in_array($category, $categories))
		{
			return TRUE;
		}
		return FALSE;
	}
	
	function get_categories_linked($order = 'name asc', $join = ', ')
	{
		$categories = $this->categories;
		if ( ! empty($categories))
		{
			$categories_linked = array();
			foreach ($this->categories as $category)
			{
				$categories_linked[] = anchor($this->_CI->fuel->blog->url($category->get_url(FALSE)), $category->name);
			}
			$return = implode($categories_linked, $join);
			return $return;
		}
		return NULL;
	}
	
	function get_author()
	{
		$this->_CI->load->module_model(BLOG_FOLDER, 'blog_users_model');
		$author = $this->_CI->blog_users_model->find_one(array('fuel_blog_users.fuel_user_id' => $this->author_id));
		return $author;
	}

	function get_image($type = 'main')
	{
		$img = $type.'_image';
		return $this->$img;
	}

	function get_image_path($type = 'main')
	{
		$CI =& get_instance();
		$img = $this->get_image($type);
		$path = $CI->fuel->blog->config('asset_upload_path').$img;
		return assets_path($path);
	}
	
	function get_main_image_path()
	{
		return $this->get_image_path('main');
	}
	
	function get_list_image_path()
	{
		return $this->get_image_path('list');
	}
	
	function get_thumbnail_image_path()
	{
		return $this->get_image_path('thumbnail');
	}
	
	function get_url($full_path = TRUE)
	{
		$year = date('Y', strtotime($this->post_date));
		$month = date('m', strtotime($this->post_date));
		$day = date('d', strtotime($this->post_date));
		$url = $year.'/'.$month.'/'.$day.'/'.$this->slug;
		if ($full_path)
		{
			return $this->_CI->fuel->blog->url($url);
		}
		$base_uri = trim($this->_CI->fuel->blog->config('uri'), '/');
		return $base_uri.'/'.$url;
	}
	
	function get_rss_date()
	{
		return standard_date('DATE_RSS', strtotime($this->post_date));
	}
	
	function get_atom_date()
	{
		return standard_date('DATE_ATOM', strtotime($this->post_date));
	}

	function get_date_formatted($format = 'M d, Y')
	{
		return date($format, strtotime($this->post_date));
	}
	
	function get_allow_comments()
	{
		if (is_null($this->_fields['allow_comments']))
		{
			return is_true_val($this->_CI->fuel->blog->config('allow_comments'));
		}
		else
		{
			return is_true_val($this->_fields['allow_comments']);
		}
	}	
	function is_within_comment_time_limit()
	{
		$time_limit = (int) $this->_CI->fuel->blog->config('comments_time_limit') * (24 * 60 * 60);
		if (!empty($time_limit))
		{
			$post_date = strtotime($this->post_date);
			return (time() - $post_date < $time_limit);
		}
		return TRUE;
	}
	
	function get_social_bookmarking_links()
	{
		return social_bookmarking_links($this->url, $this->title);
	}
	
	function get_facebook_recommend()
	{
		return social_facebook_recommend($this->url);
	}

	function get_digg($size = 'Icon')
	{
		return social_digg($this->url, $this->title, $size);
	}

	function get_tweetme()
	{
		return social_tweetme($this->url);
	}
	
	private function _format($content)
	{
		$this->_CI->load->helper('typography');
		$this->_CI->load->helper('markdown');
		if (!empty($this->formatting) && !function_exists($this->formatting))
		{
			$this->_CI->load->helper(strtolower($this->formatting));
		}
		if (function_exists($this->formatting))
		{
			$content = call_user_func($this->formatting, $content);
		}
		return $content;
	}
}
?>