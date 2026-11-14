-- 4.0.9.167 The storefront menu becomes merchant-configured.
--
-- Until now the online store's menu rail was assembled in code: "All Products",
-- the first six categories in alphabetical order, then "Services" if the store
-- sold both — so a merchant could not say which items appeared or in what
-- order, and submenus existed only as a side effect of category parenting.
--
-- db_storefront_settings.nav_json holds the merchant's menu as a tree
-- (standard pages, categories, custom links; one level of submenus). Empty or
-- missing means "not configured" and the storefront keeps the automatic
-- behaviour, so this is opt-in per store and a no-op until a menu is saved.
-- Safe to run again.
SET @db := DATABASE();
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='nav_json')=0, 'ALTER TABLE db_storefront_settings ADD COLUMN nav_json MEDIUMTEXT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
