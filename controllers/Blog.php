<?php
require_once(MODULES_PATH.'/blog/libraries/Blog_base_controller.php');

class Blog extends Blog_base_controller {
	
	function __construct()
	{
		parent::__construct();
		
	}
	
	function _remap()
	{
		$year = ($this->fuel->blog->uri_segment(2) != 'index') ? (int) $this->fuel->blog->uri_segment(2) : NULL;
		$month = (int) $this->fuel->blog->uri_segment(3);
		$day = (int) $this->fuel->blog->uri_segment(4);
		$slug = $this->fuel->blog->uri_segment(5);

		$view_by = 'page';

		// if the first segment is id then treat the second segment as the id
		if ($this->fuel->blog->uri_segment(2) === 'id' && $this->fuel->blog->uri_segment(3))
		{
			$view_by = 'slug';
			$slug = (int) $this->fuel->blog->uri_segment(3);
			$post = $this->fuel->blog->get_post($slug);

			if (isset($post->id))
			{
				redirect($post->url);
			}
		}
		// if the first segment is comment_reply
		else if ($this->fuel->blog->uri_segment(2) === 'comment_reply' AND $this->fuel->blog->uri_segment(3))
		{
			$comment_id = (int) $this->uri->rsegment(3);
			$this->comment_reply($comment_id);
			return;
		}

		// check if the slug segment is there and then view by slug
		else if (!empty($slug))
		{
			$view_by = 'slug';
		}

		// we empty out year variable if it is page because we won't be querying on year
		else if (empty($slug))
		{
			$view_by = 'date';
		}

		// set this to false so that we can use segments for the limit
		$cache_id = fuel_cache_id();
		$cache = $this->fuel->blog->get_cache($cache_id);
		
		if (!empty($cache))
		{
			$output =& $cache;
		}
		else
		{
			$vars = $this->_common_vars();

			if ($view_by == 'slug')
			{
				return $this->post($slug, $year, $month, $day);
			}
			else if ($view_by == 'date')
			{
				$page_title_arr = array();
				$posts_date = mktime(0, 0, 0, $month, (empty($day)) ? 1 : $day, $year);
				if (!empty($day)) $page_title_arr[] = $day;
				if (!empty($month)) $page_title_arr[] = date('M', $posts_date);
				if (!empty($year)) $page_title_arr[] = $year;
				
				$limit = $this->fuel->blog->config('per_page');
				$offset = (((int)$this->input->get('page') - 1) * $limit);
				$offset = ($offset < 0 ? 0 : $offset);
				
				if (!empty($offset))
				{
					$page_title_arr[] = lang('blog_page_num_title', $offset, $offset + $limit);
				}

				// run before_posts_by_date hook
				$hook_params = array('year' => $year, 'month' => $month, 'day' => $day, 'slug' => $slug, 'limit' => $limit);
				$this->fuel->blog->run_hook('before_posts_by_date', $hook_params);
				$vars = array_merge($vars, $hook_params);
				$vars['page_title'] = $page_title_arr;
				$vars['posts'] = $this->fuel->blog->get_posts_by_date($year, (int) $month, $day, $slug, $limit, $offset);
				$vars['year'] = (!empty($year)) ? $year : NULL;
				$vars['month'] = (!empty($month)) ? $month : NULL;
				$vars['day'] = (!empty($day)) ? $day : NULL;
				$vars['offset'] = $offset;
				$vars['limit'] = $limit;

				// run hook again to get the proper count
				$hook_params['type'] = 'count';
				$this->fuel->blog->run_hook('before_posts_by_date', $hook_params);
				$vars['post_count'] = $this->fuel->blog->get_posts_by_date_count($year, (int) $month, $day, $slug);
				
				// create pagination
				$url_segs = array();
				if (!empty($year)) $url_segs[] = $year;
				if (!empty($month)) $url_segs[] = sprintf("%02d", $month);
				if (!empty($day)) $url_segs[] = sprintf("%02d", $day);
				$base_url = implode('/', $url_segs);
				$vars['pagination'] = $this->fuel->blog->pagination($vars['post_count'], $base_url);
			}

			// show the index page if the page doesn't have any uri_segment(3)'
			$view = ($this->fuel->blog->uri_segment(2) == 'index' OR ($this->fuel->blog->uri_segment(2) == 'page' AND !$this->fuel->blog->uri_segment(3))) ? 'index' : 'posts';
			$output = $this->_render($view, $vars, TRUE);
			$this->fuel->blog->save_cache($cache_id, $output);
		}
		
		$this->output->set_output($output);
	}
	
