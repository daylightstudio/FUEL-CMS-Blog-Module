<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once(FUEL_PATH.'models/'.(( version_compare(FUEL_VERSION, '1.4', '<') ) ? 'b' : 'B').'ase_module_model.php');
require_once(MODULES_PATH.'/blog/config/blog_constants.php');

class Blog_posts_model extends Base_module_model {

	public $category = null;
	public $required = array('title', 'content');
	public $hidden_fields = array('content_filtered');
	public $filter_join = 'and';
	public $filters = array('title', 'content_filtered', 'fuel_users.first_name', 'fuel_users.last_name');
	public $unique_fields = array('slug');
	public $linked_fields = array('slug' => array('title' => 'url_title'));
	public $display_unpublished_if_logged_in = TRUE; // determines whether to display unpublished content on the front end if you are logged in to the CMS
	public $boolean_fields = array('sticky');
	public $foreign_keys = array('category_id' => array(FUEL_FOLDER => 'fuel_categories_model', 'where' => '(context = "blog" OR context = "") AND language = "{language}" OR language = ""'));

	public $has_many = array(
		'tags' => array(
			'model' => array(BLOG_FOLDER => 'blog_tags_model'),
			//'where' => 'fuel_categories.context = "blog"', // added in constructor so that the the table name isn't hard coded in query
			),
		'related_posts' => array(
			'model' => array(BLOG_FOLDER => 'blog_posts_model')
			),
		'blocks' => array(
			'model' => array(FUEL_FOLDER => 'fuel_blocks_model')
			)
		);

	function __construct()
	{
		parent::__construct('blog_posts', BLOG_FOLDER); // table name
		$CI =& get_instance();

		if ($CI->fuel->blog->config('multiple_authors'))
		{
			$authors = array('authors' => array('model' => array(BLOG_FOLDER => 'blog_users_model')));
			$this->has_many = array_merge($authors, $this->has_many);
		}
		$this->has_many['tags']['where'] = '(FIND_IN_SET("blog", '.$this->_tables['fuel_tags'].'.context) OR '.$this->_tables['fuel_tags'].'.context="")';
		$this->foreign_keys['category_id']['where'] = '(FIND_IN_SET("blog", '.$this->_tables['fuel_categories'].'.context) OR '.$this->_tables['fuel_categories'].'.context="")';

		// set the filter again here just in case the table names are different
		$this->filters = array('title', 'content_filtered', $this->_tables['fuel_users'].'.first_name', $this->_tables['fuel_users'].'.last_name');

		if ($this->fuel->blog->config('limit_to_user'))
		{
			$this->limit_to_user_field = 'author_id';
		}

	}

	// used for the FUEL admin
	function list_items($limit = NULL, $offset = NULL, $col = 'publish_date', $order = 'desc', $just_count = FALSE)
	{
		$CI =& get_instance();
		if ($CI->fuel->blog->config('multiple_authors'))
		{
			$select = $this->_tables['blog_posts'].'.id, title, '.$this->_tables['blog_posts'].'.publish_date';
		}
		else
		{
			$select = $this->_tables['blog_posts'].'.id, '.$this->_tables['blog_posts'].'.title, IF('.$this->_tables['fuel_users'].'.first_name IS NULL, display_name, CONCAT('.$this->_tables['fuel_users'].'.first_name, " ", '.$this->_tables['fuel_users'].'.last_name)) AS author, '.$this->_tables['blog_posts'].'.publish_date';
		}

		if ($CI->fuel->language->has_multiple()) $select .= ', '.$this->_tables['blog_posts'].'.language';
		$select .= ', sticky, '.$this->_tables['blog_posts'].'.published';
		$this->db->select($select, FALSE);

		$this->db->join($this->_tables['blog_users'], $this->_tables['blog_users'].'.id = '.$this->_tables['blog_posts'].'.author_id', 'left');
		$this->db->join($this->_tables['fuel_users'], $this->_tables['blog_posts'].'.author_id = '.$this->_tables['fuel_users'].'.id', 'left');

		$data = parent::list_items($limit, $offset, $col, $order, $just_count);
		return $data;
	}

