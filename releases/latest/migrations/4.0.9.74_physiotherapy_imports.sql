-- ============================================================
-- MartPoint 4.0.9.74 — Physiotherapy Stage 7
-- Legacy import framework (Smart Hospital 4.0 → MartPoint) and
-- opening-position evidence fields. Guarded/idempotent.
-- ============================================================

-- ---------- Import batches ----------
CREATE TABLE IF NOT EXISTS `db_migration_batches` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `source_system` varchar(40) NOT NULL,
  `label` varchar(160) DEFAULT NULL,
  `mode` varchar(12) NOT NULL DEFAULT 'import',   -- import | dry_run
  `status` varchar(24) NOT NULL DEFAULT 'running', -- running|paused|completed|completed_with_exceptions|rolled_back|failed
  `rows_seen` int NOT NULL DEFAULT 0,
  `rows_imported` int NOT NULL DEFAULT 0,
  `rows_skipped` int NOT NULL DEFAULT 0,
  `rows_conflicts` int NOT NULL DEFAULT 0,
  `rows_failed` int NOT NULL DEFAULT 0,
  `checkpoint_json` text DEFAULT NULL,
  `started_by` int DEFAULT NULL,
  `started_by_name` varchar(80) DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `rolled_back_at` datetime DEFAULT NULL,
  `rolled_back_by` int DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_batch_store` (`store_id`,`source_system`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Import row ledger ----------
-- One row per source record seen. The dedupe index is across batches so a
-- re-run batch detects what an earlier batch already imported.
CREATE TABLE IF NOT EXISTS `db_migration_rows` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int unsigned NOT NULL,
  `store_id` int NOT NULL,
  `source_table` varchar(80) NOT NULL,
  `source_id` varchar(80) NOT NULL,
  `action` varchar(16) NOT NULL,           -- inserted|skipped|conflict|failed|rolled_back
  `targets_json` text DEFAULT NULL,        -- [{table:"db_patients", id:12}, …] written by this row
  `dupe_hint` varchar(160) DEFAULT NULL,   -- why a conflict was flagged (e.g. shared phone)
  `error` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `rolled_back_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_import_dedupe` (`store_id`,`source_table`,`source_id`,`batch_id`),
  KEY `idx_rows_seen` (`store_id`,`source_table`,`source_id`),
  KEY `idx_rows_batch` (`batch_id`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Opening-position evidence ----------
SET @c := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_opening_position_items' AND column_name='evidence_document_id');
SET @sql := IF(@c=0,
  'ALTER TABLE `db_opening_position_items` ADD COLUMN `evidence_document_id` int DEFAULT NULL',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- ---------- Legacy-debt service item ----------
-- Non-sellable service item so migrated-debt invoice lines join cleanly in
-- item-level sales reports (db_salesitems.item_id is NOT NULL).
INSERT INTO db_items (store_id, item_code, item_name, service_bit, not_for_sale,
                      category_id, unit_id, sales_price, price, purchase_price,
                      profit_margin, tax_id, status, system_ip, system_name,
                      created_by, created_date, created_time)
SELECT p.store_id, 'LEGACY-DEBT', 'Legacy opening debt (imported)', 1, 1,
       NULL, NULL, 0, 0, 0, 0, NULL, 1, 'system', 'migration', 'system',
       CURDATE(), CURTIME()
FROM (SELECT DISTINCT store_id FROM db_store_industry_settings WHERE industry_type='physiotherapy_rehabilitation'
      UNION SELECT DISTINCT store_id FROM db_store_business_profile WHERE industry_type='physiotherapy_rehabilitation') p
WHERE NOT EXISTS (
  SELECT 1 FROM db_items i WHERE i.store_id = p.store_id AND i.item_code = 'LEGACY-DEBT'
);
