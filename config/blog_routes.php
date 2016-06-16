<?php 
$blog_controllers = array('posts', 'comments', 'categories', 'links', 'users');

foreach($blog_controllers as $c)
{
	$route[FUEL_ROUTE.'blog/'.$c] = FUEL_FOLDER.'/module';
	$route[FUEL_ROUTE.'blog/'.$c.'/(.*)'] = FUEL_FOLDER.'/module/$1';
}

$route[FUEL_ROUTE.'blog/settings'] = BLOG_FOLDER.'/settings';
unset($blog_controllers);

// for multi-language sites
$blog_lang_controllers = array('authors', 'categories', 'feed', 'search', 'archives', 'tags');
foreach($blog_lang_controllers as $c)
{
	$route['(.{2})/blog/'.$c.'(/.+)?'] = 'blog/'.$c.'/$1';
}
$route['(.{2})/blog(/.+)?'] = 'blog/$1';
unset($blog_lang_controllers);