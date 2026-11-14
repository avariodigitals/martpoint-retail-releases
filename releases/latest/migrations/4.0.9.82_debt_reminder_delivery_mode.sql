-- ============================================================
-- 4.0.9.82 — Debt reminder delivery mode (idempotent)
-- Separates "delivery mode" (how reminders go out: legacy vs
-- outbox) from "pause all reminders" (enabled). Preserves any
-- existing reminder configuration — a store already on the
-- outbox path (the previous behaviour, where enabled=1 meant the
-- outbox scheduler) is migrated to delivery_mode='outbox'; every
-- other store defaults to 'legacy' and keeps its current enabled
-- state so no reminder config is lost.
-- ============================================================

-- Add the column only if it is missing (idempotent on re-run).
SET @col := IF(
  (SELECT COUNT(*) FROM information_schema.columns
   WHERE table_schema = DATABASE()
     AND table_name = 'db_debt_reminder_config'
     AND column_name = 'delivery_mode') = 0,
  'ALTER TABLE `db_debt_reminder_config` ADD COLUMN `delivery_mode` varchar(10) NOT NULL DEFAULT ''legacy'' AFTER `enabled`',
  'SELECT 1'
);
PREPARE s FROM @col; EXECUTE s; DEALLOCATE PREPARE s;

-- Preserve prior behaviour: a store that had enabled=1 (which used to mean
-- "outbox scheduler") is switched to delivery_mode='outbox' so it keeps
-- sending through the outbox. Its enabled flag is left untouched (still the
-- pause-all switch). Stores that were not on the outbox default to 'legacy'.
UPDATE db_debt_reminder_config
   SET delivery_mode = 'outbox'
 WHERE (delivery_mode IS NULL OR delivery_mode = '')
   AND enabled = 1;

-- Any remaining unset rows fall back to the column default 'legacy'.
UPDATE db_debt_reminder_config
   SET delivery_mode = 'legacy'
 WHERE (delivery_mode IS NULL OR delivery_mode = '');
