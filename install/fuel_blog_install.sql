# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: localhost (MySQL 5.5.9)
# Database: fuel_widgicorp
# Generation Time: 2012-04-26 23:24:42 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table fuel_blog_categories
# ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `fuel_blog_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'If left blank, the slug will automatically be created for you.',
  `precedence` int(11) unsigned DEFAULT '0',
  `published` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  UNIQUE KEY `permalink` (`slug`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `fuel_blog_categories` (`id`, `name`, `slug`, `precedence`, `published`)
VALUES
  (1, 'Uncategorized', 'uncategorized', 0, 'yes');



# Dump of table fuel_blog_comments
# ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `fuel_blog_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned NOT NULL,
  `author_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `author_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `author_website` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `author_ip` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `is_spam` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `published` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `date_added` datetime NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table fuel_blog_links
# ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `fuel_blog_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `target` enum('blank','self','parent') DEFAULT 'blank',
  `description` varchar(100) DEFAULT NULL,
  `precedence` int(11) NOT NULL DEFAULT '0',
  `published` enum('yes','no') DEFAULT 'yes',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table fuel_blog_posts
# ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `fuel_blog_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `content_filtered` text COLLATE utf8_unicode_ci NOT NULL,
  `formatting` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `excerpt` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'A condensed version of the content',
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'This is the last part of the url string. If left blank, the slug will automatically be created for you.',
  `author_id` int(10) unsigned NOT NULL COMMENT 'If left blank, you will assumed be the author.',
  `main_image` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `list_image` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `thumbnail_image` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sticky` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `allow_comments` enum('yes','no') COLLATE utf8_unicode_ci DEFAULT 'no',
  `post_date` datetime NOT NULL,
  `date_added` datetime DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `published` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`),
  UNIQUE KEY `permalink` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table fuel_blog_users
# ------------------------------------------------------------

CREATE TABLE `fuel_blog_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fuel_user_id` int(10) unsigned NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `website` varchar(100) NOT NULL,
  `about` text NOT NULL,
  `avatar_image` varchar(255) NOT NULL DEFAULT '',
  `twitter` varchar(255) NOT NULL DEFAULT '',
  `facebook` varchar(255) NOT NULL DEFAULT '',
  `linkedin` varchar(255) NOT NULL DEFAULT '',
  `google` varchar(255) NOT NULL DEFAULT '',
  `date_added` datetime DEFAULT NULL,
  `active` enum('yes','no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
