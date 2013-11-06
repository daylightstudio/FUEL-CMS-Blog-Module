<?php
require_once(MODULES_PATH.'/blog/libraries/Blog_base_controller.php');

class Feed extends Blog_base_controller {
	
	function __construct()
	{
		parent::__construct();
	}
	
	function index()
	{
		$this->rss();
	}
	
	function atom()
	{
		// set the header type
		$this->fuel->blog->feed_header();
		print($this->fuel->blog->feed_output('atom'));
	}

	function rss()
	{
		// set the header type
		$this->fuel->blog->feed_header();
		print($this->fuel->blog->feed_output('rss'));
	}

}