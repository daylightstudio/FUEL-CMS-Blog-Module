DROP TABLE IF EXISTS `fuel_blog_users`;
DROP TABLE IF EXISTS `fuel_blog_posts`;
DROP TABLE IF EXISTS `fuel_blog_links`;
DROP TABLE IF EXISTS `fuel_blog_comments`;
DROP TABLE IF EXISTS `fuel_blog_categories`;
DELETE FROM `fuel_permissions` WHERE `name` LIKE 'blog/%';