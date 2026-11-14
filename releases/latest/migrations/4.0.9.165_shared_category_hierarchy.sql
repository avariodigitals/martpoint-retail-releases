-- Additive, industry-neutral hierarchy; existing category IDs and item assignments stay intact.
SET @mp_cat_sql := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_category' AND COLUMN_NAME='parent_id')=0,'ALTER TABLE db_category ADD parent_id INT NULL DEFAULT NULL','SELECT 1');
PREPARE mp_cat_stmt FROM @mp_cat_sql; EXECUTE mp_cat_stmt; DEALLOCATE PREPARE mp_cat_stmt;
SET @mp_cat_sql := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_category' AND INDEX_NAME='category_parent')=0,'ALTER TABLE db_category ADD KEY category_parent(store_id,parent_id)','SELECT 1');
PREPARE mp_cat_stmt FROM @mp_cat_sql; EXECUTE mp_cat_stmt; DEALLOCATE PREPARE mp_cat_stmt;