	function tree($just_published = FALSE)
	{
		return $this->_tree('foreign_keys');
	}

	function form_fields($values = array(), $related = array())
	{
		$fields = parent::form_fields($values);
		$CI =& get_instance();

		$blog_users = $CI->fuel->blog->model('blog_users');
		$blog_config = $CI->fuel->blog->config();

		$user_options = $blog_users->options_list();
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
		$fields['title']['style'] = 'width: 500px;';
		$fields['slug']['style'] = 'width: 500px;';
		$fields['language'] = array('type' => 'select', 'options' => $this->fuel->language->options(), 'value' => $this->fuel->language->default_option(), 'hide_if_one' => TRUE);
		$fields['content']['style'] = 'width: 680px; height: 400px';
		$fields['excerpt']['style'] = 'width: 680px;';

		if (!is_true_val($CI->fuel->blog->config('allow_comments')))
		{
			unset($fields['allow_comments']);
		}

		unset($fields['content_filtered']);
		$fields['date_added']['type'] = 'hidden'; // so it will auto add
		//$fields['date_added']['type'] = 'datetime'; // so it will auto add
		$fields['last_modified']['type'] = 'hidden'; // so it will auto add

		$image_sizes = $CI->fuel->blog->config('image_sizes');
		$image_types = array('main', 'list', 'thumbnail');
		$images = array();

		foreach($image_types as $type)
		{
			if ($image_sizes[$type] == FALSE)
			{
				unset($fields[$type.'_image']);
				continue;
			}
			if (!empty($image_sizes[$type]))
			{
				$fields[$type.'_image'] = array_merge($fields[$type.'_image'], $image_sizes[$type]);
			}
			$fields[$type.'_image']['folder'] = $CI->fuel->blog->config('asset_upload_path');

			if (!empty($image_sizes[$type]['width']) OR !empty($image_sizes[$type]['height']))
			{
				$image_comment = 'Recommended dimensions are ';
				if (!empty($image_sizes[$type]['width'])) $image_comment .= $image_sizes[$type]['width'].'w';
				if (!empty($image_sizes[$type]['height'])) $image_comment .= ' x ';
				if (!empty($image_sizes[$type]['height'])) $image_comment .= $image_sizes[$type]['height'].'h';
				$fields[$type.'_image']['comment'] = $image_comment;
			}
			$images[] = $type.'_image';
			$fields[$type.'_image']['multiple'] = FALSE;
		}

		if(array_key_exists('main_image', $fields)) $fields['main_image']['img_styles'] = 'float: left; width: 200px;';
		if(array_key_exists('list_image', $fields)) $fields['list_image']['img_styles'] = 'float: left; width: 100px;';
		if(array_key_exists('thumbnail_image', $fields)) $fields['thumbnail_image']['img_styles'] = 'float: left; width: 60px;';

		if (empty($fields['publish_date']['value']))
		{
			$fields['publish_date']['value'] = datetime_now();
		}

		$fields['blocks']['sorting'] = TRUE;


		if (!empty($values['id']))
		{
			unset($fields['related_posts']['options'][$values['id']]);
		}

		if ($CI->fuel->blog->config('multiple_authors'))
		{
			unset($fields['author_id']);
		}

		// explicitly set labels for related fields to use the lang values
		$fields['category_id']['label'] = lang('form_label_category');
		$fields['tags']['label'] = lang('form_label_tags');
		$fields['related_posts']['label'] = lang('form_label_related_posts');
		$fields['blocks']['label'] = lang('form_label_blocks');

		$fields['category_id']['comment'] = lang('form_category_comment');
		$fields['tags']['comment'] = lang('form_tags_comment');
		if ($CI->fuel->language->has_multiple())
		{
			$fields['tags']['type'] = 'dependent';
			$fields['tags']['depends_on'] = 'language';
			$fields['tags']['url'] = fuel_url('tags/ajax/options');
			$fields['tags']['multiple'] = TRUE;

			$fields['related_posts']['type'] = 'dependent';
			$fields['related_posts']['depends_on'] = 'language';
			$fields['related_posts']['url'] = fuel_url('blog/posts/ajax/options');
			if (!empty($values['id']))
			{
				$fields['related_posts']['additional_ajax_data'] = array('exclude' => $values['id']);
			}


			$fields['related_posts']['multiple'] = TRUE;

			$fields['blocks']['type'] = 'dependent';
			$fields['blocks']['depends_on'] = 'language';
			$fields['blocks']['url'] = fuel_url('blocks/ajax/options');
			$fields['blocks']['multiple'] = TRUE;
		}

		$fields['page_title'] = array('size' => 100, 'comment' => 'If no page title is provided, it will default to the title of the blog post');
		$fields['meta_description'] = array('type' => 'textarea', 'class' => 'no_editor', 'rows' => 3);
		$fields['meta_keywords'] = array('type' => 'textarea', 'class' => 'no_editor', 'rows' => 3);
		$fields['canonical'] = array('size' => 100, 'comment' => 'This field is used to help prevent duplicate content issues for search engines');

		$fields['Open Graph'] = array('type' => 'section');
		$fields['og_title'] = array('size' => 100);
		$fields['og_description'] = array('size' => 100);
		$fields['og_image'] = array('img_styles' => 'float: left; width: 100px;', 'folder' => $CI->fuel->blog->config('asset_upload_path'));

		//$fields['category_id']['add_params'] = 'context=blog';

		// find the first category with a context of "blog"
		$blog_category = current($CI->fuel->categories->find_by_context('blog'));
		if (isset($blog_category->id))
		{
			$fields['tags']['add_params'] = 'category_id='.$blog_category->id;
		}
		//$fields['tags']['add_params'] = 'context=blog';
		$fields['tags']['module'] = 'tags'; // must be set here since blog_tags is used as the module

		// setup tabs
		$fields['Content'] = array('type' => 'fieldset', 'class' => 'tab');
		$fields['Images'] = array('type' => 'fieldset', 'class' => 'tab');
		$fields['Settings'] = array('type' => 'fieldset', 'class' => 'tab');
		$fields['Meta'] = array('type' => 'fieldset', 'class' => 'tab');
		$fields['Associations'] = array('type' => 'fieldset', 'class' => 'tab');

		// now set the order
		$order = array(	'Content',
						'title',
						'slug',
						'language',
						'content',
						'formatting',
						'excerpt',
						'author_id',
						'authors',
						'published',
						'Images',
						'Settings',
						'allow_comments',
						'sticky',
						'publish_date',
						'Meta',
						'page_title',
						'meta_description',
						'meta_keywords',
						'canonical',
						'Open Graph',
						'og_title',
						'og_description',
						'og_image',
						'Associations',
						'category_id',
						'tags',
						'related_posts',
						'blocks',
						);
		//insert thumbnails into order array
		$i = 11;
		foreach($images as $image)
		{
			array_splice($order, $i, 0, $image);
			++$i;
		}
		foreach($order as $key => $val)
		{
			if (isset($fields[$val]))
			{
				$fields[$val]['order'] = $key + 1;	
			}
		}
		return $fields;
	}

