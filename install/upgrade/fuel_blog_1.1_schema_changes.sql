ALTER TABLE `fuel_blog_posts` CHANGE `language` `language` VARCHAR(30)  CHARACTER SET utf8  COLLATE utf8_unicode_ci  NOT NULL  DEFAULT 'english';
ALTER TABLE `fuel_blog_links` ADD `language` VARCHAR(30)  NOT NULL  DEFAULT 'english'  AFTER `precedence`;
ALTER TABLE `fuel_blog_categories` ADD `language` VARCHAR(30)  NOT NULL  DEFAULT 'english'  AFTER `precedence`;
ALTER TABLE `fuel_blog_posts` ADD `page_title` VARCHAR(255)  NOT NULL  DEFAULT ''  AFTER `post_date`;
ALTER TABLE `fuel_blog_posts` ADD `meta_description` VARCHAR(255)  NOT NULL  DEFAULT ''  AFTER `page_title`;
ALTER TABLE `fuel_blog_posts` ADD `meta_keywords` VARCHAR(255)  NOT NULL  DEFAULT ''  AFTER `meta_description`;
ALTER TABLE `fuel_blog_posts` ADD `canonical` VARCHAR(255)  NOT NULL  DEFAULT '' AFTER `meta_keywords`;
ALTER TABLE `fuel_blog_posts` ADD `og_title` VARCHAR(255)  NOT NULL  DEFAULT ''  AFTER `canonical`;
ALTER TABLE `fuel_blog_posts` ADD `og_description` VARCHAR(255)  NOT NULL  DEFAULT ''  AFTER `og_title`;
ALTER TABLE `fuel_blog_posts` ADD `og_image` VARCHAR(255)  NOT NULL  DEFAULT ''  AFTER `og_description`;
ALTER TABLE `fuel_blog_posts` ADD `category_id` INT(10) UNSIGNED NOT NULL  AFTER `og_image`;

ALTER TABLE `fuel_blog_users` CHANGE `twitter` `social_media_links` TEXT  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL;
ALTER TABLE `fuel_blog_posts` CHANGE `post_date` `publish_date` DATETIME NOT NULL;

ALTER TABLE `fuel_blog_categories` DROP INDEX `permalink`;
ALTER TABLE `fuel_blog_categories` DROP INDEX `name`;
ALTER TABLE `fuel_blog_categories` ADD UNIQUE INDEX (`name`, `language`);
ALTER TABLE `fuel_blog_categories` ADD UNIQUE INDEX (`slug`, `language`);

ALTER TABLE `fuel_blog_users` ADD `about_excerpt` TEXT  NOT NULL  AFTER `about`;


# Uncomment these if you don't want those fields in the Admin anymore since they've been rolled into the social_media_links field
#ALTER TABLE `fuel_blog_users` DROP `facebook`;
#ALTER TABLE `fuel_blog_users` DROP `linkedin`;
#ALTER TABLE `fuel_blog_users` DROP `google`;



