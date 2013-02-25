<?php 
/*
|--------------------------------------------------------------------------
| FUEL NAVIGATION: An array of navigation items for the left menu
|--------------------------------------------------------------------------
*/
$config['nav']['blog'] = array(
	'blog/posts' => lang('module_blog_posts'), 
	'blog/categories' => lang('module_blog_categories'),  
	'blog/comments' => lang('module_blog_comments'), 
	'blog/links' => lang('module_blog_links'), 
	'blog/users' => lang('module_blog_authors'), 
);

/*
|--------------------------------------------------------------------------
| Configurable in settings if blog_use_db_table_settings is set
|--------------------------------------------------------------------------
*/

// deterines whether to use this configuration below or the database for controlling the blogs behavior
$config['blog_use_db_table_settings'] = TRUE;

// set as defaults 
$config['blog'] = array();
$config['blog']['title'] = '';
$config['blog']['description'] = '';
$config['blog']['uri'] = 'blog';
$config['blog']['theme_path'] = 'themes/default';
$config['blog']['theme_layout'] = 'blog';
$config['blog']['theme_module'] = 'blog';
$config['blog']['use_cache'] = FALSE;
$config['blog']['allow_comments'] = FALSE;
$config['blog']['monitor_comments'] = TRUE;
$config['blog']['use_captchas'] = FALSE;
$config['blog']['save_spam'] = FALSE;
$config['blog']['akismet_api_key'] = '';
$config['blog']['multiple_comment_submission_time_limit'] = '';
$config['blog']['comments_time_limit'] = '';
$config['blog']['cache_ttl'] = 3600;
$config['blog']['asset_upload_path'] = 'images/blog/';
$config['blog']['per_page'] = 10;
$config['blog']['page_title_separator'] = '&laquo;';

// used for Settings area
$config['blog']['settings']['title'] = array();
$config['blog']['settings']['description'] = array('size' => '80');
$config['blog']['settings']['uri'] = array('value' => 'blog');
$config['blog']['settings']['theme_path'] = array('value' => 'themes/default');
$config['blog']['settings']['theme_layout'] = array('value' => 'blog', 'size' => '20');
$config['blog']['settings']['theme_module'] = array('value' => 'blog', 'size' => '20');
$config['blog']['settings']['use_cache'] = array('type' => 'checkbox', 'value' => '1');
$config['blog']['settings']['allow_comments'] = array('type' => 'checkbox', 'value' => '1');
$config['blog']['settings']['monitor_comments'] = array('type' => 'checkbox', 'value' => '1');
$config['blog']['settings']['use_captchas'] = array('type' => 'checkbox', 'value' => '1');
$config['blog']['settings']['save_spam'] = array('type' => 'checkbox', 'value' => '1');
$config['blog']['settings']['akismet_api_key'] = array('value' => '', 'size' => '80');
$config['blog']['settings']['multiple_comment_submission_time_limit'] = array('size' => '5', 'after_html' => lang('form_label_multiple_comment_submission_time_limit_after_html'));
$config['blog']['settings']['comments_time_limit'] = array('size' => '5', 'after_html' => lang('form_label_comments_time_limit_after_html'));
$config['blog']['settings']['cache_ttl'] = array('value' => 3600, 'size' => 5);
$config['blog']['settings']['asset_upload_path'] = array('default' => 'images/blog/');
$config['blog']['settings']['per_page'] = array('value' => 10, 'size' => 3);
$config['blog']['settings']['page_title_separator'] = array('value' => '&laquo;', 'size' => 10);


// the cache folder to hold blog cache files
$config['blog_cache_group'] = 'blog';

/*
|--------------------------------------------------------------------------
| Programmer specific config (not exposed in settings)
|--------------------------------------------------------------------------
*/
// content formatting options
$config['blog']['formatting'] = array(
	'auto_typography' => 'Automatic',
	'Markdown' => 'Markdown',
	'' => 'None'
	);

// captcha options
$config['blog']['captcha'] = array(
				'img_width'	 => 120,
				'img_height' => 26,
				'expiration' => 600, // 10 minutes
				'bg_color' => '#4b4b4b',
				'char_color' => '#ffffff,#cccccc,#ffffff,#999999,#ffffff,#cccccc',
				'line_color' => '#ff9900,#414141,#ea631d,#aaaaaa,#f0a049,#ff9900'
			);

// comment form 
$config['blog']['comment_form'] = array();
$config['blog']['comment_form']['fields'] = array();

// pagination
$config['blog']['pagination'] = array(
		'prev_link' => 'Prev',
		'next_link' => 'Next',
		'first_link' => '',
		'last_link' => '',
	);


// tables for blog
$config['tables']['blog_posts'] = 'fuel_blog_posts';
$config['tables']['blog_categories'] = 'fuel_blog_categories';
$config['tables']['blog_users'] = 'fuel_blog_users';
$config['tables']['blog_comments'] = 'fuel_blog_comments';
$config['tables']['blog_links'] = 'fuel_blog_links';
$config['tables']['blog_settings'] = 'fuel_blog_settings';
$config['tables']['blog_relationships'] = 'fuel_relationships';