	function post($slug = null, $year = null, $month = null, $day = null)
	{
		if (empty($slug))
		{
			redirect_404();
		}
		
		$this->load->library('session');
		$blog_config = $this->fuel->blog->config();

		// run before_posts_by_date hook
		$hook_params = array('slug' => $slug);
		$this->fuel->blog->run_hook('before_post', $hook_params);
		
		$post = $this->fuel->blog->get_post_by_date_slug($slug, $year, $month, $day);

		if (isset($post->id))
		{
			$vars = $this->_common_vars();
			$vars['post'] = $post;
			$vars['user'] = $this->fuel->blog->logged_in_user();
			$vars['page_title'] = $post->page_title;
			if ($post->has_meta_description()) $vars['meta_description'] = $post->meta_description;
			if ($post->has_meta_keywords()) $vars['meta_keywords'] = $post->meta_keywords;
			$vars['next'] = $this->fuel->blog->get_next_post($post);
			$vars['prev'] = $this->fuel->blog->get_prev_post($post);
			$vars['slug'] = $slug;
			$vars['is_home'] = $this->fuel->blog->is_home();
			
			$antispam = md5(random_string('unique'));
			
			$field_values = array();
			
			// post comment
			if (!empty($_POST))
			{
				$field_values = $_POST;
				
				// the id of "content" is a likely ID on the front end, so we use comment_content and need to remap
				$field_values['content'] = $field_values['new_comment'];
				unset($field_values['antispam']);
				
				if (!empty($_POST['new_comment']))
				{
					$vars['processed'] = $this->_process_comment($post);
				}
				else
				{
					add_error(lang('blog_error_blank_comment'));
				}

			}
			
			$cache_id = fuel_cache_id();
			if (!empty($cache) AND empty($_POST))
			{
				$output =& $cache;
			}
			else
			{
				$vars['thanks'] = ($this->session->flashdata('thanks')) ? blog_block('comment_thanks', $vars, TRUE) : '';
				$vars['comment_form'] = $this->fuel->blog->comment_form($post, NULL, $field_values, $blog_config['comment_form']);
				
				$output = $this->_render('post', $vars, TRUE);
				
				// save cache only if we are not posting data
				if (!empty($_POST)) 
				{
					$this->fuel->blog->save_cache($cache_id, $output);
				}
			}
			if (!empty($output))
			{
				$this->output->set_output($output);
				return;
			}
		}
		else
		{
			redirect_404();
		}
	}

	function comment_reply($comment_id)
	{
		$this->load->library('session');
		$this->load->module_model(BLOG_FOLDER, 'blog_comments_model');
		$this->load->helper('ajax');

		$comment = $this->blog_comments_model->find_by_key($comment_id);
		$output = '';

		// check if comment even exists first to replay to
		if (!isset($comment->id))
		{
			show_error(lang('blog_comment_does_not_exist'));
		}
				

		if (is_ajax())
		{
			if (!empty($_POST))
			{
		
				if (!empty($_POST['new_comment']))
				{
					$post = $comment->post;
					$comment_id = $this->_process_comment($post);
					
					// wrap it in a div and class so it can be styled
					if (has_errors())
					{
						// Set a 500 (bad) response code.
						set_status_header('500');
						$output = '<div class="comment_error">'.get_error().'</div>';
					}
					else
					{
						// set flash data so when the front end refreshes, it will be seen
						// Set a 200 (okay) response code.
						set_status_header('200');
						$output = $this->fuel->blog->block('comment_thanks');
						//$output = $comment_id;
					}
				}
				else
				{
					$output = '<div class="comment_error">'.lang('blog_error_blank_comment').'</div>';
				}

				echo $output;
				exit();
			}
			
			$form_defaults = array(
				'form_attrs' => 'method="post" action="'.site_url($this->uri->uri_string()).'#comment_form'.$comment_id.'"',
				'names_id_match' => FALSE,
				'name_prefix' => 'comment_reply'.$comment_id,
			);
			$output = $this->fuel->blog->comment_form($comment->post, $comment, $form_defaults);
			$this->output->set_output($output);
		}
	}
	
