-- Oil & gas stage 5: a service job belongs to a site, the same as a tank or a
-- cylinder does.
--
-- Multi-location fuel companies fit pumps, service generators, run tank
-- inspections and move spare parts, and the work happens AT a station, a plant
-- or a depot. Head office needs to see the whole company's jobs; an attendant
-- needs to see only the site they are assigned to. Neither is possible while a
-- job record carries only the company.
--
-- This is the custody column, not a second job engine. db_service_jobs and the
-- Operations screens stay the single place a job is created, scheduled and
-- closed; the Oil & Gas workspace only reads them through a site.
--
-- 0 means "no site chosen", which is exactly what every install has today, so
-- existing jobs and every other trade's screens are untouched by this file.
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_service_jobs' AND column_name = 'warehouse_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_service_jobs` ADD COLUMN `warehouse_id` INT NOT NULL DEFAULT 0 COMMENT ''The location the work is at; 0 = not tied to a site''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_service_jobs' AND index_name = 'idx_store_warehouse');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_service_jobs` ADD INDEX `idx_store_warehouse` (`store_id`, `warehouse_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.9.251-oil-gas-service-job-site', NOW());