	function on_before_clean($values)
	{
		$values['slug'] = (empty($values['slug']) && !empty($values['title'])) ? url_title($values['title'], 'dash', TRUE) : url_title($values['slug'], 'dash');

		if (empty($values['publish_date']))
		{
			$values['publish_date'] = datetime_now();
		}

		// create author if it doesn't exists'
		$CI =& get_instance();
		$id = (!empty($values['author_id'])) ? $values['author_id'] : $CI->fuel->auth->user_data('id');
		$blog_users = $CI->fuel->blog->model('blog_users');
		$author = $blog_users->find_one(array('fuel_user_id' => $id));
		if (!isset($author->id))
		{
			$author = $blog_users->create();
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
		parent::on_after_save($values);

		// remove cache
		$CI =& get_instance();
		$CI->fuel->blog->remove_cache();

		return $values;
	}

	function _common_query($display_unpublished_if_logged_in = NULL)
	{
		parent::_common_query();

		$this->db->select($this->_tables['blog_posts'].'.*, '.$this->_tables['blog_users'].'.display_name, CONCAT('.$this->_tables['fuel_users'].'.first_name, " ", '.$this->_tables['fuel_users'].'.last_name) as author_name', FALSE);
		$this->db->select('YEAR('.$this->_tables['blog_posts'].'.publish_date) as year, DATE_FORMAT('.$this->_tables['blog_posts'].'.publish_date, "%m") as month, DATE_FORMAT('.$this->_tables['blog_posts'].'.publish_date, "%d") as day,', FALSE);
		$rel_join = $this->_tables['blog_relationships'].'.candidate_key = '.$this->_tables['blog_posts'].'.id AND ';
		$rel_join .= $this->_tables['blog_relationships'].'.candidate_table = "'.$this->_tables['blog_posts'].'" AND ';
		$rel_join .= $this->_tables['blog_relationships'].'.foreign_table = "'.$this->_tables['blog_tags'].'"';
		$this->db->join($this->_tables['blog_categories'], $this->_tables['blog_categories'].'.id = '.$this->_tables['blog_posts'].'.category_id', 'left');
		$this->db->join($this->_tables['blog_relationships'], $rel_join, 'left');
		$this->db->join($this->_tables['blog_users'], $this->_tables['blog_users'].'.fuel_user_id = '.$this->_tables['blog_posts'].'.author_id', 'left');
		$this->db->join($this->_tables['fuel_users'], $this->_tables['fuel_users'].'.id = '.$this->_tables['blog_posts'].'.author_id', 'left');
		$this->db->join($this->_tables['blog_tags'], $this->_tables['blog_tags'].'.id = '.$this->_tables['blog_relationships'].'.foreign_key', 'left');
		$this->db->group_by($this->_tables['blog_posts'].'.id');

		if (!defined('FUEL_ADMIN') AND $this->fuel->language->has_multiple())
		{
			$language = $this->fuel->blog->language();
			$this->db->where($this->_tables['blog_posts'].'.language', $language);
		}
	}

	function preview_path($values, $path)
	{
		extract($values);
		$CI =& get_instance();
		$base_uri = $this->fuel->blog->uri();
		if ($CI->fuel->language->has_multiple() AND $values['language'] != 'english')
		{
			return "{$language}/".$base_uri."{$year}/{$month}/{$day}/{$slug}";
		}
		else
		{
			return $base_uri."{$year}/{$month}/{$day}/{$slug}";
		}
	}

}

class Blog_post_model extends Base_module_record {