	function _process_comment($post)
	{
		if (!is_true_val($this->fuel->blog->config('allow_comments'))) return;
		$this->load->helper('ajax');
		
		$notified = FALSE;
		
		// check captcha
		if (!$this->_is_valid_captcha())
		{
			add_error(lang('blog_error_captcha_mismatch'));
		}
		
		// check that the site is submitted via the websit
		if (!$this->_is_site_submitted())
		{
			add_error(lang('blog_error_comment_site_submit'));
		}
		
		// check consecutive posts
		if (!$this->_is_not_consecutive_post())
		{
			add_error(lang('blog_error_consecutive_comments'));
		}
		
		$this->load->module_model(BLOG_FOLDER, 'blog_users_model');
		$user = $this->blog_users_model->find_one(array('fuel_users.email' => $this->input->post('author_email', TRUE)));
		
		// create comment
		$this->load->module_model(BLOG_FOLDER, 'blog_comments_model');
		$comment = $this->blog_comments_model->create();
		$comment->post_id = $post->id;
		
		$comment->author_id = (!empty($user->id)) ? $user->id : NULL;
		$comment->author_name = $this->input->post('author_name', TRUE);
		$comment->author_email = $this->input->post('author_email', TRUE);
		$comment->author_website = $this->input->post('author_website', TRUE);
		$comment->author_ip = $_SERVER['REMOTE_ADDR'];
		$comment->content = trim($this->input->post('new_comment', TRUE));
		$comment->parent_id = (int) $this->input->post('parent_id', TRUE);
		$comment->date_added = NULL; // will automatically be added

		//http://googleblog.blogspot.com/2005/01/preventing-comment-spam.html
		//http://en.wikipedia.org/wiki/Spam_in_blogs

		// check double posts by IP address
		if ($comment->is_duplicate())
		{
			add_error(lang('blog_error_comment_already_submitted'));
		}
		
		// if no errors from above then proceed to submit
		if (!has_errors())
		{

			// check if it's spam... 
			// not necessary to run this here because of subsequent is_spam and is_savable calls that will run it if it hasn't run yet however makes the code a little clearer
			$comment->check_is_spam();

			// process links and add no follow attribute
			$comment = $this->_filter_comment($comment);

			// set published status to yes automaticall if the comment is by the author (and isn't considered SPAM... just to be safe)
			if ($comment->is_by_post_author() AND !is_true_val($comment->is_spam))
			{
				$comment->published = 'yes';
			}
			// set published status to no if the commenter is not the author and either the comment is marked as spam or monitoring comments is on
			elseif (!$comment->is_by_post_author() AND (is_true_val($comment->is_spam) OR $this->fuel->blog->config('monitor_comments')))
			{
				$comment->published = 'no';
			}
			
			// save comment if saveable and redirect
			if (!is_true_val($comment->is_spam) OR (is_true_val($comment->is_spam) AND $this->fuel->blog->config('save_spam')))
			{
				if ($comment->save())
				{
					// if the blog setting is on, then attempt to notify the comment author through email
					if($this->fuel->blog->config('email_notify_comment_reply')) {
						$notified = $this->_notify($comment, $post);
					}

					$this->load->library('session');
					$vars['post'] = $post;
					$vars['comment'] = $comment;
					$this->session->set_userdata('last_comment_ip', $_SERVER['REMOTE_ADDR']);
					$this->session->set_userdata('last_comment_time', time());

					if (!is_ajax())
					{
						$this->session->set_flashdata('thanks', TRUE);
						redirect($post->url);
					}
				}
				else
				{
					add_errors($comment->errors());
				}
			}
			else
			{
				add_error(lang('blog_comment_is_spam'));
			}
		}
		return $notified;
	}
	
	// check captcha validity
	function _is_valid_captcha()
	{
		$valid = TRUE;
		
		// check captcha
		if (is_true_val($this->fuel->blog->config('use_captchas')))
		{
			if (!$this->input->post('captcha'))
			{
				$valid = FALSE;
			}
			else if (!is_string($this->input->post('captcha')))
			{
				$valid = FALSE;
			}
			else
			{
				
				$post_captcha_md5 = $this->_get_encryption($this->input->post('captcha'));
				$session_captcha_md5 = $this->session->userdata('comment_captcha');
				if ($post_captcha_md5 != $session_captcha_md5)
				{
					$valid = FALSE;
				}
			}
		}
		return $valid;
	}
	
	// check to make sure the site issued a session variable to check against
	function _is_site_submitted()
	{
		return ($this->session->userdata('antispam') AND $this->input->post('antispam') == $this->session->userdata('antispam'));
	}
	
	// disallow multiple successive submissions 
	function _is_not_consecutive_post()
	{
		$valid = TRUE;
		
		$time_exp_secs = $this->fuel->blog->config('multiple_comment_submission_time_limit');
		$last_comment_time = ($this->session->userdata('last_comment_time')) ? $this->session->userdata('last_comment_time') : 0;
		$last_comment_ip = ($this->session->userdata('last_comment_ip')) ? $this->session->userdata('last_comment_ip') : 0;
		if ($_SERVER['REMOTE_ADDR'] == $last_comment_ip AND !empty($time_exp_secs))
		{
			if (time() - $last_comment_time < $time_exp_secs)
			{
				$valid = FALSE;
			}
		}
		return $valid;
	}

