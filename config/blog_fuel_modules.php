<?php
$config['modules']['blog_posts'] = array(
	'module_name' => 'Posts',
	'module_uri' => 'blog/posts',
	'model_name' => 'blog_posts_model',
	'model_location' => 'blog',
	'display_field' => 'title',
	'preview_path' => 'blog/{year}/{month}/{day}/{slug}',
	'permission' => array('blog_posts', 'create' => 'blog_posts/create', 'edit' => 'blog_posts/edit', 'publish' => 'blog_posts/publish', 'delete' => 'blog_posts/delete'),
	'instructions' => lang('module_instructions_default', 'blog posts'),
	'archivable' => TRUE,
	'configuration' => array('blog' => 'blog'),
	'nav_selected' => 'blog/posts',
//	'language' => array('blog' => 'blog'),
	'default_col' => 'publish_date',
	'default_order' => 'desc',
	'sanitize_input' => array('template','php'),
	'filters' => array(
		'category_id' => array('label' => lang('form_label_category'), 'type' => 'select', 'model' => array(FUEL_FOLDER => 'fuel_categories_model'), 'first_option' => lang('label_select_one')),
		'author_id' => array('label' => lang('form_label_author'), 'type' => 'select', 'model' => array(BLOG_FOLDER => 'blog_users_model'), 'first_option' => lang('label_select_one'))

		),
	'advanced_search' => TRUE
);

// $config['modules']['blog_categories'] = array(
// 	'module_name' => 'Categories',
// 	'module_uri' => 'blog/categories',
// 	'model_name' => 'blog_categories_model',
// 	'model_location' => 'blog',
// 	// 'table_headers' => array(
// 	// 	'id', 
// 	// 	'name', 
// 	// 	'precedence', 
// 	// 	'published', 
// 	// ),
// 	'display_field' => 'name',
// 	'preview_path' => 'blog/categories/{slug}',
// 	'permission' => 'blog_categories',
// 	'instructions' => lang('module_instructions_default', 'blog categories'),
// 	'archivable' => TRUE,
// 	'configuration' => array('blog' => 'blog'),
// 	'nav_selected' => 'blog/categories',
// //	'language' => array('blog' => 'blog')
// 	'hidden' => TRUE,
	
// );

$config['modules']['blog_comments'] = array(
	'module_name' => 'Comments',
	'module_uri' => 'blog/comments',
	'model_name' => 'blog_comments_model',
	'model_location' => 'blog',
	'table_headers' => array(
		'id', 
		'post_title', 
		'comment', 
		'comment_author_name', 
		'is_spam', 
		'date_submitted',
		'published', 
	),
	'display_field' => 'author_name',
	'default_col' => 'date_submitted',
	'default_order' => 'desc',
	'preview_path' => 'blog/{year}/{month}/{day}/{slug}',
	'permission' => array('blog_comments', 'create' => 'blog_comments/create', 'edit' => 'blog_comments/edit', 'publish' => 'blog_comments/publish', 'delete' => 'blog_comments/delete'),
	'instructions' => lang('module_instructions_default', 'blog comments'),
	'archivable' => TRUE,
	'configuration' => array('blog' => 'blog'),
	'nav_selected' => 'blog/comments',
//	'language' => array('blog' => 'blog'),
);

$config['modules']['blog_links'] = array(
	'module_name' => 'Links',
	'module_uri' => 'blog/links',
	'model_name' => 'blog_links_model',
	'model_location' => 'blog',
	'display_field' => 'url',
	'default_col' => 'name',
	'preview_path' => '',
	'permission' => array('blog_links', 'create' => 'blog_links/create', 'edit' => 'blog_links/edit', 'publish' => 'blog_links/publish', 'delete' => 'blog_links/delete'),
	'instructions' => lang('module_instructions_default', 'blog links'),
	'archivable' => TRUE,
	'configuration' => array('blog' => 'blog'),
	'nav_selected' => 'blog/links',
//	'language' => array('blog' => 'blog')
);

$config['modules']['blog_users'] = array(
	'module_name' => 'Authors',
	'module_uri' => 'blog/users',
	'model_name' => 'blog_users_model',
	'model_location' => 'blog',
	'table_headers' => array(
		'id', 
		'name', 
		'display_name', 
		'active' 
	),
	
	'display_field' => 'display_name',
	'preview_path' => 'blog/authors/{fuel_user_id}',
	'permission' => array('blog_users', 'create' => 'blog_users/create', 'edit' => 'blog_users/edit', 'publish' => 'blog_users/publish', 'delete' => 'blog_users/delete'),
	'instructions' => lang('module_instructions_default', 'blog authors'),
	'archivable' => TRUE,
	'configuration' => array('blog' => 'blog'),
	'nav_selected' => 'blog/users',
//	'language' => array('blog' => 'blog')
);