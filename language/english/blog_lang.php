<?php
/*
|--------------------------------------------------------------------------
| Module Names
|--------------------------------------------------------------------------
*/
$lang['module_blog_posts'] = 'Posts';
$lang['module_blog_categories'] = 'Categories';
$lang['module_blog_tags'] = 'Tags';
$lang['module_blog_comments'] = 'Comments';
$lang['module_blog_links'] = 'Links';
$lang['module_blog_authors'] = 'Authors';
$lang['module_blog_settings'] = 'Settings';

/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
*/
$lang['blog_error_blank_comment'] = 'Please enter in a comment.';
$lang['blog_error_invalid_comment_email'] = 'Please enter in a valid email address.';
$lang['blog_error_comment_site_submit'] = 'Comments must be submitted through the form on the website.';
$lang['blog_error_comment_already_submitted'] = 'This comment has already been submitted.';
$lang['blog_error_consecutive_comments'] = 'Please wait to post consecutive comments.';
$lang['blog_error_delete_uncategorized'] = 'You cannot delete the Uncategorized category.';
$lang['blog_comment_does_not_exist'] = 'The request blog comment does not exist.';

/*
|--------------------------------------------------------------------------
| Page Titles
|--------------------------------------------------------------------------
*/
$lang['blog_archives_page_title'] = 'Archives';
$lang['blog_authors_list_page_title'] = 'Authors';
$lang['blog_author_posts_page_title'] = '%s Posts';
$lang['blog_categories_page_title'] = 'Categories';
$lang['blog_tags_page_title'] = 'Tags';
$lang['blog_search_page_title'] = '%s Search Results';

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/
$lang['blog_page_num_title'] = 'Posts %1s-%2s';
$lang['blog_prev_page'] = '&lt;&lt; Previous Page';
$lang['blog_next_page'] = 'Next Page &gt;&gt;';
$lang['blog_first_link'] = '';
$lang['blog_last_link'] = '';


$lang['blog_error_no_posts_to_comment'] = 'There are no posts to comment on.';
$lang['blog_post_is_not_published'] = 'The post associated with this comment is not published and so no notifications will be sent on comment replies.';
$lang['blog_comment_notify_option1'] = 'All';
$lang['blog_comment_notify_option2'] = 'Commentor';
$lang['blog_comment_notify_option3'] = 'None';



/***************************************************************************
IMPORTANT: SEVERAL FORM FIELD LABELS ALREADY EXIST IN THE fuel language file
***************************************************************************/

/*
|--------------------------------------------------------------------------
| Posts (several fields are in the main form_label_ common)
|--------------------------------------------------------------------------
*/
$lang['form_label_formatting'] = 'Formatting';
$lang['form_label_author'] = 'Author';
$lang['form_label_sticky'] = 'Sticky';
$lang['form_label_allow_comments'] = 'Allow Comments';
$lang['form_label_category'] = 'Category';
$lang['form_label_tags'] = 'Tags';
$lang['form_label_main_image'] = 'Main image';
$lang['form_label_list_image'] = 'List image';
$lang['form_label_thumbnail_image'] = 'Thumbnail image';
$lang['form_label_page_title'] = 'Page title';
$lang['form_label_meta_description'] = 'Meta description';
$lang['form_label_meta_keywords'] = 'Meta keywords';
$lang['form_label_publish_date'] = 'Publish date';
$lang['form_label_related_posts'] = 'Related posts';
$lang['form_label_blocks'] = 'Blocks';

$lang['form_category_comment'] = 'Categories must have context value of "blog" OR be empty in order to be used by the Blog module.';
$lang['form_tags_comment'] = 'Tags must belong to a category that has the context of "blog" or is empty in order to be used by the Blog module.';


/*
|--------------------------------------------------------------------------
| Comments 
|--------------------------------------------------------------------------
*/
$lang['blog_comment_monitor_subject']= "%s: A comment has been added.";
$lang['blog_comment_monitor_msg']= "A comment has been added to your blog post. To review the comment login to FUEL:";
$lang['blog_comment_reply_subject']= "%1s Blog Comment Reply";
$lang['blog_comment_reply_msg']= "%1s has replied to your comment on the article %2s.";

$lang['blog_captcha_text'] = "Enter the text you see in the image in the form field above. <br />If you cannot read the text, refresh the page.";