	// process through akisment
	function _process_akismet($comment)
	{
		if ($this->fuel->blog->config('akismet_api_key'))
		{
			$this->load->module_library(BLOG_FOLDER, 'akismet');

			$akisment_comment = array(
				'author'	=> $comment->author_name,
				'email'		=> $comment->author_email,
				'body'		=> $comment->content
			);

			$config = array(
				'blog_url' => $this->fuel->blog->url(),
				'api_key' => $this->fuel->blog->config('akismet_api_key'),
				'comment' => $akisment_comment
			);

			$this->akismet->init($config);

			if ( $this->akismet->errors_exist() )
			{				
				if ( $this->akismet->is_error('AKISMET_INVALID_KEY') )
				{
					log_message('error', 'AKISMET :: Theres a problem with the api key');
				}
				elseif ( $this->akismet->is_error('AKISMET_RESPONSE_FAILED') )
				{
					log_message('error', 'AKISMET :: Looks like the servers not responding');
				}
				elseif ( $this->akismet->is_error('AKISMET_SERVER_NOT_FOUND') )
				{
					log_message('error', 'AKISMET :: Wheres the server gone?');
				}
			}
			else
			{
				$comment->is_spam = ($this->akismet->is_spam()) ? 'yes' : 'no';
			}
		}
		
		return $comment;
	}
	
	// strip out 
	function _filter_comment($comment)
	{
		$this->load->helper('security');
		$comment_attrs = array('content', 'author_name', 'author_email', 'author_website');
		foreach($comment_attrs as $filter)
		{
			$text = $comment->$filter;
			
			// first remove any nofollow attributes to clean up... not perfect but good enough
			$text = preg_replace('/<a(.+)rel=["\'](.+)["\'](.+)>/Umi', '<a$1rel="nofollow"$3>', $text);
//			$text = str_replace('<a ', '<a rel="nofollow"', $text);
			
			$text = strip_image_tags($text);
			
			$comment->$filter = $text;
		}
		return $comment;
	}
	
	function _notify($comment, $post)
	{
		// send email to post author
		if (!empty($post->author))
		{
			$config['wordwrap'] = TRUE;
			$this->load->library('email', $config);

			$this->email->from($this->fuel->config('from_email'), $this->fuel->config('site_name'));
			$this->email->to($post->author->email); 
			$this->email->subject(lang('blog_comment_monitor_subject', $this->fuel->blog->config('title')));

			$msg = lang('blog_comment_monitor_msg');
			$msg .= "\n".fuel_url('blog/comments/edit/'.$comment->id)."\n\n";

			$msg .= (is_true_val($comment->is_spam)) ? lang('blog_email_flagged_as_spam')."\n" : '';
			$msg .= lang('blog_email_published').": ".$comment->published."\n";
			$msg .= lang('blog_email_author_name').": ".$comment->author_name."\n";
			$msg .= lang('blog_email_author_email').": ".$comment->author_email."\n";
			$msg .= lang('blog_email_author_website').": ".$comment->author_website."\n";
			$msg .= lang('blog_email_author_ip').": ".gethostbyaddr($comment->author_ip)." (".$comment->author_ip.")\n";
			$msg .= lang('blog_email_content').": ".$comment->content."\n";

			$this->email->message($msg);

			return $this->email->send();
		}
		else
		{
			return FALSE;
		}
	}
	
	function _render_captcha()
	{
		$this->load->library('captcha');
		$blog_config = $this->config->item('blog');
		$assets_folders = $this->config->item('assets_folders');
		$blog_folder = MODULES_PATH.BLOG_FOLDER.'/';
		$captcha_path = $blog_folder.'assets/captchas/';
		$word = strtoupper(random_string('alnum', 5));
		
		$captcha_options = array(
						'word'		 => $word,
						'img_path'	 => $captcha_path, // system path to the image
						'img_url'	 => captcha_path('', BLOG_FOLDER), // web path to the image
						'font_path'	 => $blog_folder.'fonts/',
					);
		$captcha_options = array_merge($captcha_options, $blog_config['captcha']);
		if (!empty($_POST['captcha']) AND $this->session->userdata('comment_captcha') == $this->input->post('captcha'))
		{
			$captcha_options['word'] = $this->input->post('captcha');
		}
		$captcha = $this->captcha->get_captcha_image($captcha_options);
		$captcha_md5 = $this->_get_encryption($captcha['word']);
		$this->session->set_userdata('comment_captcha', $captcha_md5);
		
		return $captcha;
	}
	
	function _get_encryption($word)
	{
		$captcha_md5 = md5(strtoupper($word).$this->config->item('encryption_key'));
		return $captcha_md5;
	}
}