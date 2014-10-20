<h1>Settings</h1>
<p>The <dfn>blog.php</dfn> config file determines whether your blog settings are controlled by the admin interface or through the configuration settings.</p>

<h2>Setting Options</h2>
<p>Below are the fields you can control in the CMS:</p>
<ul>
	<li><strong>Title</strong> - The display name of the blog</li>
	<li><strong>Description</strong> - The descirption of the blog</li>
	<li><strong>URL</strong> - The site uri to associate with the blog</li>
	<li><strong>Theme location</strong> - The view location of the theme. Defaults to <dfn>theme/default</dfn></li>
	<li><strong>Theme layout</strong> - The layout file path to use (e.g. the _layouts/blog.php file)</li>
	<li><strong>Theme module</strong> - The module to load the theme from. The default is the <dfn>blog</dfn> module</li>
	<li><strong>Use cache</strong> - Determines whether to use the cache or not</li>
	<li><strong>Allow comments</strong> - Allow people to comment on your posts</li>
	<li><strong>Cache time to live</strong> - How long the cached file should exist</li>
	<li><strong>Use captchas</strong> - Use captchas for comment submission. <kbd>You must make the fuel/modules/blog/assets/captchas/ folder have writable permissions</kbd>.</li>
	<li><strong>Monitor comments</strong> - Monitor comments before publishing</li>
	<li><strong>Save spam</strong> - Save those comments flagged by spam</li>
	<li><strong>Akismet Key</strong> - The <a href="http://akismet.com/personal/" target="_blank">Akismet</a> antispam key</li>
	<li><strong>Comments time limit</strong> - The time limit in which you are no longer able to submit comments</li>
	<li><strong>Comment submission time limit</strong> - The time limit between a user can make comment posts</li>
	<li><strong>Asset upload path</strong> - The asset folder path to upload images</li>
	<li><strong>Per page</strong> - The number of post excerpts to show on a page</li>
	<li><strong>Page title separator</strong> - The separater to use between parts of the page title. The default is &laquo;</li>
	<li><strong>Multiple authors</strong> - Determines whether multiple authors can be used with posts</li>
	<li><strong>Social media</strong> - Determins which social media sites a blog author can associate with their profile</li>
</ul>