$lang['blog_comment_is_spam'] = "The comment posted cannot be submitted as is because it was flagged as spam.";
$lang['blog_error_captcha_mismatch'] = "The text you inputted for the image did not match.";

$lang['blog_email_flagged_as_spam'] = 'FLAGGED AS SPAM!!!';
$lang['blog_email_published'] = 'Published';
$lang['blog_email_author_name'] = 'Author Name';
$lang['blog_email_author_email'] = 'Author Email';
$lang['blog_email_author_website'] = 'Website';
$lang['blog_email_author_ip'] = 'Author IP';
$lang['blog_email_content'] = 'Content';
$lang['form_label_post_title'] = 'Post Title';
$lang['form_label_comment'] = 'Comment';
$lang['form_label_comment_author_name'] = 'Comment Author Name';
$lang['form_label_is_spam'] = 'Is Spam';
$lang['form_label_post_published'] = 'Post Published';
$lang['form_label_date_submitted'] = 'Date Submitted';
$lang['form_label_ip_host'] = 'IP/Host';
$lang['form_label_replies'] = 'Replies';
$lang['form_label_reply'] = 'Reply';
$lang['form_label_commentor'] = 'Commentor';
$lang['form_label_reply_notify'] = 'Notify';
$lang['form_label_author_ip'] = 'Author IP Address';


/*
|--------------------------------------------------------------------------
| Settings 
|--------------------------------------------------------------------------
*/
$lang['form_label_uri'] = 'URI';
$lang['form_label_theme_path'] = 'Theme Location';
$lang['form_label_theme_layout'] = 'Theme Layout';
$lang['form_label_theme_module'] = 'Theme module';
$lang['form_label_use_cache'] = 'Use Cache';
$lang['form_label_allow_comments'] = 'Allow Comments';
$lang['form_label_monitor_comments'] = 'Monitor Comments';
$lang['form_label_use_captchas'] = 'Use Captchas';
$lang['form_label_save_spam'] = 'Save Spam';
$lang['form_label_akismet_api_key'] = 'Akismet Key';
$lang['form_label_multiple_comment_submission_time_limit'] = 'Comment Submission Time Limit';
$lang['form_label_multiple_comment_submission_time_limit_after_html'] = ' (in seconds)';
$lang['form_label_comments_time_limit'] = 'Allow comments for how long';
$lang['form_label_comments_time_limit_after_html'] = ' after post date (in days)';
$lang['form_label_cache_ttl'] = 'Cache Time to Live';
$lang['form_label_asset_upload_path'] = 'Asset Upload Path';
$lang['form_label_per_page'] = 'Per Page';
$lang['form_label_page_title_separator'] = 'Page Title Separator';
$lang['form_label_email_notify_comment_reply'] = 'E-mail notifications on comment replies';


/*
|--------------------------------------------------------------------------
| Front-end
|--------------------------------------------------------------------------
*/
$lang['blog_blog'] = 'Block';
$lang['blog_archives'] = 'Archives';
$lang['blog_categories'] = 'Categories';
$lang['blog_category_posts'] = '%1s Posts';
$lang['blog_tags'] = 'Tags';
$lang['blog_tags_posts'] = '%1s Posts';
$lang['blog_publish_date_format'] = 'F d, Y';
$lang['blog_post_published'] = 'Published';
$lang['blog_post_published_by'] = 'by';
$lang['blog_post_read_more'] = 'Read More';
$lang['blog_search'] = '%1s Search Result(s) Returned for “%2s”';
$lang['blog_pagination_all'] = 'Back to All Posts';
$lang['blog_pagination_next'] = 'Next';
$lang['blog_pagination_prev'] = 'Previous';
$lang['blog_comment_heading'] = 'Comment';
$lang['blog_leave_comment_heading'] = 'Leave a Comment';
$lang['blog_comments_off'] = 'Comments have been turned off for this post.';
$lang['blog_comment_thanks'] = 'Thanks for the comment';
$lang['blog_comments_monitored'] = 'Comments for this posting are being monitored and will be published upon the author\'s approval.';
$lang['blog_share'] = 'Share this post';
$lang['blog_related_posts'] = 'Related Posts';
$lang['blog_links'] = 'Featured Links';
$lang['blog_about_author'] = 'About Author';
$lang['blog_post_date_format'] = 'F j, Y';
