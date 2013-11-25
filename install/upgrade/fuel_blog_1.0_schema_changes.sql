ALTER TABLE `fuel_blog_posts` ADD `main_image` VARCHAR(100)  NOT NULL  DEFAULT ''  AFTER `author_id`;
ALTER TABLE `fuel_blog_posts` ADD `thumbnail_image` VARCHAR(100)  NOT NULL  DEFAULT ''  AFTER `main_image`;
ALTER TABLE `fuel_blog_categories` ADD `precedence` INT(11)  UNSIGNED  DEFAULT 0  AFTER `permalink`;
UPDATE `fuel_blog_settings` SET `value` = '0' WHERE `name` = 'use_captchas';
ALTER TABLE `fuel_blog_categories` CHANGE `permalink` `slug` VARCHAR(255)  NOT NULL  DEFAULT ''  COMMENT 'If left blank, the slug will automatically be created for you.';
ALTER TABLE `fuel_blog_posts` CHANGE `permalink` `slug` VARCHAR(255)  NOT NULL  DEFAULT ''  COMMENT 'This is the last part of the url string. If left blank, the slug will automatically be created for you.';
ALTER TABLE `fuel_blog_posts` ADD `list_image` VARCHAR(100)  NOT NULL  DEFAULT ''  AFTER `main_image`;
ALTER TABLE `fuel_blog_posts` ADD `post_date` DATETIME  NOT NULL  AFTER `allow_comments`;
INSERT INTO `fuel_blog_settings` (`name`, `value`) VALUES ('page_title_separator', '&laquo;');
ALTER TABLE `fuel_blog_comments` CHANGE `author_ip` `author_ip` VARCHAR(50)  NOT NULL  DEFAULT '';
ALTER TABLE `fuel_blog_users` ADD `linkedin` VARCHAR(255)  NOT NULL  DEFAULT ''  AFTER `facebook`;
ALTER TABLE `fuel_blog_users` ADD `google` VARCHAR(255)  NOT NULL  DEFAULT ''  AFTER `linkedin`;

ALTER TABLE `fuel_blog_users` CHANGE `fuel_user_id` `fuel_user_id` INT(10)  UNSIGNED  NOT NULL;
ALTER TABLE `fuel_blog_users` DROP PRIMARY KEY;
ALTER TABLE `fuel_blog_users` ADD `id` INT  UNSIGNED  NOT NULL  AUTO_INCREMENT  PRIMARY KEY FIRST;
UPDATE `fuel_blog_users` SET `id` = `fuel_user_id`;



# Migrate Fuel Blog Categories to Fuel Relationships

INSERT INTO `fuel_relationships` (`candidate_table`, `candidate_key`, `foreign_table`, `foreign_key`) 
(SELECT 'fuel_blog_posts', `post_id`, 'fuel_blog_categories', `category_id` FROM `fuel_blog_posts_to_categories`);

DROP TABLE `fuel_blog_posts_to_categories`;


# Migrate Fuel Blog Settings to Fuel Settings

INSERT INTO `fuel_settings` (`module`, `key`, `value`) 
(SELECT 'blog', `name`, `value` FROM `fuel_blog_settings`);

DROP TABLE `fuel_blog_settings`;
