-- ============================================================================
-- MartPoint 4.0.9.116 — per-job invoice style (combined or detailed)
--
-- Adds ONE nullable column. Nothing else changes, and every existing job keeps
-- behaving exactly as it does today.
--
-- WHY
--
-- A print job carries catalogue lines — each with its own description, quantity
-- and price — and those lines already map 1:1 onto db_quotationitems. Some jobs
-- want that detail itemised on the invoice ("A5 flyers 500 @ 40", "A5 flyers
-- 500 @ 52"); others want one line for the whole job ("Business Cards ×500 =
-- ₦45,000").
--
-- That is a presentation choice, not a data one, so the column records the
-- preference and nothing is restructured. The underlying lines are untouched
-- either way, which means switching a job between the two can never lose detail
-- — the invoice re-reads the same rows and only changes how they are grouped.
--
-- DEFAULT: 'combined' (one line per job).
--
-- One line per job is the simpler, more common invoice, so a job that has never
-- expressed a preference gets it. A store that wants itemised invoices sets it
-- per job and the choice sticks.

-- 'combined' | 'detailed'. Nullable so an existing row is not rewritten, and
-- read through a coalesce so NULL behaves as 'combined'.
SET @has_col = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'db_print_jobs'
    AND COLUMN_NAME  = 'invoice_style'
);
SET @sql = IF(@has_col = 0,
  'ALTER TABLE `db_print_jobs` ADD COLUMN `invoice_style` VARCHAR(16) NULL DEFAULT NULL COMMENT ''combined|detailed — how job lines are grouped on the invoice''',
  'DO 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- Normalise anything unrecognised (including NULL) to the shipped default, so
-- the invoice reader never has to guess. Idempotent: re-running changes nothing.
SET @has_col2 = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'db_print_jobs'
    AND COLUMN_NAME  = 'invoice_style'
);
SET @sql2 = IF(@has_col2 = 1,
  'UPDATE `db_print_jobs` SET `invoice_style` = ''combined''
     WHERE `invoice_style` IS NULL
        OR `invoice_style` NOT IN (''combined'', ''detailed'')',
  'DO 0');
PREPARE st2 FROM @sql2; EXECUTE st2; DEALLOCATE PREPARE st2;