	private $_tables;
	public $author_name;

	function get_page_title()
	{
		if (empty($this->_fields['page_title']))
		{
			return $this->title;
		}
		return $this->_fields['page_title'];
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

	function get_excerpt($char_limit = NULL, $end_char = '&#8230;')
	{
		$this->_CI->load->helper('text');
		$excerpt = (empty($this->_fields['excerpt'])) ? $this->content : $this->_fields['excerpt'];

		if (!empty($char_limit))
		{
			// must strip tags to get accruate character count
			$excerpt = strip_tags($excerpt);
			$excerpt = character_limiter($excerpt, $char_limit, $end_char);
		}
		return $excerpt;
	}

	function get_excerpt_formatted($char_limit = NULL, $readmore = '')
	{
		$excerpt = $this->get_excerpt($char_limit);
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
		return strtotime($this->publish_date) > time();
	}

	function is_published()
	{
		return ($this->published === 'yes');
	}

	function get_comments($order = 'date_added asc', $limit = NULL)
	{
		$blog_comments = $this->_CI->fuel->blog->model('blog_comments');
		$where = array('post_id' => $this->id, $this->_parent_model->tables('blog_comments').'.published' => 'yes');
		$order = $this->_parent_model->tables('blog_comments').'.'.$order;
		$comments = $blog_comments->find_all($where, $order, $limit);
		return $comments;
	}

	function get_comments_count($order = 'date_added asc', $limit = NULL)
	{
		$blog_comments = $this->_CI->fuel->blog->model('blog_comments');
		$where = array('post_id' => $this->id, $this->_parent_model->tables('blog_comments').'.published' => 'yes');
		$cnt = $blog_comments->record_count($where, $order, $limit);
		return $cnt;
	}

	function get_comments_formatted($block = 'comment', $parent_id = 0, $container_class = 'child')
	{

		// initialization... grab all comments
		$items = array();
		$comments = $this->get_comments();

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

	function get_category()
	{
		$model = $this->_CI->fuel->blog->model('categories');
		return $model->find_by_key($this->category_id);
	}

	function get_tags_linked($order = 'name asc', $join = ', ', $link_params = array())
	{
		$tags = $this->tags;
		if ( ! empty($tags))
		{
			$tags_linked = array();
			foreach ($tags as $tag)
			{
				$tags_linked[] = anchor($this->_CI->fuel->blog->url($tag->get_url(FALSE)), $tag->name, $link_params);
			}
			$return = implode($tags_linked, $join);
			return $return;
		}
		return NULL;
	}

	function get_category_link($link_params = array())
	{
		if ($this->has_category_id())
		{
			$category = $this->category;
			if (isset($category->id))
			{
				return anchor($this->_CI->fuel->blog->url($category->get_url(FALSE)), $category->name, $link_params);
			}
		}

		return NULL;

	}

	function get_author($all = FALSE)
	{
		$cache_key ='author'.$all;
		if (!isset($this->_objs[$cache_key]))
		{
			if ($this->_CI->fuel->blog->config('multiple_authors'))
			{
				$authors_model = $this->get_authors(TRUE);
				$where = array();
				if (!$all)
				{
					$where[$this->_parent_model->tables('blog_users').'.active'] = 'yes';
				}
				$this->_objs[$cache_key] = $this->find_one($where);
			}
			else
			{
				$where = array($this->_parent_model->tables('blog_users').'.fuel_user_id' => $this->author_id);
				if (!$all)
				{
					$where[$this->_parent_model->tables('blog_users').'.active'] = 'yes';
				}
				$this->_objs[$cache_key] = $this->lazy_load($where, array(BLOG_FOLDER => 'blog_users_model'), FALSE);
			}
		}
		return $this->_objs[$cache_key];
	}


	function has_author($active = FALSE)
	{
		$author = $this->get_author($active);
		return !empty($author);
	}

	function get_author_link()
	{
		$author = $this->get_author(TRUE);
		if(is_true_val($author->active))
		{
			return '<a href="'.$author->url.'">'.$author->display_name.'</a>';
		}else{
			return $author->display_name;
		}
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
		$path = trim($CI->fuel->blog->config('asset_upload_path'),'/').'/'.$img;

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
		$year = date('Y', strtotime($this->publish_date));
		$month = date('m', strtotime($this->publish_date));
		$day = date('d', strtotime($this->publish_date));
		$url = $year.'/'.$month.'/'.$day.'/'.$this->slug;
		if ($full_path)
		{
			return $this->_CI->fuel->blog->url($url);
		}
		$base_uri = trim($this->_CI->fuel->blog->config('uri'), '/');
		return $base_uri.'/'.$url;
	}

	function get_link_title($attrs = array())
	{
		return anchor($this->url, $this->title, $attrs);
	}

	function get_rss_date()
	{
		return standard_date('DATE_RSS', strtotime($this->publish_date));
	}

	function get_atom_date()
	{
		return standard_date('DATE_ATOM', strtotime($this->publish_date));
	}

	function get_date_formatted($format = 'M d, Y')
	{
		return date($format, strtotime($this->publish_date));
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
			$publish_date = strtotime($this->publish_date);
			return (time() - $publish_date < $time_limit);
		}
		return TRUE;
	}

	function get_prev_post()
	{
		return $this->_CI->fuel->blog->prev_post($this);
	}

	function get_prev_post_url()
	{
		$prev = $this->prev_post;
		if ($prev)
		{
			return $prev->url;
		}
	}

	function get_next_post()
	{
		return $this->_CI->fuel->blog->next_post($this);
	}

	function get_next_post_url()
	{
		$next = $this->next_post;
		if ($next)
		{
			return $next->url;
		}
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
