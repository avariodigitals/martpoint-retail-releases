-- Restaurant item routing: only made-to-order items should create KDS work.
-- Existing catalogue items default to counter so enabling the feature does not
-- unexpectedly send every historical sale to the kitchen.
--
-- This migration is executed by the fleet updater as individual statements on
-- one persistent DB connection. Keep the PREPARE / EXECUTE / DEALLOCATE block
-- together; do not run only the EXECUTE statement in a SQL-console preview.
-- Fresh installs already have this field in db.txt; DO 0 makes this migration
-- safe there as well as on MySQL 5.7 / MariaDB (which lack ADD COLUMN IF NOT
-- EXISTS).
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE()
     AND table_name = 'db_items'
     AND column_name = 'fulfilment_route') = 0,
  'ALTER TABLE `db_items` ADD COLUMN `fulfilment_route` VARCHAR(20) NOT NULL DEFAULT ''counter'' COMMENT ''counter | kitchen | bar''',
  'DO 0'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
